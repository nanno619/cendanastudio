<?php

namespace App\Http\Controllers\Settings;

use App\Enums\OvertimeCapture;
use App\Enums\OvertimeRounding;
use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\AttendanceOvertimeSetting;
use Illuminate\View\View;
use Spatie\Activitylog\Models\Activity;

class AttendanceOvertimeSettingsController extends Controller
{
    /**
     * Show how overtime is recorded and approved, and who last changed it.
     */
    public function __invoke(): View
    {
        $setting = AttendanceOvertimeSetting::current();

        return view('settings.hadir.overtime', [
            'setting' => $setting,
            'captureMethods' => collect(OvertimeCapture::cases())->mapWithKeys(fn (OvertimeCapture $method): array => [$method->value => __($method->label())])->all(),
            'roundings' => collect(OvertimeRounding::cases())->mapWithKeys(fn (OvertimeRounding $rounding): array => [$rounding->value => __($rounding->label())])->all(),
            'roles' => collect(Role::cases())->mapWithKeys(fn (Role $role): array => [$role->value => __($role->value)])->all(),
            'lastChange' => $setting->exists
                ? Activity::query()->with('causer')->whereMorphedTo('subject', $setting)->latest('id')->first()
                : null,
            'timezone' => config('cendana.timezone'),
        ]);
    }
}
