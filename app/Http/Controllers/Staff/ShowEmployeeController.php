<?php

namespace App\Http\Controllers\Staff;

use App\Enums\LookupCategory;
use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\LookupItem;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Activitylog\Models\Activity;

class ShowEmployeeController extends Controller
{
    /**
     * Show a staff member's full record.
     */
    public function __invoke(Request $request, Employee $employee): View
    {
        $employee->load(['user.roles', 'reportingManager.user:id,name', 'directReports.user:id,name', 'emergencyContacts']);

        return view('staff.show', [
            'employee' => $employee,
            'labels' => collect(LookupCategory::cases())->mapWithKeys(fn (LookupCategory $category): array => [$category->value => LookupItem::labels($category)])->only([
                LookupCategory::Gender->value, LookupCategory::Race->value, LookupCategory::MaritalStatus->value, LookupCategory::MalaysianStates->value,
                LookupCategory::Department->value, LookupCategory::Position->value, LookupCategory::Banks->value, LookupCategory::Relationship->value,
            ])->all(),
            'canEdit' => ! $employee->user->hasRole(Role::SuperAdmin) || $request->user()->hasRole(Role::SuperAdmin),
            'lastChange' => Activity::query()->with('causer')->whereMorphedTo('subject', $employee)->latest('id')->first(),
            'timezone' => config('cendana.timezone'),
        ]);
    }
}
