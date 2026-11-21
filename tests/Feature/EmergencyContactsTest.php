<?php

namespace Tests\Feature;

use App\Enums\LookupCategory;
use App\Enums\Role as RoleName;
use App\Models\EmergencyContact;
use App\Models\Employee;
use App\Models\LookupItem;
use App\Models\User;
use Database\Seeders\LookupSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class EmergencyContactsTest extends TestCase
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
        $this->seed(LookupSeeder::class);
        $this->superAdmin = User::factory()->create()->assignRole(RoleName::SuperAdmin);
    }

    public function test_relationship_lookup_is_seeded(): void
    {
        $this->assertArrayHasKey('spouse', LookupItem::options(LookupCategory::Relationship));
        $this->assertSame('Pasangan', LookupItem::query()->inCategory(LookupCategory::Relationship)->where('code', 'spouse')->value('label_ms'));
    }

    public function test_adding_staff_saves_filled_contacts_in_order_and_skips_blank_rows(): void
    {
        Notification::fake();

        $this->actingAs($this->superAdmin)->post(route('staff.store'), $this->payload([
            'contacts' => [
                ['name' => 'Ahmad bin Ali', 'relationship' => 'father', 'phone' => '013-111 2222'],
                ['name' => '', 'relationship' => '', 'phone' => ''],
                ['name' => 'Siti Aminah', 'relationship' => 'spouse', 'phone' => '+60 12-333 4444'],
            ],
        ]))->assertSessionHasNoErrors();

        $contacts = Employee::query()->sole()->emergencyContacts;
        $this->assertSame(['Ahmad bin Ali', 'Siti Aminah'], $contacts->pluck('name')->all());
        $this->assertSame([1, 2], $contacts->pluck('sort_order')->all());
        $this->assertSame('Added emergency contact', Activity::query()->whereMorphedTo('subject', $contacts->first())->sole()->description);
    }

    public function test_editing_updates_contacts_in_place_and_removes_cleared_ones(): void
    {
        $employee = Employee::factory()->create();
        $first = EmergencyContact::factory()->for($employee)->create(['name' => 'First', 'relationship' => 'father', 'phone' => '013-111 2222', 'sort_order' => 1]);
        $second = EmergencyContact::factory()->for($employee)->create(['name' => 'Second', 'sort_order' => 2]);
        Activity::query()->delete();

        $this->actingAs($this->superAdmin)->get(route('staff.edit', $employee))
            ->assertOk()
            ->assertSee('value="First"', false)
            ->assertSee('value="Second"', false);

        $this->actingAs($this->superAdmin)->put(route('staff.update', $employee), $this->payloadFor($employee, [
            'contacts' => [['name' => 'First', 'relationship' => 'father', 'phone' => '013-999 0000']],
        ]))->assertSessionHasNoErrors();

        $this->assertSame(['First'], $employee->emergencyContacts()->pluck('name')->all());
        $this->assertTrue($employee->emergencyContacts()->first()->is($first));
        $this->assertModelMissing($second);
        $this->assertSame(['phone' => '013-999 0000'], Activity::query()->whereMorphedTo('subject', $first)->sole()->attribute_changes['attributes']);
        $this->assertSame('Removed emergency contact', Activity::query()->where('event', 'deleted')->sole()->description);
    }

    public function test_profile_lists_contacts_with_relationship_and_phone_link(): void
    {
        $employee = Employee::factory()->create();
        EmergencyContact::factory()->for($employee)->create(['name' => 'Ahmad bin Ali', 'relationship' => 'father', 'phone' => '013-111 2222']);

        $this->actingAs($this->superAdmin)->get(route('staff.show', $employee))
            ->assertOk()
            ->assertSeeInOrder(['Emergency contacts', 'Ahmad bin Ali', 'Father', '013-111 2222'])
            ->assertSee('href="tel:0131112222"', false);
    }

    public function test_a_deactivated_relationship_already_in_use_can_be_kept(): void
    {
        $employee = Employee::factory()->create();
        EmergencyContact::factory()->for($employee)->create(['relationship' => 'guardian']);
        LookupItem::query()->inCategory(LookupCategory::Relationship)->where('code', 'guardian')->update(['is_active' => false]);

        $this->actingAs($this->superAdmin)->get(route('staff.edit', $employee))->assertSee('<option value="guardian"', false);
        $this->actingAs($this->superAdmin)->put(route('staff.update', $employee), $this->payloadFor($employee, [
            'contacts' => [['name' => 'Kept', 'relationship' => 'guardian', 'phone' => '013-111 2222']],
        ]))->assertSessionHasNoErrors();

        $this->actingAs($this->superAdmin)->from(route('staff.create'))->post(route('staff.store'), $this->payload([
            'contacts' => [['name' => 'New', 'relationship' => 'guardian', 'phone' => '013-111 2222']],
        ]))->assertSessionHasErrors('contacts.0.relationship');
    }

    /**
     * @param  array<int, array<string, string>>  $contacts
     */
    #[DataProvider('invalidContacts')]
    public function test_invalid_contacts_are_rejected_against_their_row(array $contacts, string $field): void
    {
        $this->actingAs($this->superAdmin)
            ->from(route('staff.create'))
            ->post(route('staff.store'), $this->payload(['contacts' => $contacts]))
            ->assertRedirect(route('staff.create'))
            ->assertSessionHasErrors($field);

        $this->assertSame(0, Employee::query()->count());
    }

    /**
     * @return array<string, array{array<int, array<string, string>>, string}>
     */
    public static function invalidContacts(): array
    {
        $row = ['name' => 'Ahmad', 'relationship' => 'father', 'phone' => '013-111 2222'];

        return [
            'partly filled row without a phone' => [[2 => ['name' => 'Ahmad', 'relationship' => '', 'phone' => '']], 'contacts.2.phone'],
            'partly filled row without a name' => [[1 => ['name' => '', 'relationship' => 'father', 'phone' => '013-111 2222']], 'contacts.1.name'],
            'unknown relationship' => [[['name' => 'Ahmad', 'relationship' => 'neighbour', 'phone' => '013-111 2222']], 'contacts.0.relationship'],
            'bad phone' => [[['name' => 'Ahmad', 'relationship' => 'father', 'phone' => 'call later']], 'contacts.0.phone'],
            'four contacts' => [[$row, $row, $row, $row], 'contacts'],
        ];
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Nur Aisyah binti Ahmad',
            'email' => 'nur.aisyah@example.com',
            'role' => 'Staff',
            'staff_no' => 'S0042',
            'identity_no' => '920202101234',
            'children_count' => '0',
            'employment_type' => 'permanent',
            'join_date' => '2026-01-05',
            'is_manual_worker' => '0',
            'basic_salary' => '4500',
        ], $overrides);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payloadFor(Employee $employee, array $overrides = []): array
    {
        return $this->payload([
            'name' => $employee->user->name,
            'email' => $employee->user->email,
            'staff_no' => $employee->staff_no,
            'identity_no' => $employee->identity_no,
            'join_date' => $employee->join_date->toDateString(),
            ...$overrides,
        ]);
    }
}
