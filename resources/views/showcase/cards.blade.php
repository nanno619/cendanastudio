<x-layouts.admin title="{{ __('Cards') }}" group="{{ __('Components') }}">
<x-ui.page-header title="{{ __('Cards') }}" description="{{ __('Stat, profile, pricing, product, activity and list card variations.') }}" />

<div class="space-y-4">
  <h2 class="text-lg font-semibold tracking-tight">{{ __('Stat cards') }}</h2>
<section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4" aria-label="{{ __('Coloured icons') }}">
  <x-ui.stat-card title="{{ __('Revenue') }}" value="RM 16.7k" icon="dollar" tile="bg-soft text-soft-foreground" tone="success" direction="up" delta="+13.2%" />
  <x-ui.stat-card title="{{ __('New customers') }}" value="1,284" icon="userplus" tile="bg-success-soft text-success" tone="success" direction="up" delta="+8.1%" />
  <x-ui.stat-card title="{{ __('Active orders') }}" value="356" icon="cart" tile="bg-warning-soft text-warning" tone="danger" direction="down" delta="−2.4%" />
  <x-ui.stat-card title="{{ __('Conversion') }}" value="3.42%" icon="activity" tile="bg-info-soft text-info" tone="success" direction="up" delta="{{ __('+0.3 pts') }}" />
</section>

<section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4" aria-label="{{ __('With sparklines') }}">
  <x-ui.stat-card title="{{ __('Visits') }}" value="10,100" icon="globe" tile="bg-soft text-soft-foreground" tone="success" direction="up" delta="+5.4%" note="{{ __('7 days') }}" spark="[12,14,13,16,15,18,19]" />
  <x-ui.stat-card title="{{ __('Abandoned carts') }}" value="212" icon="cart" tile="bg-danger-soft text-danger" tone="danger" direction="up" delta="+3.1%" note="{{ __('7 days') }}" spark="[9,10,9,11,12,12,14]" />
  <x-ui.stat-card title="{{ __('Average order') }}" value="RM 47.00" icon="tag" tile="bg-info-soft text-info" tone="success" direction="up" delta="+2.0%" note="{{ __('7 days') }}" spark="[150,152,151,158,160,162,164]" />
  <x-ui.stat-card title="{{ __('Returns') }}" value="1.2%" icon="refresh" tile="bg-success-soft text-success" tone="success" direction="down" delta="{{ __('−0.4 pts') }}" note="{{ __('7 days') }}" spark="[2,1.9,1.7,1.6,1.5,1.3,1.2]" />
</section>

<section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4" aria-label="{{ __('Style variations') }}">
  <div class="card p-6">
    <p class="text-sm font-medium text-muted-foreground">{{ __('This month\'s target') }}</p>
    <p class="mt-2 text-2xl font-semibold tabular-nums">78%</p>
    <div class="progress mt-4"><div class="progress-bar" style="width:78%"></div></div>
    <p class="mt-2 text-xs text-muted-foreground">{{ __('RM 16.7k of RM 21.4k') }}</p>
  </div>
  <div class="rounded-xl bg-primary p-6 text-primary-foreground shadow-sm">
    <p class="text-sm font-medium opacity-80">{{ __('Available balance') }}</p>
    <p class="mt-2 text-2xl font-semibold tabular-nums">RM 3.7k</p>
    <button type="button" class="mt-4 inline-flex h-8 items-center gap-1.5 rounded-md bg-primary-foreground/15 px-3 text-xs font-medium hover:bg-primary-foreground/25">{{ __('Withdraw funds') }} <x-ui.icon name="arrow-right" class="h-3.5 w-3.5" /></button>
  </div>
  <div class="rounded-xl border border-dashed p-6">
    <p class="text-sm font-medium text-muted-foreground">{{ __('Dashed outline') }}</p>
    <p class="mt-2 text-2xl font-semibold tabular-nums">2,481</p>
    <p class="mt-1 text-xs text-muted-foreground">{{ __('Minimal style without shadow') }}</p>
  </div>
  <div class="rounded-xl bg-success-soft p-6">
    <p class="text-sm font-medium text-success">{{ __('Coloured soft card') }}</p>
    <p class="mt-2 text-2xl font-semibold tabular-nums text-success">+24.8%</p>
    <p class="mt-1 text-xs text-success/80">{{ __('Annual growth') }}</p>
  </div>
