<?php

namespace App\Http\Controllers\Settings;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\ClaimSetting;
use App\Models\VisitSetting;
use Illuminate\View\View;
use Spatie\Activitylog\Models\Activity;

class VisitSettingsController extends Controller
{
    /**
     * Show the rules for work away from the office and who last changed them.
     */
    public function __invoke(): View
    {
        $setting = VisitSetting::current();

        return view('settings.hadir.visits', [
            'setting' => $setting,
            'roles' => Role::cases(),
            'roleOptions' => collect(Role::cases())->mapWithKeys(fn (Role $role): array => [$role->value => __($role->value)])->all(),
            'mileageEnabled' => ClaimSetting::current()->mileage_enabled,
            'lastChange' => $setting->exists
                ? Activity::query()->with('causer')->whereMorphedTo('subject', $setting)->latest('id')->first()
                : null,
            'timezone' => config('cendana.timezone'),
        ]);
    }
}
