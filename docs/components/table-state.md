# 📋 Table state

`<x-ui.table-state>` shows feedback for tables and data areas: a skeleton, no data, no filter results, or a failed load. Source: `resources/views/components/ui/table-state.blade.php`.

## 🧩 Props and states

| Prop | Default | Purpose |
| --- | --- | --- |
| `state` | `empty` | `loading`, `empty`, `filtered`, `error` |
| `title` | `null` | Replaces the built-in heading, except for `loading` |
| `description` | `null` | Replaces the built-in explanation, except for `loading` |
| Main slot | empty | Recovery buttons for states other than `loading` |

The `loading` state uses `role="status"` and a four-row skeleton. `error` uses `role="alert"`; `empty`/`filtered` use `role="status"`. An unrecognised state falls back to the `empty` text, but the `state` value still sets the role/classes: only use the four supported values. The built-in texts are translated.

## ⏳ Loading, empty and filter results

```blade
<x-ui.table-state state="loading" />
<x-ui.table-state state="empty" title="{{ __('No customers yet') }}"
    description="{{ __('New customers will appear after the first transaction.') }}" />
<x-ui.table-state state="filtered" title="{{ __('No customers found') }}">
    <a href="{{ route('examples.records.index') }}" class="btn btn-outline">{{ __('Clear filters') }}</a>
</x-ui.table-state>
```

The slot isn't rendered for `loading`; show actions once the data has finished processing.

## ❌ Errors with a recovery action

```blade
<x-ui.table-state state="error" title="{{ __('Data failed to load') }}"
    description="{{ __('Check your connection and try again.') }}">
    <a href="{{ request()->fullUrl() }}" class="btn btn-outline">{{ __('Reload') }}</a>
</x-ui.table-state>
```

## 🗃️ Choosing the state from a Laravel query

```blade
@if ($records->isEmpty())
    <x-ui.table-state :state="request()->filled('q') ? 'filtered' : 'empty'" />
@else
    <table class="table">{{-- $records rows --}}</table>
@endif
```

Inside a `<table>`, put the component in a single `<td colspan="...">` so the table markup stays valid, or place it outside the table in a `.card` wrapper. Don't assume the built-in `loading` state runs automatically for a synchronous server query; only show it when there's genuinely an asynchronous process.
