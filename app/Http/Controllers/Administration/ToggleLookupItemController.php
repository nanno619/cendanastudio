<?php

namespace App\Http\Controllers\Administration;

use App\Enums\LookupCategory;
use App\Http\Controllers\Controller;
use App\Models\LookupItem;
use Illuminate\Http\RedirectResponse;

class ToggleLookupItemController extends Controller
{
    /**
     * Switch an item between active and inactive. Items are never deleted.
     */
    public function __invoke(LookupCategory $category, LookupItem $item): RedirectResponse
    {
        abort_unless($item->category === $category, 404);

        $item->update(['is_active' => ! $item->is_active]);

        return back()->with('status', $item->is_active
            ? __(':label is active again.', ['label' => $item->label_en])
            : __(':label was deactivated and is hidden from dropdowns.', ['label' => $item->label_en]));
    }
}
