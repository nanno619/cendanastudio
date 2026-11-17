<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\UpdatePayslipSettingsRequest;
use App\Models\PayslipSetting;
use Illuminate\Http\RedirectResponse;

class UpdatePayslipSettingsController extends Controller
{
    /**
     * Save the payslip options, creating their single row on first save.
     */
    public function __invoke(UpdatePayslipSettingsRequest $request): RedirectResponse
    {
        PayslipSetting::query()->firstOrNew()->fill($request->validated())->save();

        return redirect()->route('settings.payroll.payslip')
            ->with('status', __('Payslip settings were saved.'));
    }
}
