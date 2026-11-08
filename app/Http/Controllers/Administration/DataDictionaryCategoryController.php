<?php

namespace App\Http\Controllers\Administration;

use App\Enums\LookupCategory;
use App\Http\Controllers\Controller;
use App\Models\LookupItem;
use Illuminate\View\View;

class DataDictionaryCategoryController extends Controller
{
    /**
     * Show all items of one Data Dictionary category, active and inactive, in display order.
     */
    public function __invoke(LookupCategory $category): View
    {
        return view('administrations.data-dictionary.show', [
            'category' => $category,
            'items' => LookupItem::query()->inCategory($category)->ordered()->get(),
            'timezone' => config('cendana.timezone'),
        ]);
    }
}
