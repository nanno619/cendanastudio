<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DeleteEmployeeDocumentController extends Controller
{
    /**
     * Delete a document and its file, and audit it. Only people who may change the record can.
     */
    public function __invoke(Request $request, Employee $employee, string $document): RedirectResponse
    {
        abort_unless($employee->isEditableBy($request->user()), 403);

        $media = $employee->document($document);
        $fileName = $media->file_name;

        activity()
            ->causedBy($request->user())
            ->performedOn($employee)
            ->event('document-deleted')
            ->withProperties(['file' => $fileName, 'type' => $media->getCustomProperty('type')])
            ->log('Deleted staff document');

        $media->delete();

        return redirect()->route('staff.show', $employee)->withFragment('documents')
            ->with('status', __(':file was deleted.', ['file' => $fileName]));
    }
}
