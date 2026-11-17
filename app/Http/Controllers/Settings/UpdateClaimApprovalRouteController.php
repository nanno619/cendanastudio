<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\SaveClaimApprovalRouteRequest;
use App\Models\ClaimApprovalRoute;
use Illuminate\Http\RedirectResponse;

class UpdateClaimApprovalRouteController extends Controller
{
    /**
     * Save a claim approval route's levels.
     */
    public function __invoke(SaveClaimApprovalRouteRequest $request, ClaimApprovalRoute $claimApprovalRoute): RedirectResponse
    {
        $claimApprovalRoute->update(['levels' => $request->levels()]);

        return redirect()->route('settings.claim.approval.index')
            ->with('status', __('Approval route was saved.'));
    }
}
