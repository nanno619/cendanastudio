<?php

namespace App\Http\Controllers\Administration;

use App\Enums\LookupCategory;
use App\Http\Controllers\Controller;
use App\Http\Requests\Administration\SaveLookupItemRequest;
use App\Models\LookupItem;
use Illuminate\Http\RedirectResponse;

class UpdateLookupItemController extends Controller
{
    /**
     * Save validated changes to an item; its code is never changed.
     */
    public function __invoke(SaveLookupItemRequest $request, LookupCategory $category, LookupItem $item): RedirectResponse
    {
        abort_unless($item->category === $category, 404);

        $item->update($request->validated());

        return redirect()->route('administrations.data-dictionary.show', $category)
            ->with('status', __(':label was updated.', ['label' => $item->label_en]));
    }
}
