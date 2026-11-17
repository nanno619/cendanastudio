<?php

namespace App\Http\Controllers\Staff;

use App\Enums\DocumentType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\UploadEmployeeDocumentRequest;
use App\Models\Employee;
use Illuminate\Http\RedirectResponse;

class UploadEmployeeDocumentController extends Controller
{
    /**
     * Store a document on the staff record's private documents collection and audit it.
     */
    public function __invoke(UploadEmployeeDocumentRequest $request, Employee $employee): RedirectResponse
    {
        $type = DocumentType::from($request->validated('type'));

        $media = $employee->addMediaFromRequest('document')
            ->withCustomProperties([
                'type' => $type->value,
                'note' => $request->validated('note'),
                'uploaded_by' => $request->user()->id,
            ])
            ->toMediaCollection('documents');

        activity()
            ->causedBy($request->user())
            ->performedOn($employee)
            ->event('document-uploaded')
            ->withProperties(['file' => $media->file_name, 'type' => $type->value])
            ->log('Uploaded staff document');

        return redirect()->route('staff.show', $employee)->withFragment('documents')
            ->with('status', __(':file was uploaded.', ['file' => $media->file_name]));
    }
}
