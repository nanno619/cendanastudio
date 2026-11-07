# 🔎 Filter bar

`<x-ui.filter-bar>` is a reusable GET form for searching and filtering list pages. Source: `resources/views/components/ui/filter-bar.blade.php`.

## 🧩 Props & slots

| Input | Default | Purpose |
| --- | --- | --- |
| `action` | required | GET form URL |
| `searchName` | `q` | Search parameter name |
| `search` | `null` | Initial value (falls back to `request(searchName)`) |
| `placeholder` | “Search data…” (translated) | Input hint |
| `resetUrl` | `null` | URL for the Reset button; hidden when empty |
| Main slot | — | Other filters inside the form |
| `active` | optional | Active-filter summary after the form |

The component uses `x-ui.input` for search and `x-ui.button type="submit"`. Slot content sits inside the form; use field names that match your controller's query.

## 📝 A simple search

```blade
<x-ui.filter-bar :action="route('examples.records.index')"
    :reset-url="route('examples.records.index')" placeholder="{{ __('Search entry titles…') }}" />
```

## 🔽 With a status and active chips

```blade
<x-ui.filter-bar :action="route('examples.records.index')" :reset-url="route('examples.records.index')">
    <div class="min-w-40 space-y-1.5">
        <label class="label" for="status-filter">{{ __('Status') }}</label>
        <x-ui.select id="status-filter" name="status" placeholder="{{ __('All statuses') }}"
            :value="request('status')" :options="['active' => __('Active'), 'draft' => __('Draft')]" :searchable="true" />
    </div>
    <x-slot:active>
        @if(request()->filled('status'))<x-ui.badge variant="info">{{ __('Status:') }} {{ request('status') }}</x-ui.badge>@endif
    </x-slot:active>
</x-ui.filter-bar>
```

## 🗃️ The controller query

```php
$records = Record::query()
    ->when($request->filled('q'), fn ($query) => $query->where('title', 'like', '%'.$request->input('q').'%'))
    ->when($request->filled('status'), fn ($query) => $query->where('status', $request->input('status')))
    ->paginate(15)->withQueryString();
```

Validate the status before querying if the choices come from the request. The filter bar doesn't filter with JavaScript; the GET form sends the parameters.
