<?php

namespace Tests\Feature;

use App\Enums\ClientStatus;
use App\Enums\Role as RoleName;
use App\Models\Client;
use App\Models\ClientContact;
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

    public function test_records_are_identified_by_ulid_and_start_active(): void
    {
        $client = Client::create(['name' => 'Petronas Dagangan Bhd']);

        $this->assertIsInt($client->id);
        $this->assertSame(26, strlen($client->ulid));
        $this->assertSame(ClientStatus::Active, $client->status);
        $this->assertStringEndsWith('/crm/clients/'.$client->ulid, route('crm.clients.show', $client));
    }

    public function test_viewers_see_crm_editors_change_clients_and_others_are_forbidden(): void
    {
        $client = Client::factory()->create(['name' => 'Petronas Dagangan Bhd']);

        $this->get(route('crm.index'))->assertRedirect(route('login'));

        foreach ([RoleName::Admin, RoleName::Finance] as $role) {
            $user = $this->userWith($role);
            $this->actingAs($user)->get(route('crm.index'))
                ->assertOk()
                ->assertSee('Petronas Dagangan Bhd')
                ->assertSee(route('crm.index'))
                ->assertDontSee(route('crm.clients.create'));
            $this->actingAs($user)->get(route('crm.clients.show', $client))->assertOk()->assertDontSee(route('crm.clients.edit', $client));
            $this->actingAs($user)->get(route('crm.clients.edit', $client))->assertForbidden();
        }

        foreach ([RoleName::SuperAdmin, RoleName::Director, RoleName::ProjectManager, RoleName::Sales] as $role) {
            $user = $this->userWith($role);
            $this->actingAs($user)->get(route('crm.index'))->assertSee(route('crm.clients.create'));
            $this->actingAs($user)->get(route('crm.clients.edit', $client))->assertOk();
        }

        foreach ([RoleName::Staff, RoleName::Consultant] as $role) {
            $user = $this->userWith($role);
            $this->actingAs($user)->get(route('admin.dashboard'))->assertDontSee(route('crm.index'));
            $this->actingAs($user)->get(route('crm.index'))->assertForbidden();
            $this->actingAs($user)->get(route('crm.clients.show', $client))->assertForbidden();
        }
    }

    public function test_crm_page_counts_clients_by_status_and_filters_by_status_and_keyword(): void
    {
        Client::factory()->count(2)->create();
        Client::factory()->create(['name' => 'Closed Trading Sdn Bhd', 'status' => ClientStatus::Closure, 'status_reason' => 'Contract ended']);
        Client::factory()->create(['name' => 'Bad Payer Sdn Bhd', 'registration_no' => '201901012345', 'status' => ClientStatus::Blacklist, 'status_reason' => 'Unpaid invoices']);
        $director = $this->userWith(RoleName::Director);

        $page = $this->actingAs($director)->get(route('crm.index'))->assertOk();
        $page->assertSeeInOrder(['Active', '2', 'Closure', '1', 'Blacklist', '1']);
        $page->assertSee(route('crm.index', ['status' => 'blacklist']), false);

        $this->actingAs($director)->get(route('crm.index', ['status' => 'blacklist']))
            ->assertSee('Bad Payer Sdn Bhd')->assertDontSee('Closed Trading Sdn Bhd')
            ->assertSee('aria-current="true"', false);
        $this->actingAs($director)->get(route('crm.index', ['q' => '2019-0101']))
            ->assertSee('Bad Payer Sdn Bhd')->assertDontSee('Closed Trading Sdn Bhd');
    }

    public function test_adding_a_client_saves_its_details_and_contacts_and_is_audited(): void
    {
        $sales = $this->userWith(RoleName::Sales);

        $response = $this->actingAs($sales)->post(route('crm.clients.store'), [
            'name' => 'Petronas Dagangan Bhd',
            'registration_no' => '2023-0101-2345',
            'email' => ' Info@PDB.com.my ',
            'phone' => '03-2051 5000',
            'postcode' => '50088',
            'city' => 'Kuala Lumpur',
            'state' => 'kuala-lumpur',
            'status' => 'active',
            'status_reason' => 'Ignored while active',
            'contacts' => [
                ['name' => 'Aminah Yusof', 'position' => 'Procurement Manager', 'phone' => '012-345 6789', 'email' => 'Aminah@PDB.com.my'],
                ['name' => '', 'position' => '', 'phone' => '', 'email' => ''],
                ['name' => 'Lim Wei', 'position' => '', 'phone' => '', 'email' => ''],
            ],
        ]);

        $client = Client::query()->sole();
        $response->assertRedirect(route('crm.clients.show', $client))->assertSessionHas('status');
        $this->assertSame('202301012345', $client->registration_no);
        $this->assertSame('info@pdb.com.my', $client->email);
        $this->assertSame(ClientStatus::Active, $client->status);
        $this->assertNull($client->status_reason);
        $this->assertSame(['Aminah Yusof', 'Lim Wei'], $client->contacts->pluck('name')->all());
        $this->assertSame('aminah@pdb.com.my', $client->contacts->first()->email);

        $activity = Activity::query()->whereMorphedTo('subject', $client)->sole();
        $this->assertSame('Created client', $activity->description);
        $this->assertTrue($activity->causer->is($sales));
    }

    public function test_closing_or_blacklisting_needs_a_reason_which_is_shown_audited_and_cleared_when_active_again(): void
    {
        $client = Client::factory()->create();
        $director = $this->userWith(RoleName::Director);
        Activity::query()->delete();

        $this->actingAs($director)->put(route('crm.clients.update', $client), ['name' => $client->name, 'status' => 'blacklist', 'status_reason' => ''])
            ->assertSessionHasErrors('status_reason');

        $this->actingAs($director)->put(route('crm.clients.update', $client), ['name' => $client->name, 'status' => 'blacklist', 'status_reason' => 'Unpaid invoices since 2025'])
            ->assertRedirect(route('crm.clients.show', $client));
        $client->refresh();
        $this->assertSame(ClientStatus::Blacklist, $client->status);
        $this->assertSame('blacklist', Activity::query()->whereMorphedTo('subject', $client)->sole()->attribute_changes['attributes']['status']);
        $this->actingAs($director)->get(route('crm.clients.show', $client))->assertSee('Unpaid invoices since 2025')->assertSee('Blacklist');

        $this->actingAs($director)->put(route('crm.clients.update', $client), ['name' => $client->name, 'status' => 'active', 'status_reason' => 'Unpaid invoices since 2025']);
        $client->refresh();
        $this->assertSame(ClientStatus::Active, $client->status);
        $this->assertNull($client->status_reason);
    }

    public function test_editing_updates_contacts_in_place_and_removes_cleared_ones(): void
    {
        $client = Client::factory()->create();
        $first = ClientContact::factory()->for($client)->create(['name' => 'Aminah Yusof', 'sort_order' => 1]);
        ClientContact::factory()->for($client)->create(['name' => 'Lim Wei', 'sort_order' => 2]);

        $this->actingAs($this->userWith(RoleName::ProjectManager))->put(route('crm.clients.update', $client), [
            'name' => 'Renamed Sdn Bhd',
            'status' => 'active',
            'contacts' => [['name' => 'Aminah Yusof', 'position' => 'Head of Procurement']],
        ])->assertRedirect(route('crm.clients.show', $client));

        $client->refresh();
        $this->assertSame('Renamed Sdn Bhd', $client->name);
        $this->assertSame([$first->id], $client->contacts->pluck('id')->all());
        $this->assertSame('Head of Procurement', $client->contacts->first()->position);
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    #[DataProvider('invalidClients')]
    public function test_invalid_clients_are_rejected(array $overrides, string $field): void
    {
        Client::factory()->create(['registration_no' => '202301012345']);

        $this->actingAs($this->userWith(RoleName::Director))
            ->from(route('crm.clients.create'))
            ->post(route('crm.clients.store'), [...['name' => 'New Client Sdn Bhd', 'status' => 'active'], ...$overrides])
            ->assertRedirect(route('crm.clients.create'))
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
            'unknown status' => [['status' => 'vip'], 'status'],
            'closure without a reason' => [['status' => 'closure', 'status_reason' => ''], 'status_reason'],
            'contact without a name' => [['contacts' => [1 => ['name' => '', 'phone' => '012-345 6789']]], 'contacts.1.name'],
            'contact with a bad email' => [['contacts' => [['name' => 'Aminah', 'email' => 'not-an-email']]], 'contacts.0.email'],
            'too many contacts' => [['contacts' => array_fill(0, 6, ['name' => 'Someone'])], 'contacts'],
        ];
    }
}