</section>
</div>

<div class="space-y-4">
  <h2 class="text-lg font-semibold tracking-tight">{{ __('General cards') }}</h2>
<section class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
  <!-- Header with actions, content, footer -->
  <div class="card">
    <div class="flex items-start justify-between gap-3 p-6 pb-4">
      <div><h3 class="card-title">{{ __('Basic card') }}</h3><p class="card-desc mt-1.5">{{ __('Header, content and footer.') }}</p></div>
      <div data-dropdown>
        <button type="button" data-dropdown-toggle aria-expanded="false" aria-label="{{ __('Options') }}" class="btn btn-ghost btn-icon btn-sm text-muted-foreground"><x-ui.icon name="more-v" /></button>
        <div class="menu menu-right" hidden>
          <button type="button" class="menu-item"><x-ui.icon name="edit" />{{ __('Edit') }}</button>
          <button type="button" class="menu-item"><x-ui.icon name="copy" />{{ __('Duplicate') }}</button>
          <div class="menu-sep"></div>
          <button type="button" class="menu-item menu-item-danger"><x-ui.icon name="trash" />{{ __('Delete') }}</button>
        </div>
      </div>
    </div>
    <div class="card-content text-muted-foreground">{{ __('Cards are the main container. Arrange them in a grid and use') }} <code class="rounded bg-muted px-1 py-0.5 text-xs">card-header</code>, <code class="rounded bg-muted px-1 py-0.5 text-xs">card-content</code>{{ __(', and') }} <code class="rounded bg-muted px-1 py-0.5 text-xs">card-footer</code>.</div>
    <div class="card-footer justify-end"><button type="button" class="btn btn-outline btn-sm">{{ __('Cancel') }}</button><button type="button" class="btn btn-primary btn-sm">{{ __('Save') }}</button></div>
  </div>

  <!-- Profile -->
  <div class="card overflow-hidden">
    <div class="h-20 bg-soft"></div>
    <div class="px-6 pb-6">
      <span class="avatar avatar-xl -mt-8 border-4 border-card">AR</span>
      <h3 class="mt-3 font-semibold leading-none">Ayu Rahmawati</h3>
      <p class="mt-1 text-sm text-muted-foreground">{{ __('Store manager · Yogyakarta') }}</p>
      <dl class="mt-4 grid grid-cols-3 divide-x rounded-lg border text-center">
        <div class="py-2"><dt class="text-xs text-muted-foreground">{{ __('Orders') }}</dt><dd class="font-semibold tabular-nums">1,204</dd></div>
        <div class="py-2"><dt class="text-xs text-muted-foreground">{{ __('Reviews') }}</dt><dd class="font-semibold tabular-nums">318</dd></div>
        <div class="py-2"><dt class="text-xs text-muted-foreground">{{ __('Rating') }}</dt><dd class="font-semibold tabular-nums">4.9</dd></div>
      </dl>
      <div class="mt-4 flex gap-2"><button type="button" class="btn btn-primary flex-1"><x-ui.icon name="mail" />{{ __('Message') }}</button><button type="button" class="btn btn-outline flex-1">{{ __('Follow') }}</button></div>
    </div>
  </div>

  <!-- Aktivitas -->
  <div class="card">
    <div class="card-header"><h3 class="card-title">{{ __('Recent activity') }}</h3><p class="card-desc">{{ __('A timeline of store events.') }}</p></div>
    <ol class="space-y-5 p-6 pt-0">
      @foreach (config('cendana.demo.activity') as $item)<li class="flex items-start gap-3">
        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full {{ $item['tile'] }}"><x-ui.icon :name="$item['icon']" /></span>
        <div class="min-w-0 leading-snug"><p class="font-medium">{{ __($item['text']) }}</p><p class="text-xs text-muted-foreground">{{ __($item['time']) }}</p></div>
      </li>@endforeach
    </ol>
  </div>
