<?php

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Support\ClientForm;
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

        return view('crm.form', ['client' => new Client, ...ClientForm::options()]);
    }
}
