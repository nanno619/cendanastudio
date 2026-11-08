<x-layouts.admin title="{{ __('Tables') }}" group="{{ __('Components') }}">
<x-ui.page-header title="{{ __('Tables') }}" description="{{ __('Interactive, striped and compact tables with totals, product progress and empty states.') }}" />

<!-- 1. Interactive data table -->
<div class="card" data-table data-page-size="8">
  <div class="flex flex-col gap-3 p-6 pb-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
      <h2 class="card-title">{{ __('Customers') }}</h2>
      <p class="card-desc mt-1.5">{{ __('Search, filter, sort (click a column heading), select rows and paginate.') }}</p>
    </div>
    <button type="button" class="btn btn-primary btn-sm"><x-ui.icon name="plus" />{{ __('Add customer') }}</button>
  </div>

  <div class="flex flex-col gap-2 border-t px-6 py-3 sm:flex-row sm:items-center">
    <div class="relative sm:w-64">
      <x-ui.icon name="search" class="pointer-events-none absolute left-2.5 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
      <input type="search" class="input pl-8" placeholder="{{ __('Search name or email') }}" aria-label="{{ __('Search customers') }}" data-table-search>
    </div>
    <select class="select sm:w-40" aria-label="{{ __('Filter by status') }}" data-table-filter>
      <option value="">{{ __('All statuses') }}</option>
      <option value="active">{{ __('Active') }}</option>
      <option value="pending">{{ __('Pending') }}</option>
      <option value="inactive">{{ __('Inactive') }}</option>
    </select>
    <div data-bulk hidden class="flex items-center gap-2 text-sm sm:ml-auto">
      <span class="text-muted-foreground"><span data-bulk-count class="font-medium text-foreground">0</span> {{ __('selected') }}</span>
      <button type="button" class="btn btn-outline btn-sm"><x-ui.icon name="download" />{{ __('Export') }}</button>
      <button type="button" class="btn btn-destructive btn-sm" data-toast="danger" data-toast-title="{{ __('Example: data deleted') }}"><x-ui.icon name="trash" />{{ __('Delete') }}</button>
    </div>
  </div>

  <div class="overflow-x-auto border-t">
    <table class="table min-w-[760px]">
      <thead>
        <tr>
          <th class="w-12" scope="col"><span class="sr-only">{{ __('Actions') }}</span></th>
          <th class="w-10"><input type="checkbox" data-check-all aria-label="{{ __('Select all') }}"></th>
          <th data-sort>{{ __('Customers') }}</th>
          <th data-sort>{{ __('Role') }}</th>
          <th>{{ __('Status') }}</th>
          <th data-sort="num" class="text-right">{{ __('Orders') }}</th>
          <th data-sort>{{ __('Last active') }}</th>
        </tr>
      </thead>
      <tbody>
        @foreach (config('cendana.demo.users') as $item)<tr data-status="{{ $item['statusKey'] }}">
          <td data-actions>
            <div data-dropdown>
              <button type="button" data-dropdown-toggle aria-expanded="false" aria-label="{{ __('Actions for :name', ['name' => $item['name']]) }}" data-tip="{{ __('Actions') }}" data-tip-pos="right" class="btn btn-ghost btn-icon btn-sm text-muted-foreground"><x-ui.icon name="more" /></button>
              <div class="menu" hidden>
                <button type="button" class="menu-item"><x-ui.icon name="eye" />{{ __('View details') }}</button>
                <button type="button" class="menu-item"><x-ui.icon name="edit" />{{ __('Edit') }}</button>
                <button type="button" class="menu-item"><x-ui.icon name="mail" />{{ __('Send email') }}</button>
                <div class="menu-sep"></div>
                <button type="button" class="menu-item menu-item-danger"><x-ui.icon name="trash" />{{ __('Delete') }}</button>
              </div>
            </div>
          </td>
          <td><input type="checkbox" data-check-row aria-label="{{ __('Select :name', ['name' => $item['name']]) }}"></td>
          <td><div class="flex items-center gap-3"><span class="avatar avatar-{{ $item['avTone'] }}">{{ $item['initials'] }}</span><div class="leading-tight"><p class="font-medium">{{ $item['name'] }}</p><p class="text-xs text-muted-foreground">{{ $item['email'] }}</p></div></div></td>
          <td>{{ __($item['role']) }}</td>
          <td><span class="badge badge-{{ $item['tone'] }} badge-dot">{{ __($item['status']) }}</span></td>
          <td class="text-right tabular-nums" data-value="{{ $item['orders'] }}">{{ $item['orders'] }}</td>
          <td class="text-muted-foreground">{{ __($item['seen']) }}</td>
        </tr>@endforeach
      </tbody>
    </table>
    <div data-table-empty hidden class="p-10 text-center text-muted-foreground">{{ __('No data matches your search.') }}</div>
  </div>

  <div class="flex flex-col items-center justify-between gap-3 border-t px-6 py-3 sm:flex-row">
    <p data-table-info class="text-sm text-muted-foreground"></p>
    <div data-table-pager class="flex items-center gap-1"></div>
  </div>
</div>

