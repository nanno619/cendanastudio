# 📅 Date range

`<x-ui.date-range>` offers 7/30-day presets and two date inputs enhanced by Flatpickr. Sources: `resources/views/components/ui/date-range.blade.php`, `resources/js/admin/advanced-inputs.js` and `enhanced-controls.js`.

## 🧩 API

| Prop | Default | Purpose |
| --- | --- | --- |
| `id` | required | Base for the `{id}-from` and `{id}-to` IDs |
| `label` | “Date range” (translated) | Fieldset heading |
| `fromName`, `toName` | `null` | Request names for the start/end |
| `from`, `to` | `null` | Initial ISO `Y-m-d` values; `old()` wins when names are set |
| Root attributes | — | Passed to `<fieldset data-date-range>` |

Without `fromName`/`toName`, the inputs have no `name` and are only a local demo. The submitted value is always `Y-m-d`, even though the calendar shows month names in the interface language (English or Malay). Without JS, the text inputs ask for ISO dates; server validation is still needed.

## 📆 Demo use and multiple instances

```blade
<x-ui.date-range id="sales-period" label="{{ __('Sales period') }}" from-name="from" to-name="to" />
<x-ui.date-range id="visits-period" label="{{ __('Visits period') }}" class="mt-6" />
```

The `id` must be unique so the “From” and “To” labels point at the right inputs. A preset sets the last day to today and includes the first day in the range; “Delete” clears both.

## 🔗 Sending the filter to the backend

To use it in a GET form, set the field names and initial values:

```blade
<form method="GET" action="{{ route('examples.records.index') }}">
    <x-ui.date-range id="filter-period" from-name="from" to-name="to"
        :from="request('from')" :to="request('to')" />
    <x-ui.button type="submit">{{ __('Apply') }}</x-ui.button>
</form>
```

The preset buttons don't send a request on their own. If two instances are in the same form, give each range **different input names**. The Delete button clears both dates but still needs a submit to refresh the server list.

## ✅ Date validation

```php
$filters = $request->validate([
    'from' => ['nullable', 'date'],
    'to' => ['nullable', 'date', 'after_or_equal:from'],
]);
```

The order error shown by the component is browser validation only. Date queries can use `whereDate('created_at', '>=', $filters['from'])` and a similar end bound once the values are checked.
