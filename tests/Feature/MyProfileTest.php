<?php

namespace Tests\Feature;

use App\Enums\Role as RoleName;
use App\Models\EmergencyContact;
use App\Models\Employee;
use App\Models\User;
use Database\Seeders\LookupSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Activitylog\Models\Activity;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class MyProfileTest extends TestCase
{
    use RefreshDatabase;

    private Employee $employee;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app->make(PermissionRegistrar::class)->forgetCachedPermissions();
        foreach (RoleName::cases() as $role) {
            Role::findOrCreate($role->value);
        }
        $this->seed(LookupSeeder::class);
        $this->employee = Employee::factory()->create([
            'basic_salary' => '4321.00',
            'bank' => 'maybank',
            'bank_account_no' => '512345678901',
            'epf_no' => '12345678',
        ]);
        $this->employee->user->assignRole(RoleName::Staff);
    }

    public function test_guests_are_redirected_to_sign_in(): void
    {
        $this->get(route('my-profile.show'))->assertRedirect(route('login'));
        $this->put(route('my-profile.update'), [])->assertRedirect(route('login'));
    }

    public function test_staff_see_their_record_with_the_bank_account_masked_and_a_menu_link(): void
    {
        $this->actingAs($this->employee->user)->get(route('admin.dashboard'))->assertSee(route('my-profile.show'));

        $this->actingAs($this->employee->user)->get(route('my-profile.show'))
            ->assertOk()
            ->assertSee('RM 4,321.00')
            ->assertSee('••••••••8901')
            ->assertDontSee('512345678901')
            ->assertSee('12345678')
            ->assertSee('Managed by HR')
            ->assertSee('name="phone"', false)
            ->assertSee('name="contacts[0][name]"', false)
            ->assertDontSee('name="basic_salary"', false);
    }

    public function test_accounts_without_a_staff_record_see_a_notice_and_no_menu_link(): void
    {
        $admin = User::factory()->create()->assignRole(RoleName::SuperAdmin);

        $this->actingAs($admin)->get(route('admin.dashboard'))->assertDontSee(route('my-profile.show'));
        $this->actingAs($admin)->get(route('my-profile.show'))->assertOk()->assertSee('No staff record');
        $this->actingAs($admin)->put(route('my-profile.update'), ['children_count' => '0'])->assertForbidden();
    }

    public function test_staff_save_their_personal_details_and_contacts_and_it_is_audited_as_them(): void
    {
        Activity::query()->delete();

        $this->actingAs($this->employee->user)->put(route('my-profile.update'), [
            'gender' => 'female',
            'marital_status' => 'married',
            'children_count' => '2',
            'spouse_working' => '1',
            'phone' => '012-345 6789',
            'address_line_1' => '12 Jalan Cendana',
            'postcode' => '50450',
            'city' => 'Kuala Lumpur',
            'state' => 'kuala-lumpur',
            'contacts' => [['name' => 'Ahmad bin Ali', 'relationship' => 'spouse', 'phone' => '013-111 2222']],
        ])->assertRedirect(route('my-profile.show'))->assertSessionHas('status', 'Your details were saved.');

        $this->employee->refresh();
        $this->assertSame('012-345 6789', $this->employee->phone);
        $this->assertSame(2, $this->employee->children_count);
        $this->assertTrue($this->employee->spouse_working);
        $this->assertSame(['Ahmad bin Ali'], $this->employee->emergencyContacts->pluck('name')->all());

        $activity = Activity::query()->whereMorphedTo('subject', $this->employee)->sole();
        $this->assertSame('Updated staff record', $activity->description);
        $this->assertTrue($activity->causer->is($this->employee->user));
    }

    public function test_protected_fields_in_the_request_are_ignored(): void
    {
        $this->actingAs($this->employee->user)->put(route('my-profile.update'), [
            'children_count' => '0',
            'basic_salary' => '99999',
            'bank_account_no' => '999999999',
            'identity_no' => '010101010101',
            'staff_no' => 'BOSS',
            'last_working_date' => '2020-01-01',
        ])->assertSessionHasNoErrors();

        $this->employee->refresh();
        $this->assertSame('4321.00', $this->employee->basic_salary);
        $this->assertSame('512345678901', $this->employee->bank_account_no);
        $this->assertNull($this->employee->last_working_date);
        $this->assertNotSame('BOSS', $this->employee->staff_no);
    }

    public function test_invalid_details_are_rejected(): void
    {
        $this->actingAs($this->employee->user)
            ->from(route('my-profile.show'))
            ->put(route('my-profile.update'), [
                'children_count' => '',
                'phone' => 'call me',
                'contacts' => [1 => ['name' => 'Half filled', 'relationship' => '', 'phone' => '']],
            ])
            ->assertRedirect(route('my-profile.show'))
            ->assertSessionHasErrors(['children_count', 'phone', 'contacts.1.relationship', 'contacts.1.phone']);
    }

    public function test_reminder_shows_until_phone_address_and_a_contact_are_filled_in(): void
    {
        $this->actingAs($this->employee->user->fresh())->get(route('admin.dashboard'))
            ->assertSee('Please complete your profile')
            ->assertSee('Complete my profile');

        $this->employee->update(['phone' => '012-345 6789', 'address_line_1' => '12 Jalan Cendana']);
        $this->actingAs($this->employee->user->fresh())->get(route('admin.dashboard'))->assertSee('Please complete your profile');

        EmergencyContact::factory()->for($this->employee)->create();
        $this->actingAs($this->employee->user->fresh())->get(route('admin.dashboard'))->assertDontSee('Please complete your profile');
        $this->actingAs($this->employee->user->fresh())->get(route('my-profile.show'))->assertDontSee('Please complete your profile');
    }

    public function test_staff_download_their_own_documents_only(): void
    {
        Storage::fake(Employee::DOCUMENTS_DISK);
        $mine = $this->addDocument($this->employee, 'offer-letter.pdf');
        $theirs = $this->addDocument(Employee::factory()->create(), 'someone-else.pdf');

        $this->actingAs($this->employee->user)->get(route('my-profile.show'))
            ->assertSee('offer-letter.pdf')
            ->assertSee(route('my-profile.documents.download', $mine->uuid))
            ->assertDontSee('someone-else.pdf');

        $this->actingAs($this->employee->user)->get(route('my-profile.documents.download', $mine->uuid))
            ->assertOk()
            ->assertDownload('offer-letter.pdf');
        $this->assertTrue(Activity::query()->where('event', 'document-downloaded')->sole()->causer->is($this->employee->user));

        $this->actingAs($this->employee->user)->get(route('my-profile.documents.download', $theirs->uuid))->assertNotFound();
        $this->actingAs($this->employee->user)->get(route('staff.documents.download', [$this->employee, $mine->uuid]))->assertForbidden();
    }

    public function test_staff_cannot_change_their_own_name_or_email_in_account_settings(): void
    {
        $user = $this->employee->user;

        $this->actingAs($user)->get(route('admin.settings'))
            ->assertOk()
            ->assertSee('HR changes them for you')
            ->assertDontSee('action="'.route('user-profile-information.update').'"', false);

        $this->actingAs($user)->put(route('user-profile-information.update'), ['name' => 'Someone Else', 'email' => $user->email])
            ->assertSessionHasErrorsIn('updateProfileInformation', 'name');
        $this->assertNotSame('Someone Else', $user->fresh()->name);

        $this->actingAs($user)->put(route('user-profile-information.update'), ['name' => $user->name, 'email' => 'new@example.com'])
            ->assertSessionHasErrorsIn('updateProfileInformation', 'name');
        $this->assertNotSame('new@example.com', $user->fresh()->email);
    }

    public function test_accounts_without_a_staff_record_can_still_change_their_name_and_email(): void
    {
        $admin = User::factory()->create();

        $this->actingAs($admin)->get(route('admin.settings'))->assertSee('action="'.route('user-profile-information.update').'"', false);
        $this->actingAs($admin)->put(route('user-profile-information.update'), ['name' => 'Renamed Admin', 'email' => 'renamed@example.com'])
            ->assertSessionHasNoErrors();
        $this->assertSame('Renamed Admin', $admin->fresh()->name);
    }

    private function addDocument(Employee $employee, string $name): Media
    {
        return $employee->addMedia(UploadedFile::fake()->createWithContent($name, "%PDF-1.4\n%%EOF"))
            ->withCustomProperties(['type' => 'offer-letter', 'note' => null, 'uploaded_by' => null])
            ->toMediaCollection('documents');
    }
}
