# 📊 Server-side tables & charts

## 📄 Choose a pagination approach

`data-table` filters, sorts and paginates **the HTML rows already on the page**. If the controller only renders 15 of thousands of entries, browser search won't find entries on other server pages. For large datasets, use Laravel queries, pagination and URL parameters, and remove the `data-table` attribute so the two mechanisms don't clash.

```php
public function index(Request $request): View
{
    $records = Record::query()
        ->when($request->filled('q'), fn ($query) => $query->where('title', 'like', '%'.$request->input('q').'%'))
        ->when($request->filled('status'), fn ($query) => $query->where('status', $request->input('status')))
        ->orderByDesc('created_at')
        ->paginate(15)
        ->withQueryString();

    return view('examples.records.index', compact('records'));
}
```

```blade
<form method="GET" action="{{ route('examples.records.index') }}" class="flex gap-2">
    <input class="input" type="search" name="q" value="{{ request('q') }}" aria-label="{{ __('Search entries') }}" />
    <select class="select" name="status" aria-label="{{ __('Filter by status') }}">
        <option value="">{{ __('All statuses') }}</option>
        <option value="active" @selected(request('status') === 'active')>{{ __('Active') }}</option>
        <option value="draft" @selected(request('status') === 'draft')>{{ __('Draft') }}</option>
    </select>
    <x-ui.button type="submit">{{ __('Apply') }}</x-ui.button>
</form>

{{-- Render the HTML table from $records; don't put data-table on the wrapper. --}}
{{ $records->links() }}
```

If you use `x-ui.table-state` as a fallback, distinguish a table with no data yet (`empty`) from one that's empty because of a search (`filtered`). For server-side sorting, only accept column names from an allowlist before passing them to `orderBy`; never use free-form column names from request input. The existing `x-ui.pagination` isn't fully wired to the paginator's `$elements` yet; use `$records->links()` until that component is finished.

## 📈 Build charts from real data

`x-ui.chart` props accept PHP arrays. For example, count entries per status in the controller:

```php
$counts = Record::query()
    ->selectRaw('status, COUNT(*) as total')
    ->groupBy('status')
    ->pluck('total', 'status');

$labels = ['draft' => __('Draft'), 'in-review' => __('In review'), 'active' => __('Active'), 'archived' => __('Archived')];

$chartItems = collect($labels)
    ->values()
    ->map(fn ($label, $index) => [
        'label' => $label,
        'value' => (int) ($counts[array_keys($labels)[$index]] ?? 0),
        'color' => $index + 1,
    ])
    ->all();

return view('admin.analytics', compact('chartItems'));
```

```blade
@if (collect($chartItems)->sum('value') > 0)
    <x-ui.chart type="donut" aria="{{ __('Entry status breakdown') }}"
        :items="$chartItems" total-label="{{ __('Entries') }}" />
@else
    <x-ui.table-state state="empty" title="{{ __('No entries yet') }}" />
@endif
```

The example assumes the `Record` model/table from the [CRUD guide](/integration/crud). For trend charts, build `labels` and `series` in the same period order, and fill in every period including those with zero. The data in `admin/dashboard.blade.php` is still static, so replace the stat card and chart values there once your app's queries exist.
