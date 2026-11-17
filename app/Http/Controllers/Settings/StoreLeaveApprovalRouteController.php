<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\SaveLeaveApprovalRouteRequest;
use App\Models\LeaveApprovalRoute;
use Illuminate\Http\RedirectResponse;

class StoreLeaveApprovalRouteController extends Controller
{
    /**
     * Add an approval route for a role, or the default route.
     */
    public function __invoke(SaveLeaveApprovalRouteRequest $request): RedirectResponse
    {
        LeaveApprovalRoute::create([
            'applicant_role' => $request->validated('applicant_role') ?: null,
            'levels' => $request->levels(),
        ]);

        return redirect()->route('settings.leave.approval.index')
            ->with('status', __('Approval route was saved.'));
    }
}
