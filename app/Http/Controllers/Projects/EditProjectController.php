<?php

namespace App\Http\Controllers\Projects;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Support\ProjectForm;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EditProjectController extends Controller
{
    /**
     * Show the form for changing a project.
     */
    public function __invoke(Request $request, Project $project): View
    {
        abort_unless($project->isVisibleTo($request->user()), 404);
        abort_unless($project->isEditableBy($request->user()), 403);

        return view('projects.form', [
            'project' => $project->load(['client:id,ulid', 'projectManager.user:id,name']),
            'selectedClient' => $project->client->ulid,
            'canAssignManager' => Project::canAssignManager($request->user()),
            ...ProjectForm::options($project),
        ]);
    }
}
