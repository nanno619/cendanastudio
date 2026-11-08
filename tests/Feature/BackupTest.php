<?php

namespace Tests\Feature;

use App\Enums\Role as RoleName;
use App\Models\User;
use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Spatie\Activitylog\Models\Activity;
use Spatie\Backup\Events\BackupHasFailed;
use Spatie\Backup\Events\BackupWasSuccessful;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class BackupTest extends TestCase
{
    use RefreshDatabase;

    private const FILE = '2026-11-08-17-00-00.zip';

    private User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app->make(PermissionRegistrar::class)->forgetCachedPermissions();
        Role::findOrCreate(RoleName::SuperAdmin->value);
        Role::findOrCreate(RoleName::Director->value);
        $this->superAdmin = User::factory()->create()->assignRole(RoleName::SuperAdmin);

        // Creating fixtures is itself audited; start each test from an empty trail.
        Activity::query()->delete();

        Storage::fake('backups');
        config(['backup.backup.name' => 'cendana-studio', 'backup.backup.destination.disks' => ['backups']]);
    }

    public function test_other_roles_are_forbidden(): void
    {
        $director = User::factory()->create()->assignRole(RoleName::Director);

        $this->actingAs($director)->get(route('administrations.backups.index'))->assertForbidden();
        $this->actingAs($director)->post(route('administrations.backups.store'))->assertForbidden();
        $this->actingAs($director)->get(route('administrations.backups.download', ['backups', self::FILE]))->assertForbidden();
    }

    public function test_page_lists_backups_and_warns_when_there_is_no_off_site_copy(): void
    {
        Storage::disk('backups')->put('cendana-studio/'.self::FILE, str_repeat('x', 2048));

        $this->actingAs($this->superAdmin)
            ->get(route('administrations.backups.index'))
            ->assertOk()
            ->assertSee(self::FILE)
            ->assertSee('2.0 KB')
            ->assertSee(route('administrations.backups.download', ['backups', self::FILE]))
            ->assertSee('No off-site copy yet');
    }

    public function test_page_shows_the_empty_state_before_the_first_backup(): void
    {
        $this->actingAs($this->superAdmin)
            ->get(route('administrations.backups.index'))
            ->assertOk()
            ->assertSee('No backups yet');
    }

    public function test_back_up_now_runs_the_backup_command(): void
    {
        Artisan::shouldReceive('call')->once()->with('backup:run')->andReturn(0);

        $this->actingAs($this->superAdmin)
            ->post(route('administrations.backups.store'))
            ->assertRedirect(route('administrations.backups.index'))
            ->assertSessionHas('status', 'Backup completed.');
    }

    public function test_back_up_now_reports_a_failure(): void
    {
        Artisan::shouldReceive('call')->once()->with('backup:run')->andReturn(1);

        $this->actingAs($this->superAdmin)
            ->post(route('administrations.backups.store'))
            ->assertSessionHas('error');
    }

    public function test_download_streams_a_listed_backup_and_is_audited(): void
    {
        Storage::disk('backups')->put('cendana-studio/'.self::FILE, 'zip-bytes');

        $response = $this->actingAs($this->superAdmin)->get(route('administrations.backups.download', ['backups', self::FILE]));

        $response->assertOk()->assertDownload(self::FILE);
        $this->assertSame('zip-bytes', $response->streamedContent());
        $this->assertTrue(Activity::query()->where('event', 'backup-downloaded')->sole()->causer->is($this->superAdmin));
    }

    public function test_download_refuses_unknown_files_and_unconfigured_disks(): void
    {
        Storage::disk('backups')->put('cendana-studio/'.self::FILE, 'zip-bytes');

        $this->actingAs($this->superAdmin)->get(route('administrations.backups.download', ['backups', '2020-01-01-00-00-00.zip']))->assertNotFound();
        $this->actingAs($this->superAdmin)->get(route('administrations.backups.download', ['local', self::FILE]))->assertNotFound();
        $this->actingAs($this->superAdmin)->get('/administrations/backups/backups/..%2F.env')->assertNotFound();
    }

    public function test_completed_and_failed_backups_are_audited(): void
    {
        event(new BackupWasSuccessful('backups', 'cendana-studio'));
        event(new BackupHasFailed(new \Exception('mysqldump: not found'), 'backups', 'cendana-studio'));

        $this->assertSame(
            ['backup-created' => 'Backup saved to backups', 'backup-failed' => 'Backup failed'],
            Activity::query()->orderBy('id')->pluck('description', 'event')->all(),
        );
        $this->assertSame('mysqldump: not found', Activity::query()->where('event', 'backup-failed')->sole()->properties['error']);
    }

    public function test_backups_are_scheduled_nightly_in_malaysia_time(): void
    {
        $backup = collect($this->app->make(Schedule::class)->events())
            ->first(fn (Event $event): bool => str_contains($event->command, 'backup:run'));

        $this->assertNotNull($backup);
        $this->assertSame('0 1 * * *', $backup->expression);
        $this->assertSame(config('cendana.timezone'), $backup->timezone);
    }
}
