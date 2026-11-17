<?php

namespace Tests\Feature;

use App\Enums\Role as RoleName;
use App\Models\LeaveApprovalRoute;
use App\Models\LeaveSetting;
use App\Models\User;
use Database\Seeders\LeaveApprovalRouteSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class LeaveApprovalTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app->make(PermissionRegistrar::class)->forgetCachedPermissions();
        Role::findOrCreate(RoleName::SuperAdmin->value);
        Role::findOrCreate(RoleName::Admin->value);
        $this->superAdmin = User::factory()->create(['name' => 'Aisyah Super'])->assignRole(RoleName::SuperAdmin);
    }

    public function test_guests_are_redirected_to_sign_in(): void
    {
        $this->get(route('settings.leave.approval.index'))->assertRedirect(route('login'));
    }

    public function test_other_roles_are_forbidden_and_do_not_see_the_menu_link(): void
    {
        $admin = User::factory()->create()->assignRole(RoleName::Admin);
        $route = LeaveApprovalRoute::factory()->forRole(RoleName::Staff)->create();

        $this->actingAs($admin)->get(route('admin.dashboard'))->assertDontSee(route('settings.leave.approval.index'));
        $this->actingAs($admin)->get(route('settings.leave.approval.index'))->assertForbidden();
        $this->actingAs($admin)->get(route('settings.leave.approval.routes.create'))->assertForbidden();
        $this->actingAs($admin)->post(route('settings.leave.approval.routes.store'), ['applicant_role' => 'Trainee', 'levels' => ['role:Admin']])->assertForbidden();
        $this->actingAs($admin)->get(route('settings.leave.approval.routes.edit', $route))->assertForbidden();
        $this->actingAs($admin)->put(route('settings.leave.approval.routes.update', $route), ['levels' => ['role:Admin']])->assertForbidden();
        $this->actingAs($admin)->delete(route('settings.leave.approval.routes.destroy', $route))->assertForbidden();
        $this->actingAs($admin)->put(route('settings.leave.approval.cancellation'), ['cancellation_requires_approval' => '0'])->assertForbidden();
    }

    public function test_index_lists_the_default_route_first_with_approvers_in_order(): void
    {
        $approver = User::factory()->create(['name' => 'Siti Approver']);
        LeaveApprovalRoute::factory()->forRole(RoleName::Staff)->create(['levels' => [
            ['type' => 'role', 'value' => 'Project Manager'],
            ['type' => 'user', 'value' => $approver->id],
            ['type' => 'user', 'value' => 999999],
        ]]);
        LeaveApprovalRoute::factory()->create();

        $this->actingAs($this->superAdmin)->get(route('admin.dashboard'))->assertSee(route('settings.leave.approval.index'));

        $this->actingAs($this->superAdmin)->get(route('settings.leave.approval.index'))
            ->assertOk()
            ->assertSeeInOrder(['Everyone else', 'Admin', 'Staff', 'Project Manager', 'Siti Approver', 'User no longer exists'])
            ->assertDontSee('Without a default route');
    }

    public function test_index_warns_when_there_is_no_default_route(): void
    {
        $this->actingAs($this->superAdmin)->get(route('settings.leave.approval.index'))
            ->assertOk()
            ->assertSee('Without a default route, roles without their own route go to Super Admin.');
    }

    public function test_create_offers_only_roles_without_a_route(): void
    {
        LeaveApprovalRoute::factory()->create();
        LeaveApprovalRoute::factory()->forRole(RoleName::Staff)->create();

        $this->actingAs($this->superAdmin)->get(route('settings.leave.approval.routes.create'))
            ->assertOk()
            ->assertSee('<option value="Trainee"', false)
            ->assertDontSee('<option value="Staff"', false)
            ->assertDontSee('Everyone else (default)')
            ->assertSee('<option value="user:'.$this->superAdmin->id.'"', false);
    }

    public function test_storing_a_route_drops_blank_levels_and_is_audited(): void
    {
        $approver = User::factory()->create();

        $this->actingAs($this->superAdmin)
            ->post(route('settings.leave.approval.routes.store'), [
                'applicant_role' => 'Staff',
                'levels' => ['role:Project Manager', '', 'user:'.$approver->id, '', ''],
            ])
            ->assertRedirect(route('settings.leave.approval.index'))
            ->assertSessionHas('status', 'Approval route was saved.');

        $route = LeaveApprovalRoute::query()->sole();
        $this->assertSame(RoleName::Staff, $route->applicant_role);
        $this->assertSame([['type' => 'role', 'value' => 'Project Manager'], ['type' => 'user', 'value' => $approver->id]], $route->levels);

        $activity = Activity::query()->whereMorphedTo('subject', $route)->sole();
        $this->assertSame('Created leave approval route', $activity->description);
        $this->assertTrue($activity->causer->is($this->superAdmin));
    }

    public function test_a_default_route_can_be_created_once(): void
    {
        $this->actingAs($this->superAdmin)->get(route('settings.leave.approval.routes.create'))->assertSee('Everyone else (default)');

        $this->actingAs($this->superAdmin)
            ->post(route('settings.leave.approval.routes.store'), ['applicant_role' => '', 'levels' => ['role:Admin']])
            ->assertRedirect(route('settings.leave.approval.index'));
        $this->assertTrue(LeaveApprovalRoute::query()->sole()->isDefault());

        $this->actingAs($this->superAdmin)
            ->from(route('settings.leave.approval.routes.create'))
            ->post(route('settings.leave.approval.routes.store'), ['applicant_role' => '', 'levels' => ['role:Director']])
            ->assertSessionHasErrors('applicant_role');
        $this->assertDatabaseCount('leave_approval_routes', 1);
    }

    public function test_a_role_can_have_only_one_route(): void
    {
        LeaveApprovalRoute::factory()->forRole(RoleName::Staff)->create();

        $this->actingAs($this->superAdmin)
            ->from(route('settings.leave.approval.routes.create'))
            ->post(route('settings.leave.approval.routes.store'), ['applicant_role' => 'Staff', 'levels' => ['role:Admin']])
            ->assertSessionHasErrors('applicant_role');

        $this->assertDatabaseCount('leave_approval_routes', 1);
    }

    public function test_updating_changes_levels_but_never_the_role(): void
    {
        $route = LeaveApprovalRoute::factory()->forRole(RoleName::Staff)->create();

        $this->actingAs($this->superAdmin)->get(route('settings.leave.approval.routes.edit', $route))
            ->assertOk()
            ->assertSee('<option value="role:Admin" selected', false);

        $this->actingAs($this->superAdmin)
            ->put(route('settings.leave.approval.routes.update', $route), ['applicant_role' => 'Director', 'levels' => ['role:Director', 'role:Admin']])
            ->assertRedirect(route('settings.leave.approval.index'));

        $route->refresh();
        $this->assertSame(RoleName::Staff, $route->applicant_role);
        $this->assertSame([['type' => 'role', 'value' => 'Director'], ['type' => 'role', 'value' => 'Admin']], $route->levels);
    }

    public function test_role_routes_can_be_deleted_but_the_default_cannot(): void
    {
        $default = LeaveApprovalRoute::factory()->create();
        $staff = LeaveApprovalRoute::factory()->forRole(RoleName::Staff)->create();

        $this->actingAs($this->superAdmin)->delete(route('settings.leave.approval.routes.destroy', $default))->assertForbidden();

        $this->actingAs($this->superAdmin)->delete(route('settings.leave.approval.routes.destroy', $staff))
            ->assertRedirect(route('settings.leave.approval.index'))
            ->assertSessionHas('status', 'Approval route was deleted.');

        $this->assertModelMissing($staff);
        $this->assertModelExists($default);
        $this->assertSame('Deleted leave approval route', Activity::query()->where('event', 'deleted')->sole()->description);
    }

    /**
     * @param  list<string>  $levels
     */
    #[DataProvider('invalidLevels')]
    public function test_invalid_levels_are_rejected(array $levels, string $field): void
    {
        $this->actingAs($this->superAdmin)
            ->from(route('settings.leave.approval.routes.create'))
            ->post(route('settings.leave.approval.routes.store'), ['applicant_role' => 'Staff', 'levels' => $levels])
            ->assertRedirect(route('settings.leave.approval.routes.create'))
            ->assertSessionHasErrors($field);

        $this->assertDatabaseCount('leave_approval_routes', 0);
    }

    /**
     * @return array<string, array{list<string>, string}>
     */
    public static function invalidLevels(): array
    {
        return [
            'no levels' => [['', ''], 'levels'],
            'six levels' => [['role:Admin', 'role:Director', 'role:Finance', 'role:Staff', 'role:Sales', 'role:Support'], 'levels'],
            'unknown role' => [['role:Astronaut'], 'levels.0'],
            'missing user' => [['user:999999'], 'levels.0'],
            'duplicate level' => [['role:Admin', 'role:Admin'], 'levels.1'],
            'malformed value' => [['Admin'], 'levels.0'],
        ];
    }

    public function test_cancellation_rule_is_saved_on_leave_settings_and_audited(): void
    {
        $this->actingAs($this->superAdmin)->get(route('settings.leave.approval.index'))
            ->assertSee('name="cancellation_requires_approval" value="1"', false);

        $this->actingAs($this->superAdmin)
            ->put(route('settings.leave.approval.cancellation'), ['cancellation_requires_approval' => '0'])
            ->assertRedirect(route('settings.leave.approval.index'))
            ->assertSessionHas('status', 'Cancellation rule was saved.');

        $setting = LeaveSetting::query()->sole();
        $this->assertFalse($setting->cancellation_requires_approval);
        $this->assertSame(3, $setting->annual_leave_notice_days);
        $this->assertSame('Updated leave settings', Activity::query()->whereMorphedTo('subject', $setting)->sole()->description);
    }

    public function test_for_role_falls_back_to_the_default_route(): void
    {
        $this->assertNull(LeaveApprovalRoute::forRole(RoleName::Staff));

        $default = LeaveApprovalRoute::factory()->create();
        $staff = LeaveApprovalRoute::factory()->forRole(RoleName::Staff)->create();

        $this->assertTrue(LeaveApprovalRoute::forRole(RoleName::Staff)->is($staff));
        $this->assertTrue(LeaveApprovalRoute::forRole(RoleName::Director)->is($default));
    }

    public function test_seeder_creates_the_default_admin_route_once_and_writes_no_audit(): void
    {
        $this->seed(LeaveApprovalRouteSeeder::class);
        $default = LeaveApprovalRoute::query()->sole();
        $this->assertSame([['type' => 'role', 'value' => 'Admin']], $default->levels);

        $default->update(['levels' => [['type' => 'role', 'value' => 'Director']]]);
        Activity::query()->delete();
        $this->seed(LeaveApprovalRouteSeeder::class);

        $this->assertSame([['type' => 'role', 'value' => 'Director']], LeaveApprovalRoute::query()->sole()->levels);
        $this->assertSame(0, Activity::query()->count());
    }
}
