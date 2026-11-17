<?php

namespace App\Http\Controllers\Settings;

use App\Enums\ProRataBasis;
use App\Http\Controllers\Controller;
use App\Models\ProRataSetting;
use Illuminate\View\View;
use Spatie\Activitylog\Models\Activity;

class ProRataSettingsController extends Controller
{
    /**
     * Show the part-month wage rules and who last changed them.
     */
    public function __invoke(): View
    {
        $setting = ProRataSetting::current();

        return view('settings.payroll.pro-rata', [
            'setting' => $setting,
            'bases' => collect(ProRataBasis::cases())->mapWithKeys(fn (ProRataBasis $basis): array => [$basis->value => __($basis->label())])->all(),
            'lastChange' => $setting->exists
                ? Activity::query()->with('causer')->whereMorphedTo('subject', $setting)->latest('id')->first()
                : null,
            'timezone' => config('cendana.timezone'),
        ]);
    }
}
