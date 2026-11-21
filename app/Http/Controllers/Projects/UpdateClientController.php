<?php

namespace App\Http\Controllers\Projects;

use App\Http\Controllers\Controller;
use App\Http\Requests\Projects\SaveClientRequest;
use App\Models\Client;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class UpdateClientController extends Controller
{
    /**
     * Save changes to a client and its contact persons.
     */
    public function __invoke(SaveClientRequest $request, Client $client): RedirectResponse
    {
        DB::transaction(function () use ($request, $client): void {
            $client->update($request->clientAttributes());
            $client->syncContacts($request->contacts());
        });

        return redirect()->route('clients.show', $client)->with('status', __('Changes to :name were saved.', ['name' => $client->name]));
    }
}
