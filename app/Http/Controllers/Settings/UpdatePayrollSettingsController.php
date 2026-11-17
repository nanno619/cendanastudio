<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\UpdatePayrollSettingsRequest;
use App\Models\PayrollSetting;
use Illuminate\Http\RedirectResponse;

class UpdatePayrollSettingsController extends Controller
{
    /**
     * Save the company-wide payroll rules, creating their single row on first save. The pay day is
     * cleared for the last working day and the approver when approval is off.
     */
    public function __invoke(UpdatePayrollSettingsRequest $request): RedirectResponse
    {
        PayrollSetting::query()->firstOrNew()->fill([
            'pay_day' => null,
            'approver_role' => null,
            ...$request->validated(),
        ])->save();

        return redirect()->route('settings.payroll.general')
            ->with('status', __('Payroll settings were saved.'));
    }
}
