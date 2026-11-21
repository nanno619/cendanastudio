<?php

namespace App\Http\Controllers\Projects;

use App\Http\Controllers\Controller;
use App\Http\Requests\Projects\SaveProjectRequest;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;

class UpdateProjectController extends Controller
{
    /**
     * Save changes to a project.
     */
    public function __invoke(SaveProjectRequest $request, Project $project): RedirectResponse
    {
        $project->update($request->projectAttributes());

        return redirect()->route('projects.show', $project)->with('status', __('Changes to :name were saved.', ['name' => $project->name]));
    }
}
