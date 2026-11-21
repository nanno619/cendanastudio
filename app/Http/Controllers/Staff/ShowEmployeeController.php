<?php

namespace App\Http\Controllers\Staff;

use App\Enums\DocumentType;
use App\Enums\LookupCategory;
use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\FileSecuritySetting;
use App\Models\LookupItem;
use App\Models\User;
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
        $employee->load(['user.roles', 'reportingManager.user:id,name', 'directReports.user:id,name', 'emergencyContacts', 'media']);

        return view('staff.show', [
            'employee' => $employee,
            'labels' => collect(LookupCategory::cases())->mapWithKeys(fn (LookupCategory $category): array => [$category->value => LookupItem::labels($category)])->only([
                LookupCategory::Gender->value, LookupCategory::Race->value, LookupCategory::MaritalStatus->value, LookupCategory::MalaysianStates->value,
                LookupCategory::Department->value, LookupCategory::Position->value, LookupCategory::Subsidiary->value, LookupCategory::Banks->value, LookupCategory::Relationship->value,
            ])->all(),
            'canEdit' => $employee->isEditableBy($request->user()),
            'lastChange' => Activity::query()->with('causer')->whereMorphedTo('subject', $employee)
                ->where(fn ($query) => $query->whereNull('event')->orWhere('event', '!=', 'document-downloaded'))
                ->latest('id')->first(),
            'timezone' => config('cendana.timezone'),
            'documents' => $employee->getMedia('documents')->sortByDesc('created_at')->values(),
            'documentUploaders' => User::query()->whereKey($employee->getMedia('documents')->map->getCustomProperty('uploaded_by')->filter()->unique())->pluck('name', 'id'),
            'documentTypes' => collect(DocumentType::cases())->mapWithKeys(fn (DocumentType $type): array => [$type->value => __($type->label())])->all(),
            'uploadPolicy' => FileSecuritySetting::current(),
        ]);
    }
}
