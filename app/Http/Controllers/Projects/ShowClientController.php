<?php

namespace App\Http\Controllers\Projects;

use App\Enums\LookupCategory;
use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\LookupItem;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShowClientController extends Controller
{
    /**
     * Show a client, its contact persons and the projects done for it that this person can see.
     */
    public function __invoke(Request $request, Client $client): View
    {
        $client->load('contacts');

        return view('projects.clients.show', [
            'client' => $client,
            'projects' => $client->projects()->visibleTo($request->user())->with('projectManager.user:id,name')->latest('start_date')->get(),
            'stateLabel' => $client->state ? (LookupItem::labels(LookupCategory::MalaysianStates)[$client->state] ?? $client->state) : null,
            'canEdit' => Client::isEditableBy($request->user()),
            'canCreateProject' => Project::isCreatableBy($request->user()),
        ]);
    }
}
