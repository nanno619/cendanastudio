# 📑 Detail list

`<x-ui.detail-list>` shows label/value pairs in a semantic `<dl>`. It suits entry details, profiles or order metadata. Source: `resources/views/components/ui/detail-list.blade.php`.

## 🧩 API

| Prop/slot | Default | Purpose |
| --- | --- | --- |
| `title` | `null` | Section heading |
| `items` | `[]` | Array of `['label' => ..., 'value' => ...]` |
| `columns` | `2` | `2` gives two columns from the `sm` breakpoint; `1` gives one |
| Main slot | — | Extra `<div><dt>…</dt><dd>…</dd></div>` rows |

Values are rendered as escaped Blade text. If a `value` is null or missing, the component shows `—`.

## 📝 Details from a controller

```blade
<x-ui.detail-list title="{{ __('Entry information') }}" :items="[
    ['label' => __('Code'), 'value' => $record->code],
    ['label' => __('Title'), 'value' => $record->title],
    ['label' => __('Status'), 'value' => __(ucfirst($record->status))],
]" />
```

## 📐 One column and custom values

```blade
<x-ui.detail-list title="{{ __('Payment information') }}" :columns="1" :items="[
    ['label' => __('Method'), 'value' => __('Bank transfer')],
    ['label' => __('Reference number'), 'value' => $payment->reference],
]">
    <div><dt class="text-xs text-muted-foreground">{{ __('Total') }}</dt><dd class="mt-1 font-semibold">RM {{ number_format($payment->amount, 2) }}</dd></div>
</x-ui.detail-list>
```

Format money and dates in the controller or while building the array; the component doesn't do domain formatting. The slot lets you put badges or links inside a `<dd>` when a value needs markup.
