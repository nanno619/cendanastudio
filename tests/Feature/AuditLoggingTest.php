<?php

namespace Tests\Feature;

use App\Enums\Role as RoleName;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Password;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class AuditLoggingTest extends TestCase
{
    use RefreshDatabase;

    private const USER_AGENT = 'Mozilla/5.0 (Macintosh) Chrome/142.0';

    protected function setUp(): void
    {
        parent::setUp();

        $this->app->make(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_sign_in_is_recorded_with_ip_and_browser(): void
    {
        $user = User::factory()->create();
        Activity::query()->delete();

        $this->withHeader('User-Agent', self::USER_AGENT)
            ->post(route('login.store'), ['email' => $user->email, 'password' => 'password']);

        $activity = Activity::query()->where('event', 'login')->sole();
        $this->assertSame('Signed in', $activity->description);
        $this->assertTrue($activity->causer->is($user));
        $this->assertSame('127.0.0.1', $activity->properties['ip']);
        $this->assertSame(self::USER_AGENT, $activity->properties['user_agent']);
    }

    public function test_sign_out_is_recorded(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('logout'));

        $this->assertTrue(Activity::query()->where('event', 'logout')->sole()->causer->is($user));
    }

    public function test_wrong_password_is_recorded_against_the_account_without_a_causer(): void
    {
        $user = User::factory()->create();

        $this->post(route('login.store'), ['email' => $user->email, 'password' => 'not-the-password']);

        $activity = Activity::query()->where('event', 'failed-login')->sole();
        $this->assertSame('Failed sign-in: wrong password', $activity->description);
        $this->assertNull($activity->causer_id);
        $this->assertTrue($activity->subject->is($user));
        $this->assertSame($user->email, $activity->properties['email']);
        $this->assertStringNotContainsString('not-the-password', $activity->toJson());
    }

    public function test_unknown_email_is_recorded_with_the_attempted_address(): void
    {
        $this->post(route('login.store'), ['email' => 'nobody@example.test', 'password' => 'whatever-it-is']);

        $activity = Activity::query()->where('event', 'failed-login')->sole();
        $this->assertSame('Failed sign-in: unknown email', $activity->description);
        $this->assertNull($activity->subject_id);
        $this->assertSame('nobody@example.test', $activity->properties['email']);
    }

    public function test_lockout_is_recorded_once_attempts_run_out(): void
    {
        $user = User::factory()->create();

        foreach (range(1, 6) as $attempt) {
            $this->post(route('login.store'), ['email' => $user->email, 'password' => 'wrong-password']);
        }

        $this->assertSame($user->email, Activity::query()->where('event', 'lockout')->sole()->properties['email']);
    }

    public function test_profile_change_records_old_and_new_values(): void
    {
        $user = User::factory()->create(['name' => 'Old Name']);
        Activity::query()->delete();

        $this->actingAs($user)->put(route('user-profile-information.update'), ['name' => 'New Name', 'email' => $user->email]);

        $activity = Activity::query()->where('event', 'updated')->sole();
        $this->assertSame('Updated user account', $activity->description);
        $this->assertTrue($activity->causer->is($user));
        $this->assertSame(['name' => 'Old Name'], $activity->attribute_changes['old']);
        $this->assertSame(['name' => 'New Name'], $activity->attribute_changes['attributes']);
    }

    public function test_password_change_is_recorded_without_the_hash(): void
    {
        $user = User::factory()->create();
        $oldHash = $user->password;
        Activity::query()->delete();

        $this->actingAs($user)->put(route('user-password.update'), [
            'current_password' => 'password',
            'password' => 'another-secret-pass',
            'password_confirmation' => 'another-secret-pass',
        ]);

        $activity = Activity::query()->where('event', 'updated')->sole();
        $this->assertSame('Changed password', $activity->description);
        $this->assertSame(['password' => '[hidden]'], $activity->attribute_changes['attributes']);
        $this->assertStringNotContainsString($oldHash, $activity->toJson());
        $this->assertStringNotContainsString($user->fresh()->password, $activity->toJson());
    }

    public function test_password_reset_is_recorded_against_the_user(): void
    {
        $user = User::factory()->create();
        Activity::query()->delete();

        $this->post(route('password.update'), [
            'token' => Password::createToken($user),
            'email' => $user->email,
            'password' => 'new-secret-password',
            'password_confirmation' => 'new-secret-password',
        ]);

        $activity = Activity::query()->sole();
        $this->assertSame('password-reset', $activity->event);
        $this->assertTrue($activity->causer->is($user));
        $this->assertStringNotContainsString($user->fresh()->password, $activity->toJson());
    }

    public function test_role_changes_are_recorded_on_the_user(): void
    {
        Role::findOrCreate(RoleName::Finance->value);
        $admin = User::factory()->create();
        $user = User::factory()->create();
        Activity::query()->delete();

        $this->actingAs($admin);
        $user->assignRole(RoleName::Finance);
        $user->removeRole(RoleName::Finance);

        $this->assertSame(
            ['role-assigned' => 'Assigned role Finance', 'role-removed' => 'Removed role Finance'],
            Activity::query()->orderBy('id')->pluck('description', 'event')->all(),
        );
        $this->assertTrue(Activity::query()->where('event', 'role-assigned')->sole()->causer->is($admin));
        $this->assertTrue(Activity::query()->where('event', 'role-assigned')->sole()->subject->is($user));
    }

    public function test_user_creation_and_deletion_are_recorded(): void
    {
        $user = User::factory()->create();
        $user->delete();

        $this->assertSame(
            ['created' => 'Created user account', 'deleted' => 'Deleted user account'],
            Activity::query()->orderBy('id')->pluck('description', 'event')->all(),
        );
    }

    public function test_seeding_users_and_roles_writes_no_audit_entries(): void
    {
        config(['cendana.seed_admin' => ['name' => 'Owner', 'email' => 'owner@example.test', 'password' => 'a-long-secret-pass']]);

        $this->seed(DatabaseSeeder::class);

        $this->assertSame(0, Activity::query()->where('log_name', '!=', 'demo')->count());
    }
}
