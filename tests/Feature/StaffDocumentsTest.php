<?php

namespace Tests\Feature;

use App\Enums\Role as RoleName;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Activitylog\Models\Activity;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class StaffDocumentsTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;

    private Employee $employee;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake(Employee::DOCUMENTS_DISK);
        Storage::fake('public');
        $this->app->make(PermissionRegistrar::class)->forgetCachedPermissions();
        foreach (RoleName::cases() as $role) {
            Role::findOrCreate($role->value);
        }
        $this->superAdmin = User::factory()->create(['name' => 'Aisyah Super'])->assignRole(RoleName::SuperAdmin);
        $this->employee = Employee::factory()->create();
    }

    public function test_upload_stores_the_file_privately_with_its_details_and_is_audited(): void
    {
        $this->actingAs($this->superAdmin)
            ->post(route('staff.documents.store', $this->employee), [
                'document' => $this->pdf('offer-letter.pdf'),
                'type' => 'offer-letter',
                'note' => 'Signed copy',
            ])
            ->assertRedirect(route('staff.show', $this->employee).'#documents')
            ->assertSessionHas('status', 'offer-letter.pdf was uploaded.');

        $media = $this->employee->fresh()->getFirstMedia('documents');
        $this->assertSame(Employee::DOCUMENTS_DISK, $media->disk);
        $this->assertSame('offer-letter', $media->getCustomProperty('type'));
        $this->assertSame('Signed copy', $media->getCustomProperty('note'));
        $this->assertSame($this->superAdmin->id, $media->getCustomProperty('uploaded_by'));
        Storage::disk(Employee::DOCUMENTS_DISK)->assertExists($media->getPathRelativeToRoot());
        $this->assertSame([], Storage::disk('public')->allFiles());

        $activity = Activity::query()->where('event', 'document-uploaded')->sole();
        $this->assertSame('Uploaded staff document', $activity->description);
        $this->assertTrue($activity->subject->is($this->employee));
        $this->assertSame('offer-letter.pdf', $activity->properties['file']);
    }

    public function test_profile_lists_documents_with_type_uploader_and_download_link(): void
    {
        $media = $this->addDocument('ic.pdf', 'ic-copy');

        $this->actingAs($this->superAdmin)->get(route('staff.show', $this->employee))
            ->assertOk()
            ->assertSeeInOrder(['Documents', 'ic.pdf', 'IC or passport copy', 'Aisyah Super'])
            ->assertSee(route('staff.documents.download', [$this->employee, $media->uuid]))
            ->assertSee('name="document"', false)
            ->assertSee('accept=".pdf,', false);
    }

    public function test_download_streams_the_file_and_is_audited_but_not_shown_as_the_last_change(): void
    {
        $media = $this->addDocument('ic.pdf', 'ic-copy');
        $finance = User::factory()->create(['name' => 'Farid Finance'])->assignRole(RoleName::Finance);

        $response = $this->actingAs($finance)->get(route('staff.documents.download', [$this->employee, $media->uuid]));

        $response->assertOk()->assertDownload('ic.pdf');
        $this->assertStringStartsWith('%PDF', $response->streamedContent());

        $activity = Activity::query()->where('event', 'document-downloaded')->sole();
        $this->assertTrue($activity->causer->is($finance));
        $this->assertSame('Downloaded staff document', $activity->description);

        $this->actingAs($this->superAdmin)->get(route('staff.show', $this->employee))->assertDontSee('Last changed by Farid Finance');
    }

    public function test_documents_are_closed_to_guests_and_other_roles(): void
    {
        $media = $this->addDocument('ic.pdf', 'ic-copy');
        $staff = User::factory()->create()->assignRole(RoleName::Staff);

        $this->get(route('staff.documents.download', [$this->employee, $media->uuid]))->assertRedirect(route('login'));
        $this->actingAs($staff)->get(route('staff.documents.download', [$this->employee, $media->uuid]))->assertForbidden();
        $this->actingAs($staff)->post(route('staff.documents.store', $this->employee), ['document' => $this->pdf('x.pdf'), 'type' => 'other'])->assertForbidden();
        $this->actingAs($staff)->delete(route('staff.documents.destroy', [$this->employee, $media->uuid]))->assertForbidden();
    }

    public function test_a_document_can_only_be_reached_through_its_own_record(): void
    {
        $media = $this->addDocument('ic.pdf', 'ic-copy');
        $other = Employee::factory()->create();

        $this->actingAs($this->superAdmin)->get(route('staff.documents.download', [$other, $media->uuid]))->assertNotFound();
        $this->actingAs($this->superAdmin)->delete(route('staff.documents.destroy', [$other, $media->uuid]))->assertNotFound();
        $this->actingAs($this->superAdmin)->get(route('staff.documents.download', [$this->employee, (string) Str::uuid()]))->assertNotFound();
        $this->assertModelExists($media);
    }

    public function test_delete_removes_the_file_and_is_audited(): void
    {
        $media = $this->addDocument('old.pdf', 'other');
        $path = $media->getPathRelativeToRoot();

        $this->actingAs($this->superAdmin)->delete(route('staff.documents.destroy', [$this->employee, $media->uuid]))
            ->assertRedirect(route('staff.show', $this->employee).'#documents')
            ->assertSessionHas('status', 'old.pdf was deleted.');

        $this->assertModelMissing($media);
        Storage::disk(Employee::DOCUMENTS_DISK)->assertMissing($path);
        $this->assertSame('Deleted staff document', Activity::query()->where('event', 'document-deleted')->sole()->description);
    }

    public function test_only_super_admin_can_change_a_super_admins_documents_but_others_can_download_them(): void
    {
        $this->employee->user->assignRole(RoleName::SuperAdmin);
        $media = $this->addDocument('ic.pdf', 'ic-copy');
        $admin = User::factory()->create()->assignRole(RoleName::Admin);

        $this->actingAs($admin)->get(route('staff.show', $this->employee))->assertOk()->assertDontSee('name="document"', false);
        $this->actingAs($admin)->get(route('staff.documents.download', [$this->employee, $media->uuid]))->assertOk();
        $this->actingAs($admin)->post(route('staff.documents.store', $this->employee), ['document' => $this->pdf('x.pdf'), 'type' => 'other'])->assertForbidden();
        $this->actingAs($admin)->delete(route('staff.documents.destroy', [$this->employee, $media->uuid]))->assertForbidden();
        $this->assertModelExists($media);
    }

    public function test_backups_include_private_staff_documents(): void
    {
        $this->assertContains(storage_path('app/private'), config('backup.backup.source.files.include'));
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    #[DataProvider('invalidUploads')]
    public function test_invalid_uploads_are_rejected(array $overrides, string $field): void
    {
        $this->actingAs($this->superAdmin)
            ->from(route('staff.show', $this->employee))
            ->post(route('staff.documents.store', $this->employee), array_merge(['type' => 'certificate', 'note' => ''], $overrides))
            ->assertRedirect(route('staff.show', $this->employee))
            ->assertSessionHasErrors($field);

        $this->assertSame(0, Media::query()->count());
    }

    /**
     * @return array<string, array{array<string, mixed>, string}>
     */
    public static function invalidUploads(): array
    {
        return [
            'no file' => [[], 'document'],
            'type not allowed by file security' => [['document' => UploadedFile::fake()->create('notes.txt', 1, 'text/plain')], 'document'],
            'blocked extension inside the name' => [['document' => UploadedFile::fake()->create('shell.php.pdf', 1, 'application/pdf')], 'document'],
            'unknown document type' => [['document' => UploadedFile::fake()->createWithContent('a.pdf', "%PDF-1.4\n%%EOF"), 'type' => 'passport-photo'], 'type'],
            'note too long' => [['document' => UploadedFile::fake()->createWithContent('a.pdf', "%PDF-1.4\n%%EOF"), 'note' => str_repeat('a', 201)], 'note'],
        ];
    }

    private function pdf(string $name): UploadedFile
    {
        return UploadedFile::fake()->createWithContent($name, "%PDF-1.4\n1 0 obj << >> endobj\n%%EOF");
    }

    private function addDocument(string $name, string $type): Media
    {
        return $this->employee->addMedia($this->pdf($name))
            ->withCustomProperties(['type' => $type, 'note' => null, 'uploaded_by' => $this->superAdmin->id])
            ->toMediaCollection('documents');
    }
}
