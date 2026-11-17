<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DownloadEmployeeDocumentController extends Controller
{
    /**
     * Stream one of a staff record's documents from the private disk as a download, and audit who
     * fetched it. Route middleware limits this to record managers.
     */
    public function __invoke(Request $request, Employee $employee, string $document): Response
    {
        $media = $employee->document($document);

        activity()
            ->causedBy($request->user())
            ->performedOn($employee)
            ->event('document-downloaded')
            ->withProperties(['file' => $media->file_name, 'type' => $media->getCustomProperty('type')])
            ->log('Downloaded staff document');

        return $media->toResponse($request);
    }
}
