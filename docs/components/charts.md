# 📊 Charts & stats

`x-ui.chart` renders a `data-chart` element that `resources/js/admin/charts.js` draws as SVG, with no external chart dependency. Colours follow the `--chart-1` to `--chart-5` tokens in `resources/css/app.css`.

## 🧱 Charts from the component {#charts-from-the-component}

Types supported by the JavaScript: `line`, `bar`, `area`, `stacked`, `donut`, `radial`, `spark`, `hbar`. The component supports `type`, `labels`, `series`, `items`, `values`, `height` (default 240), `aria`, `summary`, `legend`, `smooth`, `prefix`, `suffix`, `size` and `totalLabel`. The data shape depends on the type; the canonical examples are in `resources/views/showcase/charts.blade.php` and the dashboard.

```blade
<x-ui.chart
    type="bar"
    aria="{{ __('Orders per month') }}"
    :labels="array_map('__', ['Jul', 'Aug', 'Sep'])"
    :series="[['name' => __('Orders'), 'data' => [24, 32, 40], 'color' => 1]]"
    :legend="true"
    summary="{{ __('Orders grew over three months.') }}" />
```

For a donut, use `items` with labels and values:

```blade
<x-ui.chart type="donut" aria="{{ __('Orders by channel') }}"
    :items="[
        ['label' => __('Website'), 'value' => 60, 'color' => 1],
        ['label' => __('Physical store'), 'value' => 40, 'color' => 2],
    ]" total-label="{{ __('Orders') }}" />
```

`labels` is the horizontal axis for cartesian charts; each `series` item has a `name`, `data` (in the same order as the labels) and an optional `color`. `summary` becomes a `<figcaption>`, so the chart has a text description. The numbers aren't queried automatically; prepare the arrays in a controller as in the [server-side chart example](/integration/data). Wrap labels and series names in `__()` so they follow the interface language; month and day abbreviations (`Jan`, `Mon`…) already have Malay translations.

### 📈 Line, area, bar and stacked

All four types use `labels` with `series`. `labels` is the order of categories/periods; each `series` must have a `name` and a `data` array of the same length and order. `bar` compares values across labels; `stacked` sums every series' contribution for the same label; `area` fills the space under the curve; `line` keeps only the line. `smooth` affects lines/areas, `legend` shows the series list, and `prefix` and `suffix` format the numbers on the chart. Numbers use Malaysian formatting (`1,234.5`).

### 🍩 Donut and advanced types

`donut` uses `items` with a `label`, a `value` and an optional `color`. `totalLabel` captions the total. The JS engine also supports `radial`, `hbar` and `spark`; `spark` is easiest to see in the dashboard's `x-ui.stat-card`. Before using an advanced type, check its attribute structure in `resources/js/admin/charts.js`: the Blade component doesn't expose root-level `value`, `label`, `kind` or `color` props for every raw variation, and some need `data-chart` markup directly, as on the admin pages.

### 🧩 Accessibility and data input

The `aria` prop becomes the generated SVG's label, and `summary` becomes a `<figcaption>` readers can follow without interpreting the chart. Write a summary that states the pattern or key values, not just the title again. Don't put raw request data into JSON attributes; prepare and validate the values in the controller. When every value is empty or zero, show an empty state instead of forcing a chart.

## 🔄 Empty data and redrawing

For a range with no values, show `<x-ui.table-state state="empty">` instead of an empty chart. Charts initialise on page load (`window.Charts.init(document)` through `App.initPage`). If the data on the same chart element changes, update its `dataset` and call `window.Charts.draw(element)`. `Charts.init` skips elements it has already initialised; don't call it expecting old data to redraw.

`x-ui.stat-card` uses the same chart engine for its sparkline. Prop details and examples are in [layout & display](/components/layout).
