<?php

namespace App\Http\Controllers\Settings;

use App\Enums\EpfCalculationMethod;
use App\Http\Controllers\Controller;
use App\Models\EpfSetting;
use Illuminate\View\View;
use Spatie\Activitylog\Models\Activity;

class EpfSettingsController extends Controller
{
    /**
     * Show the EPF contribution rules and who last changed them.
     */
    public function __invoke(): View
    {
        $setting = EpfSetting::current();

        return view('settings.payroll.epf', [
            'setting' => $setting,
            'methods' => collect(EpfCalculationMethod::cases())->mapWithKeys(fn (EpfCalculationMethod $method): array => [$method->value => __($method->label())])->all(),
            'lastChange' => $setting->exists
                ? Activity::query()->with('causer')->whereMorphedTo('subject', $setting)->latest('id')->first()
                : null,
            'timezone' => config('cendana.timezone'),
        ]);
    }
}
