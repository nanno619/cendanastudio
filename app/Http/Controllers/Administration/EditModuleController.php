<?php

namespace App\Http\Controllers\Administration;

use App\Enums\ModuleKey;
use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\Module;
use Illuminate\View\View;
use Spatie\Activitylog\Models\Activity;

class EditModuleController extends Controller
{
    /**
     * Show a module's name, icon, order, switch and roles.
     */
    public function __invoke(ModuleKey $moduleKey): View
    {
        $module = Module::for($moduleKey);

        return view('administrations.modules.edit', [
            'module' => $module,
            'roles' => array_values(array_filter(Role::cases(), fn (Role $role): bool => $role !== Role::SuperAdmin)),
            'lastChange' => $module->exists
                ? Activity::query()->with('causer')->whereMorphedTo('subject', $module)->latest('id')->first()
                : null,
            'timezone' => config('cendana.timezone'),
        ]);
    }
}
