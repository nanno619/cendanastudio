<?php

namespace App\Http\Controllers\Administration;

use App\Http\Controllers\Controller;
use Illuminate\View\View;
use Spatie\Backup\BackupDestination\Backup;
use Spatie\Backup\Config\Config;
use Spatie\Backup\Tasks\Monitor\BackupDestinationStatus;
use Spatie\Backup\Tasks\Monitor\BackupDestinationStatusFactory;

class BackupController extends Controller
{
    /**
     * Show each backup location's health and the backups kept on the first one.
     */
    public function __invoke(Config $config): View
    {
        $statuses = BackupDestinationStatusFactory::createForMonitorConfig($config->monitoredBackups)->values();
        $primary = $statuses->first()?->backupDestination();
        $offsite = $statuses->slice(1)->map(fn (BackupDestinationStatus $status) => $status->backupDestination())
            ->filter->isReachable();
        $offsiteNames = $offsite->flatMap(fn ($destination) => $destination->backups()->map(fn (Backup $backup): string => basename($backup->path())))->unique();

        return view('administrations.backups.index', [
            'statuses' => $statuses,
            'backups' => $primary && $primary->isReachable() ? $primary->backups() : collect(),
            'primaryDisk' => $primary?->diskName(),
            'offsiteNames' => $offsite->isNotEmpty() ? $offsiteNames : null,
            'timezone' => config('cendana.timezone'),
        ]);
    }
}