</section>

<!-- Pricing -->
<section class="grid gap-4 md:grid-cols-3" aria-label="{{ __('Pricing plans') }}">
  <div class="card p-6">
    <h3 class="font-semibold">Starter</h3>
    <p class="mt-1 text-sm text-muted-foreground">{{ __('For stores just getting started.') }}</p>
    <p class="mt-4"><span class="text-3xl font-semibold tabular-nums">RM 0</span><span class="text-muted-foreground"> {{ __('/ month') }}</span></p>
    <ul class="mt-5 space-y-2.5 text-sm">
      <li class="flex items-center gap-2"><x-ui.icon name="check" class="h-4 w-4 text-success" />{{ __('50 products') }}</li>
      <li class="flex items-center gap-2"><x-ui.icon name="check" class="h-4 w-4 text-success" />{{ __('1 user') }}</li>
      <li class="flex items-center gap-2"><x-ui.icon name="check" class="h-4 w-4 text-success" />{{ __('Basic reports') }}</li>
    </ul>
    <button type="button" class="btn btn-outline mt-6 w-full">{{ __('Choose Starter') }}</button>
  </div>
  <div class="relative rounded-xl border-2 border-primary bg-card p-6 shadow-sm">
    <span class="badge badge-primary absolute -top-3 left-6">{{ __('Most popular') }}</span>
    <h3 class="font-semibold">Pro</h3>
    <p class="mt-1 text-sm text-muted-foreground">{{ __('For growing stores.') }}</p>
    <p class="mt-4"><span class="text-3xl font-semibold tabular-nums">RM 49</span><span class="text-muted-foreground"> {{ __('/ month') }}</span></p>
    <ul class="mt-5 space-y-2.5 text-sm">
      <li class="flex items-center gap-2"><x-ui.icon name="check" class="h-4 w-4 text-success" />{{ __('Unlimited products') }}</li>
      <li class="flex items-center gap-2"><x-ui.icon name="check" class="h-4 w-4 text-success" />{{ __('5 users') }}</li>
      <li class="flex items-center gap-2"><x-ui.icon name="check" class="h-4 w-4 text-success" />{{ __('Advanced analytics') }}</li>
      <li class="flex items-center gap-2"><x-ui.icon name="check" class="h-4 w-4 text-success" />{{ __('Marketplace integrations') }}</li>
    </ul>
    <button type="button" class="btn btn-primary mt-6 w-full">{{ __('Choose Pro') }}</button>
  </div>
  <div class="card p-6">
    <h3 class="font-semibold">{{ __('Business') }}</h3>
    <p class="mt-1 text-sm text-muted-foreground">{{ __('For teams and multiple branches.') }}</p>
    <p class="mt-4"><span class="text-3xl font-semibold tabular-nums">RM 149</span><span class="text-muted-foreground"> {{ __('/ month') }}</span></p>
    <ul class="mt-5 space-y-2.5 text-sm">
      <li class="flex items-center gap-2"><x-ui.icon name="check" class="h-4 w-4 text-success" />{{ __('Everything in Pro') }}</li>
      <li class="flex items-center gap-2"><x-ui.icon name="check" class="h-4 w-4 text-success" />{{ __('Unlimited users') }}</li>
      <li class="flex items-center gap-2"><x-ui.icon name="check" class="h-4 w-4 text-success" />{{ __('Roles and access') }}</li>
    </ul>
    <button type="button" class="btn btn-outline mt-6 w-full">{{ __('Contact us') }}</button>
  </div>
</section>

