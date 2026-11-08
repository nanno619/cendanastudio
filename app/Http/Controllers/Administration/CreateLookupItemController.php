<?php

namespace App\Http\Controllers\Administration;

use App\Enums\LookupCategory;
use App\Http\Controllers\Controller;
use App\Models\LookupItem;
use Illuminate\View\View;

class CreateLookupItemController extends Controller
{
    /**
     * Show the form for adding an item, placed after the current last item by default.
     */
    public function __invoke(LookupCategory $category): View
    {
        $nextOrder = (int) LookupItem::query()->inCategory($category)->max('sort_order') + 10;

        return view('administrations.data-dictionary.item-form', [
            'category' => $category,
            'item' => new LookupItem(['sort_order' => min($nextOrder, 65535), 'is_active' => true]),
        ]);
    }
}
