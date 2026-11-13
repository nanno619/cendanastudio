<?php

namespace Tests\Feature;

use App\Enums\Role as RoleName;
use App\Models\Company;
use App\Models\User;
use Database\Seeders\LookupSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class CompanyTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app->make(PermissionRegistrar::class)->forgetCachedPermissions();
        Role::findOrCreate(RoleName::SuperAdmin->value);
        Role::findOrCreate(RoleName::Admin->value);
        $this->superAdmin = User::factory()->create()->assignRole(RoleName::SuperAdmin);
        $this->seed(LookupSeeder::class);
        Storage::fake('public');
    }

    public function test_current_returns_an_unsaved_malaysian_company_before_details_are_saved(): void
    {
        $company = Company::current();

        $this->assertFalse($company->exists);
        $this->assertSame('MY', $company->country);
    }

    public function test_current_returns_the_saved_company(): void
    {
        $saved = Company::factory()->create();

        $this->assertTrue(Company::current()->is($saved));
    }

    public function test_company_is_identified_by_ulid_and_keeps_an_integer_id(): void
    {
        $company = Company::factory()->create();

        $this->assertIsInt($company->id);
        $this->assertTrue(Str::isUlid($company->ulid));
        $this->assertSame($company->ulid, $company->getRouteKey());
    }

    public function test_updating_company_details_is_audited_with_only_the_changes(): void
    {
        $company = Company::factory()->create(['name' => 'Old Name Sdn. Bhd.']);
        Activity::query()->delete();

        $company->update(['name' => 'New Name Sdn. Bhd.']);

        $activity = Activity::query()->sole();
        $this->assertSame('updated', $activity->event);
        $this->assertSame('Updated company details', $activity->description);
        $this->assertSame(['name' => 'Old Name Sdn. Bhd.'], $activity->attribute_changes['old']);
        $this->assertSame(['name' => 'New Name Sdn. Bhd.'], $activity->attribute_changes['attributes']);
    }

    public function test_guests_are_redirected_to_sign_in(): void
    {
        $this->get(route('settings.company.basic-details'))->assertRedirect(route('login'));
    }

    public function test_other_roles_are_forbidden_and_do_not_see_the_menu_link(): void
    {
        $admin = User::factory()->create()->assignRole(RoleName::Admin);

        $this->actingAs($admin)->get(route('settings.company.basic-details'))->assertForbidden();
        $this->actingAs($admin)->put(route('settings.company.basic-details.update'), $this->payload())->assertForbidden();
        $this->actingAs($admin)->get(route('admin.dashboard'))->assertDontSee(route('settings.company.basic-details'));
        $this->assertDatabaseCount('companies', 0);
    }

    public function test_super_admin_sees_the_menu_link_and_an_empty_form_before_saving(): void
    {
        $this->actingAs($this->superAdmin)->get(route('admin.dashboard'))
            ->assertSee(route('settings.company.basic-details'));

        $this->actingAs($this->superAdmin)->get(route('settings.company.basic-details'))
            ->assertOk()
            ->assertSee('<option value="selangor"', false)
            ->assertSee('Not saved yet.');
    }

    public function test_first_save_creates_the_company_and_is_audited(): void
    {
        $this->actingAs($this->superAdmin)
            ->put(route('settings.company.basic-details.update'), $this->payload([
                'registration_no' => '2023-0101 2345',
                'old_registration_no' => ' 1234567-a ',
            ]))
            ->assertRedirect(route('settings.company.basic-details'))
            ->assertSessionHas('status', 'Company details were saved.');

        $company = Company::query()->sole();
        $this->assertSame('Cendana Studio Sdn. Bhd.', $company->name);
        $this->assertSame('202301012345', $company->registration_no);
        $this->assertSame('1234567-A', $company->old_registration_no);
        $this->assertSame('selangor', $company->state);
        $this->assertSame('MY', $company->country);
        $this->assertNull($company->zakat_no);

        $activity = Activity::query()->where('event', 'created')->whereMorphedTo('subject', $company)->sole();
        $this->assertSame('Created company details', $activity->description);
        $this->assertTrue($activity->causer->is($this->superAdmin));
    }

    public function test_saving_again_updates_the_same_company(): void
    {
        $company = Company::factory()->create();

        $this->actingAs($this->superAdmin)
            ->put(route('settings.company.basic-details.update'), $this->payload(['name' => 'Renamed Sdn. Bhd.']))
            ->assertRedirect(route('settings.company.basic-details'));

        $this->assertDatabaseCount('companies', 1);
        $this->assertSame('Renamed Sdn. Bhd.', $company->fresh()->name);

        $this->actingAs($this->superAdmin)->get(route('settings.company.basic-details'))
            ->assertSee('value="Renamed Sdn. Bhd."', false)
            ->assertSee('Last changed by');
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    #[DataProvider('invalidDetails')]
    public function test_invalid_details_are_rejected(array $overrides, string $field): void
    {
        $this->actingAs($this->superAdmin)
            ->from(route('settings.company.basic-details'))
            ->put(route('settings.company.basic-details.update'), $this->payload($overrides))
            ->assertRedirect(route('settings.company.basic-details'))
            ->assertSessionHasErrors($field);

        $this->assertDatabaseCount('companies', 0);
    }

    /**
     * @return array<string, array{array<string, mixed>, string}>
     */
    public static function invalidDetails(): array
    {
        return [
            'missing name' => [['name' => ''], 'name'],
            'short SSM number' => [['registration_no' => '20230101'], 'registration_no'],
            'bad old SSM number' => [['old_registration_no' => 'ABC'], 'old_registration_no'],
            'four-digit postcode' => [['postcode' => '4000'], 'postcode'],
            'unknown state' => [['state' => 'atlantis'], 'state'],
            'letters in phone' => [['phone' => 'call me'], 'phone'],
            'bad website' => [['website' => 'not a url'], 'website'],
        ];
    }

    public function test_logo_is_uploaded_replaced_and_removed(): void
    {
        $this->actingAs($this->superAdmin)
            ->put(route('settings.company.basic-details.update'), $this->payload(['logo' => UploadedFile::fake()->image('first.png', 200, 80)]))
            ->assertSessionHasNoErrors();

        $company = Company::query()->sole();
        $this->assertSame('first.png', $company->getFirstMedia('logo')->file_name);

        $this->actingAs($this->superAdmin)
            ->put(route('settings.company.basic-details.update'), $this->payload(['logo' => UploadedFile::fake()->image('second.jpg', 200, 80)]))
            ->assertSessionHasNoErrors();

        $this->assertSame(['second.jpg'], $company->fresh()->getMedia('logo')->pluck('file_name')->all());

        $this->actingAs($this->superAdmin)
            ->put(route('settings.company.basic-details.update'), $this->payload(['remove_logo' => '1']))
            ->assertSessionHasNoErrors();

        $this->assertCount(0, $company->fresh()->getMedia('logo'));
    }

    public function test_logo_must_be_an_image(): void
    {
        $this->actingAs($this->superAdmin)
            ->put(route('settings.company.basic-details.update'), $this->payload(['logo' => UploadedFile::fake()->create('logo.pdf', 20, 'application/pdf')]))
            ->assertSessionHasErrors('logo');

        $this->assertDatabaseCount('media', 0);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Cendana Studio Sdn. Bhd.',
            'registration_no' => '202301012345',
            'old_registration_no' => '',
            'email' => 'hello@example.com',
            'phone' => '03-1234 5678',
            'website' => 'https://example.com',
            'address_line_1' => '12, Jalan Cendana',
            'address_line_2' => '',
            'postcode' => '40000',
            'city' => 'Shah Alam',
            'state' => 'selangor',
            'epf_no' => '12345678',
            'socso_no' => 'E1234567890',
            'eis_no' => 'E1234567890',
            'income_tax_no' => 'E1234567890',
            'zakat_no' => '',
        ], $overrides);
    }
}