<!-- Products -->
<section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4" aria-label="{{ __('Product cards') }}">
  <div class="card overflow-hidden">
    <div class="relative flex h-36 items-center justify-center bg-soft text-soft-foreground"><x-ui.icon name="coffee" class="h-10 w-10" /><span class="badge badge-primary absolute left-3 top-3">{{ __('New') }}</span></div>
    <div class="p-4"><p class="text-xs text-muted-foreground">{{ __('Coffee beans') }}</p><h3 class="font-semibold">Arabika Gayo 1 kg</h3>
      <div class="mt-3 flex items-center justify-between"><span class="font-semibold tabular-nums">RM 48.00</span><span class="inline-flex items-center gap-1 text-xs text-muted-foreground"><x-ui.icon name="star" class="h-3.5 w-3.5 text-caution" />4.9</span></div>
      <button type="button" class="btn btn-outline btn-sm mt-3 w-full"><x-ui.icon name="cart" />{{ __('Add to cart') }}</button></div>
  </div>
  <div class="card overflow-hidden">
    <div class="flex h-36 items-center justify-center bg-info-soft text-info"><x-ui.icon name="package" class="h-10 w-10" /></div>
    <div class="p-4"><p class="text-xs text-muted-foreground">{{ __('Bundles') }}</p><h3 class="font-semibold">{{ __('5-variety sampler pack') }}</h3>
      <div class="mt-3 flex items-center justify-between"><span class="font-semibold tabular-nums">RM 61.00</span><span class="inline-flex items-center gap-1 text-xs text-muted-foreground"><x-ui.icon name="star" class="h-3.5 w-3.5 text-caution" />4.9</span></div>
      <button type="button" class="btn btn-outline btn-sm mt-3 w-full"><x-ui.icon name="cart" />{{ __('Add to cart') }}</button></div>
  </div>
  <div class="card overflow-hidden">
    <div class="relative flex h-36 items-center justify-center bg-warning-soft text-warning"><x-ui.icon name="zap" class="h-10 w-10" /><span class="badge badge-solid-warning absolute left-3 top-3">{{ __('20% off') }}</span></div>
    <div class="p-4"><p class="text-xs text-muted-foreground">{{ __('Equipment') }}</p><h3 class="font-semibold">French press 600 ml</h3>
      <div class="mt-3 flex items-center justify-between"><span class="font-semibold tabular-nums">RM 76.00</span><span class="inline-flex items-center gap-1 text-xs text-muted-foreground"><x-ui.icon name="star" class="h-3.5 w-3.5 text-caution" />4.6</span></div>
      <button type="button" class="btn btn-outline btn-sm mt-3 w-full"><x-ui.icon name="cart" />{{ __('Add to cart') }}</button></div>
  </div>
  <div class="card overflow-hidden opacity-90">
    <div class="flex h-36 items-center justify-center bg-muted text-muted-foreground"><x-ui.icon name="image" class="h-10 w-10" /></div>
    <div class="p-4"><p class="text-xs text-muted-foreground">{{ __('Equipment') }}</p><h3 class="font-semibold">Grinder manual</h3>
      <div class="mt-3 flex items-center justify-between"><span class="font-semibold tabular-nums">RM 135.00</span><span class="badge badge-danger">{{ __('Sold out') }}</span></div>
      <button type="button" class="btn btn-secondary btn-sm mt-3 w-full" disabled>{{ __('Out of stock') }}</button></div>
  </div>
</section>

