<?php

namespace Tests\Feature;

use App\Enums\FileType;
use App\Enums\Role as RoleName;
use App\Http\Requests\Administration\UpdateFileSecurityRequest;
use App\Models\FileSecuritySetting;
use App\Models\User;
use App\Rules\AllowedUpload;
use Database\Seeders\FileSecuritySettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class FileSecurityTest extends TestCase
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

    public function test_guests_are_redirected_to_sign_in(): void
    {
        $this->get(route('administrations.file-security.index'))->assertRedirect(route('login'));
    }

    public function test_other_roles_are_forbidden(): void
    {
        $admin = User::factory()->create()->assignRole(RoleName::Admin);

        $this->actingAs($admin)->get(route('administrations.file-security.index'))->assertForbidden();
        $this->actingAs($admin)->put(route('administrations.file-security.update'), $this->payload())->assertForbidden();
        $this->actingAs($admin)->get(route('admin.dashboard'))->assertDontSee(route('administrations.file-security.index'));
    }

    public function test_menu_shows_file_security_to_super_admin(): void
    {
        $this->actingAs($this->superAdmin)
            ->get(route('admin.dashboard'))
            ->assertSee(route('administrations.file-security.index'));
    }

    public function test_page_shows_defaults_before_anything_is_saved(): void
    {
        $this->actingAs($this->superAdmin)
            ->get(route('administrations.file-security.index'))
            ->assertOk()
            ->assertSee('value="pdf" class="mt-0.5" checked', false)
            ->assertDontSee('value="archive" class="mt-0.5" checked', false)
            ->assertSee('value="10"', false)
            ->assertSee('.phtml')
            ->assertSee('Using the default settings until you save.');
    }

    public function test_super_admin_can_save_the_policy_and_it_is_audited(): void
    {
        FileSecuritySetting::factory()->create();
        Activity::query()->delete();

        $this->actingAs($this->superAdmin)
            ->put(route('administrations.file-security.update'), $this->payload(['allowed_types' => ['archive', 'pdf'], 'max_file_size_mb' => 25, 'verify_content' => '0']))
            ->assertRedirect(route('administrations.file-security.index'))
            ->assertSessionHas('status', 'File security settings were saved.');

        $setting = FileSecuritySetting::query()->sole();
        $this->assertSame([FileType::Pdf, FileType::Archive], $setting->allowed_types->all());
        $this->assertSame(25, $setting->max_file_size_mb);
        $this->assertFalse($setting->verify_content);

        $activity = Activity::query()->where('event', 'updated')->sole();
        $this->assertSame('Updated file security settings', $activity->description);
        $this->assertTrue($activity->causer->is($this->superAdmin));

        $this->actingAs($this->superAdmin)
            ->get(route('administrations.file-security.index'))
            ->assertSee(route('administrations.audit-trail.show', $activity));
    }

    public function test_first_save_creates_the_policy(): void
    {
        $this->actingAs($this->superAdmin)
            ->put(route('administrations.file-security.update'), $this->payload())
            ->assertRedirect(route('administrations.file-security.index'));

        $this->assertSame([FileType::Image], FileSecuritySetting::query()->sole()->allowed_types->all());
    }

    /**
     * @return array<string, array{array<string, mixed>, string}>
     */
    public static function invalidPolicies(): array
    {
        return [
            'no types' => [['allowed_types' => []], 'allowed_types'],
            'unknown type' => [['allowed_types' => ['exe']], 'allowed_types.0'],
            'size below 1' => [['max_file_size_mb' => 0], 'max_file_size_mb'],
            'size above server limit' => [['max_file_size_mb' => UpdateFileSecurityRequest::serverLimitMb() + 1], 'max_file_size_mb'],
        ];
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    #[DataProvider('invalidPolicies')]
    public function test_invalid_policy_is_rejected(array $overrides, string $field): void
    {
        $this->actingAs($this->superAdmin)
            ->from(route('administrations.file-security.index'))
            ->put(route('administrations.file-security.update'), $this->payload($overrides))
            ->assertRedirect(route('administrations.file-security.index'))
            ->assertSessionHasErrors($field);

        $this->assertDatabaseCount('file_security_settings', 0);
    }

    public function test_seeder_creates_defaults_and_keeps_saved_settings(): void
    {
        $this->seed(FileSecuritySettingSeeder::class);
        $this->assertSame(10, FileSecuritySetting::query()->sole()->max_file_size_mb);
        $this->assertSame(0, Activity::query()->where('subject_type', (new FileSecuritySetting)->getMorphClass())->count());

        FileSecuritySetting::query()->update(['max_file_size_mb' => 3]);
        $this->seed(FileSecuritySettingSeeder::class);

        $this->assertSame(3, FileSecuritySetting::query()->sole()->max_file_size_mb);
    }

    public function test_upload_rule_accepts_an_allowed_file(): void
    {
        $this->assertNull($this->uploadError(UploadedFile::fake()->create('Quote.PDF', 100, 'application/pdf')));
    }

    /**
     * @return array<string, array{UploadedFile, string}>
     */
    public static function rejectedUploads(): array
    {
        $typeError = 'The document must be one of these file types: pdf.';

        return [
            'type not allowed' => [UploadedFile::fake()->create('backup.zip', 10, 'application/zip'), $typeError],
            'unknown extension' => [UploadedFile::fake()->create('setup.exe', 10, 'application/x-msdownload'), $typeError],
            'no extension' => [UploadedFile::fake()->create('report', 10, 'application/pdf'), $typeError],
            'blocked segment' => [UploadedFile::fake()->create('invoice.php.pdf', 10, 'application/pdf'), 'The document has a file type that is never allowed.'],
            'too large' => [UploadedFile::fake()->create('scan.pdf', 2049, 'application/pdf'), 'The document must not be larger than 2 MB.'],
            'content mismatch' => [UploadedFile::fake()->create('report.pdf', 10, 'application/x-msdownload'), 'The document content does not match its file extension.'],
        ];
    }

    #[DataProvider('rejectedUploads')]
    public function test_upload_rule_rejects_files_outside_the_policy(UploadedFile $file, string $error): void
    {
        FileSecuritySetting::factory()->create(['allowed_types' => [FileType::Pdf], 'max_file_size_mb' => 2]);

        $this->assertSame($error, $this->uploadError($file));
    }

    public function test_content_check_can_be_turned_off(): void
    {
        FileSecuritySetting::factory()->create(['verify_content' => false]);

        $this->assertNull($this->uploadError(UploadedFile::fake()->create('report.pdf', 10, 'application/x-msdownload')));
    }

    private function uploadError(UploadedFile $file): ?string
    {
        return Validator::make(['document' => $file], ['document' => ['required', 'file', new AllowedUpload]])->errors()->first('document') ?: null;
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return array_merge(['allowed_types' => ['image'], 'max_file_size_mb' => 10, 'verify_content' => '1'], $overrides);
    }
}
