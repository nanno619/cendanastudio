<?php

namespace App\Http\Controllers\Projects;

use App\Http\Controllers\Controller;
use App\Http\Requests\Projects\SaveProjectRequest;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;

class StoreProjectController extends Controller
{
    /**
     * Add a project.
     */
    public function __invoke(SaveProjectRequest $request): RedirectResponse
    {
        $project = Project::create($request->projectAttributes());

        return redirect()->route('projects.show', $project)->with('status', __(':name was added.', ['name' => $project->name]));
    }
}
