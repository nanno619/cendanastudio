<?php

namespace App\Http\Controllers\Settings;

use App\Enums\Weekday;
use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\UpdateLeaveSettingsRequest;
use App\Models\LeaveSetting;
use Illuminate\Http\RedirectResponse;

class UpdateLeaveSettingsController extends Controller
{
    /**
     * Save the company-wide leave rules, creating their single row on first save.
     */
    public function __invoke(UpdateLeaveSettingsRequest $request): RedirectResponse
    {
        $setting = LeaveSetting::query()->firstOrNew();
        $validated = $request->validated();

        // Store days Monday first so the audit trail only shows a change when the selection changes.
        $validated['working_days'] = array_values(array_filter(
            Weekday::cases(),
            fn (Weekday $day): bool => in_array((string) $day->value, array_map('strval', $validated['working_days']), true),
        ));
        $validated['max_negative_days'] ??= null;

        $setting->fill($validated)->save();

        return redirect()->route('settings.leave.general')
            ->with('status', __('Leave settings were saved.'));
    }
}
