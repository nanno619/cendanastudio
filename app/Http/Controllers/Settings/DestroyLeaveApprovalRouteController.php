<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\LeaveApprovalRoute;
use Illuminate\Http\RedirectResponse;

class DestroyLeaveApprovalRouteController extends Controller
{
    /**
     * Delete a role's route, so the role falls back to the default route. The default route can't be deleted.
     */
    public function __invoke(LeaveApprovalRoute $approvalRoute): RedirectResponse
    {
        abort_if($approvalRoute->isDefault(), 403);

        $approvalRoute->delete();

        return redirect()->route('settings.leave.approval.index')
            ->with('status', __('Approval route was deleted.'));
    }
}
