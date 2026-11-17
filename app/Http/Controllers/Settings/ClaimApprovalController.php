<?php

namespace App\Http\Controllers\Settings;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\ClaimApprovalRoute;
use App\Models\User;
use Illuminate\View\View;

class ClaimApprovalController extends Controller
{
    /**
     * List the claim approval routes, default first, then in role order.
     */
    public function __invoke(): View
    {
        $routes = ClaimApprovalRoute::query()->get()
            ->sortBy(fn (ClaimApprovalRoute $route): int => $route->isDefault() ? -1 : array_search($route->applicant_role, Role::cases(), true))
            ->values();

        $userIds = $routes->flatMap(fn (ClaimApprovalRoute $route): array => collect($route->levels)->where('type', 'user')->pluck('value')->all())->unique();

        return view('settings.claim.approval.index', [
            'routes' => $routes,
            'users' => User::query()->whereKey($userIds)->pluck('name', 'id'),
            'hasDefault' => $routes->contains(fn (ClaimApprovalRoute $route): bool => $route->isDefault()),
            'canAdd' => $routes->count() < count(Role::cases()) + 1,
        ]);
    }
}
