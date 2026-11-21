<?php

namespace App\Http\Controllers\Staff;

use App\Enums\DocumentType;
use App\Enums\LookupCategory;
use App\Http\Controllers\Controller;
use App\Models\LookupItem;
use App\Support\StaffForm;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MyProfileController extends Controller
{
    /**
     * Show the signed-in person's own staff record: what HR manages, read-only, and the personal details
     * and emergency contacts they can change. Accounts without a staff record see a short notice.
     */
    public function __invoke(Request $request): View
    {
        $employee = $request->user()->employee;

        if ($employee === null) {
            return view('staff.my-profile', ['employee' => null]);
        }

        $employee->load(['reportingManager.user:id,name', 'emergencyContacts', 'media']);

        return view('staff.my-profile', [
            'employee' => $employee,
            ...StaffForm::personalOptions($employee),
            'labels' => collect([LookupCategory::Department, LookupCategory::Position, LookupCategory::Subsidiary, LookupCategory::Banks])
                ->mapWithKeys(fn (LookupCategory $category): array => [$category->value => LookupItem::labels($category)])->all(),
            'documents' => $employee->getMedia('documents')->sortByDesc('created_at')->values(),
            'documentTypes' => collect(DocumentType::cases())->mapWithKeys(fn (DocumentType $type): array => [$type->value => __($type->label())])->all(),
            'timezone' => config('cendana.timezone'),
        ]);
    }
}
