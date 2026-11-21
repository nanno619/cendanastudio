<?php

namespace Tests\Feature;

use App\Enums\ModuleKey;
use App\Enums\ProjectStatus;
use App\Enums\Role as RoleName;
use App\Models\Client;
use App\Models\EmergencyContact;
use App\Models\Employee;
use App\Models\Module;
use App\Models\Project;
use App\Models\User;
use Database\Seeders\LookupSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class ProjectsTest extends TestCase
{
    use RefreshDatabase;

    private Client $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app->make(PermissionRegistrar::class)->forgetCachedPermissions();
        foreach (RoleName::cases() as $role) {
            Role::findOrCreate($role->value);
        }
        $this->seed(LookupSeeder::class);
        $this->client = Client::factory()->create(['name' => 'Petronas Dagangan Bhd']);
    }

    /**
     * A signed-in staff member with the given role and a complete profile, so they aren't held on My profile.
     */
    private function staffWith(RoleName $role): Employee
    {
        $employee = Employee::factory()->create([
            'phone' => '012-345 6789', 'address_line_1' => '12 Jalan Cendana', 'postcode' => '50450', 'city' => 'Kuala Lumpur', 'state' => 'kuala-lumpur',
        ]);
        EmergencyContact::factory()->for($employee)->create();
        $employee->user->assignRole($role);

        return $employee;
    }

    public function test_records_are_identified_by_ulid_and_keep_an_integer_id(): void
    {
        $project = Project::factory()->create();

        $this->assertIsInt($project->id);
        $this->assertSame(26, strlen($project->ulid));
        $this->assertStringEndsWith('/projects/'.$project->ulid, route('projects.show', $project));
    }

    public function test_overseers_see_every_project_and_others_only_the_ones_they_manage(): void
    {
        $this->get(route('projects.index'))->assertRedirect(route('login'));

        $manager = $this->staffWith(RoleName::Consultant);
        Project::factory()->for($this->client)->create(['name' => 'Retail Rollout', 'project_manager_id' => $manager->id]);
        $other = Project::factory()->for($this->client)->create(['name' => 'Fleet Audit']);

        foreach ([RoleName::Director, RoleName::ProjectManager, RoleName::Admin] as $role) {
            $this->actingAs($this->staffWith($role)->user)->get(route('projects.index'))
                ->assertOk()->assertSee('Retail Rollout')->assertSee('Fleet Audit');
        }

        $this->actingAs($manager->user)->get(route('projects.index'))->assertSee('Retail Rollout')->assertDontSee('Fleet Audit');
        $this->actingAs($manager->user)->get(route('projects.show', $other))->assertNotFound();
        $this->actingAs($this->staffWith(RoleName::Finance)->user)->get(route('projects.show', $other))->assertNotFound();
    }

    public function test_list_hides_finished_projects_unless_filtered_and_filters_by_client_and_keyword(): void
    {
        $director = $this->staffWith(RoleName::Director)->user;
        $elsewhere = Client::factory()->create();
        Project::factory()->for($this->client)->create(['code' => 'PRJ-001', 'name' => 'Retail Rollout']);
        Project::factory()->for($elsewhere)->create(['code' => 'PRJ-002', 'name' => 'Fleet Audit']);
        Project::factory()->for($this->client)->create(['code' => 'PRJ-003', 'name' => 'Old Migration', 'status' => ProjectStatus::Completed]);

        $this->actingAs($director)->get(route('projects.index'))->assertSee('Retail Rollout')->assertSee('Fleet Audit')->assertDontSee('Old Migration');
        $this->actingAs($director)->get(route('projects.index', ['status' => 'completed']))->assertSee('Old Migration')->assertDontSee('Retail Rollout');
        $this->actingAs($director)->get(route('projects.index', ['client' => $elsewhere->ulid]))->assertSee('Fleet Audit')->assertDontSee('Retail Rollout');
        $this->actingAs($director)->get(route('projects.index', ['q' => 'prj-001']))->assertSee('Retail Rollout')->assertDontSee('Fleet Audit');
    }

    public function test_director_adds_a_project_with_any_manager_and_it_is_audited(): void
    {
        $director = $this->staffWith(RoleName::Director)->user;
        $manager = $this->staffWith(RoleName::ProjectManager);

        $this->actingAs($director)->get(route('projects.create', ['client' => $this->client->ulid]))
            ->assertOk()
            ->assertSee('<option value="'.$this->client->ulid.'" selected', false)
            ->assertSee('<option value="'.$manager->ulid.'"', false);

        $response = $this->actingAs($director)->post(route('projects.store'), $this->payload([
            'code' => 'prj 2026-001',
            'project_manager' => $manager->ulid,
        ]));

        $project = Project::query()->sole();
        $response->assertRedirect(route('projects.show', $project));
        $this->assertSame('PRJ2026-001', $project->code);
        $this->assertTrue($project->client->is($this->client));
        $this->assertTrue($project->projectManager->is($manager));
        $this->assertSame(ProjectStatus::Planning, $project->status);

        $activity = Activity::query()->whereMorphedTo('subject', $project)->sole();
        $this->assertSame('Created project', $activity->description);
        $this->assertTrue($activity->causer->is($director));
    }

    public function test_project_manager_manages_the_projects_they_add_and_edits_only_those(): void
    {
        $manager = $this->staffWith(RoleName::ProjectManager);
        $someoneElse = $this->staffWith(RoleName::Consultant);

        $this->actingAs($manager->user)->get(route('projects.create'))->assertOk()->assertDontSee('<select name="project_manager"', false);
        $this->actingAs($manager->user)->post(route('projects.store'), $this->payload(['project_manager' => $someoneElse->ulid]))
            ->assertSessionHasNoErrors();

        $mine = Project::query()->sole();
        $this->assertTrue($mine->projectManager->is($manager));

        $this->actingAs($manager->user)->put(route('projects.update', $mine), $this->payload(['name' => 'Renamed', 'project_manager' => $someoneElse->ulid]))
            ->assertRedirect(route('projects.show', $mine));
        $mine->refresh();
        $this->assertSame('Renamed', $mine->name);
        $this->assertTrue($mine->projectManager->is($manager));

        $theirs = Project::factory()->for($this->client)->create(['code' => 'PRJ-OTHER']);
        $this->actingAs($manager->user)->get(route('projects.show', $theirs))->assertOk()->assertDontSee(route('projects.edit', $theirs));
        $this->actingAs($manager->user)->get(route('projects.edit', $theirs))->assertForbidden();
        $this->actingAs($manager->user)->put(route('projects.update', $theirs), $this->payload(['code' => 'PRJ-OTHER']))->assertForbidden();
    }

    public function test_project_managers_without_a_staff_record_cannot_add_projects(): void
    {
        $this->actingAs(User::factory()->create()->assignRole(RoleName::ProjectManager))
            ->post(route('projects.store'), $this->payload())
            ->assertSessionHasErrors('project_manager');

        $this->assertSame(0, Project::query()->count());
    }

    public function test_other_roles_cannot_add_or_edit_projects(): void
    {
        $project = Project::factory()->for($this->client)->create();

        foreach ([RoleName::Admin, RoleName::Finance, RoleName::Staff] as $role) {
            $user = $this->staffWith($role)->user;
            $this->actingAs($user)->get(route('projects.create'))->assertForbidden();
            $this->actingAs($user)->post(route('projects.store'), $this->payload())->assertForbidden();
        }

        $this->actingAs($this->staffWith(RoleName::Admin)->user)->get(route('projects.edit', $project))->assertForbidden();
        $this->assertSame(1, Project::query()->count());
    }

    public function test_switching_the_module_off_hides_projects_and_the_menu(): void
    {
        $director = $this->staffWith(RoleName::Director)->user;
        $this->actingAs($director)->get(route('admin.dashboard'))->assertSee(route('projects.index'));

        Module::factory()->forKey(ModuleKey::Projects)->create(['is_enabled' => false]);

        $this->actingAs($director)->get(route('projects.index'))->assertNotFound();
        $this->actingAs($director)->get(route('admin.dashboard'))->assertDontSee(route('projects.index'));
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    #[DataProvider('invalidProjects')]
    public function test_invalid_projects_are_rejected(array $overrides, string $field): void
    {
        Project::factory()->for($this->client)->create(['code' => 'PRJ-TAKEN']);

        $this->actingAs($this->staffWith(RoleName::Director)->user)
            ->from(route('projects.create'))
            ->post(route('projects.store'), $this->payload($overrides))
            ->assertRedirect(route('projects.create'))
            ->assertSessionHasErrors($field);

        $this->assertSame(1, Project::query()->count());
    }

    /**
     * @return array<string, array{array<string, mixed>, string}>
     */
    public static function invalidProjects(): array
    {
        return [
            'code in use' => [['code' => 'prj-taken'], 'code'],
            'code with symbols' => [['code' => 'PRJ#1'], 'code'],
            'no name' => [['name' => ''], 'name'],
            'unknown client' => [['client' => '01JUNKNOWNULID0000000000000'], 'client'],
            'unknown manager' => [['project_manager' => '01JUNKNOWNULID0000000000000'], 'project_manager'],
            'unknown status' => [['status' => 'paused'], 'status'],
            'ends before it starts' => [['start_date' => '2026-06-01', 'end_date' => '2026-05-31'], 'end_date'],
        ];
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'code' => 'PRJ-2026-001',
            'name' => 'Retail Rollout',
            'client' => $this->client->ulid,
            'project_manager' => '',
            'status' => 'planning',
            'start_date' => '2026-01-05',
            'end_date' => '2026-12-31',
            'description' => 'Roll out the new POS to 40 stations.',
        ], $overrides);
    }
}
