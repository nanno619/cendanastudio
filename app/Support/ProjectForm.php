<?php

namespace App\Support;

use App\Enums\ProjectStatus;
use App\Models\Client;
use App\Models\Employee;
use App\Models\Project;

/**
 * Dropdown options for the project add/edit form.
 */
class ProjectForm
{
    /**
     * Clients by name and staff who haven't left (plus the current manager if they have), keyed by ULID.
     *
     * @return array{clients: array<string, string>, managers: array<string, string>, statuses: array<string, string>}
     */
    public static function options(?Project $project = null): array
    {
        return [
            'clients' => Client::query()->orderBy('name')->pluck('name', 'ulid')->all(),
            'managers' => Employee::query()
                ->join('users', 'users.id', '=', 'employees.user_id')
                ->where(fn ($query) => $query
                    ->where(fn ($current) => $current->whereNull('last_working_date')->orWhere('last_working_date', '>=', Employee::today()->toDateString()))
                    ->when($project?->project_manager_id, fn ($query, int $id) => $query->orWhere('employees.id', $id)))
                ->orderBy('users.name')
                ->pluck('users.name', 'employees.ulid')
                ->all(),
            'statuses' => collect(ProjectStatus::cases())->mapWithKeys(fn (ProjectStatus $status): array => [$status->value => __($status->label())])->all(),
        ];
    }
}