<!-- 2 and 3 -->
<section class="grid gap-4 xl:grid-cols-2">
  <div class="card min-w-0">
    <div class="card-header"><h2 class="card-title">{{ __('Striped table') }}</h2><p class="card-desc">{{ __('Variant') }} <code class="rounded bg-muted px-1 py-0.5 text-xs">table-striped</code>.</p></div>
    <div class="overflow-x-auto border-t">
      <table class="table table-striped min-w-[480px]">
        <thead><tr><th>{{ __('Orders') }}</th><th>{{ __('Customers') }}</th><th>{{ __('Status') }}</th><th class="text-right">{{ __('Total') }}</th></tr></thead>
        <tbody>
          @foreach (config('cendana.demo.orders') as $item)<tr><td class="font-medium">{{ $item['id'] }}</td><td>{{ $item['customer'] }}</td><td><span class="badge badge-{{ $item['tone'] }}">{{ __($item['status']) }}</span></td><td class="text-right tabular-nums {{ $item['struck'] }}">{{ $item['total'] }}</td></tr>@endforeach
        </tbody>
      </table>
    </div>
  </div>

  <div class="card min-w-0">
    <div class="card-header"><h2 class="card-title">{{ __('Compact table with totals') }}</h2><p class="card-desc">{{ __('Variant') }} <code class="rounded bg-muted px-1 py-0.5 text-xs">table-compact table-bordered</code> {{ __('and') }} <code class="rounded bg-muted px-1 py-0.5 text-xs">tfoot</code>.</p></div>
    <div class="card-content overflow-x-auto">
      <table class="table table-compact table-bordered min-w-[440px]">
        <thead><tr><th>Item</th><th class="text-right">{{ __('Qty') }}</th><th class="text-right">{{ __('Price') }}</th><th class="text-right">Subtotal</th></tr></thead>
        <tbody>
          <tr><td>Arabika Gayo 1 kg</td><td class="text-right tabular-nums">2</td><td class="text-right tabular-nums">48.00</td><td class="text-right tabular-nums">96.00</td></tr>
          <tr><td>Toraja Sapan 500 g</td><td class="text-right tabular-nums">1</td><td class="text-right tabular-nums">36.00</td><td class="text-right tabular-nums">36.00</td></tr>
          <tr><td>French press 600 ml</td><td class="text-right tabular-nums">1</td><td class="text-right tabular-nums">76.00</td><td class="text-right tabular-nums">76.00</td></tr>
          <tr><td>{{ __('Shipping') }}</td><td class="text-right tabular-nums">1</td><td class="text-right tabular-nums">6.00</td><td class="text-right tabular-nums">6.00</td></tr>
        </tbody>
        <tfoot><tr><td colspan="3" class="text-right">{{ __('Total') }}</td><td class="text-right tabular-nums">RM 214.00</td></tr></tfoot>
      </table>
    </div>
  </div>
</section>

<!-- 4. Products -->
<div class="card">
  <div class="flex items-center justify-between gap-4 p-6 pb-4">
    <div><h2 class="card-title">{{ __('Product inventory') }}</h2><p class="card-desc mt-1.5">{{ __('Stock progress bars, ratings and status.') }}</p></div>
    <button type="button" class="btn btn-outline btn-sm"><x-ui.icon name="filter" />{{ __('Filter') }}</button>
  </div>
  <div class="overflow-x-auto border-t">
    <table class="table min-w-[760px]">
      <thead><tr><th>{{ __('Products') }}</th><th>{{ __('Category') }}</th><th class="w-48">{{ __('Stock') }}</th><th>{{ __('Rating') }}</th><th>{{ __('Status') }}</th><th class="text-right">{{ __('Price') }}</th></tr></thead>
      <tbody>
        @foreach (config('cendana.demo.products') as $item)<tr>
          <td><div class="flex items-center gap-3"><span class="avatar avatar-square avatar-neutral"><x-ui.icon name="coffee" /></span><div class="leading-tight"><p class="font-medium">{{ $item['name'] }}</p><p class="text-xs text-muted-foreground">{{ $item['sku'] }}</p></div></div></td>
          <td>{{ __($item['category']) }}</td>
          <td><div class="flex items-center gap-3"><div class="progress"><div class="progress-bar {{ $item['bar'] }}" style="width:{{ $item['pct'] }}%"></div></div><span class="w-8 text-right text-xs tabular-nums text-muted-foreground">{{ $item['stock'] }}</span></div></td>
          <td><span class="inline-flex items-center gap-1"><x-ui.icon name="star" class="h-3.5 w-3.5 text-caution" />{{ $item['rating'] }}</span></td>
          <td><span class="badge badge-{{ $item['tone'] }} badge-dot">{{ __($item['status']) }}</span></td>
          <td class="text-right font-medium tabular-nums">{{ $item['price'] }}</td>
        </tr>@endforeach
      </tbody>
    </table>
  </div>
</div>

<!-- 5. Empty state -->
<div class="card">
  <div class="flex flex-col items-center gap-3 px-6 py-14 text-center">
    <span class="flex h-12 w-12 items-center justify-center rounded-full bg-muted text-muted-foreground"><x-ui.icon name="package" class="h-6 w-6" /></span>
    <div>
      <h2 class="font-semibold">{{ __('No orders yet') }}</h2>
      <p class="mx-auto mt-1 max-w-sm text-muted-foreground">{{ __('Incoming orders will show up here. Share your store link to start receiving orders.') }}</p>
    </div>
    <div class="mt-2 flex gap-2"><button type="button" class="btn btn-outline">{{ __('Copy store link') }}</button><button type="button" class="btn btn-primary"><x-ui.icon name="plus" />{{ __('Add product') }}</button></div>
  </div>
</div>
</x-layouts.admin>
