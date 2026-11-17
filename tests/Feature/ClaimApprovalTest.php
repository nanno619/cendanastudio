<?php

namespace Tests\Feature;

use App\Enums\Role as RoleName;
use App\Models\ClaimApprovalRoute;
use App\Models\User;
use Database\Seeders\ClaimApprovalRouteSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class ClaimApprovalTest extends TestCase
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
        $this->get(route('settings.claim.approval.index'))->assertRedirect(route('login'));
    }

    public function test_other_roles_are_forbidden_and_do_not_see_the_menu_link(): void
    {
        $admin = User::factory()->create()->assignRole(RoleName::Admin);
        $route = ClaimApprovalRoute::factory()->forRole(RoleName::Staff)->create();
        $levels = [['approver' => 'role:Admin']];

        $this->actingAs($admin)->get(route('admin.dashboard'))->assertDontSee(route('settings.claim.approval.index'));
        $this->actingAs($admin)->get(route('settings.claim.approval.index'))->assertForbidden();
        $this->actingAs($admin)->get(route('settings.claim.approval.routes.create'))->assertForbidden();
        $this->actingAs($admin)->post(route('settings.claim.approval.routes.store'), ['applicant_role' => 'Trainee', 'levels' => $levels])->assertForbidden();
        $this->actingAs($admin)->get(route('settings.claim.approval.routes.edit', $route))->assertForbidden();
        $this->actingAs($admin)->put(route('settings.claim.approval.routes.update', $route), ['levels' => $levels])->assertForbidden();
        $this->actingAs($admin)->delete(route('settings.claim.approval.routes.destroy', $route))->assertForbidden();
    }

    public function test_index_lists_the_default_route_first_with_approvers_and_amounts_in_order(): void
    {
        $approver = User::factory()->create(['name' => 'Siti Approver']);
        ClaimApprovalRoute::factory()->forRole(RoleName::Staff)->create(['levels' => [
            ['type' => 'role', 'value' => 'Project Manager', 'above' => null],
            ['type' => 'user', 'value' => $approver->id, 'above' => '1000.00'],
            ['type' => 'user', 'value' => 999999, 'above' => null],
        ]]);
        ClaimApprovalRoute::factory()->create();

        $this->actingAs($this->superAdmin)->get(route('admin.dashboard'))->assertSee(route('settings.claim.approval.index'));

        $this->actingAs($this->superAdmin)->get(route('settings.claim.approval.index'))
            ->assertOk()
            ->assertSeeInOrder(['Everyone else', 'Finance', 'Staff', 'Project Manager', 'Siti Approver', 'above RM 1,000.00', 'User no longer exists'])
            ->assertDontSee('Without a default route');
    }

    public function test_index_warns_when_there_is_no_default_route(): void
    {
        $this->actingAs($this->superAdmin)->get(route('settings.claim.approval.index'))
            ->assertOk()
            ->assertSee('Without a default route, roles without their own route go to Super Admin.');
    }

    public function test_create_offers_only_roles_without_a_route(): void
    {
        ClaimApprovalRoute::factory()->create();
        ClaimApprovalRoute::factory()->forRole(RoleName::Staff)->create();

        $this->actingAs($this->superAdmin)->get(route('settings.claim.approval.routes.create'))
            ->assertOk()
            ->assertSee('<option value="Trainee"', false)
            ->assertDontSee('<option value="Staff"', false)
            ->assertDontSee('Everyone else (default)')
            ->assertSee('<option value="user:'.$this->superAdmin->id.'"', false);
    }

    public function test_storing_a_route_drops_blank_levels_keeps_amounts_and_is_audited(): void
    {
        $approver = User::factory()->create();

        $this->actingAs($this->superAdmin)
            ->post(route('settings.claim.approval.routes.store'), [
                'applicant_role' => 'Staff',
                'levels' => [
                    ['approver' => 'role:Project Manager', 'above' => ''],
                    ['approver' => '', 'above' => '50'],
                    ['approver' => 'user:'.$approver->id, 'above' => '500'],
                ],
            ])
            ->assertRedirect(route('settings.claim.approval.index'))
            ->assertSessionHas('status', 'Approval route was saved.');

        $route = ClaimApprovalRoute::query()->sole();
        $this->assertSame(RoleName::Staff, $route->applicant_role);
        $this->assertSame([
            ['type' => 'role', 'value' => 'Project Manager', 'above' => null],
            ['type' => 'user', 'value' => $approver->id, 'above' => '500.00'],
        ], $route->levels);

        $activity = Activity::query()->whereMorphedTo('subject', $route)->sole();
        $this->assertSame('Created claim approval route', $activity->description);
        $this->assertTrue($activity->causer->is($this->superAdmin));
    }

    public function test_a_default_route_can_be_created_once(): void
    {
        $this->actingAs($this->superAdmin)->get(route('settings.claim.approval.routes.create'))->assertSee('Everyone else (default)');

        $this->actingAs($this->superAdmin)
            ->post(route('settings.claim.approval.routes.store'), ['applicant_role' => '', 'levels' => [['approver' => 'role:Finance']]])
            ->assertRedirect(route('settings.claim.approval.index'));
        $this->assertTrue(ClaimApprovalRoute::query()->sole()->isDefault());

        $this->actingAs($this->superAdmin)
            ->from(route('settings.claim.approval.routes.create'))
            ->post(route('settings.claim.approval.routes.store'), ['applicant_role' => '', 'levels' => [['approver' => 'role:Director']]])
            ->assertSessionHasErrors('applicant_role');
        $this->assertDatabaseCount('claim_approval_routes', 1);
    }

    public function test_a_role_can_have_only_one_route(): void
    {
        ClaimApprovalRoute::factory()->forRole(RoleName::Staff)->create();

        $this->actingAs($this->superAdmin)
            ->from(route('settings.claim.approval.routes.create'))
            ->post(route('settings.claim.approval.routes.store'), ['applicant_role' => 'Staff', 'levels' => [['approver' => 'role:Admin']]])
            ->assertSessionHasErrors('applicant_role');

        $this->assertDatabaseCount('claim_approval_routes', 1);
    }

    public function test_updating_changes_levels_but_never_the_role(): void
    {
        $route = ClaimApprovalRoute::factory()->forRole(RoleName::Staff)->create([
            'levels' => [['type' => 'role', 'value' => 'Finance', 'above' => null], ['type' => 'role', 'value' => 'Director', 'above' => '2000.00']],
        ]);

        $this->actingAs($this->superAdmin)->get(route('settings.claim.approval.routes.edit', $route))
            ->assertOk()
            ->assertSee('<option value="role:Finance" selected', false)
            ->assertSee('value="2000.00"', false);

        $this->actingAs($this->superAdmin)
            ->put(route('settings.claim.approval.routes.update', $route), [
                'applicant_role' => 'Director',
                'levels' => [['approver' => 'role:Director'], ['approver' => 'role:Finance', 'above' => '99.5']],
            ])
            ->assertRedirect(route('settings.claim.approval.index'));

        $route->refresh();
        $this->assertSame(RoleName::Staff, $route->applicant_role);
        $this->assertSame([
            ['type' => 'role', 'value' => 'Director', 'above' => null],
            ['type' => 'role', 'value' => 'Finance', 'above' => '99.50'],
        ], $route->levels);
    }

    public function test_role_routes_can_be_deleted_but_the_default_cannot(): void
    {
        $default = ClaimApprovalRoute::factory()->create();
        $staff = ClaimApprovalRoute::factory()->forRole(RoleName::Staff)->create();

        $this->actingAs($this->superAdmin)->delete(route('settings.claim.approval.routes.destroy', $default))->assertForbidden();

        $this->actingAs($this->superAdmin)->delete(route('settings.claim.approval.routes.destroy', $staff))
            ->assertRedirect(route('settings.claim.approval.index'))
            ->assertSessionHas('status', 'Approval route was deleted.');

        $this->assertModelMissing($staff);
        $this->assertModelExists($default);
        $this->assertSame('Deleted claim approval route', Activity::query()->where('event', 'deleted')->sole()->description);
    }

    /**
     * @param  list<array<string, string>>  $levels
     */
    #[DataProvider('invalidLevels')]
    public function test_invalid_levels_are_rejected(array $levels, string $field): void
    {
        $this->actingAs($this->superAdmin)
            ->from(route('settings.claim.approval.routes.create'))
            ->post(route('settings.claim.approval.routes.store'), ['applicant_role' => 'Staff', 'levels' => $levels])
            ->assertRedirect(route('settings.claim.approval.routes.create'))
            ->assertSessionHasErrors($field);

        $this->assertDatabaseCount('claim_approval_routes', 0);
    }

    /**
     * @return array<string, array{list<array<string, string>>, string}>
     */
    public static function invalidLevels(): array
    {
        $level = fn (string $approver, string $above = ''): array => ['approver' => $approver, 'above' => $above];

        return [
            'no levels' => [[$level(''), $level('')], 'levels'],
            'six levels' => [array_map($level, ['role:Admin', 'role:Director', 'role:Finance', 'role:Staff', 'role:Sales', 'role:Support']), 'levels'],
            'unknown role' => [[$level('role:Astronaut')], 'levels.0.approver'],
            'missing user' => [[$level('user:999999')], 'levels.0.approver'],
            'duplicate approver' => [[$level('role:Admin'), $level('role:Admin')], 'levels.1.approver'],
            'amount on level 1' => [[$level('role:Admin', '100')], 'levels.0.above'],
            'zero amount' => [[$level('role:Admin'), $level('role:Director', '0')], 'levels.1.above'],
            'amount with three decimals' => [[$level('role:Admin'), $level('role:Director', '10.125')], 'levels.1.above'],
        ];
    }

    public function test_levels_for_skips_levels_whose_amount_the_claim_does_not_exceed(): void
    {
        $route = ClaimApprovalRoute::factory()->make(['levels' => [
            ['type' => 'role', 'value' => 'Project Manager', 'above' => null],
            ['type' => 'role', 'value' => 'Finance', 'above' => '500.00'],
            ['type' => 'role', 'value' => 'Director', 'above' => '5000.00'],
        ]]);

        $this->assertSame(['Project Manager'], array_column($route->levelsFor('500.00'), 'value'));
        $this->assertSame(['Project Manager', 'Finance'], array_column($route->levelsFor(500.01), 'value'));
        $this->assertSame(['Project Manager', 'Finance', 'Director'], array_column($route->levelsFor('8000'), 'value'));
    }

    public function test_for_role_falls_back_to_the_default_route(): void
    {
        $this->assertNull(ClaimApprovalRoute::forRole(RoleName::Staff));

        $default = ClaimApprovalRoute::factory()->create();
        $staff = ClaimApprovalRoute::factory()->forRole(RoleName::Staff)->create();

        $this->assertTrue(ClaimApprovalRoute::forRole(RoleName::Staff)->is($staff));
        $this->assertTrue(ClaimApprovalRoute::forRole(RoleName::Director)->is($default));
    }

    public function test_seeder_creates_the_default_finance_route_once_and_writes_no_audit(): void
    {
        $this->seed(ClaimApprovalRouteSeeder::class);
        $default = ClaimApprovalRoute::query()->sole();
        $this->assertSame([['type' => 'role', 'value' => 'Finance', 'above' => null]], $default->levels);

        $default->update(['levels' => [['type' => 'role', 'value' => 'Director', 'above' => null]]]);
        Activity::query()->delete();
        $this->seed(ClaimApprovalRouteSeeder::class);

        $this->assertSame([['type' => 'role', 'value' => 'Director', 'above' => null]], ClaimApprovalRoute::query()->sole()->levels);
        $this->assertSame(0, Activity::query()->count());
    }
}
