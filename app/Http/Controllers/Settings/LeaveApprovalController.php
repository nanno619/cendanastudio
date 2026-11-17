<?php

namespace App\Http\Controllers\Settings;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\LeaveApprovalRoute;
use App\Models\LeaveSetting;
use App\Models\User;
use Illuminate\View\View;

class LeaveApprovalController extends Controller
{
    /**
     * List the approval routes (default first, then in role order) and the cancellation rule.
     */
    public function __invoke(): View
    {
        $routes = LeaveApprovalRoute::query()->get()
            ->sortBy(fn (LeaveApprovalRoute $route): int => $route->isDefault() ? -1 : array_search($route->applicant_role, Role::cases(), true))
            ->values();

        $userIds = $routes->flatMap(fn (LeaveApprovalRoute $route): array => collect($route->levels)->where('type', 'user')->pluck('value')->all())->unique();

        return view('settings.leave.approval.index', [
            'routes' => $routes,
            'users' => User::query()->whereKey($userIds)->pluck('name', 'id'),
            'hasDefault' => $routes->contains(fn (LeaveApprovalRoute $route): bool => $route->isDefault()),
            'canAdd' => $routes->count() < count(Role::cases()) + 1,
            'setting' => LeaveSetting::current(),
        ]);
    }
}
