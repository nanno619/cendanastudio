<?php

namespace App\Http\Controllers\Settings;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\UpdateVisitSettingsRequest;
use App\Models\VisitSetting;
use Illuminate\Http\RedirectResponse;

class UpdateVisitSettingsController extends Controller
{
    /**
     * Save the visit rules, creating the single row on first save. Roles are stored in Role order so the
     * audit trail only shows a change when the selection changes; the approver is cleared while approval is off.
     */
    public function __invoke(UpdateVisitSettingsRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $chosen = $validated['allowed_roles'] ?? [];
        $validated['allowed_roles'] = array_values(array_filter(Role::cases(), fn (Role $role): bool => in_array($role->value, $chosen, true)));

        VisitSetting::query()->firstOrNew()->fill([
            'approver_role' => null,
            ...$validated,
        ])->save();

        return redirect()->route('settings.hadir.visits')
            ->with('status', __('Visit settings were saved.'));
    }
}
