<?php

namespace App\Listeners;

use Illuminate\Support\Str;
use Spatie\Backup\Events\BackupHasFailed;
use Spatie\Backup\Events\BackupWasSuccessful;

/**
 * Writes completed and failed backups (scheduled or started from the Backups page) to the audit trail.
 */
class RecordBackupActivity
{
    public function handleSuccess(BackupWasSuccessful $event): void
    {
        activity()->event('backup-created')
            ->withProperties(['disk' => $event->diskName])
            ->log('Backup saved to '.$event->diskName);
    }

    public function handleFailure(BackupHasFailed $event): void
    {
        activity()->event('backup-failed')
            ->withProperties(array_filter(['disk' => $event->diskName, 'error' => Str::limit($event->exception->getMessage(), 500)]))
            ->log('Backup failed');
    }
}
