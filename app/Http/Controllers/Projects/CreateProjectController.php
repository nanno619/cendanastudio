<?php

namespace App\Http\Controllers\Projects;

use App\Enums\ProjectStatus;
use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Support\ProjectForm;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CreateProjectController extends Controller
{
    /**
     * Show the form for a new project. A client can be preselected with ?client={ulid}.
     */
    public function __invoke(Request $request): View
    {
        abort_unless(Project::isCreatableBy($request->user()), 403);

        return view('projects.form', [
            'project' => new Project(['status' => ProjectStatus::Planning]),
            'selectedClient' => $request->query('client'),
            'canAssignManager' => Project::canAssignManager($request->user()),
            ...ProjectForm::options(),
        ]);
    }
}
