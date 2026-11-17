<?php

namespace Tests\Feature;

use App\Enums\MissedClockOut;
use App\Enums\Role as RoleName;
use App\Models\AttendanceSetting;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class AttendanceSettingsTest extends TestCase
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
        $default = AttendanceSetting::current();
        $this->assertFalse($default->exists);
        $this->assertSame('09:00', $default->work_start_time);
        $this->assertSame('18:00', $default->work_end_time);
        $this->assertSame(60, $default->break_minutes);
        $this->assertSame(10, $default->late_grace_minutes);
        $this->assertSame(MissedClockOut::FlagForReview, $default->missed_clock_out);
        $this->assertTrue($default->record_location);
        $this->assertFalse($default->geofence_enabled);

        $saved = AttendanceSetting::factory()->create(['work_start_time' => '08:30']);
        $this->assertTrue(AttendanceSetting::current()->is($saved));
        $this->assertSame('08:30', AttendanceSetting::current()->work_start_time);
    }

    public function test_settings_are_identified_by_ulid_and_keep_an_integer_id(): void
    {
        $setting = AttendanceSetting::factory()->create();

        $this->assertIsInt($setting->id);
        $this->assertTrue(Str::isUlid($setting->ulid));
        $this->assertSame($setting->ulid, $setting->getRouteKey());
    }

    public function test_working_minutes_lateness_and_geofence(): void
    {
        $setting = AttendanceSetting::current();
        $this->assertSame(480, $setting->workingMinutesPerDay());

        $this->assertFalse($setting->isLate(CarbonImmutable::parse('2026-11-17 09:10', config('cendana.timezone'))));
        $this->assertTrue($setting->isLate(CarbonImmutable::parse('2026-11-17 09:11', config('cendana.timezone'))));
        $this->assertTrue($setting->isLate(CarbonImmutable::parse('2026-11-17 01:11', 'UTC')));

        $this->assertTrue($setting->isWithinGeofence(1.3521, 103.8198));

        $geofenced = AttendanceSetting::factory()->withGeofence()->make();
        $this->assertTrue($geofenced->isWithinGeofence(3.1580, 101.7120));
        $this->assertFalse($geofenced->isWithinGeofence(3.1390, 101.6869));
    }

    public function test_guests_are_redirected_to_sign_in(): void
    {
        $this->get(route('settings.hadir.general'))->assertRedirect(route('login'));
    }

    public function test_other_roles_are_forbidden_and_do_not_see_the_menu_link(): void
    {
        $admin = User::factory()->create()->assignRole(RoleName::Admin);

        $this->actingAs($admin)->get(route('admin.dashboard'))->assertDontSee(route('settings.hadir.general'));
        $this->actingAs($admin)->get(route('settings.hadir.general'))->assertForbidden();
        $this->actingAs($admin)->put(route('settings.hadir.general.update'), $this->payload())->assertForbidden();
    }

    public function test_super_admin_sees_the_defaults_and_fixed_rules(): void
    {
        $this->actingAs($this->superAdmin)->get(route('admin.dashboard'))->assertSee(route('settings.hadir.general'));

        $this->actingAs($this->superAdmin)->get(route('settings.hadir.general'))
            ->assertOk()
            ->assertSee('name="work_start_time"', false)
            ->assertSee('value="09:00"', false)
            ->assertSee('8 a day and 45 a week')
            ->assertSee(route('settings.leave.general'))
            ->assertSee('Using the default settings until you save.');
    }

    public function test_first_save_creates_the_settings_and_is_audited(): void
    {
        $this->actingAs($this->superAdmin)
            ->put(route('settings.hadir.general.update'), $this->payload([
                'work_start_time' => '08:30',
                'work_end_time' => '17:30',
                'geofence_enabled' => '1',
                'office_latitude' => '3.1578',
                'office_longitude' => '101.7119',
                'geofence_radius_m' => '250',
            ]))
            ->assertRedirect(route('settings.hadir.general'))
            ->assertSessionHas('status', 'Attendance settings were saved.');

        $setting = AttendanceSetting::query()->sole();
        $this->assertSame('08:30', $setting->work_start_time);
        $this->assertTrue($setting->geofence_enabled);
        $this->assertSame('3.157800', $setting->office_latitude);
        $this->assertSame(250, $setting->geofence_radius_m);

        $activity = Activity::query()->whereMorphedTo('subject', $setting)->sole();
        $this->assertSame('Updated attendance settings', $activity->description);
        $this->assertTrue($activity->causer->is($this->superAdmin));
    }

    public function test_saving_again_updates_the_same_row_and_audits_only_the_changes(): void
    {
        $setting = AttendanceSetting::factory()->create();
        Activity::query()->delete();

        $this->actingAs($this->superAdmin)
            ->put(route('settings.hadir.general.update'), $this->payload(['late_grace_minutes' => '15']))
            ->assertRedirect(route('settings.hadir.general'));

        $this->assertDatabaseCount('attendance_settings', 1);
        $this->assertSame(15, $setting->fresh()->late_grace_minutes);
        $this->assertSame(['late_grace_minutes' => 15], Activity::query()->sole()->attribute_changes['attributes']);

        $this->actingAs($this->superAdmin)->get(route('settings.hadir.general'))->assertSee('Last changed by');
    }

    public function test_office_location_is_cleared_when_the_geofence_is_off(): void
    {
        AttendanceSetting::factory()->withGeofence()->create();

        $this->actingAs($this->superAdmin)
            ->put(route('settings.hadir.general.update'), $this->payload(['geofence_enabled' => '0', 'office_latitude' => '3.1', 'office_longitude' => '101.7', 'geofence_radius_m' => '100']));

        $setting = AttendanceSetting::query()->sole();
        $this->assertNull($setting->office_latitude);
        $this->assertNull($setting->office_longitude);
        $this->assertNull($setting->geofence_radius_m);
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    #[DataProvider('invalidSettings')]
    public function test_invalid_settings_are_rejected(array $overrides, string $field): void
    {
        $this->actingAs($this->superAdmin)
            ->from(route('settings.hadir.general'))
            ->put(route('settings.hadir.general.update'), $this->payload($overrides))
            ->assertRedirect(route('settings.hadir.general'))
            ->assertSessionHasErrors($field);

        $this->assertDatabaseCount('attendance_settings', 0);
    }

    /**
     * @return array<string, array{array<string, mixed>, string}>
     */
    public static function invalidSettings(): array
    {
        $geofence = ['geofence_enabled' => '1', 'office_latitude' => '3.1578', 'office_longitude' => '101.7119', 'geofence_radius_m' => '200'];

        return [
            'malformed start time' => [['work_start_time' => '9am'], 'work_start_time'],
            'end before start' => [['work_end_time' => '08:00'], 'work_end_time'],
            'break as long as the day' => [['work_start_time' => '09:00', 'work_end_time' => '10:00', 'break_minutes' => '60'], 'break_minutes'],
            'grace over an hour' => [['late_grace_minutes' => '61'], 'late_grace_minutes'],
            'unknown missed clock-out rule' => [['missed_clock_out' => 'ignore'], 'missed_clock_out'],
            'geofence without latitude' => [[...$geofence, 'office_latitude' => ''], 'office_latitude'],
            'latitude out of range' => [[...$geofence, 'office_latitude' => '91'], 'office_latitude'],
            'radius too small' => [[...$geofence, 'geofence_radius_m' => '10'], 'geofence_radius_m'],
            'geofence without recording location' => [[...$geofence, 'record_location' => '0'], 'record_location'],
        ];
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'work_start_time' => '09:00',
            'work_end_time' => '18:00',
            'break_minutes' => '60',
            'late_grace_minutes' => '10',
            'missed_clock_out' => 'flag-for-review',
            'record_location' => '1',
            'geofence_enabled' => '0',
            'office_latitude' => '',
            'office_longitude' => '',
            'geofence_radius_m' => '',
            'require_selfie' => '0',
        ], $overrides);
    }
}
