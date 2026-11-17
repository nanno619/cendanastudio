<?php

namespace App\Http\Controllers\Settings;

use App\Enums\MissedClockOut;
use App\Http\Controllers\Controller;
use App\Models\AttendanceSetting;
use Illuminate\View\View;
use Spatie\Activitylog\Models\Activity;

class AttendanceSettingsController extends Controller
{
    /**
     * Show the company-wide attendance rules and who last changed them.
     */
    public function __invoke(): View
    {
        $setting = AttendanceSetting::current();

        return view('settings.hadir.general', [
            'setting' => $setting,
            'missedClockOutOptions' => collect(MissedClockOut::cases())->mapWithKeys(fn (MissedClockOut $option): array => [$option->value => __($option->label())])->all(),
            'lastChange' => $setting->exists
                ? Activity::query()->with('causer')->whereMorphedTo('subject', $setting)->latest('id')->first()
                : null,
            'timezone' => config('cendana.timezone'),
        ]);
    }
}
