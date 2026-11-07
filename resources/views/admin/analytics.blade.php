<x-layouts.admin title="{{ __('Analytics') }}" group="{{ __('Platform') }}">
<div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
  <x-ui.page-header title="{{ __('Analytics') }}" description="{{ __('Chart gallery: area, line, bar, stacked, donut, radial and sparkline.') }}" />
  <div class="tabs-pill" role="tablist" data-tabs>
    <button type="button" role="tab" class="tab" aria-selected="false" tabindex="-1">{{ __('7 days') }}</button>
    <button type="button" role="tab" class="tab" aria-selected="true">{{ __('30 days') }}</button>
    <button type="button" role="tab" class="tab" aria-selected="false" tabindex="-1">{{ __('90 days') }}</button>
  </div>
</div>

<x-ui.card title="{{ __('Visitors and orders') }}" description="{{ __('Area chart with two series. Hover to see the tooltip.') }}" class="min-w-0">
  <div data-chart="area" data-smooth data-height="300"
    data-labels='["1","4","7","10","13","16","19","22","25","28"]'
    data-series="{{ json_encode([['name' => __('Visitors'), 'data' => [420, 510, 480, 620, 590, 710, 680, 790, 760, 880]], ['name' => __('Orders'), 'data' => [38, 52, 47, 66, 58, 79, 71, 88, 82, 97], 'color' => '2']]) }}"></div>
</x-ui.card>

<section class="grid gap-4 lg:grid-cols-2">
  <x-ui.card title="{{ __('Conversion rate by channel') }}" description="{{ __('Line chart, three series (%).') }}" class="min-w-0">
    <div data-chart="line" data-smooth data-height="260" data-suffix="%"
      data-labels="{{ json_encode(array_map('__', ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'])) }}"
      data-series="{{ json_encode([['name' => __('Organic'), 'data' => [3.1, 3.4, 3.2, 3.8, 3.6, 4.1, 4.4]], ['name' => __('Social media'), 'data' => [2.2, 2.6, 2.4, 2.9, 3.1, 3.5, 3.3], 'color' => '2'], ['name' => __('Ads'), 'data' => [1.6, 1.9, 2.1, 1.8, 2.3, 2.2, 2.6], 'color' => '3']]) }}"></div>
  </x-ui.card>

  <x-ui.card title="{{ __('Revenue vs target') }}" description="{{ __('Grouped bars (thousand ringgit).') }}" class="min-w-0">
    <div data-chart="bar" data-height="260" data-prefix="RM " data-suffix="k"
      data-labels="{{ json_encode(array_map('__', ['Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep'])) }}"
      data-series="{{ json_encode([['name' => __('Actual'), 'data' => [11.3, 12.7, 13.7, 12.9, 14.7, 16.7]], ['name' => __('Target'), 'data' => [11.4, 12, 12.9, 13.7, 14.3, 15.7], 'color' => '5']]) }}"></div>
  </x-ui.card>
</section>

<section class="grid gap-4 lg:grid-cols-3">
  <x-ui.card title="{{ __('Orders by channel') }}" description="{{ __('Stacked bars, last 7 days.') }}" class="min-w-0 lg:col-span-2">
    <div data-chart="stacked" data-height="270"
      data-labels="{{ json_encode(array_map('__', ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'])) }}"
      data-series="{{ json_encode([['name' => __('Website'), 'data' => [22, 26, 24, 31, 29, 38, 35]], ['name' => 'Marketplace', 'data' => [14, 18, 16, 21, 24, 27, 25], 'color' => '2'], ['name' => 'WhatsApp', 'data' => [8, 9, 12, 10, 13, 15, 14], 'color' => '3']]) }}"></div>
  </x-ui.card>

  <x-ui.card title="{{ __('Devices') }}" description="{{ __('Donut with an interactive legend.') }}">
    <div data-chart="donut" data-size="150" data-total-label="{{ __('Sessions') }}"
      data-items="{{ json_encode([['label' => __('Mobile'), 'value' => 6240], ['label' => __('Desktop'), 'value' => 3120], ['label' => __('Tablet'), 'value' => 740]]) }}"></div>
  </x-ui.card>
</section>

