<?php

namespace Tests\Feature;

use App\Enums\ProRataBasis;
use App\Enums\Role as RoleName;
use App\Enums\Weekday;
use App\Models\LeaveSetting;
use App\Models\ProRataSetting;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class ProRataSettingsTest extends TestCase
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

    public function test_current_returns_defaults_then_the_saved_row(): void
    {
        $default = ProRataSetting::current();
        $this->assertFalse($default->exists);
        $this->assertSame(ProRataBasis::CalendarDays, $default->joiner_leaver_basis);
        $this->assertSame(ProRataBasis::FixedDays, $default->unpaid_leave_basis);
        $this->assertSame(26, $default->fixed_days);
        $this->assertTrue($default->prorate_fixed_allowances);

        $saved = ProRataSetting::factory()->create(['fixed_days' => 22]);
        $this->assertTrue(ProRataSetting::current()->is($saved));
        $this->assertSame(22, ProRataSetting::current()->fixed_days);
    }

    public function test_settings_are_identified_by_ulid_and_keep_an_integer_id(): void
    {
        $setting = ProRataSetting::factory()->create();

        $this->assertIsInt($setting->id);
        $this->assertTrue(Str::isUlid($setting->ulid));
        $this->assertSame($setting->ulid, $setting->getRouteKey());
    }

    public function test_days_for_each_basis(): void
    {
        $setting = ProRataSetting::current();
        $february = CarbonImmutable::parse('2026-02-15');
        $leave = LeaveSetting::current();

        $this->assertSame(28.0, $setting->daysFor(ProRataBasis::CalendarDays, $february, $leave));
        $this->assertSame(26.0, $setting->daysFor(ProRataBasis::FixedDays, $february, $leave));
        $this->assertSame(20.0, $setting->daysFor(ProRataBasis::WorkingDays, $february, $leave));

        $leave->working_days = [...$leave->working_days->all(), Weekday::Saturday];
        $leave->half_working_day = Weekday::Saturday;
        $this->assertSame(22.0, $setting->daysFor(ProRataBasis::WorkingDays, $february, $leave));
    }

    public function test_guests_are_redirected_to_sign_in(): void
    {
        $this->get(route('settings.payroll.pro-rata'))->assertRedirect(route('login'));
    }

    public function test_other_roles_are_forbidden_and_do_not_see_the_menu_link(): void
    {
        $admin = User::factory()->create()->assignRole(RoleName::Admin);

        $this->actingAs($admin)->get(route('admin.dashboard'))->assertDontSee(route('settings.payroll.pro-rata'));
        $this->actingAs($admin)->get(route('settings.payroll.pro-rata'))->assertForbidden();
        $this->actingAs($admin)->put(route('settings.payroll.pro-rata.update'), $this->payload())->assertForbidden();
    }

    public function test_super_admin_sees_the_defaults_and_fixed_rules(): void
    {
        $this->actingAs($this->superAdmin)->get(route('admin.dashboard'))->assertSee(route('settings.payroll.pro-rata'));

        $this->actingAs($this->superAdmin)->get(route('settings.payroll.pro-rata'))
            ->assertOk()
            ->assertSee('name="joiner_leaver_basis"', false)
            ->assertSee('value="26"', false)
            ->assertSee('The joining day and the last working day are both paid.')
            ->assertSee(route('settings.leave.general'))
            ->assertSee('Using the default settings until you save.');
    }

    public function test_first_save_creates_the_settings_and_is_audited(): void
    {
        $this->actingAs($this->superAdmin)
            ->put(route('settings.payroll.pro-rata.update'), $this->payload(['joiner_leaver_basis' => 'working-days', 'prorate_fixed_allowances' => '0']))
            ->assertRedirect(route('settings.payroll.pro-rata'))
            ->assertSessionHas('status', 'Pro-rata settings were saved.');

        $setting = ProRataSetting::query()->sole();
        $this->assertSame(ProRataBasis::WorkingDays, $setting->joiner_leaver_basis);
        $this->assertFalse($setting->prorate_fixed_allowances);

        $activity = Activity::query()->whereMorphedTo('subject', $setting)->sole();
        $this->assertSame('Updated pro-rata settings', $activity->description);
        $this->assertTrue($activity->causer->is($this->superAdmin));
    }

    public function test_saving_again_updates_the_same_row_and_audits_only_the_changes(): void
    {
        $setting = ProRataSetting::factory()->create();
        Activity::query()->delete();

        $this->actingAs($this->superAdmin)
            ->put(route('settings.payroll.pro-rata.update'), $this->payload(['fixed_days' => '30']))
            ->assertRedirect(route('settings.payroll.pro-rata'));

        $this->assertDatabaseCount('pro_rata_settings', 1);
        $this->assertSame(30, $setting->fresh()->fixed_days);
        $this->assertSame(['fixed_days' => 30], Activity::query()->sole()->attribute_changes['attributes']);

        $this->actingAs($this->superAdmin)->get(route('settings.payroll.pro-rata'))->assertSee('Last changed by');
    }

    public function test_fixed_days_are_cleared_when_no_basis_uses_them(): void
    {
        $this->actingAs($this->superAdmin)
            ->put(route('settings.payroll.pro-rata.update'), $this->payload(['unpaid_leave_basis' => 'calendar-days', 'fixed_days' => '']));

        $setting = ProRataSetting::query()->sole();
        $this->assertFalse($setting->usesFixedDays());
        $this->assertNull($setting->fixed_days);
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    #[DataProvider('invalidSettings')]
    public function test_invalid_settings_are_rejected(array $overrides, string $field): void
    {
        $this->actingAs($this->superAdmin)
            ->from(route('settings.payroll.pro-rata'))
            ->put(route('settings.payroll.pro-rata.update'), $this->payload($overrides))
            ->assertRedirect(route('settings.payroll.pro-rata'))
            ->assertSessionHasErrors($field);

        $this->assertDatabaseCount('pro_rata_settings', 0);
    }

    /**
     * @return array<string, array{array<string, mixed>, string}>
     */
    public static function invalidSettings(): array
    {
        return [
            'unknown joiner basis' => [['joiner_leaver_basis' => 'weeks'], 'joiner_leaver_basis'],
            'missing unpaid leave basis' => [['unpaid_leave_basis' => ''], 'unpaid_leave_basis'],
            'fixed basis without days' => [['fixed_days' => ''], 'fixed_days'],
            'fixed days below 20' => [['fixed_days' => '19'], 'fixed_days'],
            'fixed days above 31' => [['fixed_days' => '32'], 'fixed_days'],
            'joiner fixed basis without days' => [['joiner_leaver_basis' => 'fixed-days', 'unpaid_leave_basis' => 'calendar-days', 'fixed_days' => ''], 'fixed_days'],
        ];
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'joiner_leaver_basis' => 'calendar-days',
            'unpaid_leave_basis' => 'fixed-days',
            'fixed_days' => '26',
            'prorate_fixed_allowances' => '1',
        ], $overrides);
    }
}
