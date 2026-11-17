<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\ClaimApprovalRoute;
use Illuminate\Http\RedirectResponse;

class DestroyClaimApprovalRouteController extends Controller
{
    /**
     * Delete a role's route, so the role falls back to the default route. The default route can't be deleted.
     */
    public function __invoke(ClaimApprovalRoute $claimApprovalRoute): RedirectResponse
    {
        abort_if($claimApprovalRoute->isDefault(), 403);

        $claimApprovalRoute->delete();

        return redirect()->route('settings.claim.approval.index')
            ->with('status', __('Approval route was deleted.'));
    }
}
