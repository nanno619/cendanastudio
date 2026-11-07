# 📈 Chart

`<x-ui.chart>` puts the chart configuration in `data-*` attributes; `resources/js/admin/charts.js` draws the SVG once the page is ready. There's no external chart dependency. Component file: `resources/views/components/ui/chart.blade.php`.

## 🧩 API and data shape

| Prop | Default | Purpose |
| --- | --- | --- |
| `type` | required | `line`, `area`, `bar`, `stacked`, `donut`, `radial`, `spark`, `hbar` |
| `labels`, `series`, `items`, `values` | `[]` | Data arrays for the chart type |
| `height` | `240` | Height of cartesian/spark charts (px) |
| `aria` | “Chart” (translated) | SVG label for accessibility |
| `summary` | `null` | `<figcaption>` after the chart |
| `legend`, `smooth` | `false` | Legend/curves on cartesian charts |
| `prefix`, `suffix` | `''` | Number format for tooltips/labels |
| `size`, `totalLabel` | `null` | Size/total label (mainly donut) |

PHP arrays are encoded as JSON in the attributes. Choose the type from the data question: a trend over time (`line`/`area`), a comparison (`bar`), stacked contribution (`stacked`), or composition (`donut`).

## 📉 Line and area: two trend examples

```blade
<x-ui.chart type="line" aria="{{ __('Daily site and app visits') }}"
    :labels="array_map('__', ['Mon', 'Tue', 'Wed', 'Thu'])"
    :series="[
        ['name' => __('Site'), 'data' => [42, 48, 46, 58], 'color' => 1],
        ['name' => __('App'), 'data' => [26, 31, 29, 36], 'color' => 2],
    ]" summary="{{ __('Site visits peaked on Thursday.') }}" />

<x-ui.chart type="area" :smooth="true" aria="{{ __('Four-month entry trend') }}"
    :labels="array_map('__', ['Jun', 'Jul', 'Aug', 'Sep'])"
    :series="[['name' => __('Entries'), 'data' => [18, 25, 29, 38], 'color' => 2]]" />
```

Each series' `data` array must line up with `labels`. Two series on a line chart show a legend automatically. `smooth` means the renderer draws curves on the line/area; it isn't a statistical filter on the data.

## 📊 Bar and stacked: comparisons

```blade
<x-ui.chart type="bar" aria="{{ __('Orders per month') }}" :legend="true"
    :labels="array_map('__', ['Jul', 'Aug', 'Sep'])"
    :series="[['name' => __('Orders'), 'data' => [34, 42, 51], 'color' => 1]]" />

<x-ui.chart type="stacked" aria="{{ __('Work by channel') }}"
    :labels="array_map('__', ['Mon', 'Tue', 'Wed'])"
    :series="[
        ['name' => __('Manual'), 'data' => [12, 15, 14], 'color' => 1],
        ['name' => __('Automated'), 'data' => [7, 8, 10], 'color' => 2],
    ]" />
```

## 🍩 Donut and hbar: composition

```blade
<x-ui.chart type="donut" aria="{{ __('Entry sources') }}" total-label="{{ __('Entries') }}" :size="160"
    :items="[
        ['label' => __('Manual'), 'value' => 520, 'color' => 1],
        ['label' => __('Imported'), 'value' => 280, 'color' => 2],
        ['label' => __('Automated'), 'value' => 200, 'color' => 3],
    ]" summary="{{ __('Of 1,000 entries, 52% were created manually.') }}" />

<x-ui.chart type="hbar" aria="{{ __('Work by team') }}" suffix=" {{ __('tasks') }}"
    :items="[
        ['label' => __('Core team'), 'value' => 35, 'color' => 1],
        ['label' => __('Collaborators'), 'value' => 20, 'color' => 2],
    ]" />
```

Donut and hbar need positive values; the renderer divides by the total/maximum. If everything is zero, show an [empty table state](/components/table-state) instead of computing 0/0 percentages. Hbar currently builds its labels through `innerHTML` in JS: don't pass free-form user text as labels without escaping or sanitising it.

## ⚙️ Radial and spark: extra attributes

The `radial` type reads `data-value`, `data-label` and `data-color`, while `spark` can read `data-kind`/`data-color`. `x-ui.chart` has **no dedicated props** for these, and the component's `$attributes` apply to the `<figure>`, not the inner chart element. So write `data-chart` markup directly, or extend the component, before using these types.

```blade
<div data-chart="radial" data-value="72" data-label="{{ __('Target') }}" data-color="chart-2" data-size="120"></div>
<div data-chart="spark" data-aria="{{ __('Order trend') }}" data-values="[12,15,14,19,24]"
    data-kind="area" data-color="success" data-height="48"></div>
```

Use colour names from the system (`chart-1`..`chart-5`, `success`), not untrusted values from the request. `x-ui.stat-card` already presents a sparkline inside a KPI card.

## 🔄 Laravel data, accessibility and redrawing

Prepare `labels`/`series`/`items` in the controller and pass them with `:labels="$labels"`, `:series="$series"`. Give an `aria` that describes the chart and a `summary` stating the finding in plain language. `window.Charts.init(scope)` only initialises new elements; after replacing the `dataset` on an existing element, call `window.Charts.draw(element)` to redraw. A chart with no data is better replaced by an empty state. A connected query example is in [charts from the server](/integration/data#build-charts-from-real-data).