<!-- Task list, notifications, quote -->
<section class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
  <div class="card">
    <div class="card-header"><h3 class="card-title">{{ __('Today\'s tasks') }}</h3><p class="card-desc">{{ __('Tick to mark as done.') }}</p></div>
    <ul class="space-y-3 p-6 pt-0">
      <li><label class="flex cursor-pointer items-center gap-3"><input type="checkbox" class="peer" checked><span class="peer-checked:text-muted-foreground peer-checked:line-through">{{ __('Pack 12 orders') }}</span></label></li>
      <li><label class="flex cursor-pointer items-center gap-3"><input type="checkbox" class="peer" checked><span class="peer-checked:text-muted-foreground peer-checked:line-through">{{ __('Reply to customer reviews') }}</span></label></li>
      <li><label class="flex cursor-pointer items-center gap-3"><input type="checkbox" class="peer"><span class="peer-checked:text-muted-foreground peer-checked:line-through">{{ __('Restock Toraja Sapan') }}</span></label></li>
      <li><label class="flex cursor-pointer items-center gap-3"><input type="checkbox" class="peer"><span class="peer-checked:text-muted-foreground peer-checked:line-through">{{ __('Weekly sales recap') }}</span></label></li>
    </ul>
  </div>

  <div class="card">
    <div class="card-header"><h3 class="card-title">{{ __('Inbox') }}</h3><p class="card-desc">{{ __('A dot marks unread messages.') }}</p></div>
    <ul class="divide-y border-t">
      <li class="flex items-start gap-3 px-6 py-3"><span class="avatar avatar-info">PM</span><div class="min-w-0 flex-1 leading-snug"><p class="flex items-center gap-2 font-medium">Putri Maharani<span class="h-2 w-2 rounded-full bg-primary"></span></p><p class="truncate text-muted-foreground">{{ __('Can the Gayo beans be ground fine?') }}</p></div><span class="text-xs text-muted-foreground">09.12</span></li>
      <li class="flex items-start gap-3 px-6 py-3"><span class="avatar avatar-success">AS</span><div class="min-w-0 flex-1 leading-snug"><p class="flex items-center gap-2 font-medium">Andika Saputra<span class="h-2 w-2 rounded-full bg-primary"></span></p><p class="truncate text-muted-foreground">{{ __('My parcel has arrived, thank you.') }}</p></div><span class="text-xs text-muted-foreground">08.40</span></li>
      <li class="flex items-start gap-3 px-6 py-3"><span class="avatar avatar-warning">NP</span><div class="min-w-0 flex-1 leading-snug"><p class="font-medium">Nadia Permata</p><p class="truncate text-muted-foreground">{{ __('Can you ship out of town?') }}</p></div><span class="text-xs text-muted-foreground">{{ __('Yesterday') }}</span></li>
    </ul>
  </div>

  <div class="card p-6">
    <span class="text-primary"><x-ui.icon name="message" class="h-6 w-6" /></span>
    <blockquote class="mt-3 leading-relaxed">{{ __('“This dashboard makes my daily recap much faster. Stock, orders and revenue are all on one screen.”') }}</blockquote>
    <div class="mt-5 flex items-center gap-3"><span class="avatar avatar-soft">HW</span><div class="leading-tight"><p class="font-medium">Hendra Wijaya</p><p class="text-xs text-muted-foreground">{{ __('Owner, Kopi Kenanga') }}</p></div></div>
  </div>
</section>

<!-- Horizontal card and empty state -->
<section class="grid gap-4 lg:grid-cols-2">
  <div class="card flex flex-col overflow-hidden sm:flex-row">
    <div class="flex h-32 items-center justify-center bg-soft text-soft-foreground sm:h-auto sm:w-40 sm:shrink-0"><x-ui.icon name="truck" class="h-10 w-10" /></div>
    <div class="p-6"><span class="badge badge-info">{{ __('Delivery') }}</span><h3 class="mt-2 font-semibold">{{ __('Free shipping across Java') }}</h3><p class="mt-1 text-muted-foreground">{{ __('Valid on purchases over RM 70 until the end of the month.') }}</p><a href="#" class="btn btn-link mt-3">{{ __('Learn more') }} <x-ui.icon name="arrow-right" /></a></div>
  </div>
  <div class="card flex flex-col items-center justify-center gap-3 p-8 text-center">
    <span class="flex h-12 w-12 items-center justify-center rounded-full bg-muted text-muted-foreground"><x-ui.icon name="file-text" class="h-6 w-6" /></span>
    <h3 class="font-semibold">{{ __('No reports yet') }}</h3>
    <p class="max-w-xs text-muted-foreground">{{ __('Create your first report to see a sales summary.') }}</p>
    <button type="button" class="btn btn-primary"><x-ui.icon name="plus" />{{ __('Create report') }}</button>
  </div>
</section>
</div>
</x-layouts.admin>
