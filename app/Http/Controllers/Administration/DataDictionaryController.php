<?php

namespace App\Http\Controllers\Administration;

use App\Enums\LookupCategory;
use App\Http\Controllers\Controller;
use App\Models\LookupItem;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class DataDictionaryController extends Controller
{
    /**
     * List every Data Dictionary category with its item counts.
     */
    public function __invoke(): View
    {
        $stats = LookupItem::query()
            ->toBase()
            ->selectRaw('category, COUNT(*) as total, SUM(CASE WHEN is_active THEN 1 ELSE 0 END) as active, MAX(updated_at) as last_updated')
            ->groupBy('category')
            ->get()
            ->keyBy('category');

        return view('administrations.data-dictionary.index', [
            'categories' => collect(LookupCategory::cases())->map(fn (LookupCategory $category): array => [
                'category' => $category,
                'total' => (int) ($stats[$category->value]->total ?? 0),
                'active' => (int) ($stats[$category->value]->active ?? 0),
                'lastUpdated' => isset($stats[$category->value]) ? Carbon::parse($stats[$category->value]->last_updated, 'UTC') : null,
            ]),
            'timezone' => config('cendana.timezone'),
        ]);
    }
}
