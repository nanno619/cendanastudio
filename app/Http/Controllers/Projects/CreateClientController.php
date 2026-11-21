<?php

namespace App\Http\Controllers\Projects;

use App\Enums\LookupCategory;
use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\LookupItem;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CreateClientController extends Controller
{
    /**
     * Show the form for a new client.
     */
    public function __invoke(Request $request): View
    {
        abort_unless(Client::isEditableBy($request->user()), 403);

        return view('projects.clients.form', [
            'client' => new Client,
            'states' => LookupItem::options(LookupCategory::MalaysianStates),
        ]);
    }
}
