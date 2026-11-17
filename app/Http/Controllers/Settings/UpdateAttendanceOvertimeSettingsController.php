<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\UpdateAttendanceOvertimeSettingsRequest;
use App\Models\AttendanceOvertimeSetting;
use Illuminate\Http\RedirectResponse;

class UpdateAttendanceOvertimeSettingsController extends Controller
{
    /**
     * Save how overtime is recorded and approved, creating the single row on first save.
     */
    public function __invoke(UpdateAttendanceOvertimeSettingsRequest $request): RedirectResponse
    {
        AttendanceOvertimeSetting::query()->firstOrNew()->fill($request->validated())->save();

        return redirect()->route('settings.hadir.overtime')
            ->with('status', __('Overtime settings were saved.'));
    }
}
