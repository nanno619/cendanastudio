# 📋 Tables & data states

## 🧱 HTML tables

There's no `<x-ui.table>` component yet. Use a `<table class="table">` inside an `overflow-x-auto` wrapper, as in `resources/views/showcase/tables.blade.php`. CSS variants: `table-striped`, `table-compact`, `table-bordered`.

```blade
<div class="card overflow-x-auto">
    <table class="table min-w-[600px]">
        <thead><tr><th>{{ __('Name') }}</th><th>{{ __('Status') }}</th></tr></thead>
        <tbody>
            @foreach ($records as $record)
                <tr><td>{{ $record->title }}</td><td>{{ __($record->status) }}</td></tr>
            @endforeach
        </tbody>
    </table>
</div>
```

## 🔎 Built-in interactive tables

`data-table` on the wrapper turns on the interactions in `resources/js/admin/interactions.js`: search with `data-table-search`, a `data-table-filter` select that matches each `<tr>`'s `data-status`, sorting with `<th data-sort>` (`data-sort="num"` for numbers), pagination in `data-table-pager`, a summary in `data-table-info`, and an empty view in `data-table-empty`. `data-page-size` sets the rows per page (JS default: 8). The `data-check-all` and `data-check-row` checkboxes drive `data-bulk`/`data-bulk-count`. See the full implementation at `/components/tables` or `/examples/records`.

```blade
<div class="card" data-table data-page-size="6">
    <input type="search" data-table-search aria-label="{{ __('Search entries') }}" class="input" />
    <select data-table-filter class="select" aria-label="{{ __('Filter by status') }}">
        <option value="">{{ __('All') }}</option><option value="active">{{ __('Active') }}</option>
    </select>
    <div class="overflow-x-auto">
        <table class="table"><thead><tr><th data-sort>{{ __('Title') }}</th><th>{{ __('Status') }}</th></tr></thead>
            <tbody>
                @foreach ($records as $record)
                    <tr data-status="{{ $record->status }}"><td>{{ $record->title }}</td><td>{{ __($record->status) }}</td></tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div data-table-empty hidden>{{ __('No results') }}</div>
    <p data-table-info></p><div data-table-pager></div>
</div>
```

### 🖱️ Row actions

Put the actions column **first**. Each action is an icon button with a tooltip: `data-tip` shows the label on hover and keyboard focus, `data-tip-pos="right"` keeps it inside the table's scroll area, and `aria-label` names the row for screen readers. Mark the cell `data-actions` so table search ignores it. With several actions, use one "more" icon that opens a `data-dropdown` menu.

```blade
<thead><tr><th class="w-12" scope="col"><span class="sr-only">{{ __('Actions') }}</span></th><th>{{ __('Title') }}</th></tr></thead>
<tbody>
    <tr>
        <td data-actions>
            <a href="{{ route('records.show', $record) }}" class="btn btn-ghost btn-icon btn-sm text-muted-foreground"
               data-tip="{{ __('View') }}" data-tip-pos="right" aria-label="{{ __('View :name', ['name' => $record->title]) }}">
                <x-ui.icon name="eye" />
            </a>
        </td>
        <td>{{ $record->title }}</td>
    </tr>
</tbody>
```

The demo table only processes **rows already rendered in the browser**. Don't mix this `data-table` with server pagination and expect search to cover the whole database; follow [server-side tables](/integration/data) for large data.

### ↕️ Search, filter and sort

Search compares the text of every cell except those marked `data-actions` (the row actions cell) and sends no request. The filter reads `data-status` on each `<tr>` and matches it exactly against the `<select>` value; make sure the options and the rows use the same slug, e.g. `active`. For numbers, put `data-sort="num"` on the `<th>`, and if the cell shows formatted text (such as `RM 1,000.00`), add `data-value="1000"` to the `<td>` so sorting uses the raw number. Other cell text is sorted using the page's active language (`<html lang>`). The info line and pager labels come from the [JavaScript translations](/guide/theming#language).

### ☑️ Row selection and pagination

`data-check-all` selects the `data-check-row` checkboxes on visible rows; the selected count drives `data-bulk-count` and `data-bulk`. Filtering or changing page clears the selection. The bulk action buttons in the showcase are UI examples only: to delete or export real data, put the ID in each checkbox's `value`, collect the IDs, then send a request to the right route. The `data-table-pager` controls are generated as browser buttons, not Laravel route links.

## 🧭 Empty, loading and error states {#empty-loading-and-error-states}

`x-ui.table-state` takes `state` (`empty`, `loading`, `filtered`, `error`), `title`, `description` and a slot for recovery buttons. `loading` always shows a skeleton; the other states show built-in text that you can override.

| `state` | Meaning | When to show it |
| --- | --- | --- |
| `loading` | Four skeleton rows with `role="status"` | While a data request is in progress |
| `empty` (default) | No data at all yet | Before the user creates their first entry |
| `filtered` | No matches | After a search or filter returns zero rows |
| `error` | Data failed to load; `role="alert"` | When a request fails; include a retry action in the slot |

`title` and `description` replace the default text, but in the `loading` state the component always renders the skeleton without custom text or slot. Slot buttons only appear when the slot isn't empty and the state isn't `loading`. The default texts are translated.

```blade
<x-ui.table-state state="filtered" title="{{ __('No results') }}">
    <a href="{{ route('examples.records.index') }}" class="btn btn-outline">{{ __('Clear filters') }}</a>
</x-ui.table-state>
```

`x-ui.empty` is a generic empty view with `icon`, `title` and `description` props and an action slot. `x-ui.pagination` **isn't used on the showcase pages yet**; its implementation uses `$elements` without building it from the paginator, so don't rely on it directly for `$records->links()` output until it's adapted. For integration today, use Laravel's `$records->links()` or write your own pagination partial.

### 🗃️ `x-ui.empty` {#x-ui-empty}

Use it for no-content states outside a table, such as an orders page opened for the first time. `icon` defaults to `file-text`; `title` defaults to “No data yet”. The optional slot can hold a link to create data or a next-step hint. This component doesn't distinguish a failed load from a filter with no results: use `x-ui.table-state` for those.

```blade
<x-ui.empty icon="package" title="{{ __('No products yet') }}"
    description="{{ __('Products you add will appear here.') }}">
    <a href="{{ route('products.create') }}" class="btn btn-primary">{{ __('Add product') }}</a>
</x-ui.empty>
```

The `products.create` route above is an example route you'd create in your app, not a built-in one.

### 📄 `x-ui.pagination` (not production-ready) {#x-ui-pagination-not-ready}

The component file takes a `paginator` prop and calls `hasPages()`, `firstItem()`, `lastItem()`, `total()` and the previous/next URLs. However, its page-number loop uses `$elements`, which the component never defines. When you develop this component, build the list of page elements from the paginator or use Laravel's complete pagination view. For `cursorPaginate()`, the `total()`/`lastItem()` contract also differs; these docs use `paginate()`.
