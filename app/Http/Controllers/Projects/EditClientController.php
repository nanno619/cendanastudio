<?php

namespace App\Http\Controllers\Projects;

use App\Enums\LookupCategory;
use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\LookupItem;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EditClientController extends Controller
{
    /**
     * Show the form for changing a client and its contact persons.
     */
    public function __invoke(Request $request, Client $client): View
    {
        abort_unless(Client::isEditableBy($request->user()), 403);

        $states = LookupItem::options(LookupCategory::MalaysianStates);
        if (filled($client->state) && ! isset($states[$client->state])) {
            $states[$client->state] = LookupItem::labels(LookupCategory::MalaysianStates)[$client->state] ?? $client->state;
        }

        return view('projects.clients.form', ['client' => $client->load('contacts'), 'states' => $states]);
    }
}