<section class="grid gap-4 md:grid-cols-3">
  <div class="card p-6">
    <h2 class="card-title">{{ __('Sales target') }}</h2>
    <p class="card-desc mt-1.5">{{ __('September') }}</p>
    <div class="mt-5 flex items-center gap-5">
      <div data-chart="radial" data-value="78" data-label="{{ __('Achieved') }}" data-color="1"></div>
      <dl class="space-y-2 text-sm"><div><dt class="text-muted-foreground">{{ __('Actual') }}</dt><dd class="font-medium">RM 16.7k</dd></div><div><dt class="text-muted-foreground">{{ __('Target') }}</dt><dd class="font-medium">RM 21.4k</dd></div></dl>
    </div>
  </div>
  <div class="card p-6">
    <h2 class="card-title">{{ __('Customer satisfaction') }}</h2>
    <p class="card-desc mt-1.5">{{ __('30-day average review') }}</p>
    <div class="mt-5 flex items-center gap-5">
      <div data-chart="radial" data-value="92" data-label="{{ __('Satisfied') }}" data-color="2"></div>
      <dl class="space-y-2 text-sm"><div><dt class="text-muted-foreground">{{ __('Rating') }}</dt><dd class="font-medium">4.8 / 5</dd></div><div><dt class="text-muted-foreground">{{ __('Reviews') }}</dt><dd class="font-medium">1,126</dd></div></dl>
    </div>
  </div>
  <div class="card p-6">
    <h2 class="card-title">{{ __('Warehouse capacity') }}</h2>
    <p class="card-desc mt-1.5">{{ __('Currently filled') }}</p>
    <div class="mt-5 flex items-center gap-5">
      <div data-chart="radial" data-value="64" data-label="{{ __('Filled') }}" data-color="3"></div>
      <dl class="space-y-2 text-sm"><div><dt class="text-muted-foreground">{{ __('Used') }}</dt><dd class="font-medium">{{ __('640 racks') }}</dd></div><div><dt class="text-muted-foreground">{{ __('Total') }}</dt><dd class="font-medium">{{ __('1,000 racks') }}</dd></div></dl>
    </div>
  </div>
</section>

<section class="grid gap-4 lg:grid-cols-2">
  <x-ui.card title="{{ __('Best-selling products') }}" description="{{ __('Horizontal bars (units sold).') }}">
    <div data-chart="hbar" data-suffix=" {{ __('units') }}" data-items='[{"label":"Arabika Gayo 1 kg","value":412},{"label":"{{ __('5-variety sampler pack') }}","value":338},{"label":"Robusta Temanggung 1 kg","value":276},{"label":"French press 600 ml","value":181},{"label":"Toraja Sapan 500 g","value":149}]'></div>
  </x-ui.card>

  <x-ui.card title="{{ __('Top cities') }}" description="{{ __('Orders by destination city.') }}">
    <div data-chart="hbar" data-color="2" data-items='[{"label":"Yogyakarta","value":540},{"label":"Jakarta","value":486},{"label":"Bandung","value":312},{"label":"Surabaya","value":254},{"label":"Semarang","value":198}]'></div>
  </x-ui.card>
</section>

<div class="card min-w-0">
  <div class="card-header">
    <h2 class="card-title">{{ __('Acquisition channels') }}</h2>
    <p class="card-desc">{{ __('Sparklines inside a table (line, bar and area).') }}</p>
  </div>
  <div class="overflow-x-auto border-t">
    <table class="table min-w-[600px]">
      <thead><tr><th>{{ __('Channel') }}</th><th class="text-right">{{ __('Visits') }}</th><th class="text-right">{{ __('Conversion') }}</th><th class="w-44">{{ __('7-day trend') }}</th></tr></thead>
      <tbody>
        <tr><td class="font-medium">{{ __('Organic search') }}</td><td class="text-right tabular-nums">4,200</td><td class="text-right tabular-nums">4.4%</td><td><div data-chart="spark" data-kind="area" data-color="success" data-values="[12,14,13,17,16,19,22]" data-height="32"></div></td></tr>
        <tr><td class="font-medium">{{ __('Social media') }}</td><td class="text-right tabular-nums">2,900</td><td class="text-right tabular-nums">3.3%</td><td><div data-chart="spark" data-kind="bar" data-color="chart-1" data-values="[8,11,9,13,12,15,14]" data-height="32"></div></td></tr>
        <tr><td class="font-medium">{{ __('Direct') }}</td><td class="text-right tabular-nums">1,800</td><td class="text-right tabular-nums">2.9%</td><td><div data-chart="spark" data-kind="line" data-color="info" data-values="[9,8,9,10,9,11,10]" data-height="32"></div></td></tr>
        <tr><td class="font-medium">{{ __('Paid ads') }}</td><td class="text-right tabular-nums">1,100</td><td class="text-right tabular-nums">2.6%</td><td><div data-chart="spark" data-kind="area" data-color="danger" data-values="[14,13,12,12,10,9,8]" data-height="32"></div></td></tr>
      </tbody>
    </table>
  </div>
</div>
</x-layouts.admin>
