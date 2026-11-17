<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\ClaimApprovalRoute;
use App\Models\User;
use Illuminate\View\View;
use Spatie\Activitylog\Models\Activity;

class EditClaimApprovalRouteController extends Controller
{
    /**
     * Show a claim approval route's levels; its role can't change.
     */
    public function __invoke(ClaimApprovalRoute $claimApprovalRoute): View
    {
        return view('settings.claim.approval.form', [
            'approvalRoute' => $claimApprovalRoute,
            'users' => User::query()->orderBy('name')->get(['id', 'name']),
            'lastChange' => Activity::query()->with('causer')->whereMorphedTo('subject', $claimApprovalRoute)->latest('id')->first(),
            'timezone' => config('cendana.timezone'),
        ]);
    }
}
