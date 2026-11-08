<?php

namespace App\Http\Controllers\Administration;

use App\Http\Controllers\Controller;
use Spatie\Backup\BackupDestination\Backup;
use Spatie\Backup\BackupDestination\BackupDestination;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DownloadBackupController extends Controller
{
    /**
     * Stream one backup zip. Only names listed on a configured backup disk are accepted, so no other file can be read.
     */
    public function __invoke(string $disk, string $file): StreamedResponse
    {
        abort_unless(in_array($disk, config('backup.backup.destination.disks'), true), 404);

        $backup = BackupDestination::create($disk, config('backup.backup.name'))->backups()
            ->first(fn (Backup $backup): bool => basename($backup->path()) === $file);

        abort_unless($backup, 404);

        activity()->event('backup-downloaded')
            ->withProperties(['disk' => $disk, 'file' => $file])
            ->log("Downloaded backup {$file}");

        return response()->streamDownload(function () use ($backup): void {
            $stream = $backup->stream();
            fpassthru($stream);
            fclose($stream);
        }, $file, ['Content-Type' => 'application/zip']);
    }
}
