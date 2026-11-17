<?php

namespace App\Http\Controllers\Settings;

use App\Enums\ClaimPaymentMethod;
use App\Http\Controllers\Controller;
use App\Models\ClaimSetting;
use Illuminate\View\View;
use Spatie\Activitylog\Models\Activity;

class ClaimSettingsController extends Controller
{
    /**
     * Show the company-wide claim rules and who last changed them.
     */
    public function __invoke(): View
    {
        $setting = ClaimSetting::current();

        return view('settings.claim.general', [
            'setting' => $setting,
            'paymentMethods' => collect(ClaimPaymentMethod::cases())->mapWithKeys(fn (ClaimPaymentMethod $method): array => [$method->value => __($method->label())])->all(),
            'lastChange' => $setting->exists
                ? Activity::query()->with('causer')->whereMorphedTo('subject', $setting)->latest('id')->first()
                : null,
            'timezone' => config('cendana.timezone'),
        ]);
    }
}
