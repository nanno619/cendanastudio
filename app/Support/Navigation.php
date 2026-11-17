<?php

namespace App\Support;

use App\Enums\ModuleKey;
use App\Models\Module;
use App\Models\User;

/**
 * Builds the sidebar from config('cendana.navigation') for one user. Groups flagged `showcase` are dropped
 * when the showcase is off; items with a `role` key (one role or a list) are shown only to users holding one of them; items with a `module` key
 * are shown only while the module is available to the user, take the module's name and icon, and are
 * ordered by the module's sort order (after the group's other items). Empty groups are dropped. Group and
 * top-level item labels come back translated.
 */
class Navigation
{
    /**
     * @return list<array{label: string, items: list<array<string, mixed>>}>
     */
    public static function for(?User $user): array
    {
        $modules = Module::registry();
        $groups = [];

        foreach (config('cendana.navigation') as $group) {
            if (($group['showcase'] ?? false) && ! config('cendana.showcase')) {
                continue;
            }

            $items = [];

            foreach ($group['items'] as $position => $item) {
                if (filled($item['role'] ?? null) && ! ($user?->hasAnyRole((array) $item['role']) ?? false)) {
                    continue;
                }

                $module = isset($item['module']) ? $modules->get(ModuleKey::from($item['module'])->value) : null;

                if ($module !== null && ! $module->isAvailableTo($user)) {
                    continue;
                }

                $items[] = [
                    ...$item,
                    'label' => $module?->label() ?? __($item['label']),
                    'icon' => $module?->icon ?? ($item['icon'] ?? null),
                    'sort' => [$module === null ? 0 : 1, $module?->sort_order ?? 0, $position],
                ];
            }

            if ($items === []) {
                continue;
            }

            usort($items, fn (array $a, array $b): int => $a['sort'] <=> $b['sort']);

            $groups[] = ['label' => __($group['label']), 'items' => $items];
        }

        return $groups;
    }
}
