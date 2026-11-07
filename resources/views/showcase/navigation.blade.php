<x-layouts.admin title="{{ __('Navigation') }}" group="{{ __('Components') }}">
<x-ui.page-header title="{{ __('Navigation') }}" description="{{ __('Tabs, accordions, dropdowns, pagination, breadcrumbs, steppers and menu lists.') }}" />

<section class="grid gap-4 lg:grid-cols-2">
  <x-ui.card title="{{ __('Underline tabs') }}" description="{{ __('Navigation with panels. Use the left and right arrow keys.') }}" class="min-w-0">
    <div data-tabs>
      <div class="tabs-underline" role="tablist" aria-label="{{ __('Order details') }}">
        <button type="button" role="tab" class="tab" id="tu-1" aria-selected="true" aria-controls="tp-1">{{ __('Overview') }}</button>
        <button type="button" role="tab" class="tab" id="tu-2" aria-selected="false" tabindex="-1" aria-controls="tp-2">Item <span class="tab-count">4</span></button>
        <button type="button" role="tab" class="tab" id="tu-3" aria-selected="false" tabindex="-1" aria-controls="tp-3">{{ __('History') }}</button>
      </div>
      <div id="tp-1" role="tabpanel" aria-labelledby="tu-1" class="pt-4 text-muted-foreground">{{ __('Order #KK-2841 summary: paid by bank transfer, shipped by standard courier.') }}</div>
      <div id="tp-2" role="tabpanel" aria-labelledby="tu-2" class="pt-4 text-muted-foreground" hidden>{{ __('Four items: Arabika Gayo 1 kg × 2, Toraja Sapan 500 g, French press 600 ml.') }}</div>
      <div id="tp-3" role="tabpanel" aria-labelledby="tu-3" class="pt-4 text-muted-foreground" hidden>{{ __('Created 28 Sep, paid 28 Sep, packed 28 Sep, shipped 29 Sep.') }}</div>
    </div>
  </x-ui.card>

  <x-ui.card title="{{ __('Pill tabs and soft tabs') }}" description="{{ __('Two more styles of the same component.') }}" class="min-w-0">
    <div data-tabs>
      <div class="tabs-pill" role="tablist" aria-label="{{ __('Period') }}">
        <button type="button" role="tab" class="tab" id="tq-1" aria-selected="true" aria-controls="tr-1"><x-ui.icon name="chart" />{{ __('Charts') }}</button>
        <button type="button" role="tab" class="tab" id="tq-2" aria-selected="false" tabindex="-1" aria-controls="tr-2"><x-ui.icon name="table" />{{ __('Tables') }}</button>
      </div>
      <div id="tr-1" role="tabpanel" aria-labelledby="tq-1" class="pt-4 text-muted-foreground">{{ __('Chart panel.') }}</div>
      <div id="tr-2" role="tabpanel" aria-labelledby="tq-2" class="pt-4 text-muted-foreground" hidden>{{ __('Table panel.') }}</div>
    </div>
    <div class="mt-6" data-tabs>
      <div class="tabs-soft" role="tablist" aria-label="{{ __('Filter') }}">
        <button type="button" role="tab" class="tab" aria-selected="true">{{ __('All') }}</button>
        <button type="button" role="tab" class="tab" aria-selected="false" tabindex="-1">{{ __('Active') }}</button>
        <button type="button" role="tab" class="tab" aria-selected="false" tabindex="-1">{{ __('Archived') }}</button>
      </div>
    </div>
  </x-ui.card>

  <x-ui.card title="{{ __('Accordion') }}" description="{{ __('Uses the built-in details element, no JavaScript.') }}" class="min-w-0">
    <div class="accordion">
      <details open><summary>{{ __('How do I add a product?') }}<x-ui.icon name="chevron-down" class="h-4 w-4 text-muted-foreground transition-transform" /></summary><div>{{ __('Open the Products menu, click Add product, then fill in the name, price and stock.') }}</div></details>
      <details><summary>{{ __('Which payment methods are supported?') }}<x-ui.icon name="chevron-down" class="h-4 w-4 text-muted-foreground transition-transform" /></summary><div>{{ __('Bank transfer, e-wallets, credit cards and cash on delivery.') }}</div></details>
      <details><summary>{{ __('Can I export data?') }}<x-ui.icon name="chevron-down" class="h-4 w-4 text-muted-foreground transition-transform" /></summary><div>{{ __('Yes. Every table has an export button for CSV and Excel.') }}</div></details>
    </div>
  </x-ui.card>

  <x-ui.card title="{{ __('Dropdown menus') }}" description="{{ __('Plain menus, with icons, separators and a user menu.') }}" class="min-w-0">
    <div class="flex flex-wrap gap-3">
      <div data-dropdown>
        <button type="button" class="btn btn-outline" data-dropdown-toggle aria-expanded="false">{{ __('Options') }}<x-ui.icon name="chevron-down" /></button>
        <div class="menu" hidden>
          <p class="menu-label">{{ __('Actions') }}</p>
          <button type="button" class="menu-item"><x-ui.icon name="edit" />{{ __('Edit') }}</button>
          <button type="button" class="menu-item"><x-ui.icon name="copy" />{{ __('Duplicate') }}</button>
          <button type="button" class="menu-item"><x-ui.icon name="download" />{{ __('Export') }}</button>
          <div class="menu-sep"></div>
          <button type="button" class="menu-item menu-item-danger"><x-ui.icon name="trash" />{{ __('Delete') }}</button>
        </div>
      </div>
      <div data-dropdown>
        <button type="button" class="btn btn-ghost h-auto gap-2 p-1 pr-2" data-dropdown-toggle aria-expanded="false"><span class="avatar avatar-sm">AR</span>Ayu<x-ui.icon name="chevron-down" class="h-4 w-4 text-muted-foreground" /></button>
        <div class="menu w-56" hidden>
          <div class="px-2 py-1.5"><p class="font-medium">Ayu Rahmawati</p><p class="text-xs text-muted-foreground">ayu@kopikenanga.id</p></div>
          <div class="menu-sep"></div>
          <a href="{{ route('admin.settings') }}" class="menu-item"><x-ui.icon name="user" />{{ __('Profile') }}</a>
          <a href="{{ route('admin.settings') }}" class="menu-item"><x-ui.icon name="settings" />{{ __('Settings') }}</a>
          <a href="#" class="menu-item"><x-ui.icon name="card" />{{ __('Billing') }}</a>
          <div class="menu-sep"></div>
          <a href="{{ route('login') }}" class="menu-item"><x-ui.icon name="logout" />{{ __('Sign out') }}</a>
        </div>
      </div>
    </div>
  </x-ui.card>
