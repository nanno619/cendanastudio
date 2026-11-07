# 📊 Stat card

`<x-ui.stat-card>` shows a KPI number, an icon, a delta, a comparison note and an optional sparkline. Source: `resources/views/components/ui/stat-card.blade.php`; examples at `/dashboard` and `/components/cards`.

## 🧩 Props

| Prop | Default | Purpose |
| --- | --- | --- |
| `title`, `value`, `icon`, `delta` | required | Label, number, icon name, change text |
| `tile` | `bg-soft text-soft-foreground` | Icon area colour classes |
| `tone` | `success` | Badge/sparkline colour (`success`, `danger` in the examples) |
| `direction` | `up` | `trend-up` or `trend-down` icon |
| `note` | “vs last month” (translated) | Period/comparison note |
| `spark` | `null` | JSON string of numbers for the trend (the array path needs adjusting) |

Extra attributes on the tag pass through to the card wrapper. `delta` is always shown, even when empty; set it to match the real comparison.

## 📈 Growth and decline

```blade
<div class="grid gap-4 sm:grid-cols-2">
    <x-ui.stat-card title="{{ __('New customers') }}" value="1,284" icon="userplus"
        delta="+8.1%" tone="success" direction="up" />
    <x-ui.stat-card title="{{ __('Active orders') }}" value="356" icon="cart"
        delta="−2.4%" tone="danger" direction="down"
        tile="bg-warning-soft text-warning" note="{{ __('vs last week') }}" />
</div>
```

`tone` is a **display colour**, not a business-sentiment formula: falling returns can be good news, so pick the colour from what the metric means, not just the arrow direction.

## 📉 Sparklines and controller values

```php
return view('admin.dashboard', [
    'activeOrders' => 356,
    'weeklyOrders' => [42, 50, 47, 53, 61, 58, 66],
]);
```

```blade
<x-ui.stat-card title="{{ __('Active orders') }}" :value="number_format($activeOrders)"
    icon="cart" delta="+8%" note="{{ __('Last 7 days') }}" :spark="json_encode($weeklyOrders)" />
```

Pass `spark` as a **JSON string of numbers** so `charts.js` can read `data-values`. The array path in the component uses `Js::from()`, which outputs a JavaScript expression instead of a plain JSON string; because `charts.js` uses `JSON.parse`, use `json_encode` as shown for controller data. Give the numbers in chronological order; avoid empty arrays or non-numeric values.

::: info Another stats component
`x-admin.stat-card` also exists but has a **different API** (`label`, `trend`, `href`, and a `spark` array that draws an SVG directly). The active dashboard uses `x-ui.stat-card`; this guide follows that UI version.
:::
