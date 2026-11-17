<?php

namespace App\Http\Controllers\Settings;

use App\Enums\LeaveRounding;
use App\Enums\LookupCategory;
use App\Enums\UnusedLeaveTreatment;
use App\Enums\Weekday;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\LeaveSetting;
use App\Models\LookupItem;
use Illuminate\View\View;
use Spatie\Activitylog\Models\Activity;

class LeaveSettingsController extends Controller
{
    /**
     * Show the company-wide leave rules and who last changed them.
     */
    public function __invoke(): View
    {
        $setting = LeaveSetting::current();

        return view('settings.leave.general', [
            'setting' => $setting,
            'weekdays' => Weekday::cases(),
            'months' => collect(range(1, 12))->mapWithKeys(fn (int $month): array => [$month => now()->startOfYear()->month($month)->translatedFormat('F')])->all(),
            'states' => LookupItem::options(LookupCategory::MalaysianStates),
            'holidayState' => $setting->public_holiday_state ?? ($setting->exists ? null : Company::current()->state),
            'roundings' => collect(LeaveRounding::cases())->mapWithKeys(fn (LeaveRounding $rounding): array => [$rounding->value => __($rounding->label())])->all(),
            'treatments' => collect(UnusedLeaveTreatment::cases())->mapWithKeys(fn (UnusedLeaveTreatment $treatment): array => [$treatment->value => __($treatment->label())])->all(),
            'lastChange' => $setting->exists
                ? Activity::query()->with('causer')->whereMorphedTo('subject', $setting)->latest('id')->first()
                : null,
            'timezone' => config('cendana.timezone'),
        ]);
    }
}
