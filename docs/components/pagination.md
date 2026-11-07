# 📄 Pagination

`<x-ui.pagination>` is a draft paginator view in `resources/views/components/ui/pagination.blade.php`. **It isn't ready to use directly yet**: the file takes a `paginator`, but its page-number loop refers to `$elements` without filling it. The component also expects `total()`, so it's closer to `paginate()` than to `simplePaginate()`/`cursorPaginate()`.

## 🧩 Intended API

| Prop | Purpose |
| --- | --- |
| `paginator` | A `LengthAwarePaginator` for `hasPages`, first/last item, total and page URLs |

The UI structure when pagination shows: “Showing X–Y of Z” text, previous/next buttons, page numbers, ellipses and the active page marker (all translated). When `$paginator->hasPages()` is false, no markup is output.

## ✅ The ready-to-use option: Laravel's paginator

```php
$records = Record::query()->latest()->paginate(15)->withQueryString();
return view('examples.records.index', compact('records'));
```

```blade
@foreach ($records as $record)
    <p>{{ $record->title }}</p>
@endforeach

{{ $records->links() }}
```

This uses Laravel's pagination view, **not** `x-ui.pagination`. `withQueryString()` keeps the search/filter URL parameters. The example `Record` model is created in the [CRUD guide](/integration/crud).

## 🛠️ If you want the custom component

Once you've added proper page-element building to that component, call:

```blade
<x-ui.pagination :paginator="$records" />
```

The example above **only works after fixing** `$elements`; without that fix, the page numbers have no data. Alternatively, apply the design to an official Laravel pagination view and call `$records->links('view-name')`. Don't turn on client-side `data-table` alongside server pagination for the same dataset; the JS search only sees the rows currently rendered.
