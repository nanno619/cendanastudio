<?php

namespace App\Http\Controllers\Administration;

use App\Enums\FileType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Administration\UpdateFileSecurityRequest;
use App\Models\FileSecuritySetting;
use Illuminate\Http\RedirectResponse;

class UpdateFileSecurityController extends Controller
{
    /**
     * Save the upload policy, creating its single row on first save.
     */
    public function __invoke(UpdateFileSecurityRequest $request): RedirectResponse
    {
        $setting = FileSecuritySetting::query()->firstOrNew();
        $validated = $request->validated();

        // Store types in enum order so the audit trail only shows a change when the selection changes.
        $validated['allowed_types'] = array_values(array_filter(
            FileType::cases(),
            fn (FileType $type): bool => in_array($type->value, $validated['allowed_types'], true),
        ));

        $setting->fill($validated)->save();

        return redirect()->route('administrations.file-security.index')
            ->with('status', __('File security settings were saved.'));
    }
}
