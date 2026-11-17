<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\UpdateProRataSettingsRequest;
use App\Models\ProRataSetting;
use Illuminate\Http\RedirectResponse;

class UpdateProRataSettingsController extends Controller
{
    /**
     * Save the part-month wage rules, creating their single row on first save. The fixed number of
     * days is cleared when neither basis uses it.
     */
    public function __invoke(UpdateProRataSettingsRequest $request): RedirectResponse
    {
        ProRataSetting::query()->firstOrNew()->fill([
            'fixed_days' => null,
            ...$request->validated(),
        ])->save();

        return redirect()->route('settings.payroll.pro-rata')
            ->with('status', __('Pro-rata settings were saved.'));
    }
}
