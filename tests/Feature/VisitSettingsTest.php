<?php

namespace Tests\Feature;

use App\Enums\Role as RoleName;
use App\Models\ClaimSetting;
use App\Models\User;
use App\Models\VisitSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class VisitSettingsTest extends TestCase
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
        $default = VisitSetting::current();
        $this->assertFalse($default->exists);
        $this->assertTrue($default->visits_enabled);
        $this->assertSame([RoleName::ProjectManager, RoleName::Consultant, RoleName::Support, RoleName::Sales], $default->allowed_roles->all());
        $this->assertTrue($default->counts_as_attendance);
        $this->assertFalse($default->requires_approval);
        $this->assertNull($default->approver_role);

        $saved = VisitSetting::factory()->create(['require_photo' => true]);
        $this->assertTrue(VisitSetting::current()->is($saved));
        $this->assertTrue(VisitSetting::current()->require_photo);
    }

    public function test_settings_are_identified_by_ulid_and_keep_an_integer_id(): void
    {
        $setting = VisitSetting::factory()->create();

        $this->assertIsInt($setting->id);
        $this->assertTrue(Str::isUlid($setting->ulid));
        $this->assertSame($setting->ulid, $setting->getRouteKey());
    }

    public function test_allows_any_of_checks_roles_and_the_on_switch(): void
    {
        $setting = VisitSetting::current();

        $this->assertTrue($setting->allowsAnyOf([RoleName::Staff, RoleName::Sales]));
        $this->assertFalse($setting->allowsAnyOf([RoleName::Staff]));
        $this->assertTrue($setting->allowsAnyOf([RoleName::SuperAdmin]));

        $setting->visits_enabled = false;
        $this->assertFalse($setting->allowsAnyOf([RoleName::Sales, RoleName::SuperAdmin]));
    }

    public function test_guests_are_redirected_to_sign_in(): void
    {
        $this->get(route('settings.hadir.visits'))->assertRedirect(route('login'));
    }

    public function test_other_roles_are_forbidden_and_do_not_see_the_menu_link(): void
    {
        $admin = User::factory()->create()->assignRole(RoleName::Admin);

        $this->actingAs($admin)->get(route('admin.dashboard'))->assertDontSee(route('settings.hadir.visits'));
        $this->actingAs($admin)->get(route('settings.hadir.visits'))->assertForbidden();
        $this->actingAs($admin)->put(route('settings.hadir.visits.update'), $this->payload())->assertForbidden();
    }

    public function test_super_admin_sees_the_defaults_and_the_mileage_notice(): void
    {
        $this->actingAs($this->superAdmin)->get(route('admin.dashboard'))->assertSee(route('settings.hadir.visits'));

        $this->actingAs($this->superAdmin)->get(route('settings.hadir.visits'))
            ->assertOk()
            ->assertSee('name="allowed_roles[]" value="Sales" class="mt-0.5" checked', false)
            ->assertDontSee('value="Super Admin" class="mt-0.5"', false)
            ->assertSee('Mileage claims are off in')
            ->assertSee(route('settings.claim.general'))
            ->assertSee('Using the default settings until you save.');

        ClaimSetting::factory()->create(['mileage_enabled' => true, 'mileage_rate' => '0.60']);

        $this->actingAs($this->superAdmin)->get(route('settings.hadir.visits'))->assertDontSee('Mileage claims are off in');
    }

    public function test_first_save_creates_the_settings_in_role_order_and_is_audited(): void
    {
        $this->actingAs($this->superAdmin)
            ->put(route('settings.hadir.visits.update'), $this->payload([
                'allowed_roles' => ['Sales', 'Director'],
                'requires_approval' => '1',
                'approver_role' => 'Project Manager',
            ]))
            ->assertRedirect(route('settings.hadir.visits'))
            ->assertSessionHas('status', 'Visit settings were saved.');

        $setting = VisitSetting::query()->sole();
        $this->assertSame([RoleName::Director, RoleName::Sales], $setting->allowed_roles->all());
        $this->assertSame(RoleName::ProjectManager, $setting->approver_role);

        $activity = Activity::query()->whereMorphedTo('subject', $setting)->sole();
        $this->assertSame('Updated visit settings', $activity->description);
        $this->assertTrue($activity->causer->is($this->superAdmin));
    }

    public function test_saving_again_updates_the_same_row_and_audits_only_the_changes(): void
    {
        $setting = VisitSetting::factory()->create();
        Activity::query()->delete();

        $this->actingAs($this->superAdmin)
            ->put(route('settings.hadir.visits.update'), $this->payload(['require_photo' => '1']))
            ->assertRedirect(route('settings.hadir.visits'));

        $this->assertDatabaseCount('visit_settings', 1);
        $this->assertTrue($setting->fresh()->require_photo);
        $this->assertSame(['require_photo' => true], Activity::query()->sole()->attribute_changes['attributes']);

        $this->actingAs($this->superAdmin)->get(route('settings.hadir.visits'))->assertSee('Last changed by');
    }

    public function test_approver_is_cleared_when_approval_is_off_and_roles_may_be_empty_when_visits_are_off(): void
    {
        $this->actingAs($this->superAdmin)
            ->put(route('settings.hadir.visits.update'), $this->payload([
                'visits_enabled' => '0',
                'allowed_roles' => null,
                'requires_approval' => '0',
                'approver_role' => 'Director',
            ]))
            ->assertSessionHasNoErrors();

        $setting = VisitSetting::query()->sole();
        $this->assertFalse($setting->visits_enabled);
        $this->assertTrue($setting->allowed_roles->isEmpty());
        $this->assertNull($setting->approver_role);
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    #[DataProvider('invalidSettings')]
    public function test_invalid_settings_are_rejected(array $overrides, string $field): void
    {
        $this->actingAs($this->superAdmin)
            ->from(route('settings.hadir.visits'))
            ->put(route('settings.hadir.visits.update'), $this->payload($overrides))
            ->assertRedirect(route('settings.hadir.visits'))
            ->assertSessionHasErrors($field);

        $this->assertDatabaseCount('visit_settings', 0);
    }

    /**
     * @return array<string, array{array<string, mixed>, string}>
     */
    public static function invalidSettings(): array
    {
        return [
            'no roles while visits are on' => [['allowed_roles' => null], 'allowed_roles'],
            'unknown role' => [['allowed_roles' => ['Astronaut']], 'allowed_roles.0'],
            'duplicate role' => [['allowed_roles' => ['Sales', 'Sales']], 'allowed_roles.1'],
            'approval without an approver' => [['requires_approval' => '1', 'approver_role' => ''], 'approver_role'],
            'switch not a boolean' => [['require_photo' => 'maybe'], 'require_photo'],
        ];
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return array_filter(array_merge([
            'visits_enabled' => '1',
            'allowed_roles' => ['Project Manager', 'Consultant', 'Support', 'Sales'],
            'counts_as_attendance' => '1',
            'require_client' => '1',
            'require_purpose' => '1',
            'require_photo' => '0',
            'requires_approval' => '0',
            'approver_role' => '',
            'suggest_mileage_claim' => '0',
        ], $overrides), fn ($value): bool => $value !== null);
    }
}
