<?php

namespace Tests\Feature;

use App\Enums\EpfCalculationMethod;
use App\Enums\Role as RoleName;
use App\Models\EpfSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class EpfSettingsTest extends TestCase
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
    }

    public function test_current_returns_statutory_defaults_then_the_saved_row(): void
    {
        $default = EpfSetting::current();
        $this->assertFalse($default->exists);
        $this->assertSame('11.00', $default->malaysian_employee_rate);
        $this->assertSame('13.00', $default->malaysian_employer_rate);
        $this->assertSame('12.00', $default->malaysian_employer_rate_above_threshold);
        $this->assertSame('5000.00', $default->wage_threshold);
        $this->assertSame('4.00', $default->senior_employer_rate);
        $this->assertSame('2.00', $default->foreign_employee_rate);
        $this->assertSame(EpfCalculationMethod::ThirdSchedule, $default->calculation_method);

        $saved = EpfSetting::factory()->create(['malaysian_employee_rate' => 12]);
        $this->assertTrue(EpfSetting::current()->is($saved));
        $this->assertSame('12.00', EpfSetting::current()->malaysian_employee_rate);
    }

    public function test_settings_are_identified_by_ulid_and_keep_an_integer_id(): void
    {
        $setting = EpfSetting::factory()->create();

        $this->assertIsInt($setting->id);
        $this->assertTrue(Str::isUlid($setting->ulid));
        $this->assertSame($setting->ulid, $setting->getRouteKey());
    }

    public function test_guests_are_redirected_to_sign_in(): void
    {
        $this->get(route('settings.payroll.epf'))->assertRedirect(route('login'));
    }

    public function test_other_roles_are_forbidden_and_do_not_see_the_menu_link(): void
    {
        $admin = User::factory()->create()->assignRole(RoleName::Admin);

        $this->actingAs($admin)->get(route('admin.dashboard'))->assertDontSee(route('settings.payroll.epf'));
        $this->actingAs($admin)->get(route('settings.payroll.epf'))->assertForbidden();
        $this->actingAs($admin)->put(route('settings.payroll.epf.update'), $this->payload())->assertForbidden();
    }

    public function test_super_admin_sees_the_statutory_defaults_and_fixed_rules(): void
    {
        $this->actingAs($this->superAdmin)->get(route('admin.dashboard'))->assertSee(route('settings.payroll.epf'));

        $this->actingAs($this->superAdmin)->get(route('settings.payroll.epf'))
            ->assertOk()
            ->assertSee('name="malaysian_employee_rate"', false)
            ->assertSee('value="11.00"', false)
            ->assertSee('value="5000.00"', false)
            ->assertSee('by the 15th of the following month')
            ->assertSee(route('settings.company.bank-account-details'))
            ->assertSee('Using the default settings until you save.');
    }

    public function test_first_save_creates_the_settings_and_is_audited(): void
    {
        $this->actingAs($this->superAdmin)
            ->put(route('settings.payroll.epf.update'), $this->payload(['allow_employer_top_up' => '1', 'employer_top_up_rate' => '15']))
            ->assertRedirect(route('settings.payroll.epf'))
            ->assertSessionHas('status', 'EPF settings were saved.');

        $setting = EpfSetting::query()->sole();
        $this->assertTrue($setting->allow_employer_top_up);
        $this->assertSame('15.00', $setting->employer_top_up_rate);

        $activity = Activity::query()->whereMorphedTo('subject', $setting)->sole();
        $this->assertSame('Updated EPF settings', $activity->description);
        $this->assertTrue($activity->causer->is($this->superAdmin));
    }

    public function test_saving_again_updates_the_same_row_and_audits_only_the_changes(): void
    {
        $setting = EpfSetting::factory()->create();
        Activity::query()->delete();

        $this->actingAs($this->superAdmin)
            ->put(route('settings.payroll.epf.update'), $this->payload(['foreign_employer_rate' => '2.5']))
            ->assertRedirect(route('settings.payroll.epf'));

        $this->assertDatabaseCount('epf_settings', 1);
        $this->assertSame('2.50', $setting->fresh()->foreign_employer_rate);
        $this->assertSame(['foreign_employer_rate' => '2.50'], Activity::query()->sole()->attribute_changes['attributes']);

        $this->actingAs($this->superAdmin)->get(route('settings.payroll.epf'))->assertSee('Last changed by');
    }

    public function test_top_up_rate_is_cleared_when_top_up_is_off(): void
    {
        $this->actingAs($this->superAdmin)
            ->put(route('settings.payroll.epf.update'), $this->payload(['allow_employer_top_up' => '0', 'employer_top_up_rate' => '15']));

        $this->assertNull(EpfSetting::query()->sole()->employer_top_up_rate);
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    #[DataProvider('invalidSettings')]
    public function test_invalid_settings_are_rejected(array $overrides, string $field): void
    {
        $this->actingAs($this->superAdmin)
            ->from(route('settings.payroll.epf'))
            ->put(route('settings.payroll.epf.update'), $this->payload($overrides))
            ->assertRedirect(route('settings.payroll.epf'))
            ->assertSessionHasErrors($field);

        $this->assertDatabaseCount('epf_settings', 0);
    }

    /**
     * @return array<string, array{array<string, mixed>, string}>
     */
    public static function invalidSettings(): array
    {
        return [
            'missing employee rate' => [['malaysian_employee_rate' => ''], 'malaysian_employee_rate'],
            'negative rate' => [['senior_employer_rate' => '-1'], 'senior_employer_rate'],
            'rate over 30%' => [['foreign_employee_rate' => '31'], 'foreign_employee_rate'],
            'rate with three decimals' => [['malaysian_employer_rate' => '13.125'], 'malaysian_employer_rate'],
            'zero wage threshold' => [['wage_threshold' => '0'], 'wage_threshold'],
            'unknown method' => [['calculation_method' => 'guess'], 'calculation_method'],
            'top-up without a rate' => [['allow_employer_top_up' => '1', 'employer_top_up_rate' => ''], 'employer_top_up_rate'],
            'top-up not above statutory' => [['allow_employer_top_up' => '1', 'employer_top_up_rate' => '13'], 'employer_top_up_rate'],
        ];
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'malaysian_employee_rate' => '11',
            'malaysian_employer_rate' => '13',
            'malaysian_employer_rate_above_threshold' => '12',
            'wage_threshold' => '5000',
            'senior_employee_rate' => '0',
            'senior_employer_rate' => '4',
            'foreign_employee_rate' => '2',
            'foreign_employer_rate' => '2',
            'calculation_method' => 'third-schedule',
            'allow_employer_top_up' => '0',
            'employer_top_up_rate' => '',
            'allow_employee_elected_rate' => '0',
        ], $overrides);
    }
}
