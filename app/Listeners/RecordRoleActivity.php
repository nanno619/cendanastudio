<?php

namespace App\Listeners;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Spatie\Permission\Events\RoleAttachedEvent;
use Spatie\Permission\Events\RoleDetachedEvent;
use Spatie\Permission\Models\Role;

/**
 * Writes role assignments and removals to the audit trail, on the user who gained or lost the role.
 */
class RecordRoleActivity
{
    public function handleAttached(RoleAttachedEvent $event): void
    {
        $this->record($event->model, $event->rolesOrIds, 'role-assigned', 'Assigned role :roles');
    }

    public function handleDetached(RoleDetachedEvent $event): void
    {
        $this->record($event->model, $event->rolesOrIds, 'role-removed', 'Removed role :roles');
    }

    private function record(Model $model, mixed $rolesOrIds, string $event, string $description): void
    {
        $roles = $this->roleNames($rolesOrIds);

        if ($roles === []) {
            return;
        }

        activity()->performedOn($model)->event($event)
            ->withProperties(['roles' => implode(', ', $roles)])
            ->log(str_replace(':roles', implode(', ', $roles), $description));
    }

    /**
     * @return list<string>
     */
    private function roleNames(mixed $rolesOrIds): array
    {
        $items = collect($rolesOrIds instanceof Collection ? $rolesOrIds->all() : Arr::wrap($rolesOrIds));
        $names = $items->filter(fn (mixed $item): bool => $item instanceof Role)->map(fn (Role $role): string => $role->name);
        $ids = $items->reject(fn (mixed $item): bool => $item instanceof Role);

        if ($ids->isNotEmpty()) {
            $names = $names->merge(Role::query()->whereKey($ids->all())->orderBy('name')->pluck('name'));
        }

        return $names->unique()->values()->all();
    }
}
