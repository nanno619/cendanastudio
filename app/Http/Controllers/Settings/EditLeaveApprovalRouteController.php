<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\LeaveApprovalRoute;
use App\Models\User;
use Illuminate\View\View;
use Spatie\Activitylog\Models\Activity;

class EditLeaveApprovalRouteController extends Controller
{
    /**
     * Show an approval route's levels; its role can't change.
     */
    public function __invoke(LeaveApprovalRoute $approvalRoute): View
    {
        return view('settings.leave.approval.form', [
            'approvalRoute' => $approvalRoute,
            'users' => User::query()->orderBy('name')->get(['id', 'name']),
            'lastChange' => Activity::query()->with('causer')->whereMorphedTo('subject', $approvalRoute)->latest('id')->first(),
            'timezone' => config('cendana.timezone'),
        ]);
    }
}
