<?php

namespace App\Http\Controllers\Crm;

use App\Enums\ClientStatus;
use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ClientController extends Controller
{
    /**
     * The CRM page: how many clients are active, closed or blacklisted, and the client list by name,
     * searchable by name, SSM number or email and filtered by status. Route middleware limits this to
     * Client::VIEWER_ROLES.
     */
    public function __invoke(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::enum(ClientStatus::class)],
        ]);

        $counts = Client::query()->toBase()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

        $clients = Client::query()
            ->with(['contacts' => fn ($query) => $query->where('sort_order', 1)])
            ->when($filters['q'] ?? null, fn (Builder $query, string $keyword) => $query->where(
                fn (Builder $query) => $query
                    ->where('name', 'like', "%{$keyword}%")
                    ->orWhere('registration_no', 'like', '%'.preg_replace('/[\s-]/', '', $keyword).'%')
                    ->orWhere('email', 'like', "%{$keyword}%"),
            ))
            ->when($filters['status'] ?? null, fn (Builder $query, string $status) => $query->where('status', $status))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('crm.index', [
            'clients' => $clients,
            'filters' => $filters,
            'counts' => collect(ClientStatus::cases())->mapWithKeys(fn (ClientStatus $status): array => [$status->value => (int) ($counts[$status->value] ?? 0)])->all(),
            'statuses' => collect(ClientStatus::cases())->mapWithKeys(fn (ClientStatus $status): array => [$status->value => __($status->label())])->all(),
            'canEdit' => Client::isEditableBy($request->user()),
        ]);
    }
}
