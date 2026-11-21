<?php

namespace App\Http\Controllers\Projects;

use App\Enums\ProjectStatus;
use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Project;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProjectController extends Controller
{
    /**
     * List the projects this person can see, newest first, filtered by keyword, client and status. Without
     * a status filter, completed and cancelled projects are hidden.
     */
    public function __invoke(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'client' => ['nullable', 'string', 'max:26'],
            'status' => ['nullable', Rule::enum(ProjectStatus::class)],
        ]);

        $projects = Project::query()
            ->visibleTo($request->user())
            ->with(['client:id,ulid,name', 'projectManager.user:id,name'])
            ->when($filters['q'] ?? null, fn (Builder $query, string $keyword) => $query->where(
                fn (Builder $query) => $query->where('code', 'like', "%{$keyword}%")->orWhere('name', 'like', "%{$keyword}%"),
            ))
            ->when($filters['client'] ?? null, fn (Builder $query, string $client) => $query->whereRelation('client', 'ulid', $client))
            ->when(
                $filters['status'] ?? null,
                fn (Builder $query, string $status) => $query->where('status', $status),
                fn (Builder $query) => $query->whereIn('status', collect(ProjectStatus::cases())->filter->isOpen()->map->value->all()),
            )
            ->latest('start_date')
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('projects.index', [
            'projects' => $projects,
            'filters' => $filters,
            'clients' => Client::query()->orderBy('name')->pluck('name', 'ulid')->all(),
            'statuses' => collect(ProjectStatus::cases())->mapWithKeys(fn (ProjectStatus $status): array => [$status->value => __($status->label())])->all(),
            'canCreate' => Project::isCreatableBy($request->user()),
        ]);
    }
}
