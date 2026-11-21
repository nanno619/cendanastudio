<?php

namespace App\Http\Controllers\Projects;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShowProjectController extends Controller
{
    /**
     * Show a project. Projects this person can't see answer 404, so their existence isn't revealed.
     */
    public function __invoke(Request $request, Project $project): View
    {
        abort_unless($project->isVisibleTo($request->user()), 404);

        return view('projects.show', [
            'project' => $project->load(['client.contacts', 'projectManager.user:id,name,email']),
            'canEdit' => $project->isEditableBy($request->user()),
            'canViewClient' => Client::isViewableBy($request->user()),
        ]);
    }
}
