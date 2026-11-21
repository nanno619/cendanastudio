<?php

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
use App\Http\Requests\Crm\SaveClientRequest;
use App\Models\Client;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class StoreClientController extends Controller
{
    /**
     * Add a client with its contact persons.
     */
    public function __invoke(SaveClientRequest $request): RedirectResponse
    {
        $client = DB::transaction(function () use ($request): Client {
            $client = Client::create($request->clientAttributes());
            $client->syncContacts($request->contacts());

            return $client;
        });

        return redirect()->route('crm.clients.show', $client)->with('status', __(':name was added.', ['name' => $client->name]));
    }
}
