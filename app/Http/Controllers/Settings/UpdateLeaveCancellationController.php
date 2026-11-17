<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\LeaveSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class UpdateLeaveCancellationController extends Controller
{
    /**
     * Save whether cancelling approved leave needs approval, on the leave settings row
     * (created with the defaults if General hasn't been saved yet).
     */
    public function __invoke(Request $request): RedirectResponse
    {
        $validated = $request->validate(['cancellation_requires_approval' => ['required', 'boolean']]);

        LeaveSetting::current()->fill($validated)->save();

        return redirect()->route('settings.leave.approval.index')
            ->with('status', __('Cancellation rule was saved.'));
    }
}
