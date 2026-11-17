<?php

namespace Tests\Feature;

use App\Enums\HrdfLevy;
use App\Enums\PayDayRule;
use App\Enums\Role as RoleName;
use App\Models\PayrollSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class PayrollSettingsTest extends TestCase
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

    public function test_current_returns_unsaved_defaults_then_the_saved_row(): void
    {
        $default = PayrollSetting::current();
        $this->assertFalse($default->exists);
        $this->assertSame(PayDayRule::FixedDay, $default->pay_day_rule);
        $this->assertSame(25, $default->pay_day);
        $this->assertSame(RoleName::Director, $default->approver_role);

        $saved = PayrollSetting::factory()->create(['cutoff_day' => 15]);
        $this->assertTrue(PayrollSetting::current()->is($saved));
        $this->assertSame(15, PayrollSetting::current()->cutoff_day);
    }

    public function test_settings_are_identified_by_ulid_and_keep_an_integer_id(): void
    {
        $setting = PayrollSetting::factory()->create();

        $this->assertIsInt($setting->id);
        $this->assertTrue(Str::isUlid($setting->ulid));
        $this->assertSame($setting->ulid, $setting->getRouteKey());
    }

    public function test_guests_are_redirected_to_sign_in(): void
    {
        $this->get(route('settings.payroll.general'))->assertRedirect(route('login'));
    }

    public function test_other_roles_are_forbidden_and_do_not_see_the_menu_link(): void
    {
        $admin = User::factory()->create()->assignRole(RoleName::Admin);

        $this->actingAs($admin)->get(route('admin.dashboard'))->assertDontSee(route('settings.payroll.general'));
        $this->actingAs($admin)->get(route('settings.payroll.general'))->assertForbidden();
        $this->actingAs($admin)->put(route('settings.payroll.general.update'), $this->payload())->assertForbidden();
    }

    public function test_super_admin_sees_the_defaults(): void
    {
        $this->actingAs($this->superAdmin)->get(route('admin.dashboard'))->assertSee(route('settings.payroll.general'));

        $this->actingAs($this->superAdmin)->get(route('settings.payroll.general'))
            ->assertOk()
            ->assertSee('<option value="fixed-day" selected', false)
            ->assertSee('value="25"', false)
            ->assertSee('<option value="Director" selected', false)
            ->assertSee('Using the default settings until you save.');
    }

    public function test_first_save_creates_the_settings_and_is_audited(): void
    {
        $this->actingAs($this->superAdmin)
            ->put(route('settings.payroll.general.update'), $this->payload(['hrdf_levy' => 'one-percent']))
            ->assertRedirect(route('settings.payroll.general'))
            ->assertSessionHas('status', 'Payroll settings were saved.');

        $setting = PayrollSetting::query()->sole();
        $this->assertSame(HrdfLevy::OnePercent, $setting->hrdf_levy);
        $this->assertSame(RoleName::Finance, $setting->approver_role);

        $activity = Activity::query()->whereMorphedTo('subject', $setting)->sole();
        $this->assertSame('Updated payroll settings', $activity->description);
        $this->assertTrue($activity->causer->is($this->superAdmin));
    }

    public function test_saving_again_updates_the_same_row_and_audits_only_the_changes(): void
    {
        $setting = PayrollSetting::factory()->create(['approver_role' => RoleName::Finance]);
        Activity::query()->delete();

        $this->actingAs($this->superAdmin)
            ->put(route('settings.payroll.general.update'), $this->payload(['cutoff_day' => '15']))
            ->assertRedirect(route('settings.payroll.general'));

        $this->assertDatabaseCount('payroll_settings', 1);
        $this->assertSame(15, $setting->fresh()->cutoff_day);
        $this->assertSame(['cutoff_day' => 15], Activity::query()->sole()->attribute_changes['attributes']);

        $this->actingAs($this->superAdmin)->get(route('settings.payroll.general'))->assertSee('Last changed by');
    }

    public function test_pay_day_and_approver_are_cleared_when_not_used(): void
    {
        $this->actingAs($this->superAdmin)
            ->put(route('settings.payroll.general.update'), $this->payload([
                'pay_day_rule' => 'last-working-day',
                'pay_day' => '25',
                'requires_approval' => '0',
                'approver_role' => 'Finance',
            ]));

        $setting = PayrollSetting::query()->sole();
        $this->assertSame(PayDayRule::LastWorkingDay, $setting->pay_day_rule);
        $this->assertNull($setting->pay_day);
        $this->assertFalse($setting->requires_approval);
        $this->assertNull($setting->approver_role);
    }

    public function test_next_month_pay_day_up_to_the_7th_is_accepted(): void
    {
        $this->actingAs($this->superAdmin)
            ->put(route('settings.payroll.general.update'), $this->payload(['pay_day_rule' => 'next-month-day', 'pay_day' => '7']))
            ->assertSessionHasNoErrors();

        $this->assertSame(7, PayrollSetting::query()->sole()->pay_day);
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    #[DataProvider('invalidSettings')]
    public function test_invalid_settings_are_rejected(array $overrides, string $field): void
    {
        $this->actingAs($this->superAdmin)
            ->from(route('settings.payroll.general'))
            ->put(route('settings.payroll.general.update'), $this->payload($overrides))
            ->assertRedirect(route('settings.payroll.general'))
            ->assertSessionHasErrors($field);

        $this->assertDatabaseCount('payroll_settings', 0);
    }

    /**
     * @return array<string, array{array<string, mixed>, string}>
     */
    public static function invalidSettings(): array
    {
        return [
            'unknown pay day rule' => [['pay_day_rule' => 'whenever'], 'pay_day_rule'],
            'fixed day 32' => [['pay_day' => '32'], 'pay_day'],
            'fixed day missing' => [['pay_day' => ''], 'pay_day'],
            'next-month day 8' => [['pay_day_rule' => 'next-month-day', 'pay_day' => '8'], 'pay_day'],
            'cut-off day 0' => [['cutoff_day' => '0'], 'cutoff_day'],
            'unknown adjustment' => [['pay_day_adjustment' => 'sideways'], 'pay_day_adjustment'],
            'unknown PCB method' => [['pcb_method' => 'guess'], 'pcb_method'],
            'unknown HRDF levy' => [['hrdf_levy' => 'two-percent'], 'hrdf_levy'],
            'unknown payment method' => [['payment_method' => 'crypto'], 'payment_method'],
            'unknown rounding' => [['net_pay_rounding' => 'ten-sen'], 'net_pay_rounding'],
            'approval without a role' => [['requires_approval' => '1', 'approver_role' => ''], 'approver_role'],
            'unknown role' => [['approver_role' => 'Astronaut'], 'approver_role'],
        ];
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'pay_day_rule' => 'fixed-day',
            'pay_day' => '25',
            'pay_day_adjustment' => 'previous-working-day',
            'cutoff_day' => '20',
            'socso_enabled' => '1',
            'eis_enabled' => '1',
            'pcb_enabled' => '1',
            'pcb_method' => 'computerised',
            'hrdf_levy' => 'none',
            'zakat_via_payroll' => '1',
            'payment_method' => 'bank-transfer',
            'net_pay_rounding' => 'sen',
            'requires_approval' => '1',
            'approver_role' => 'Finance',
            'lock_after_release' => '1',
        ], $overrides);
    }
}
