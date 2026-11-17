<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\UpdateOvertimeSettingsRequest;
use App\Models\OvertimeSetting;
use Illuminate\Http\RedirectResponse;

class UpdateOvertimeSettingsController extends Controller
{
    /**
     * Save the overtime pay rules, creating their single row on first save. The wage limit is
     * cleared when every staff member is eligible.
     */
    public function __invoke(UpdateOvertimeSettingsRequest $request): RedirectResponse
    {
        OvertimeSetting::query()->firstOrNew()->fill([
            'eligibility_wage_limit' => null,
            ...$request->validated(),
        ])->save();

        return redirect()->route('settings.payroll.overtime')
            ->with('status', __('Overtime settings were saved.'));
    }
}
