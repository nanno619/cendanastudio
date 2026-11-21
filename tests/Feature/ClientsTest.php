<?php

namespace Tests\Feature;

use App\Enums\ModuleKey;
use App\Enums\Role as RoleName;
use App\Models\Client;
use App\Models\ClientContact;
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

class ClientsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app->make(PermissionRegistrar::class)->forgetCachedPermissions();
        foreach (RoleName::cases() as $role) {
            Role::findOrCreate($role->value);
        }
        $this->seed(LookupSeeder::class);
    }

    private function userWith(RoleName $role): User
    {
        return User::factory()->create()->assignRole($role);
    }

    public function test_viewers_see_clients_editors_change_them_and_others_are_forbidden(): void
    {
        $client = Client::factory()->create(['name' => 'Petronas Dagangan Bhd']);

        $this->get(route('clients.index'))->assertRedirect(route('login'));

        foreach ([RoleName::Admin, RoleName::Finance] as $role) {
            $this->actingAs($this->userWith($role))->get(route('clients.index'))
                ->assertOk()
                ->assertSee('Petronas Dagangan Bhd')
                ->assertDontSee(route('clients.create'));
            $this->actingAs($this->userWith($role))->get(route('clients.edit', $client))->assertForbidden();
        }

        foreach ([RoleName::SuperAdmin, RoleName::Director, RoleName::ProjectManager] as $role) {
            $this->actingAs($this->userWith($role))->get(route('clients.index'))->assertSee(route('clients.create'));
            $this->actingAs($this->userWith($role))->get(route('clients.edit', $client))->assertOk();
        }

        foreach ([RoleName::Staff, RoleName::Consultant] as $role) {
            $this->actingAs($this->userWith($role))->get(route('clients.index'))->assertForbidden();
            $this->actingAs($this->userWith($role))->get(route('clients.show', $client))->assertForbidden();
        }
    }

    public function test_menu_shows_clients_only_to_viewers(): void
    {
        $this->actingAs($this->userWith(RoleName::Finance))->get(route('projects.index'))->assertSee(route('clients.index'));
        $this->actingAs($this->userWith(RoleName::Staff))->get(route('projects.index'))
            ->assertSee(route('projects.index'))
            ->assertDontSee(route('clients.index'));
    }

    public function test_adding_a_client_saves_its_details_and_contacts_and_is_audited(): void
    {
        $director = $this->userWith(RoleName::Director);

        $response = $this->actingAs($director)->post(route('clients.store'), [
            'name' => 'Petronas Dagangan Bhd',
            'registration_no' => '2023-0101-2345',
            'email' => ' Info@PDB.com.my ',
            'phone' => '03-2051 5000',
            'postcode' => '50088',
            'city' => 'Kuala Lumpur',
            'state' => 'kuala-lumpur',
            'contacts' => [
                ['name' => 'Aminah Yusof', 'position' => 'Procurement Manager', 'phone' => '012-345 6789', 'email' => 'Aminah@PDB.com.my'],
                ['name' => '', 'position' => '', 'phone' => '', 'email' => ''],
                ['name' => 'Lim Wei', 'position' => '', 'phone' => '', 'email' => ''],
            ],
        ]);

        $client = Client::query()->sole();
        $response->assertRedirect(route('clients.show', $client))->assertSessionHas('status');
        $this->assertSame('202301012345', $client->registration_no);
        $this->assertSame('info@pdb.com.my', $client->email);
        $this->assertSame(['Aminah Yusof', 'Lim Wei'], $client->contacts->pluck('name')->all());
        $this->assertSame('aminah@pdb.com.my', $client->contacts->first()->email);

        $activity = Activity::query()->whereMorphedTo('subject', $client)->sole();
        $this->assertSame('Created client', $activity->description);
        $this->assertTrue($activity->causer->is($director));
    }

    public function test_editing_updates_contacts_in_place_and_removes_cleared_ones(): void
    {
        $client = Client::factory()->create();
        $first = ClientContact::factory()->for($client)->create(['name' => 'Aminah Yusof', 'sort_order' => 1]);
        ClientContact::factory()->for($client)->create(['name' => 'Lim Wei', 'sort_order' => 2]);

        $this->actingAs($this->userWith(RoleName::ProjectManager))->put(route('clients.update', $client), [
            'name' => 'Renamed Sdn Bhd',
            'contacts' => [['name' => 'Aminah Yusof', 'position' => 'Head of Procurement']],
        ])->assertRedirect(route('clients.show', $client));

        $client->refresh();
        $this->assertSame('Renamed Sdn Bhd', $client->name);
        $this->assertSame([$first->id], $client->contacts->pluck('id')->all());
        $this->assertSame('Head of Procurement', $client->contacts->first()->position);
    }

    public function test_client_page_lists_only_the_projects_this_person_can_see(): void
    {
        $client = Client::factory()->create();
        Project::factory()->for($client)->create(['name' => 'Retail Rollout']);

        $this->actingAs($this->userWith(RoleName::Finance))->get(route('clients.show', $client))
            ->assertOk()
            ->assertDontSee('Retail Rollout')
            ->assertDontSee(route('projects.create', ['client' => $client->ulid]));
        $this->actingAs($this->userWith(RoleName::Admin))->get(route('clients.show', $client))->assertSee('Retail Rollout');
        $this->actingAs($this->userWith(RoleName::Director))->get(route('clients.show', $client))
            ->assertSee(route('projects.create', ['client' => $client->ulid]));
    }

    public function test_switching_the_module_off_hides_clients(): void
    {
        Module::factory()->forKey(ModuleKey::Projects)->create(['is_enabled' => false]);

        $this->actingAs($this->userWith(RoleName::Director))->get(route('clients.index'))->assertNotFound();
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    #[DataProvider('invalidClients')]
    public function test_invalid_clients_are_rejected(array $overrides, string $field): void
    {
        Client::factory()->create(['registration_no' => '202301012345']);

        $this->actingAs($this->userWith(RoleName::Director))
            ->from(route('clients.create'))
            ->post(route('clients.store'), [...['name' => 'New Client Sdn Bhd'], ...$overrides])
            ->assertRedirect(route('clients.create'))
            ->assertSessionHasErrors($field);

        $this->assertSame(1, Client::query()->count());
    }

    /**
     * @return array<string, array{array<string, mixed>, string}>
     */
    public static function invalidClients(): array
    {
        return [
            'no name' => [['name' => ''], 'name'],
            'ssm number in use' => [['registration_no' => '2023-0101-2345'], 'registration_no'],
            'ssm number too short' => [['registration_no' => '12345'], 'registration_no'],
            'bad postcode' => [['postcode' => '123'], 'postcode'],
            'unknown state' => [['state' => 'atlantis'], 'state'],
            'contact without a name' => [['contacts' => [1 => ['name' => '', 'phone' => '012-345 6789']]], 'contacts.1.name'],
            'contact with a bad email' => [['contacts' => [['name' => 'Aminah', 'email' => 'not-an-email']]], 'contacts.0.email'],
            'too many contacts' => [['contacts' => array_fill(0, 6, ['name' => 'Someone'])], 'contacts'],
        ];
    }
}
