<?php

namespace Tests\Feature;

use App\Enums\LookupCategory;
use App\Enums\Role as RoleName;
use App\Models\Company;
use App\Models\LookupItem;
use App\Models\User;
use Database\Seeders\LookupSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class CompanyBankAccountDetailsTest extends TestCase
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
    }

    public function test_guests_are_redirected_to_sign_in(): void
    {
        $this->get(route('settings.company.bank-account-details'))->assertRedirect(route('login'));
    }

    public function test_other_roles_are_forbidden_and_do_not_see_the_menu_link(): void
    {
        $admin = User::factory()->create()->assignRole(RoleName::Admin);

        $this->actingAs($admin)->get(route('admin.dashboard'))
            ->assertDontSee(route('settings.company.bank-account-details'));
        $this->actingAs($admin)->get(route('settings.company.bank-account-details'))->assertForbidden();
        $this->actingAs($admin)->put(route('settings.company.bank-account-details.update'), $this->payload())->assertForbidden();
    }

    public function test_before_basic_details_are_saved_the_page_asks_for_them_and_saving_creates_nothing(): void
    {
        $this->actingAs($this->superAdmin)->get(route('admin.dashboard'))
            ->assertSee(route('settings.company.bank-account-details'));

        $this->actingAs($this->superAdmin)->get(route('settings.company.bank-account-details'))
            ->assertOk()
            ->assertSee('Save the company’s basic details first.')
            ->assertSee(route('settings.company.basic-details'));

        $this->actingAs($this->superAdmin)
            ->from(route('settings.company.bank-account-details'))
            ->put(route('settings.company.bank-account-details.update'), $this->payload())
            ->assertRedirect(route('settings.company.bank-account-details'))
            ->assertSessionHasErrors('company');

        $this->assertDatabaseCount('companies', 0);
    }

    public function test_saving_stores_the_details_and_is_audited_with_only_the_changes(): void
    {
        $company = Company::factory()->create();
        Activity::query()->delete();

        $this->actingAs($this->superAdmin)->get(route('settings.company.bank-account-details'))
            ->assertOk()
            ->assertSee('<option value="maybank"', false);

        $this->actingAs($this->superAdmin)
            ->put(route('settings.company.bank-account-details.update'), $this->payload(['bank_account_no' => '5141-2345 6789']))
            ->assertRedirect(route('settings.company.bank-account-details'))
            ->assertSessionHas('status', 'Bank account details were saved.');

        $company->refresh();
        $this->assertSame('maybank', $company->bank);
        $this->assertSame('Cendana Studio Sdn. Bhd.', $company->bank_account_holder);
        $this->assertSame('514123456789', $company->bank_account_no);
        $this->assertSame('012345678', $company->epf_no);
        $this->assertSame('112345678', $company->epf_foreign_no);
        $this->assertSame('HRD123456', $company->hrdf_no);
        $this->assertSame('PT12345', $company->ptptn_no);
        $this->assertNull($company->zakat_no);

        $activity = Activity::query()->where('event', 'updated')->whereMorphedTo('subject', $company)->sole();
        $this->assertSame('Updated company bank account details', $activity->description);
        $this->assertTrue($activity->causer->is($this->superAdmin));
        $this->assertSame('514123456789', $activity->attribute_changes['attributes']['bank_account_no']);
        $this->assertArrayNotHasKey('name', $activity->attribute_changes['attributes']);

        $this->actingAs($this->superAdmin)->get(route('settings.company.bank-account-details'))
            ->assertSee('value="514123456789"', false)
            ->assertSee('Last changed by');
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    #[DataProvider('invalidDetails')]
    public function test_invalid_details_are_rejected(array $overrides, string $field): void
    {
        $company = Company::factory()->create(['bank' => null]);
        LookupItem::query()->inCategory(LookupCategory::Banks)->where('code', 'citibank')->update(['is_active' => false]);

        $this->actingAs($this->superAdmin)
            ->from(route('settings.company.bank-account-details'))
            ->put(route('settings.company.bank-account-details.update'), $this->payload($overrides))
            ->assertRedirect(route('settings.company.bank-account-details'))
            ->assertSessionHasErrors($field);

        $this->assertNull($company->fresh()->bank);
    }

    /**
     * @return array<string, array{array<string, mixed>, string}>
     */
    public static function invalidDetails(): array
    {
        return [
            'unknown bank' => [['bank' => 'bank-of-atlantis'], 'bank'],
            'inactive bank' => [['bank' => 'citibank'], 'bank'],
            'letters in account number' => [['bank_account_no' => '5141ABC6789'], 'bank_account_no'],
            'short account number' => [['bank_account_no' => '1234'], 'bank_account_no'],
            'long holder name' => [['bank_account_holder' => str_repeat('a', 151)], 'bank_account_holder'],
            'long HRDF number' => [['hrdf_no' => str_repeat('1', 31)], 'hrdf_no'],
        ];
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'bank' => 'maybank',
            'bank_account_holder' => 'Cendana Studio Sdn. Bhd.',
            'bank_account_no' => '514123456789',
            'epf_no' => '012345678',
            'epf_foreign_no' => '112345678',
            'socso_no' => 'E1234567890',
            'eis_no' => 'E1234567890',
            'income_tax_no' => 'E1234567890',
            'zakat_no' => '',
            'hrdf_no' => 'HRD123456',
            'ptptn_no' => 'PT12345',
        ], $overrides);
    }
}
