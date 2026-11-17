<?php

namespace App\Http\Controllers\Settings;

use App\Enums\LookupCategory;
use App\Http\Controllers\Controller;
use App\Models\LeavePolicy;
use App\Models\LookupItem;
use Illuminate\View\View;
use Spatie\Activitylog\Models\Activity;

class EditLeavePolicyController extends Controller
{
    /**
     * Show one active leave type's policy form, empty until it is first saved.
     */
    public function __invoke(string $leaveType): View
    {
        $item = LookupItem::query()->inCategory(LookupCategory::LeaveType)->active()->where('code', $leaveType)->firstOrFail();
        $policy = LeavePolicy::for($item->code);

        return view('settings.leave.policies.edit', [
            'leaveType' => $item,
            'policy' => $policy,
            'lastChange' => $policy->exists
                ? Activity::query()->with('causer')->whereMorphedTo('subject', $policy)->latest('id')->first()
                : null,
            'timezone' => config('cendana.timezone'),
        ]);
    }
}
