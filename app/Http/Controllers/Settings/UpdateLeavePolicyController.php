<?php

namespace App\Http\Controllers\Settings;

use App\Enums\LookupCategory;
use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\UpdateLeavePolicyRequest;
use App\Models\LeavePolicy;
use App\Models\LookupItem;
use Illuminate\Http\RedirectResponse;

class UpdateLeavePolicyController extends Controller
{
    /**
     * Save one active leave type's policy, creating it on first save.
     */
    public function __invoke(UpdateLeavePolicyRequest $request, string $leaveType): RedirectResponse
    {
        $item = LookupItem::query()->inCategory(LookupCategory::LeaveType)->active()->where('code', $leaveType)->firstOrFail();
        $carryForward = $request->boolean('allow_carry_forward');

        LeavePolicy::query()->firstOrNew(['leave_type' => $item->code])->fill([
            'entitlements' => $request->entitlements(),
            'is_paid' => $request->boolean('is_paid'),
            'requires_attachment' => $request->boolean('requires_attachment'),
            'allow_carry_forward' => $carryForward,
            'carry_forward_max_days' => $carryForward ? $request->validated('carry_forward_max_days') : null,
            'carry_forward_expiry_months' => $carryForward ? $request->validated('carry_forward_expiry_months') : null,
        ])->save();

        return redirect()->route('settings.leave.policies.index')
            ->with('status', __('Leave policy was saved.'));
    }
}
