<?php

namespace App\Http\Controllers\Projects;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClientController extends Controller
{
    /**
     * List clients by name, searchable by name, SSM number or email. Route middleware limits this to
     * Client::VIEWER_ROLES.
     */
    public function __invoke(Request $request): View
    {
        $filters = $request->validate(['q' => ['nullable', 'string', 'max:100']]);

        $clients = Client::query()
            ->withCount('projects')
            ->with(['contacts' => fn ($query) => $query->where('sort_order', 1)])
            ->when($filters['q'] ?? null, fn (Builder $query, string $keyword) => $query->where(
                fn (Builder $query) => $query
                    ->where('name', 'like', "%{$keyword}%")
                    ->orWhere('registration_no', 'like', '%'.preg_replace('/[\s-]/', '', $keyword).'%')
                    ->orWhere('email', 'like', "%{$keyword}%"),
            ))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('projects.clients.index', [
            'clients' => $clients,
            'filters' => $filters,
            'canEdit' => Client::isEditableBy($request->user()),
        ]);
    }
}
