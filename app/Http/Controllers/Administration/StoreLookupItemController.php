<?php

namespace App\Http\Controllers\Administration;

use App\Enums\LookupCategory;
use App\Http\Controllers\Controller;
use App\Http\Requests\Administration\SaveLookupItemRequest;
use App\Models\LookupItem;
use Illuminate\Http\RedirectResponse;

class StoreLookupItemController extends Controller
{
    /**
     * Add a validated item to the category.
     */
    public function __invoke(SaveLookupItemRequest $request, LookupCategory $category): RedirectResponse
    {
        $item = LookupItem::create([...$request->validated(), 'category' => $category]);

        return redirect()->route('administrations.data-dictionary.show', $category)
            ->with('status', __(':label was added.', ['label' => $item->label_en]));
    }
}
