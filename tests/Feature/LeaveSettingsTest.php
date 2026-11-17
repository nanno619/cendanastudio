<?php

namespace Tests\Feature;

use App\Enums\LeaveRounding;
use App\Enums\Role as RoleName;
use App\Enums\UnusedLeaveTreatment;
use App\Enums\Weekday;
use App\Models\Company;
use App\Models\LeaveSetting;
use App\Models\User;
use Database\Seeders\LookupSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class LeaveSettingsTest extends TestCase
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

    public function test_current_returns_unsaved_defaults_then_the_saved_row(): void
    {
        $default = LeaveSetting::current();
        $this->assertFalse($default->exists);
        $this->assertSame(1, $default->leave_year_start_month);
        $this->assertEquals([Weekday::Monday, Weekday::Tuesday, Weekday::Wednesday, Weekday::Thursday, Weekday::Friday], $default->working_days->all());
        $this->assertSame(LeaveRounding::NearestDay, $default->proration_rounding);

        $saved = LeaveSetting::factory()->create(['backdate_days' => 14]);
        $this->assertTrue(LeaveSetting::current()->is($saved));
        $this->assertSame(14, LeaveSetting::current()->backdate_days);
    }

    public function test_settings_are_identified_by_ulid_and_keep_an_integer_id(): void
    {
        $setting = LeaveSetting::factory()->create();

        $this->assertIsInt($setting->id);
        $this->assertTrue(Str::isUlid($setting->ulid));
        $this->assertSame($setting->ulid, $setting->getRouteKey());
    }

    public function test_guests_are_redirected_to_sign_in(): void
    {
        $this->get(route('settings.leave.general'))->assertRedirect(route('login'));
    }

    public function test_other_roles_are_forbidden_and_do_not_see_the_menu_link(): void
    {
        $admin = User::factory()->create()->assignRole(RoleName::Admin);

        $this->actingAs($admin)->get(route('admin.dashboard'))->assertDontSee(route('settings.leave.general'));
        $this->actingAs($admin)->get(route('settings.leave.general'))->assertForbidden();
        $this->actingAs($admin)->put(route('settings.leave.general.update'), $this->payload())->assertForbidden();
    }

    public function test_super_admin_sees_the_defaults_with_the_company_state_preselected(): void
    {
        Company::factory()->create(['state' => 'kedah']);

        $this->actingAs($this->superAdmin)->get(route('admin.dashboard'))->assertSee(route('settings.leave.general'));

        $this->actingAs($this->superAdmin)->get(route('settings.leave.general'))
            ->assertOk()
            ->assertSee('<option value="kedah" selected', false)
            ->assertSee('name="working_days[]" value="1" class="mt-0.5" checked', false)
            ->assertDontSee('name="working_days[]" value="6" class="mt-0.5" checked', false)
            ->assertSee('Using the default settings until you save.');
    }

    public function test_first_save_creates_the_settings_and_is_audited(): void
    {
        $this->actingAs($this->superAdmin)
            ->put(route('settings.leave.general.update'), $this->payload(['working_days' => ['6', '1', '3']]))
            ->assertRedirect(route('settings.leave.general'))
            ->assertSessionHas('status', 'Leave settings were saved.');

        $setting = LeaveSetting::query()->sole();
        $this->assertEquals([Weekday::Monday, Weekday::Wednesday, Weekday::Saturday], $setting->working_days->all());
        $this->assertSame(Weekday::Saturday, $setting->half_working_day);
        $this->assertSame('selangor', $setting->public_holiday_state);
        $this->assertSame(UnusedLeaveTreatment::PayOut, $setting->resignation_unused_leave);
        $this->assertTrue($setting->share_leave_calendar);

        $activity = Activity::query()->whereMorphedTo('subject', $setting)->sole();
        $this->assertSame('Updated leave settings', $activity->description);
        $this->assertTrue($activity->causer->is($this->superAdmin));
    }

    public function test_saving_again_updates_the_same_row_and_audits_only_the_changes(): void
    {
        $setting = LeaveSetting::factory()->create();
        Activity::query()->delete();

        $this->actingAs($this->superAdmin)
            ->put(route('settings.leave.general.update'), $this->payload([
                'working_days' => ['1', '2', '3', '4', '5'],
                'half_working_day' => '',
                'public_holiday_state' => '',
                'resignation_unused_leave' => 'offset-notice',
                'share_leave_calendar' => '0',
                'hr_notification_email' => '',
                'backdate_days' => '14',
            ]))
            ->assertRedirect(route('settings.leave.general'));

        $this->assertDatabaseCount('leave_settings', 1);
        $this->assertSame(14, $setting->fresh()->backdate_days);

        $activity = Activity::query()->sole();
        $this->assertSame(['backdate_days' => 14], $activity->attribute_changes['attributes']);

        $this->actingAs($this->superAdmin)->get(route('settings.leave.general'))->assertSee('Last changed by');
    }

    public function test_negative_balance_days_are_kept_only_while_borrowing_is_on(): void
    {
        $this->actingAs($this->superAdmin)
            ->put(route('settings.leave.general.update'), $this->payload(['allow_negative_balance' => '1', 'max_negative_days' => '2.5']));
        $this->assertSame('2.5', LeaveSetting::query()->sole()->max_negative_days);

        $this->actingAs($this->superAdmin)
            ->put(route('settings.leave.general.update'), $this->payload(['allow_negative_balance' => '0', 'max_negative_days' => '2.5']));
        $this->assertNull(LeaveSetting::query()->sole()->max_negative_days);
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    #[DataProvider('invalidSettings')]
    public function test_invalid_settings_are_rejected(array $overrides, string $field): void
    {
        $this->actingAs($this->superAdmin)
            ->from(route('settings.leave.general'))
            ->put(route('settings.leave.general.update'), $this->payload($overrides))
            ->assertRedirect(route('settings.leave.general'))
            ->assertSessionHasErrors($field);

        $this->assertDatabaseCount('leave_settings', 0);
    }

    /**
     * @return array<string, array{array<string, mixed>, string}>
     */
    public static function invalidSettings(): array
    {
        return [
            'no working days' => [['working_days' => []], 'working_days'],
            'unknown weekday' => [['working_days' => ['1', '8']], 'working_days.1'],
            'half day not a working day' => [['working_days' => ['1', '2'], 'half_working_day' => '6'], 'half_working_day'],
            'month 13' => [['leave_year_start_month' => '13'], 'leave_year_start_month'],
            'unknown state' => [['public_holiday_state' => 'atlantis'], 'public_holiday_state'],
            'borrowing without days' => [['allow_negative_balance' => '1', 'max_negative_days' => ''], 'max_negative_days'],
            'borrowing 0.3 days' => [['allow_negative_balance' => '1', 'max_negative_days' => '0.3'], 'max_negative_days'],
            'borrowing 31 days' => [['allow_negative_balance' => '1', 'max_negative_days' => '31'], 'max_negative_days'],
            'notice 91 days' => [['annual_leave_notice_days' => '91'], 'annual_leave_notice_days'],
            'bad HR email' => [['hr_notification_email' => 'not an email'], 'hr_notification_email'],
            'unknown rounding' => [['proration_rounding' => 'sideways'], 'proration_rounding'],
        ];
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'leave_year_start_month' => '1',
            'working_days' => ['1', '2', '3', '4', '5', '6'],
            'half_working_day' => '6',
            'public_holiday_state' => 'selangor',
            'allow_half_day' => '1',
            'annual_leave_notice_days' => '3',
            'backdate_days' => '7',
            'allow_leave_during_probation' => '0',
            'allow_negative_balance' => '0',
            'max_negative_days' => '',
            'prorate_entitlement' => '1',
            'proration_rounding' => 'nearest-day',
            'resignation_unused_leave' => 'pay-out',
            'share_leave_calendar' => '1',
            'hr_notification_email' => 'hr@example.com',
        ], $overrides);
    }
}
