<?php

namespace App\Http\Controllers\Administration;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use Spatie\Backup\Events\BackupHasFailed;
use Spatie\Backup\Events\BackupWasSuccessful;

class RunBackupController extends Controller
{
    /**
     * Run a full backup now. Runs in the request (no queue worker needed); a small database takes seconds.
     *
     * Backups continue when one location fails, so the command can succeed with only some copies saved;
     * the per-location events tell the user exactly which ones.
     */
    public function __invoke(): RedirectResponse
    {
        set_time_limit(600);

        $saved = [];
        $failed = [];
        Event::listen(BackupWasSuccessful::class, function (BackupWasSuccessful $event) use (&$saved): void {
            $saved[] = $event->diskName;
        });
        Event::listen(BackupHasFailed::class, function (BackupHasFailed $event) use (&$failed): void {
            $failed[] = ['disk' => $event->diskName, 'error' => Str::limit($event->exception->getMessage(), 200)];
        });

        $exitCode = Artisan::call('backup:run');
        $redirect = redirect()->route('administrations.backups.index');

        if ($exitCode !== 0 || $saved === []) {
            return $redirect->with('error', __('The backup failed. The reason is in the Audit Trail and the application log.'));
        }

        if ($failed !== []) {
            return $redirect->with('warning', __('Saved to :saved, but not to :failed: :error', [
                'saved' => $this->locationNames($saved),
                'failed' => $this->locationNames(array_filter(array_column($failed, 'disk'))) ?: __('another location'),
                'error' => $failed[0]['error'],
            ]));
        }

        return $redirect->with('status', __('Backup completed and saved to :saved.', ['saved' => $this->locationNames($saved)]));
    }

    /**
     * @param  array<int, string>  $disks
     */
    private function locationNames(array $disks): string
    {
        $labels = config('cendana.backup_locations');

        return collect($disks)->unique()->map(fn (string $disk): string => __($labels[$disk] ?? $disk))->implode(', ');
    }
}
