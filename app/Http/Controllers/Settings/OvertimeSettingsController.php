<?php

namespace App\Http\Controllers\Settings;

use App\Enums\OvertimeEligibility;
use App\Http\Controllers\Controller;
use App\Models\OvertimeSetting;
use Illuminate\View\View;
use Spatie\Activitylog\Models\Activity;

class OvertimeSettingsController extends Controller
{
    /**
     * Show the overtime pay rules and who last changed them.
     */
    public function __invoke(): View
    {
        $setting = OvertimeSetting::current();

        return view('settings.payroll.overtime', [
            'setting' => $setting,
            'eligibilities' => collect(OvertimeEligibility::cases())->mapWithKeys(fn (OvertimeEligibility $eligibility): array => [$eligibility->value => __($eligibility->label())])->all(),
            'lastChange' => $setting->exists
                ? Activity::query()->with('causer')->whereMorphedTo('subject', $setting)->latest('id')->first()
                : null,
            'timezone' => config('cendana.timezone'),
        ]);
    }
}
