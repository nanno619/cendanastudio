<?php

namespace App\Http\Controllers\Administration;

use App\Enums\LookupCategory;
use App\Http\Controllers\Controller;
use App\Models\LookupItem;
use Illuminate\View\View;

class EditLookupItemController extends Controller
{
    /**
     * Show the form for editing an item of the category.
     */
    public function __invoke(LookupCategory $category, LookupItem $item): View
    {
        abort_unless($item->category === $category, 404);

        return view('administrations.data-dictionary.item-form', ['category' => $category, 'item' => $item]);
    }
}
