<?php

namespace App\Http\Controllers\Administration;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Artisan;

class RunBackupController extends Controller
{
    /**
     * Run a full backup now. Runs in the request (no queue worker needed); a small database takes seconds.
     */
    public function __invoke(): RedirectResponse
    {
        set_time_limit(600);

        $exitCode = Artisan::call('backup:run');

        return redirect()->route('administrations.backups.index')->with(
            $exitCode === 0 ? 'status' : 'error',
            $exitCode === 0 ? __('Backup completed.') : __('The backup failed. The reason is in the Audit Trail and the application log.'),
        );
    }
}
