<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\UpdateClaimSettingsRequest;
use App\Models\ClaimSetting;
use Illuminate\Http\RedirectResponse;

class UpdateClaimSettingsController extends Controller
{
    /**
     * Save the claim rules, creating their single row on first save. The mileage rate is cleared
     * while mileage claims are off.
     */
    public function __invoke(UpdateClaimSettingsRequest $request): RedirectResponse
    {
        ClaimSetting::query()->firstOrNew()->fill([
            'mileage_rate' => null,
            ...$request->validated(),
        ])->save();

        return redirect()->route('settings.claim.general')
            ->with('status', __('Claim settings were saved.'));
    }
}
