<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\UpdateAttendanceSettingsRequest;
use App\Models\AttendanceSetting;
use Illuminate\Http\RedirectResponse;

class UpdateAttendanceSettingsController extends Controller
{
    /**
     * Save the attendance rules, creating their single row on first save. The office location and
     * radius are cleared while the geofence is off.
     */
    public function __invoke(UpdateAttendanceSettingsRequest $request): RedirectResponse
    {
        AttendanceSetting::query()->firstOrNew()->fill([
            ...array_fill_keys(AttendanceSetting::GEOFENCE_FIELDS, null),
            ...$request->validated(),
        ])->save();

        return redirect()->route('settings.hadir.general')
            ->with('status', __('Attendance settings were saved.'));
    }
}
