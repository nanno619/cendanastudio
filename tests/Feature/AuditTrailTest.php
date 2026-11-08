<?php

namespace Tests\Feature;

use App\Enums\Role as RoleName;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class AuditTrailTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app->make(PermissionRegistrar::class)->forgetCachedPermissions();
        foreach (RoleName::cases() as $role) {
            Role::findOrCreate($role->value);
        }

        $this->superAdmin = User::factory()->create(['name' => 'Studio Owner'])->assignRole(RoleName::SuperAdmin);

        // Creating fixtures is itself audited; start each test from an empty trail.
        Activity::query()->delete();
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get(route('administrations.audit-trail.index'))->assertRedirect(route('login'));
    }

    public function test_other_roles_are_forbidden(): void
    {
        $director = User::factory()->create()->assignRole(RoleName::Director);

        $this->actingAs($director)->get(route('administrations.audit-trail.index'))->assertForbidden();
    }

    public function test_super_admin_sees_activity_newest_first(): void
    {
        $this->travelTo(Carbon::parse('2026-11-08 02:00', 'UTC'));
        $this->log('Signed in', 'login', now()->subHour());
        $this->log('Updated their profile', 'updated', now()->subMinutes(5));

        $this->actingAs($this->superAdmin)
            ->get(route('administrations.audit-trail.index'))
            ->assertOk()
            ->assertSeeInOrder(['Updated their profile', 'Signed in'])
            ->assertSee('Studio Owner')
            ->assertSee('2 entries');
    }

    public function test_times_are_shown_in_the_configured_timezone(): void
    {
        config(['cendana.timezone' => 'Asia/Kuala_Lumpur']);
        $this->log('Signed in', 'login', Carbon::parse('2026-11-07 17:30', 'UTC'));

        $this->actingAs($this->superAdmin)
            ->get(route('administrations.audit-trail.index'))
            ->assertSee('01:30')
            ->assertSee('8 November 2026');
    }

    public function test_keyword_matches_description_or_causer_name(): void
    {
        $finance = User::factory()->create(['name' => 'Aminah Finance']);
        $this->log('Signed in', 'login', now(), $finance);
        $this->log('Updated their profile', 'updated', now());

        $this->actingAs($this->superAdmin)
            ->get(route('administrations.audit-trail.index', ['q' => 'Aminah']))
            ->assertSee('Signed in')
            ->assertDontSee('Updated their profile');

        $this->actingAs($this->superAdmin)
            ->get(route('administrations.audit-trail.index', ['q' => 'profile']))
            ->assertSee('Updated their profile')
            ->assertDontSee('Signed in');
    }

    public function test_filters_by_event_and_user(): void
    {
        $sales = User::factory()->create();
        $this->log('Signed in', 'login', now(), $sales);
        $this->log('Signed out', 'logout', now(), $sales);
        $this->log('Updated their profile', 'updated', now());

        $this->actingAs($this->superAdmin)
            ->get(route('administrations.audit-trail.index', ['event' => 'logout']))
            ->assertSee('Signed out')
            ->assertDontSee('Signed in')
            ->assertDontSee('Updated their profile');

        $this->actingAs($this->superAdmin)
            ->get(route('administrations.audit-trail.index', ['user' => $sales->id]))
            ->assertSee('Signed in')
            ->assertSee('Signed out')
            ->assertDontSee('Updated their profile');
    }

    public function test_date_range_uses_local_calendar_days(): void
    {
        config(['cendana.timezone' => 'Asia/Kuala_Lumpur']);
        // 17:30 UTC on the 7th is 01:30 on the 8th in Kuala Lumpur.
        $this->log('Early morning sign-in', 'login', Carbon::parse('2026-11-07 17:30', 'UTC'));

        $this->actingAs($this->superAdmin)
            ->get(route('administrations.audit-trail.index', ['from' => '2026-11-08', 'to' => '2026-11-08']))
            ->assertSee('Early morning sign-in');

        $this->actingAs($this->superAdmin)
            ->get(route('administrations.audit-trail.index', ['to' => '2026-11-07']))
            ->assertDontSee('Early morning sign-in');
    }

    public function test_end_date_before_start_date_is_rejected(): void
    {
        $this->actingAs($this->superAdmin)
            ->get(route('administrations.audit-trail.index', ['from' => '2026-11-08', 'to' => '2026-11-01']))
            ->assertSessionHasErrors('to');
    }

    public function test_empty_and_filtered_states_are_distinguished(): void
    {
        $this->actingAs($this->superAdmin)
            ->get(route('administrations.audit-trail.index'))
            ->assertSee('No activity recorded yet');

        $this->log('Signed in', 'login', now());

        $this->actingAs($this->superAdmin)
            ->get(route('administrations.audit-trail.index', ['q' => 'nothing matches this']))
            ->assertSee('No activity matches these filters.')
            ->assertSee('Clear filters');
    }

    public function test_paginates_twenty_entries_per_page_and_keeps_filters(): void
    {
        foreach (range(1, 21) as $minute) {
            $this->log("Entry {$minute}", 'login', now()->subMinutes($minute));
        }

        $this->actingAs($this->superAdmin)
            ->get(route('administrations.audit-trail.index', ['event' => 'login']))
            ->assertSee('Showing')
            ->assertDontSee('Entry 21')
            ->assertSee(e(route('administrations.audit-trail.index', ['event' => 'login', 'page' => 2])), false);

        $this->actingAs($this->superAdmin)
            ->get(route('administrations.audit-trail.index', ['event' => 'login', 'page' => 2]))
            ->assertSee('Entry 21');
    }

    public function test_entry_detail_is_forbidden_to_other_roles(): void
    {
        $activity = $this->log('Signed in', 'login', now());

        $this->actingAs(User::factory()->create()->assignRole(RoleName::Finance))
            ->get(route('administrations.audit-trail.show', $activity))
            ->assertForbidden();
    }

    public function test_missing_entry_returns_not_found(): void
    {
        $this->actingAs($this->superAdmin)->get(route('administrations.audit-trail.show', 999))->assertNotFound();
    }

    public function test_list_links_each_entry_to_its_detail_page(): void
    {
        $activity = $this->log('Signed in', 'login', now());

        $this->actingAs($this->superAdmin)
            ->get(route('administrations.audit-trail.index'))
            ->assertSee(route('administrations.audit-trail.show', $activity));
    }

    public function test_entry_detail_shows_before_and_after_values_but_hides_secrets(): void
    {
        $subject = User::factory()->create(['name' => 'Aminah Finance']);
        $activity = activity()->causedBy($this->superAdmin)->performedOn($subject)->event('updated')
            ->withChanges([
                'attributes' => ['email' => 'new@demo.test', 'password' => 'new-secret-hash'],
                'old' => ['email' => 'old@demo.test', 'password' => 'old-secret-hash'],
            ])
            ->withProperties(['ip' => '10.0.0.7'])
            ->log("Updated Aminah's account");

        $this->actingAs($this->superAdmin)
            ->get(route('administrations.audit-trail.show', $activity))
            ->assertOk()
            ->assertSee("Updated Aminah's account")
            ->assertSeeInOrder(['old@demo.test', 'new@demo.test'])
            ->assertSee('Changed (value hidden)')
            ->assertDontSee('old-secret-hash')
            ->assertDontSee('new-secret-hash')
            ->assertSee('10.0.0.7')
            ->assertSee('Aminah Finance')
            ->assertSee('Studio Owner');
    }

    public function test_entry_detail_lists_other_activity_on_the_same_record(): void
    {
        $subject = User::factory()->create();
        $other = User::factory()->create();
        $earlier = activity()->causedBy($this->superAdmin)->performedOn($subject)->event('created')->createdAt(now()->subDay())->log('Assigned a role');
        activity()->causedBy($this->superAdmin)->performedOn($other)->event('created')->log('Unrelated change');
        $activity = activity()->causedBy($this->superAdmin)->performedOn($subject)->event('updated')->log('Updated the account');

        $this->actingAs($this->superAdmin)
            ->get(route('administrations.audit-trail.show', $activity))
            ->assertSee('Other activity on this record')
            ->assertSee('Assigned a role')
            ->assertSee(route('administrations.audit-trail.show', $earlier))
            ->assertDontSee('Unrelated change');
    }

    public function test_back_link_returns_to_the_filtered_list(): void
    {
        $activity = $this->log('Signed in', 'login', now());
        $filteredList = route('administrations.audit-trail.index', ['event' => 'login', 'page' => 1]);

        $this->actingAs($this->superAdmin)
            ->from($filteredList)
            ->get(route('administrations.audit-trail.show', $activity))
            ->assertSee('href="'.e($filteredList).'"', false);

        $this->actingAs($this->superAdmin)
            ->from(route('admin.dashboard'))
            ->get(route('administrations.audit-trail.show', $activity))
            ->assertSee('href="'.e(route('administrations.audit-trail.index')).'"', false);
    }

    public function test_menu_is_shown_only_to_super_admin(): void
    {
        $this->actingAs($this->superAdmin)->get(route('admin.dashboard'))
            ->assertSee(route('administrations.audit-trail.index'))
            ->assertSee('Administrations');

        $this->actingAs(User::factory()->create()->assignRole(RoleName::Staff))->get(route('admin.dashboard'))
            ->assertDontSee(route('administrations.audit-trail.index'))
            ->assertDontSee('Administrations');
    }

    private function log(string $description, string $event, Carbon $at, ?User $causer = null): Activity
    {
        return activity()->causedBy($causer ?? $this->superAdmin)->event($event)->createdAt($at)->log($description);
    }
}
