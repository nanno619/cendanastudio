<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
 * Backups (spatie/laravel-backup), in Malaysia time. Needs the server cron entry:
 * * * * * * cd /path/to/app && php artisan schedule:run >> /dev/null 2>&1
 */
Schedule::command('backup:clean')->dailyAt('00:30')->timezone(config('cendana.timezone'))->withoutOverlapping()->onOneServer();
Schedule::command('backup:run')->dailyAt('01:00')->timezone(config('cendana.timezone'))->withoutOverlapping()->onOneServer();
Schedule::command('backup:monitor')->dailyAt('09:00')->timezone(config('cendana.timezone'));
