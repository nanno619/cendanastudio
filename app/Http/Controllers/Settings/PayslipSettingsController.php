<?php

namespace App\Http\Controllers\Settings;

use App\Enums\PayslipIdentityDisplay;
use App\Http\Controllers\Controller;
use App\Models\PayslipSetting;
use Illuminate\View\View;
use Spatie\Activitylog\Models\Activity;

class PayslipSettingsController extends Controller
{
    /**
     * Show the payslip options and who last changed them.
     */
    public function __invoke(): View
    {
        $setting = PayslipSetting::current();

        return view('settings.payroll.payslip', [
            'setting' => $setting,
            'identityDisplays' => collect(PayslipIdentityDisplay::cases())->mapWithKeys(fn (PayslipIdentityDisplay $display): array => [$display->value => __($display->label())])->all(),
            'languages' => config('cendana.locales'),
            'lastChange' => $setting->exists
                ? Activity::query()->with('causer')->whereMorphedTo('subject', $setting)->latest('id')->first()
                : null,
            'timezone' => config('cendana.timezone'),
        ]);
    }
}
