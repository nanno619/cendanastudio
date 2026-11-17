<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\SaveLeaveApprovalRouteRequest;
use App\Models\LeaveApprovalRoute;
use Illuminate\Http\RedirectResponse;

class UpdateLeaveApprovalRouteController extends Controller
{
    /**
     * Save an approval route's levels.
     */
    public function __invoke(SaveLeaveApprovalRouteRequest $request, LeaveApprovalRoute $approvalRoute): RedirectResponse
    {
        $approvalRoute->update(['levels' => $request->levels()]);

        return redirect()->route('settings.leave.approval.index')
            ->with('status', __('Approval route was saved.'));
    }
}
