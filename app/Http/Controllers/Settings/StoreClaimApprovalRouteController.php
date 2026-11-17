<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\SaveClaimApprovalRouteRequest;
use App\Models\ClaimApprovalRoute;
use Illuminate\Http\RedirectResponse;

class StoreClaimApprovalRouteController extends Controller
{
    /**
     * Add a claim approval route for a role, or the default route.
     */
    public function __invoke(SaveClaimApprovalRouteRequest $request): RedirectResponse
    {
        ClaimApprovalRoute::create([
            'applicant_role' => $request->validated('applicant_role') ?: null,
            'levels' => $request->levels(),
        ]);

        return redirect()->route('settings.claim.approval.index')
            ->with('status', __('Approval route was saved.'));
    }
}
