<?php

namespace Tests\Feature;

use App\Enums\EmployeeStatus;
use App\Enums\IdentityType;
use App\Enums\LeavingReason;
use App\Enums\Role as RoleName;
use App\Models\Employee;
use App\Models\EpfSetting;
use App\Models\User;
use App\Notifications\StaffAccountCreated;
use App\Support\MyKad;
use Carbon\CarbonImmutable;
use Database\Seeders\LookupSeeder;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class StaffRecordsTest extends TestCase
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

    public function test_mykad_numbers_give_the_date_of_birth(): void
    {
        $today = CarbonImmutable::parse('2026-11-17');

        $this->assertSame('1990-01-01', MyKad::birthDate('900101-14-5678', $today)?->toDateString());
        $this->assertSame('2005-12-31', MyKad::birthDate('051231 10 1234', $today)?->toDateString());
        $this->assertSame('1930-06-15', MyKad::birthDate('300615101234', $today)?->toDateString());
        $this->assertNull(MyKad::birthDate('901301145678', $today));
        $this->assertNull(MyKad::birthDate('12345', $today));
        $this->assertSame('900101-14-5678', MyKad::format('900101145678'));
    }

    public function test_status_follows_the_dates(): void
    {
        $this->assertSame(EmployeeStatus::Active, Employee::factory()->make()->status());
        $this->assertSame(EmployeeStatus::Probation, Employee::factory()->onProbation()->make()->status());
        $this->assertSame(EmployeeStatus::Leaving, Employee::factory()->leaving()->make()->status());
        $this->assertSame(EmployeeStatus::Left, Employee::factory()->left()->make()->status());
        $this->assertSame(EmployeeStatus::Leaving, Employee::factory()->make(['last_working_date' => Employee::today()])->status());
    }

    public function test_records_are_identified_by_ulid_and_keep_an_integer_id(): void
    {
        $employee = Employee::factory()->create();

        $this->assertIsInt($employee->id);
        $this->assertTrue(Str::isUlid($employee->ulid));
        $this->assertSame($employee->ulid, $employee->getRouteKey());
        $this->assertTrue($employee->user->employee->is($employee));
    }

    public function test_guests_are_redirected_and_other_roles_are_forbidden(): void
    {
        $employee = Employee::factory()->create();
        $staff = User::factory()->create()->assignRole(RoleName::Staff);

        $this->get(route('staff.index'))->assertRedirect(route('login'));
        $this->actingAs($staff)->get(route('admin.dashboard'))->assertDontSee(route('staff.index'));
        $this->actingAs($staff)->get(route('staff.index'))->assertForbidden();
        $this->actingAs($staff)->get(route('staff.show', $employee))->assertForbidden();
        $this->actingAs($staff)->get(route('staff.create'))->assertForbidden();
        $this->actingAs($staff)->post(route('staff.store'), $this->payload())->assertForbidden();
        $this->actingAs($staff)->put(route('staff.update', $employee), $this->payload())->assertForbidden();
    }

    public function test_admin_and_finance_can_use_staff_records(): void
    {
        $employee = Employee::factory()->create();

        foreach ([RoleName::Admin, RoleName::Finance] as $role) {
            $user = User::factory()->create()->assignRole($role);

            $this->actingAs($user)->get(route('admin.dashboard'))->assertSee(route('staff.index'));
            $this->actingAs($user)->get(route('staff.index'))->assertOk()->assertSee($employee->user->name);
            $this->actingAs($user)->get(route('staff.show', $employee))->assertOk()->assertSee('RM '.number_format((float) $employee->basic_salary, 2));
        }
    }

    public function test_index_hides_staff_who_left_and_filters_by_keyword_department_and_status(): void
    {
        $aisyah = Employee::factory()->create(['staff_no' => 'S0001', 'department' => 'finance']);
        $aisyah->user->update(['name' => 'Aisyah Rahman']);
        $ben = Employee::factory()->onProbation()->create(['department' => 'sales']);
        $gone = Employee::factory()->left()->create();

        $this->actingAs($this->superAdmin)->get(route('staff.index'))
            ->assertOk()
            ->assertSee('Aisyah Rahman')
            ->assertSee($ben->user->name)
            ->assertDontSee($gone->user->name);

        $this->actingAs($this->superAdmin)->get(route('staff.index', ['q' => 's0001']))
            ->assertSee('Aisyah Rahman')
            ->assertDontSee($ben->user->name);

        $this->actingAs($this->superAdmin)->get(route('staff.index', ['q' => MyKad::format($ben->identity_no)]))
            ->assertSee($ben->user->name)
            ->assertDontSee('Aisyah Rahman');

        $this->actingAs($this->superAdmin)->get(route('staff.index', ['department' => 'sales']))
            ->assertSee($ben->user->name)
            ->assertDontSee('Aisyah Rahman');

        $this->actingAs($this->superAdmin)->get(route('staff.index', ['status' => 'left']))
            ->assertSee($gone->user->name)
            ->assertDontSee('Aisyah Rahman');

        $this->actingAs($this->superAdmin)->get(route('staff.index', ['status' => 'probation']))
            ->assertSee($ben->user->name)
            ->assertDontSee('Aisyah Rahman');
    }

    public function test_adding_staff_creates_the_account_and_record_emails_a_password_link_and_is_audited(): void
    {
        Notification::fake();
        $manager = Employee::factory()->create();
        $admin = User::factory()->create()->assignRole(RoleName::Admin);

        $this->actingAs($admin)->get(route('staff.create'))
            ->assertOk()
            ->assertSee('<option value="'.$manager->ulid.'"', false)
            ->assertDontSee('<option value="Super Admin"', false);

        $response = $this->actingAs($admin)->post(route('staff.store'), $this->payload([
            'email' => '  Nur.Aisyah@Example.com ',
            'identity_no' => '900101-14-5678',
            'staff_no' => 's-0042',
            'reporting_manager' => $manager->ulid,
            'marital_status' => 'married',
            'spouse_working' => '1',
            'bank_account_no' => '5123 4567-8901',
        ]));

        $employee = Employee::query()->where('staff_no', 'S-0042')->sole();
        $response->assertRedirect(route('staff.show', $employee))->assertSessionHas('status');

        $user = $employee->user;
        $this->assertSame('nur.aisyah@example.com', $user->email);
        $this->assertTrue($user->hasRole(RoleName::Staff));
        $this->assertSame('900101145678', $employee->identity_no);
        $this->assertSame('1990-01-01', $employee->date_of_birth->toDateString());
        $this->assertTrue($employee->reportingManager->is($manager));
        $this->assertTrue($employee->spouse_working);
        $this->assertSame('512345678901', $employee->bank_account_no);
        Notification::assertSentTo($user, StaffAccountCreated::class, fn (StaffAccountCreated $notification): bool => Password::broker()->tokenExists($user, $notification->token));
        Notification::assertNotSentTo($user, ResetPassword::class);

        $activity = Activity::query()->whereMorphedTo('subject', $employee)->sole();
        $this->assertSame('Created staff record', $activity->description);
        $this->assertTrue($activity->causer->is($admin));
    }

    public function test_add_form_is_split_into_steps_and_edit_form_is_one_page(): void
    {
        $add = $this->actingAs($this->superAdmin)->get(route('staff.create'))->assertOk()->assertSee('data-stepper', false);
        $this->assertSame(6, substr_count($add->getContent(), 'data-step="'));
        $add->assertSeeInOrder(['data-step="Account"', 'data-step="Personal details"', 'data-step="Emergency contacts"', 'data-step="Employment"', 'data-step="Pay and statutory"', 'data-step="Review"'], false);

        $employee = Employee::factory()->create();
        $edit = $this->actingAs($this->superAdmin)->get(route('staff.edit', $employee))->assertOk();
        $this->assertStringNotContainsString('data-step="', $edit->getContent());
        $this->assertStringNotContainsString('data-stepper-nav', $edit->getContent());
    }

    public function test_only_super_admin_can_give_the_super_admin_role_or_edit_a_super_admins_record(): void
    {
        $admin = User::factory()->create()->assignRole(RoleName::Admin);
        $boss = Employee::factory()->create();
        $boss->user->assignRole(RoleName::SuperAdmin);

        $this->actingAs($admin)->from(route('staff.create'))->post(route('staff.store'), $this->payload(['role' => 'Super Admin']))
            ->assertSessionHasErrors('role');
        $this->actingAs($admin)->get(route('staff.edit', $boss))->assertForbidden();
        $this->actingAs($admin)->put(route('staff.update', $boss), $this->payload())->assertForbidden();
        $this->actingAs($admin)->get(route('staff.show', $boss))->assertOk()->assertDontSee(route('staff.edit', $boss));

        $this->actingAs($this->superAdmin)->get(route('staff.create'))->assertSee('<option value="Super Admin"', false);
        $this->actingAs($this->superAdmin)->get(route('staff.edit', $boss))->assertOk();
    }

    public function test_updating_changes_the_account_role_and_record_and_audits_only_changes(): void
    {
        $employee = Employee::factory()->create(['staff_no' => 'S0100', 'basic_salary' => '4000.00']);
        $employee->user->assignRole(RoleName::Staff);
        Activity::query()->delete();

        $this->actingAs($this->superAdmin)->get(route('staff.edit', $employee))
            ->assertOk()
            ->assertSee('value="'.MyKad::format($employee->identity_no).'"', false);

        $this->actingAs($this->superAdmin)
            ->put(route('staff.update', $employee), $this->payload([
                'name' => 'Renamed Person',
                'email' => $employee->user->email,
                'role' => 'Finance',
                'staff_no' => 'S0100',
                'identity_no' => $employee->identity_no,
                'join_date' => $employee->join_date->toDateString(),
                'basic_salary' => '4500',
            ]))
            ->assertRedirect(route('staff.show', $employee));

        $employee->refresh();
        $this->assertSame('Renamed Person', $employee->user->name);
        $this->assertSame(['Finance'], $employee->user->getRoleNames()->all());
        $this->assertSame('4500.00', $employee->basic_salary);

        $changes = Activity::query()->whereMorphedTo('subject', $employee)->sole()->attribute_changes['attributes'];
        $this->assertSame('4500.00', $changes['basic_salary']);
        $this->assertSame('Updated staff record', Activity::query()->whereMorphedTo('subject', $employee)->sole()->description);
    }

    public function test_leaving_is_recorded_and_staff_who_left_cannot_sign_in(): void
    {
        $employee = Employee::factory()->create();

        $this->actingAs($this->superAdmin)->put(route('staff.update', $employee), $this->payloadFor($employee, [
            'last_working_date' => Employee::today()->subDay()->toDateString(),
            'leaving_reason' => 'resigned',
        ]))->assertSessionHasNoErrors();

        $employee->refresh();
        $this->assertSame(LeavingReason::Resigned, $employee->leaving_reason);
        $this->assertTrue($employee->hasLeft());

        auth()->logout();
        $this->post(route('login.store'), ['email' => $employee->user->email, 'password' => 'password'])
            ->assertSessionHasErrors(['email' => 'This account is closed because its staff record shows the person has left.']);
        $this->assertGuest();
    }

    public function test_a_signed_in_person_is_signed_out_once_they_have_left(): void
    {
        $employee = Employee::factory()->left()->create();

        $this->actingAs($employee->user)->get(route('admin.dashboard'))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_staff_who_have_not_left_and_accounts_without_records_can_sign_in(): void
    {
        $leaving = Employee::factory()->leaving()->create();

        $this->post(route('login.store'), ['email' => $leaving->user->email, 'password' => 'password'])->assertRedirect();
        $this->assertAuthenticatedAs($leaving->user);
        auth()->logout();

        $this->post(route('login.store'), ['email' => $this->superAdmin->email, 'password' => 'password'])->assertRedirect();
        $this->assertAuthenticatedAs($this->superAdmin);
    }

    public function test_leaving_reason_is_cleared_without_a_last_working_day_and_nobody_can_end_their_own_record(): void
    {
        $employee = Employee::factory()->leaving()->create();

        $this->actingAs($this->superAdmin)->put(route('staff.update', $employee), $this->payloadFor($employee, ['last_working_date' => '', 'leaving_reason' => '']))
            ->assertSessionHasNoErrors();
        $this->assertNull($employee->fresh()->leaving_reason);

        $mine = Employee::factory()->create(['user_id' => $this->superAdmin->id]);
        $this->actingAs($this->superAdmin)->put(route('staff.update', $mine), $this->payloadFor($mine, ['last_working_date' => Employee::today()->toDateString(), 'leaving_reason' => 'resigned']))
            ->assertSessionHasErrors('last_working_date');
    }

    public function test_passport_holders_need_a_date_of_birth(): void
    {
        $this->actingAs($this->superAdmin)->post(route('staff.store'), $this->payload([
            'identity_type' => 'passport',
            'identity_no' => 'a1234567',
            'nationality' => 'BD',
            'date_of_birth' => '1995-04-20',
        ]))->assertSessionHasNoErrors();

        $employee = Employee::query()->where('identity_no', 'A1234567')->sole();
        $this->assertSame(IdentityType::Passport, $employee->identity_type);
        $this->assertSame('1995-04-20', $employee->date_of_birth->toDateString());
    }

    public function test_elected_epf_rate_needs_epf_settings_to_allow_it(): void
    {
        $this->actingAs($this->superAdmin)->post(route('staff.store'), $this->payload(['epf_elected_rate' => '13']))
            ->assertSessionHasErrors('epf_elected_rate');

        EpfSetting::factory()->create(['allow_employee_elected_rate' => true]);

        $this->actingAs($this->superAdmin)->post(route('staff.store'), $this->payload(['epf_elected_rate' => '11']))
            ->assertSessionHasErrors('epf_elected_rate');
        $this->actingAs($this->superAdmin)->post(route('staff.store'), $this->payload(['epf_elected_rate' => '13']))
            ->assertSessionHasNoErrors();
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    #[DataProvider('invalidRecords')]
    public function test_invalid_records_are_rejected(array $overrides, string $field): void
    {
        $existing = Employee::factory()->create(['staff_no' => 'S9999', 'identity_no' => '880808088888']);
        $existing->user->update(['email' => 'taken@example.com']);

        $this->actingAs($this->superAdmin)
            ->from(route('staff.create'))
            ->post(route('staff.store'), $this->payload($overrides))
            ->assertRedirect(route('staff.create'))
            ->assertSessionHasErrors($field);

        $this->assertSame(1, Employee::query()->count());
    }

    /**
     * @return array<string, array{array<string, mixed>, string}>
     */
    public static function invalidRecords(): array
    {
        return [
            'email in use' => [['email' => 'taken@example.com'], 'email'],
            'staff number in use' => [['staff_no' => 's9999'], 'staff_no'],
            'staff number with spaces inside' => [['staff_no' => 'S#1'], 'staff_no'],
            'mykad in use' => [['identity_no' => '880808-08-8888'], 'identity_no'],
            'mykad with an impossible date' => [['identity_no' => '901301145678'], 'identity_no'],
            'mykad too short' => [['identity_no' => '90010114'], 'identity_no'],
            'mykad holder not malaysian' => [['nationality' => 'SG'], 'nationality'],
            'passport without date of birth' => [['identity_type' => 'passport', 'identity_no' => 'A1234567', 'nationality' => 'ID', 'date_of_birth' => ''], 'date_of_birth'],
            'unknown department' => [['department' => 'astronauts'], 'department'],
            'unknown reporting manager' => [['reporting_manager' => '01JUNKNOWNULID0000000000000'], 'reporting_manager'],
            'probation before joining' => [['probation_end_date' => '2025-12-31'], 'probation_end_date'],
            'leaving without a reason' => [['last_working_date' => '2026-12-31'], 'leaving_reason'],
            'negative salary' => [['basic_salary' => '-1'], 'basic_salary'],
            'account number with letters' => [['bank_account_no' => '12AB5678'], 'bank_account_no'],
            'bad phone' => [['phone' => 'call me'], 'phone'],
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
            'identity_type' => 'mykad',
            'identity_no' => '920202-10-1234',
            'nationality' => 'MY',
            'gender' => 'female',
            'race' => 'malay',
            'marital_status' => 'single',
            'children_count' => '0',
            'spouse_working' => '',
            'phone' => '012-345 6789',
            'personal_email' => '',
            'postcode' => '50450',
            'city' => 'Kuala Lumpur',
            'state' => 'kuala-lumpur',
            'employment_type' => 'permanent',
            'department' => 'consulting',
            'position' => 'consultant',
            'reporting_manager' => '',
            'join_date' => '2026-01-05',
            'probation_end_date' => '',
            'is_manual_worker' => '0',
            'basic_salary' => '4500',
            'bank' => 'maybank',
            'bank_account_no' => '',
            'epf_no' => '',
            'socso_no' => '',
            'income_tax_no' => '',
        ], $overrides);
    }

    /**
     * A valid update payload that keeps the record's identifiers.
     *
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