</section>

<section class="grid gap-4 lg:grid-cols-2">
  <x-ui.card title="{{ __('Page navigation') }}" description="{{ __('Three styles: numbered, compact and bordered.') }}" class="min-w-0">
    <div class="space-y-5">
      <div class="flex flex-wrap items-center gap-1">
        <button type="button" class="page-btn" aria-label="{{ __('Previous') }}"><x-ui.icon name="chevron-left" /></button>
        <button type="button" class="page-btn">1</button><button type="button" class="page-btn" aria-current="page">2</button><button type="button" class="page-btn">3</button>
        <span class="px-1 text-muted-foreground">…</span><button type="button" class="page-btn">12</button>
        <button type="button" class="page-btn" aria-label="{{ __('Next') }}"><x-ui.icon name="chevron-right" /></button>
      </div>
      <div class="flex items-center gap-3"><button type="button" class="btn btn-outline btn-sm"><x-ui.icon name="chevron-left" />{{ __('Previous') }}</button><span class="text-sm text-muted-foreground">{{ __('Page 2 of 12') }}</span><button type="button" class="btn btn-outline btn-sm">{{ __('Next') }}<x-ui.icon name="chevron-right" /></button></div>
      <div class="flex items-center gap-1">
        <button type="button" class="page-btn page-btn-outline" aria-label="{{ __('Previous') }}"><x-ui.icon name="chevron-left" /></button>
        <button type="button" class="page-btn page-btn-outline">1</button><button type="button" class="page-btn page-btn-outline" aria-current="page">2</button><button type="button" class="page-btn page-btn-outline">3</button>
        <button type="button" class="page-btn page-btn-outline" aria-label="{{ __('Next') }}"><x-ui.icon name="chevron-right" /></button>
      </div>
    </div>
  </x-ui.card>

  <x-ui.card title="{{ __('Breadcrumb') }}" description="{{ __('Navigation trail.') }}" class="min-w-0">
    <div class="space-y-4 text-sm">
      <nav aria-label="{{ __('Breadcrumb') }}" class="flex flex-wrap items-center gap-1.5"><a href="#" class="text-muted-foreground hover:text-foreground">{{ __('Home') }}</a><x-ui.icon name="chevron-right" class="h-3.5 w-3.5 text-muted-foreground" /><a href="#" class="text-muted-foreground hover:text-foreground">{{ __('Orders') }}</a><x-ui.icon name="chevron-right" class="h-3.5 w-3.5 text-muted-foreground" /><span class="font-medium">#KK-2841</span></nav>
      <nav aria-label="{{ __('Breadcrumb') }}" class="flex flex-wrap items-center gap-2 text-muted-foreground"><a href="#" class="inline-flex items-center gap-1 hover:text-foreground"><x-ui.icon name="home" /></a><span>/</span><a href="#" class="hover:text-foreground">{{ __('Products') }}</a><span>/</span><span class="text-foreground">{{ __('Coffee beans') }}</span></nav>
      <nav aria-label="{{ __('Breadcrumb') }}" class="inline-flex items-center gap-1 rounded-lg border bg-card p-1"><a href="#" class="rounded-md px-2 py-1 text-muted-foreground hover:bg-accent">{{ __('Home') }}</a><a href="#" class="rounded-md px-2 py-1 text-muted-foreground hover:bg-accent">{{ __('Reports') }}</a><span class="rounded-md bg-soft px-2 py-1 font-medium text-soft-foreground">{{ __('Monthly') }}</span></nav>
    </div>
  </x-ui.card>

  <x-ui.card title="Stepper" description="{{ __('Process steps marked done, in progress and to do.') }}" class="min-w-0">
    <ol class="flex items-center">
      <li class="flex flex-1 items-center gap-3"><span class="step step-done"><x-ui.icon name="check" /></span><span class="hidden text-sm font-medium sm:block">{{ __('Cart') }}</span><span class="h-px flex-1 bg-primary"></span></li>
      <li class="flex flex-1 items-center gap-3"><span class="step step-current">2</span><span class="hidden text-sm font-medium sm:block">{{ __('Delivery') }}</span><span class="h-px flex-1 bg-border"></span></li>
      <li class="flex items-center gap-3"><span class="step step-todo">3</span><span class="hidden text-sm text-muted-foreground sm:block">{{ __('Payment') }}</span></li>
    </ol>
  </x-ui.card>

  <x-ui.card title="{{ __('Vertical menu list') }}" description="{{ __('A navigation pattern for settings pages.') }}" class="min-w-0">
    <nav class="w-full max-w-xs space-y-0.5" aria-label="{{ __('Settings') }}">
      <a href="#" class="nav-link" aria-current="page"><x-ui.icon name="user" />{{ __('Profile') }}</a>
      <a href="#" class="nav-link"><x-ui.icon name="lock" />{{ __('Security') }}</a>
      <a href="#" class="nav-link"><x-ui.icon name="bell" />{{ __('Notifications') }}<span class="ml-auto rounded-full bg-soft px-1.5 py-0.5 text-xs font-medium text-soft-foreground">3</span></a>
      <a href="#" class="nav-link"><x-ui.icon name="card" />{{ __('Billing') }}</a>
    </nav>
  </x-ui.card>
</section>
</x-layouts.admin>
