<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DownloadMyDocumentController extends Controller
{
    /**
     * Stream one of the signed-in staff member's own documents, audited like HR downloads. Anyone
     * else's document is not found.
     */
    public function __invoke(Request $request, string $document): Response
    {
        $employee = $request->user()->employee ?? abort(404);
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
