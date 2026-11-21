<?php

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Support\ClientForm;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EditClientController extends Controller
{
    /**
     * Show the form for changing a client, its status and its contact persons.
     */
    public function __invoke(Request $request, Client $client): View
    {
        abort_unless(Client::isEditableBy($request->user()), 403);

        return view('crm.form', ['client' => $client->load('contacts'), ...ClientForm::options($client)]);
    }
}
