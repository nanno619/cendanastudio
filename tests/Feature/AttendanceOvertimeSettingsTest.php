<?php

namespace Tests\Feature;

use App\Enums\OvertimeCapture;
use App\Enums\OvertimeRounding;
use App\Enums\Role as RoleName;
use App\Models\AttendanceOvertimeSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class AttendanceOvertimeSettingsTest extends TestCase
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
        $default = AttendanceOvertimeSetting::current();
        $this->assertFalse($default->exists);
        $this->assertSame(OvertimeCapture::PreApproval, $default->capture_method);
        $this->assertSame(30, $default->minimum_minutes);
        $this->assertSame(OvertimeRounding::DownToQuarterHour, $default->rounding);
        $this->assertTrue($default->allow_on_rest_days);
        $this->assertSame(RoleName::Admin, $default->approver_role);

        $saved = AttendanceOvertimeSetting::factory()->create(['minimum_minutes' => 60]);
        $this->assertTrue(AttendanceOvertimeSetting::current()->is($saved));
        $this->assertSame(60, AttendanceOvertimeSetting::current()->minimum_minutes);
    }

    public function test_settings_are_identified_by_ulid_and_keep_an_integer_id(): void
    {
        $setting = AttendanceOvertimeSetting::factory()->create();

        $this->assertIsInt($setting->id);
        $this->assertTrue(Str::isUlid($setting->ulid));
        $this->assertSame($setting->ulid, $setting->getRouteKey());
    }

    public function test_countable_minutes_apply_the_minimum_then_round_down(): void
    {
        $setting = AttendanceOvertimeSetting::current();

        $this->assertSame(0, $setting->countableMinutes(29));
        $this->assertSame(30, $setting->countableMinutes(30));
        $this->assertSame(45, $setting->countableMinutes(59));

        $setting->fill(['minimum_minutes' => 0, 'rounding' => OvertimeRounding::Exact]);
        $this->assertSame(7, $setting->countableMinutes(7));
        $this->assertSame(0, $setting->countableMinutes(-5));

        $setting->rounding = OvertimeRounding::DownToHour;
        $this->assertSame(60, $setting->countableMinutes(119));
    }

    public function test_guests_are_redirected_to_sign_in(): void
    {
        $this->get(route('settings.hadir.overtime'))->assertRedirect(route('login'));
    }

    public function test_other_roles_are_forbidden_and_do_not_see_the_menu_link(): void
    {
        $admin = User::factory()->create()->assignRole(RoleName::Admin);

        $this->actingAs($admin)->get(route('admin.dashboard'))->assertDontSee(route('settings.hadir.overtime'));
        $this->actingAs($admin)->get(route('settings.hadir.overtime'))->assertForbidden();
        $this->actingAs($admin)->put(route('settings.hadir.overtime.update'), $this->payload())->assertForbidden();
    }

    public function test_super_admin_sees_the_defaults_and_links_to_payroll_overtime(): void
    {
        $this->actingAs($this->superAdmin)->get(route('admin.dashboard'))->assertSee(route('settings.hadir.overtime'));

        $this->actingAs($this->superAdmin)->get(route('settings.hadir.overtime'))
            ->assertOk()
            ->assertSee('name="capture_method"', false)
            ->assertSee('value="30"', false)
            ->assertSee(route('settings.payroll.overtime'))
            ->assertSee(route('settings.hadir.general'))
            ->assertSee('Using the default settings until you save.');
    }

    public function test_first_save_creates_the_settings_and_is_audited(): void
    {
        $this->actingAs($this->superAdmin)
            ->put(route('settings.hadir.overtime.update'), $this->payload(['capture_method' => 'clock-records', 'approver_role' => 'Project Manager']))
            ->assertRedirect(route('settings.hadir.overtime'))
            ->assertSessionHas('status', 'Overtime settings were saved.');

        $setting = AttendanceOvertimeSetting::query()->sole();
        $this->assertSame(OvertimeCapture::ClockRecords, $setting->capture_method);
        $this->assertSame(RoleName::ProjectManager, $setting->approver_role);

        $activity = Activity::query()->whereMorphedTo('subject', $setting)->sole();
        $this->assertSame('Updated attendance overtime settings', $activity->description);
        $this->assertTrue($activity->causer->is($this->superAdmin));
    }

    public function test_saving_again_updates_the_same_row_and_audits_only_the_changes(): void
    {
        $setting = AttendanceOvertimeSetting::factory()->create();
        Activity::query()->delete();

        $this->actingAs($this->superAdmin)
            ->put(route('settings.hadir.overtime.update'), $this->payload(['rounding' => 'down-30']))
            ->assertRedirect(route('settings.hadir.overtime'));

        $this->assertDatabaseCount('attendance_overtime_settings', 1);
        $this->assertSame(OvertimeRounding::DownToHalfHour, $setting->fresh()->rounding);
        $this->assertSame(['rounding' => 'down-30'], Activity::query()->sole()->attribute_changes['attributes']);

        $this->actingAs($this->superAdmin)->get(route('settings.hadir.overtime'))->assertSee('Last changed by');
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    #[DataProvider('invalidSettings')]
    public function test_invalid_settings_are_rejected(array $overrides, string $field): void
    {
        $this->actingAs($this->superAdmin)
            ->from(route('settings.hadir.overtime'))
            ->put(route('settings.hadir.overtime.update'), $this->payload($overrides))
            ->assertRedirect(route('settings.hadir.overtime'))
            ->assertSessionHasErrors($field);

        $this->assertDatabaseCount('attendance_overtime_settings', 0);
    }

    /**
     * @return array<string, array{array<string, mixed>, string}>
     */
    public static function invalidSettings(): array
    {
        return [
            'unknown capture method' => [['capture_method' => 'guess'], 'capture_method'],
            'minimum over two hours' => [['minimum_minutes' => '121'], 'minimum_minutes'],
            'unknown rounding' => [['rounding' => 'up-15'], 'rounding'],
            'missing approver' => [['approver_role' => ''], 'approver_role'],
            'unknown approver role' => [['approver_role' => 'Boss'], 'approver_role'],
        ];
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'capture_method' => 'pre-approval',
            'minimum_minutes' => '30',
            'rounding' => 'down-15',
            'allow_on_rest_days' => '1',
            'approver_role' => 'Admin',
        ], $overrides);
    }
}
