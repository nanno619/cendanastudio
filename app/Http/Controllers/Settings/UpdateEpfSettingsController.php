<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\UpdateEpfSettingsRequest;
use App\Models\EpfSetting;
use Illuminate\Http\RedirectResponse;

class UpdateEpfSettingsController extends Controller
{
    /**
     * Save the EPF contribution rules, creating their single row on first save. The top-up rate is
     * cleared when top-up is off.
     */
    public function __invoke(UpdateEpfSettingsRequest $request): RedirectResponse
    {
        EpfSetting::query()->firstOrNew()->fill([
            'employer_top_up_rate' => null,
            ...$request->validated(),
        ])->save();

        return redirect()->route('settings.payroll.epf')
            ->with('status', __('EPF settings were saved.'));
    }
}
