<?php

namespace App\Http\Controllers\Settings;

use App\Enums\LookupCategory;
use App\Http\Controllers\Controller;
use App\Models\LeavePolicy;
use App\Models\LookupItem;
use Illuminate\View\View;

class LeavePolicyController extends Controller
{
    /**
     * List the active leave types with their policies.
     */
    public function __invoke(): View
    {
        $leaveTypes = LookupItem::query()->inCategory(LookupCategory::LeaveType)->active()->ordered()->get();

        return view('settings.leave.policies.index', [
            'leaveTypes' => $leaveTypes,
            'policies' => LeavePolicy::query()->whereIn('leave_type', $leaveTypes->pluck('code'))->get()->keyBy('leave_type'),
        ]);
    }
}
