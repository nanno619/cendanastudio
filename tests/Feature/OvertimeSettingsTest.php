<?php

namespace Tests\Feature;

use App\Enums\OvertimeEligibility;
use App\Enums\Role as RoleName;
use App\Models\OvertimeSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class OvertimeSettingsTest extends TestCase
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

    public function test_current_returns_employment_act_defaults_then_the_saved_row(): void
    {
        $default = OvertimeSetting::current();
        $this->assertFalse($default->exists);
        $this->assertSame(OvertimeEligibility::EmploymentAct, $default->eligibility);
        $this->assertSame('4000.00', $default->eligibility_wage_limit);
        $this->assertSame('1.50', $default->normal_day_rate);
        $this->assertSame('2.00', $default->rest_day_rate);
        $this->assertSame('3.00', $default->public_holiday_rate);
        $this->assertSame('8.00', $default->normal_hours_per_day);
        $this->assertSame(104, $default->monthly_limit_hours);

        $saved = OvertimeSetting::factory()->create(['normal_day_rate' => 2]);
        $this->assertTrue(OvertimeSetting::current()->is($saved));
        $this->assertSame('2.00', OvertimeSetting::current()->normal_day_rate);
    }

    public function test_settings_are_identified_by_ulid_and_keep_an_integer_id(): void
    {
        $setting = OvertimeSetting::factory()->create();

        $this->assertIsInt($setting->id);
        $this->assertTrue(Str::isUlid($setting->ulid));
        $this->assertSame($setting->ulid, $setting->getRouteKey());
    }

    public function test_hourly_rate_divides_monthly_wages_by_26_and_normal_hours(): void
    {
        $this->assertEqualsWithDelta(12.5, OvertimeSetting::current()->hourlyRate('2600.00'), 0.0001);
        $this->assertEqualsWithDelta(10.0, (new OvertimeSetting(['normal_hours_per_day' => 10]))->hourlyRate(2600), 0.0001);
    }

    public function test_guests_are_redirected_to_sign_in(): void
    {
        $this->get(route('settings.payroll.overtime'))->assertRedirect(route('login'));
    }

    public function test_other_roles_are_forbidden_and_do_not_see_the_menu_link(): void
    {
        $admin = User::factory()->create()->assignRole(RoleName::Admin);

        $this->actingAs($admin)->get(route('admin.dashboard'))->assertDontSee(route('settings.payroll.overtime'));
        $this->actingAs($admin)->get(route('settings.payroll.overtime'))->assertForbidden();
        $this->actingAs($admin)->put(route('settings.payroll.overtime.update'), $this->payload())->assertForbidden();
    }

    public function test_super_admin_sees_the_defaults_and_fixed_rules(): void
    {
        $this->actingAs($this->superAdmin)->get(route('admin.dashboard'))->assertSee(route('settings.payroll.overtime'));

        $this->actingAs($this->superAdmin)->get(route('settings.payroll.overtime'))
            ->assertOk()
            ->assertSee('name="normal_day_rate"', false)
            ->assertSee('value="1.50"', false)
            ->assertSee('value="4000.00"', false)
            ->assertSee('divided by 26')
            ->assertSee(route('settings.payroll.general'))
            ->assertSee('Using the default settings until you save.');
    }

    public function test_first_save_creates_the_settings_and_is_audited(): void
    {
        $this->actingAs($this->superAdmin)
            ->put(route('settings.payroll.overtime.update'), $this->payload(['normal_day_rate' => '1.75', 'monthly_limit_hours' => '80']))
            ->assertRedirect(route('settings.payroll.overtime'))
            ->assertSessionHas('status', 'Overtime settings were saved.');

        $setting = OvertimeSetting::query()->sole();
        $this->assertSame('1.75', $setting->normal_day_rate);
        $this->assertSame(80, $setting->monthly_limit_hours);

        $activity = Activity::query()->whereMorphedTo('subject', $setting)->sole();
        $this->assertSame('Updated overtime settings', $activity->description);
        $this->assertTrue($activity->causer->is($this->superAdmin));
    }

    public function test_saving_again_updates_the_same_row_and_audits_only_the_changes(): void
    {
        $setting = OvertimeSetting::factory()->create();
        Activity::query()->delete();

        $this->actingAs($this->superAdmin)
            ->put(route('settings.payroll.overtime.update'), $this->payload(['public_holiday_rate' => '3.5']))
            ->assertRedirect(route('settings.payroll.overtime'));

        $this->assertDatabaseCount('overtime_settings', 1);
        $this->assertSame('3.50', $setting->fresh()->public_holiday_rate);
        $this->assertSame(['public_holiday_rate' => '3.50'], Activity::query()->sole()->attribute_changes['attributes']);

        $this->actingAs($this->superAdmin)->get(route('settings.payroll.overtime'))->assertSee('Last changed by');
    }

    public function test_wage_limit_is_cleared_when_all_staff_are_eligible(): void
    {
        $this->actingAs($this->superAdmin)
            ->put(route('settings.payroll.overtime.update'), $this->payload(['eligibility' => 'all-staff', 'eligibility_wage_limit' => '4000']));

        $setting = OvertimeSetting::query()->sole();
        $this->assertSame(OvertimeEligibility::AllStaff, $setting->eligibility);
        $this->assertNull($setting->eligibility_wage_limit);
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    #[DataProvider('invalidSettings')]
    public function test_invalid_settings_are_rejected(array $overrides, string $field): void
    {
        $this->actingAs($this->superAdmin)
            ->from(route('settings.payroll.overtime'))
            ->put(route('settings.payroll.overtime.update'), $this->payload($overrides))
            ->assertRedirect(route('settings.payroll.overtime'))
            ->assertSessionHasErrors($field);

        $this->assertDatabaseCount('overtime_settings', 0);
    }

    /**
     * @return array<string, array{array<string, mixed>, string}>
     */
    public static function invalidSettings(): array
    {
        return [
            'unknown eligibility' => [['eligibility' => 'managers'], 'eligibility'],
            'Employment Act without a wage limit' => [['eligibility_wage_limit' => ''], 'eligibility_wage_limit'],
            'normal day below 1.5x' => [['normal_day_rate' => '1.25'], 'normal_day_rate'],
            'rest day below 2x' => [['rest_day_rate' => '1.5'], 'rest_day_rate'],
            'public holiday below 3x' => [['public_holiday_rate' => '2'], 'public_holiday_rate'],
            'rate with three decimals' => [['normal_day_rate' => '1.625'], 'normal_day_rate'],
            'normal hours not in half hours' => [['normal_hours_per_day' => '7.75'], 'normal_hours_per_day'],
            'monthly limit over 104 hours' => [['monthly_limit_hours' => '105'], 'monthly_limit_hours'],
        ];
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'eligibility' => 'employment-act',
            'eligibility_wage_limit' => '4000',
            'normal_day_rate' => '1.5',
            'rest_day_rate' => '2',
            'public_holiday_rate' => '3',
            'normal_hours_per_day' => '8',
            'monthly_limit_hours' => '104',
        ], $overrides);
    }
}
