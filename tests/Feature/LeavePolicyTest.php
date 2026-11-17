<?php

namespace Tests\Feature;

use App\Enums\LookupCategory;
use App\Enums\Role as RoleName;
use App\Models\LeavePolicy;
use App\Models\LookupItem;
use App\Models\User;
use Database\Seeders\LeavePolicySeeder;
use Database\Seeders\LookupSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class LeavePolicyTest extends TestCase
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

    public function test_guests_are_redirected_to_sign_in(): void
    {
        $this->get(route('settings.leave.policies.index'))->assertRedirect(route('login'));
    }

    public function test_other_roles_are_forbidden_and_do_not_see_the_menu_link(): void
    {
        $admin = User::factory()->create()->assignRole(RoleName::Admin);

        $this->actingAs($admin)->get(route('admin.dashboard'))->assertDontSee(route('settings.leave.policies.index'));
        $this->actingAs($admin)->get(route('settings.leave.policies.index'))->assertForbidden();
        $this->actingAs($admin)->get(route('settings.leave.policies.edit', 'annual'))->assertForbidden();
        $this->actingAs($admin)->put(route('settings.leave.policies.update', 'annual'), $this->payload())->assertForbidden();
    }

    public function test_index_lists_active_leave_types_with_their_policy_summaries(): void
    {
        LeavePolicy::factory()->create(['allow_carry_forward' => true, 'carry_forward_max_days' => 5, 'carry_forward_expiry_months' => 3]);
        LeavePolicy::factory()->create(['leave_type' => 'unpaid', 'is_paid' => false, 'entitlements' => []]);
        LookupItem::query()->inCategory(LookupCategory::LeaveType)->where('code', 'marriage')->update(['is_active' => false]);

        $this->actingAs($this->superAdmin)->get(route('admin.dashboard'))->assertSee(route('settings.leave.policies.index'));

        $this->actingAs($this->superAdmin)->get(route('settings.leave.policies.index'))
            ->assertOk()
            ->assertSee('Annual Leave')
            ->assertSee('8 / 12 / 16 days')
            ->assertSee('Up to 5 days, expires after 3 months')
            ->assertSee('No fixed entitlement')
            ->assertSee('Not set up')
            ->assertSee(route('settings.leave.policies.edit', 'sick'))
            ->assertDontSee('Marriage Leave');
    }

    public function test_edit_returns_404_for_unknown_or_inactive_leave_types(): void
    {
        LookupItem::query()->inCategory(LookupCategory::LeaveType)->where('code', 'marriage')->update(['is_active' => false]);

        $this->actingAs($this->superAdmin)->get(route('settings.leave.policies.edit', 'holiday-on-mars'))->assertNotFound();
        $this->actingAs($this->superAdmin)->get(route('settings.leave.policies.edit', 'marriage'))->assertNotFound();
        $this->actingAs($this->superAdmin)->put(route('settings.leave.policies.update', 'marriage'), $this->payload())->assertNotFound();
        $this->actingAs($this->superAdmin)->get(route('settings.leave.policies.edit', 'sick'))
            ->assertOk()
            ->assertSee('Sick Leave')
            ->assertSee('Not set up yet.');
    }

    public function test_first_save_creates_the_policy_with_sorted_tiers_and_is_audited(): void
    {
        $this->actingAs($this->superAdmin)
            ->put(route('settings.leave.policies.update', 'sick'), $this->payload(['entitlements' => [
                ['min_years' => '5', 'days' => '22'],
                ['min_years' => '', 'days' => ''],
                ['min_years' => '0', 'days' => '14'],
                ['min_years' => '2', 'days' => '18.5'],
            ]]))
            ->assertRedirect(route('settings.leave.policies.index'))
            ->assertSessionHas('status', 'Leave policy was saved.');

        $policy = LeavePolicy::query()->sole();
        $this->assertSame('sick', $policy->leave_type);
        $this->assertSame([['min_years' => 0, 'days' => 14], ['min_years' => 2, 'days' => 18.5], ['min_years' => 5, 'days' => 22]], $policy->entitlements);
        $this->assertTrue($policy->requires_attachment);

        $activity = Activity::query()->whereMorphedTo('subject', $policy)->sole();
        $this->assertSame('Created leave policy', $activity->description);
        $this->assertTrue($activity->causer->is($this->superAdmin));
    }

    public function test_saving_again_updates_the_same_policy_and_clears_carry_forward_when_off(): void
    {
        $policy = LeavePolicy::factory()->create(['allow_carry_forward' => true, 'carry_forward_max_days' => 5, 'carry_forward_expiry_months' => 3]);

        $this->actingAs($this->superAdmin)
            ->put(route('settings.leave.policies.update', 'annual'), $this->payload([
                'allow_carry_forward' => '0',
                'carry_forward_max_days' => '5',
                'carry_forward_expiry_months' => '3',
            ]))
            ->assertRedirect(route('settings.leave.policies.index'));

        $this->assertDatabaseCount('leave_policies', 1);
        $policy->refresh();
        $this->assertFalse($policy->allow_carry_forward);
        $this->assertNull($policy->carry_forward_max_days);
        $this->assertNull($policy->carry_forward_expiry_months);

        $this->actingAs($this->superAdmin)->get(route('settings.leave.policies.edit', 'annual'))->assertSee('Last changed by');
    }

    public function test_carry_forward_and_no_fixed_entitlement_are_saved(): void
    {
        $this->actingAs($this->superAdmin)
            ->put(route('settings.leave.policies.update', 'replacement'), $this->payload([
                'entitlements' => [['min_years' => '', 'days' => '']],
                'allow_carry_forward' => '1',
                'carry_forward_max_days' => '2.5',
                'carry_forward_expiry_months' => '',
            ]));

        $policy = LeavePolicy::for('replacement');
        $this->assertSame([], $policy->entitlements);
        $this->assertSame('2.5', $policy->carry_forward_max_days);
        $this->assertNull($policy->carry_forward_expiry_months);
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    #[DataProvider('invalidPolicies')]
    public function test_invalid_policies_are_rejected(array $overrides, string $field): void
    {
        $this->actingAs($this->superAdmin)
            ->from(route('settings.leave.policies.edit', 'annual'))
            ->put(route('settings.leave.policies.update', 'annual'), $this->payload($overrides))
            ->assertRedirect(route('settings.leave.policies.edit', 'annual'))
            ->assertSessionHasErrors($field);

        $this->assertDatabaseCount('leave_policies', 0);
    }

    /**
     * @return array<string, array{array<string, mixed>, string}>
     */
    public static function invalidPolicies(): array
    {
        return [
            'first tier not from 0 years' => [['entitlements' => [['min_years' => '1', 'days' => '8']]], 'entitlements'],
            'duplicate years' => [['entitlements' => [['min_years' => '0', 'days' => '8'], ['min_years' => '0', 'days' => '12']]], 'entitlements.1.min_years'],
            'days not in half days' => [['entitlements' => [['min_years' => '0', 'days' => '0.3']]], 'entitlements.0.days'],
            'days without years' => [['entitlements' => [['min_years' => '', 'days' => '8']]], 'entitlements.0.min_years'],
            'years over 50' => [['entitlements' => [['min_years' => '0', 'days' => '8'], ['min_years' => '51', 'days' => '12']]], 'entitlements.1.min_years'],
            'carry forward without max days' => [['allow_carry_forward' => '1', 'carry_forward_max_days' => ''], 'carry_forward_max_days'],
            'expiry 13 months' => [['allow_carry_forward' => '1', 'carry_forward_max_days' => '5', 'carry_forward_expiry_months' => '13'], 'carry_forward_expiry_months'],
        ];
    }

    public function test_entitlement_follows_years_of_service(): void
    {
        $annual = LeavePolicy::factory()->make();

        $this->assertSame(8.0, $annual->entitlementFor(1));
        $this->assertSame(12.0, $annual->entitlementFor(2));
        $this->assertSame(16.0, $annual->entitlementFor(10));
        $this->assertNull(LeavePolicy::factory()->make(['entitlements' => []])->entitlementFor(3));
    }

    public function test_seeder_creates_employment_act_defaults_keeps_edits_and_writes_no_audit(): void
    {
        LeavePolicy::factory()->create(['entitlements' => [['min_years' => 0, 'days' => 20]]]);
        LookupItem::query()->inCategory(LookupCategory::LeaveType)->where('code', 'marriage')->delete();
        Activity::query()->delete();

        $this->seed(LeavePolicySeeder::class);

        $this->assertSame(20.0, LeavePolicy::for('annual')->entitlementFor(10));
        $this->assertSame(22.0, LeavePolicy::for('sick')->entitlementFor(5));
        $this->assertSame(98.0, LeavePolicy::for('maternity')->entitlementFor(0));
        $this->assertFalse(LeavePolicy::for('unpaid')->is_paid);
        $this->assertFalse(LeavePolicy::for('marriage')->exists);
        $this->assertSame(8, LeavePolicy::query()->count());
        $this->assertSame(0, Activity::query()->count());
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'entitlements' => [['min_years' => '0', 'days' => '8'], ['min_years' => '2', 'days' => '12'], ['min_years' => '5', 'days' => '16']],
            'is_paid' => '1',
            'requires_attachment' => '1',
            'allow_carry_forward' => '0',
            'carry_forward_max_days' => '',
            'carry_forward_expiry_months' => '',
        ], $overrides);
    }
}
