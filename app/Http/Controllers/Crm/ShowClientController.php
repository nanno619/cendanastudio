<?php

namespace App\Http\Controllers\Crm;

use App\Enums\LookupCategory;
use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\LookupItem;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShowClientController extends Controller
{
    /**
     * Show a client, its status and its contact persons.
     */
    public function __invoke(Request $request, Client $client): View
    {
        return view('crm.show', [
            'client' => $client->load('contacts'),
            'stateLabel' => $client->state ? (LookupItem::labels(LookupCategory::MalaysianStates)[$client->state] ?? $client->state) : null,
            'canEdit' => Client::isEditableBy($request->user()),
        ]);
    }
}
