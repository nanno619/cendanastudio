<x-layouts.admin title="{{ __('Dashboard') }}" group="{{ __('Platform') }}">
<div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
  <x-ui.page-header title="{{ __('Overview') }}" description="{{ __('Your business performance over the last 30 days.') }}" />
  <div class="grid gap-2 sm:flex sm:items-center">
    <button type="button" class="btn btn-outline w-full sm:w-auto"><x-ui.icon name="calendar" class="h-4 w-4 text-muted-foreground" />{{ __('29 Aug – 28 Sep 2026') }}</button>
    <button type="button" class="btn btn-primary w-full sm:w-auto" data-toast="success" data-toast-title="{{ __('Preparing your report') }}" data-toast-desc="{{ __('The file will download automatically.') }}"><x-ui.icon name="download" />{{ __('Download report') }}</button>
  </div>
</div>

<!-- Statistik -->
<section aria-label="{{ __('Key statistics') }}" class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
  <x-ui.stat-card title="{{ __('Total revenue') }}" value="RM 16.7k" icon="dollar" tile="bg-soft text-soft-foreground" tone="success" direction="up" delta="+13.2%" spark="[41,45,44,48,47,52,58]" />
  <x-ui.stat-card title="{{ __('New customers') }}" value="1,284" icon="userplus" tile="bg-success-soft text-success" tone="success" direction="up" delta="+8.1%" spark="[30,34,33,38,36,41,44]" />
  <x-ui.stat-card title="{{ __('Active orders') }}" value="356" icon="cart" tile="bg-warning-soft text-warning" tone="danger" direction="down" delta="−2.4%" spark="[60,58,61,57,59,55,54]" />
  <x-ui.stat-card title="{{ __('Conversion rate') }}" value="3.42%" icon="activity" tile="bg-info-soft text-info" tone="success" direction="up" delta="{{ __('+0.3 pts') }}" spark="[2.8,3,2.9,3.1,3.2,3.3,3.4]" />
</section>

<!-- Chart + sales -->
<section class="grid gap-4 lg:grid-cols-7">
  <div class="card min-w-0 lg:col-span-4">
    <div class="card-header">
      <h2 class="card-title">{{ __('Revenue') }}</h2>
      <p class="card-desc">{{ __('Last 12 months. Total RM 148.8k.') }}</p>
    </div>
    <div class="card-content">
      <div data-chart="bar" data-height="280" data-prefix="RM " data-suffix="k"
        data-labels="{{ json_encode(array_map('__', ['Oct', 'Nov', 'Dec', 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep'])) }}"
        data-series='[{"name":"{{ __('Revenue') }}","data":[8.9,11,14.9,10.4,9.9,11.7,11.3,12.7,13.7,12.9,14.7,16.7]}]'></div>
    </div>
  </div>

  <div class="card lg:col-span-3">
    <div class="card-header">
      <h2 class="card-title">{{ __('Recent sales') }}</h2>
      <p class="card-desc">{{ __('356 orders received this month.') }}</p>
    </div>
    <ul class="space-y-5 p-6 pt-0">
      @foreach (config('cendana.demo.sales') as $item)<li class="flex items-center gap-3">
        <span class="avatar avatar-{{ $item['tone'] }}">{{ $item['initials'] }}</span>
        <div class="min-w-0 flex-1 leading-tight"><p class="truncate font-medium">{{ $item['name'] }}</p><p class="truncate text-xs text-muted-foreground">{{ $item['email'] }}</p></div>
        <span class="font-medium tabular-nums">{{ $item['amount'] }}</span>
      </li>@endforeach
    </ul>
  </div>
</section>

<!-- Orders + traffic sources -->
<section class="grid gap-4 lg:grid-cols-5">
  <div class="card min-w-0 lg:col-span-3">
    <div class="flex items-center justify-between gap-4 p-6 pb-4">
      <div>
        <h2 class="card-title">{{ __('Recent orders') }}</h2>
        <p class="card-desc mt-1.5">{{ __('The six latest orders from your business.') }}</p>
      </div>
      @if (config('cendana.showcase'))
        <a href="{{ route('showcase.tables') }}" class="btn btn-outline btn-sm">{{ __('View all') }}</a>
      @endif
    </div>
    <div class="overflow-x-auto border-t">
      <table class="table min-w-[640px]">
        <thead><tr><th>{{ __('Orders') }}</th><th>{{ __('Customers') }}</th><th class="hidden lg:table-cell">{{ __('Products') }}</th><th>{{ __('Status') }}</th><th class="hidden md:table-cell">{{ __('Date') }}</th><th class="text-right">{{ __('Total') }}</th></tr></thead>
        <tbody>
          @foreach (config('cendana.demo.orders') as $item)<tr>
            <td class="font-medium">{{ $item['id'] }}</td>
            <td>{{ $item['customer'] }}</td>
            <td class="hidden text-muted-foreground lg:table-cell">{{ __($item['product']) }}</td>
            <td><span class="badge badge-{{ $item['tone'] }} badge-dot">{{ __($item['status']) }}</span></td>
            <td class="hidden text-muted-foreground md:table-cell">{{ $item['date'] }}</td>
            <td class="text-right tabular-nums {{ $item['struck'] }}">{{ $item['total'] }}</td>
          </tr>@endforeach
        </tbody>
      </table>
    </div>
  </div>

  <div class="card min-w-0 lg:col-span-2">
    <div class="card-header">
      <h2 class="card-title">{{ __('Traffic sources') }}</h2>
      <p class="card-desc">{{ __('Visits over the last 30 days.') }}</p>
    </div>
    <div class="card-content">
      <div data-chart="donut" data-size="160" data-total-label="{{ __('Visits') }}"
        data-items="{{ json_encode([['label' => __('Organic search'), 'value' => 4200], ['label' => __('Social media'), 'value' => 2900], ['label' => __('Direct'), 'value' => 1800], ['label' => __('Paid ads'), 'value' => 1100]]) }}"></div>
    </div>
  </div>
</section>
</x-layouts.admin>
