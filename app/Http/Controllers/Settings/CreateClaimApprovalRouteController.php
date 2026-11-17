<?php

namespace App\Http\Controllers\Settings;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\ClaimApprovalRoute;
use App\Models\User;
use Illuminate\View\View;

class CreateClaimApprovalRouteController extends Controller
{
    /**
     * Show the form for a new route, offering only roles (and the default) that don't have one yet.
     */
    public function __invoke(): View
    {
        $taken = ClaimApprovalRoute::query()->pluck('applicant_role');

        return view('settings.claim.approval.form', [
            'approvalRoute' => new ClaimApprovalRoute(['levels' => []]),
            'availableRoles' => array_values(array_filter(Role::cases(), fn (Role $role): bool => ! $taken->contains($role))),
            'defaultAvailable' => ! $taken->contains(null),
            'users' => User::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }
}
