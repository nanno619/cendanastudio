<?php

namespace App\Http\Controllers\Administration;

use App\Enums\ModuleKey;
use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Http\Requests\Administration\UpdateModuleRequest;
use App\Models\Module;
use Illuminate\Http\RedirectResponse;

class UpdateModuleController extends Controller
{
    /**
     * Save a module, creating its row if it wasn't seeded. Roles are stored in Role order so the audit
     * trail only shows a change when the selection changes.
     */
    public function __invoke(UpdateModuleRequest $request, ModuleKey $moduleKey): RedirectResponse
    {
        $validated = $request->validated();
        $chosen = $validated['allowed_roles'] ?? [];
        $validated['allowed_roles'] = array_values(array_filter(Role::cases(), fn (Role $role): bool => in_array($role->value, $chosen, true)));

        $module = Module::query()->firstOrNew(['key' => $moduleKey->value], Module::defaults($moduleKey));
        $module->fill($validated)->save();

        return redirect()->route('administrations.modules.index')
            ->with('status', __(':name was updated.', ['name' => $module->label()]));
    }
}
