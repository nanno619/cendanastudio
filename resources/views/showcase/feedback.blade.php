<x-layouts.admin title="{{ __('Feedback') }}" group="{{ __('Components') }}">
<x-ui.page-header title="{{ __('Feedback') }}" description="{{ __('Alerts, toasts, modals, drawers, progress, spinners and skeletons.') }}" />

<x-ui.card title="Alert" description="{{ __('Four semantic tones, with a title, close button and actions.') }}">
  <div class="space-y-3">
    <div class="alert alert-info" role="alert"><x-ui.icon name="info" class="alert-icon mt-0.5 h-4 w-4 shrink-0" /><div><p class="alert-title">{{ __('Update available') }}</p><p class="text-muted-foreground">{{ __('A new version of the dashboard is ready to install.') }}</p></div></div>
    <div class="alert alert-success" role="alert"><x-ui.icon name="check-circle" class="alert-icon mt-0.5 h-4 w-4 shrink-0" /><div><p class="alert-title">{{ __('Order saved') }}</p><p class="text-muted-foreground">{{ __('The customer has received a confirmation email.') }}</p></div></div>
    <div class="alert alert-warning" role="alert"><x-ui.icon name="alert-triangle" class="alert-icon mt-0.5 h-4 w-4 shrink-0" /><div><p class="alert-title">{{ __('Low stock') }}</p><p class="text-muted-foreground">{{ __('Only 18 units of Toraja Sapan 500 g left.') }}</p></div></div>
    <div class="alert alert-danger" role="alert"><x-ui.icon name="alert-circle" class="alert-icon mt-0.5 h-4 w-4 shrink-0" /><div><p class="alert-title">{{ __('Payment failed') }}</p><p class="text-muted-foreground">{{ __('Please check your payment method.') }}</p></div></div>
    <div class="alert alert-info items-center" data-dismissible role="status"><x-ui.icon name="info" class="alert-icon h-4 w-4 shrink-0" /><p class="flex-1">{{ __('Alerts with a close button and actions.') }}</p><a href="#" class="btn btn-link">{{ __('View') }}</a><button type="button" data-dismiss class="rounded p-1 text-muted-foreground hover:text-foreground" aria-label="{{ __('Close') }}"><x-ui.icon name="x" /></button></div>
    <div class="alert alert-solid items-center" role="status"><x-ui.icon name="zap" class="h-4 w-4 shrink-0" /><p>{{ __('Solid alerts: high-contrast highlights for announcements.') }}</p></div>
  </div>
</x-ui.card>

<section class="grid gap-4 lg:grid-cols-2">
  <x-ui.card title="Toast" description="{{ __('Short notifications via App.toast(). Click to try.') }}">
    <div class="flex flex-wrap gap-3">
      <button type="button" class="btn btn-outline" data-toast="success" data-toast-title="Berhasil disimpan" data-toast-desc="{{ __('Your changes have been saved.') }}">Success</button>
      <button type="button" class="btn btn-outline" data-toast="danger" data-toast-title="Terjadi kesalahan" data-toast-desc="{{ __('Try again in a moment.') }}">Danger</button>
      <button type="button" class="btn btn-outline" data-toast="warning" data-toast-title="Perhatian" data-toast-desc="{{ __('Your session will expire in 5 minutes.') }}">Warning</button>
      <button type="button" class="btn btn-outline" data-toast="info" data-toast-title="{{ __('Information') }}" data-toast-desc="{{ __('You have 3 new orders.') }}">Info</button>
      <button type="button" class="btn btn-outline" data-toast="neutral" data-toast-title="Toast sederhana">{{ __('Neutral') }}</button>
    </div>
  </x-ui.card>

  <x-ui.card title="{{ __('Modals and drawers') }}" description="{{ __('Uses the browser\'s built-in dialog element (focus and Esc handled automatically).') }}">
    <div class="flex flex-wrap gap-3">
      <button type="button" class="btn btn-primary" data-modal-open="#modal-form">{{ __('Form modal') }}</button>
      <button type="button" class="btn btn-outline" data-modal-open="#modal-info">{{ __('Info modal') }}</button>
      <button type="button" class="btn btn-destructive" data-modal-open="#modal-confirm">{{ __('Delete confirmation') }}</button>
      <button type="button" class="btn btn-outline" data-modal-open="#drawer-filter"><x-ui.icon name="filter" />{{ __('Filter drawer') }}</button>
    </div>
  </x-ui.card>

  <x-ui.card title="{{ __('Progress') }}" description="{{ __('Progress bars in several tones and sizes.') }}">
    <div class="space-y-4">
      <div><div class="mb-1.5 flex justify-between text-sm"><span>{{ __('Upload') }}</span><span class="text-muted-foreground">68%</span></div><div class="progress"><div class="progress-bar" style="width:68%"></div></div></div>
      <div><div class="mb-1.5 flex justify-between text-sm"><span>{{ __('Stock is healthy') }}</span><span class="text-muted-foreground">86%</span></div><div class="progress"><div class="progress-bar bg-positive" style="width:86%"></div></div></div>
      <div><div class="mb-1.5 flex justify-between text-sm"><span>{{ __('Storage quota') }}</span><span class="text-muted-foreground">54%</span></div><div class="progress"><div class="progress-bar bg-caution" style="width:54%"></div></div></div>
      <div><div class="mb-1.5 flex justify-between text-sm"><span>{{ __('API limit') }}</span><span class="text-muted-foreground">93%</span></div><div class="progress h-3"><div class="progress-bar bg-destructive" style="width:93%"></div></div></div>
      <div class="progress h-1"><div class="progress-bar" style="width:40%"></div></div>
    </div>
  </x-ui.card>

  <x-ui.card title="Spinner" description="{{ __('Loading indicators.') }}">
    <div class="flex flex-wrap items-center gap-6">
      <span class="spinner text-primary"></span>
      <span class="spinner h-6 w-6 text-primary"></span>
      <span class="spinner h-8 w-8 border-[3px] text-info"></span>
      <span class="spinner h-8 w-8 border-[3px] text-success"></span>
      <span class="inline-flex items-center gap-2 text-muted-foreground"><span class="spinner"></span>{{ __('Loading data') }}</span>
    </div>
  </x-ui.card>
</section>

<x-ui.card title="Skeleton" description="{{ __('Placeholders while content loads.') }}">
  <div class="grid gap-6 md:grid-cols-2">
    <div class="flex items-center gap-4">
      <div class="skeleton h-12 w-12 rounded-full"></div>
      <div class="flex-1 space-y-2"><div class="skeleton h-4 w-2/3"></div><div class="skeleton h-4 w-1/2"></div></div>
    </div>
    <div class="space-y-3"><div class="skeleton h-32 w-full"></div><div class="skeleton h-4 w-full"></div><div class="skeleton h-4 w-4/5"></div></div>
  </div>
</x-ui.card>

<x-ui.modal id="modal-form" title="{{ __('Add product') }}" description="{{ __('Fill in the new product details for your catalogue.') }}">
    <div class="space-y-4">
      <div class="space-y-2"><label class="label" for="mf-name">{{ __('Product name') }}</label><input id="mf-name" class="input" placeholder="{{ __('e.g. Arabika Gayo 1 kg') }}"></div>
      <div class="grid grid-cols-2 gap-3">
        <div class="space-y-2"><label class="label" for="mf-price">{{ __('Price') }}</label><div class="input-group"><span class="input-addon">RM</span><input id="mf-price" class="input" placeholder="48.00"></div></div>
        <div class="space-y-2"><label class="label" for="mf-stock">{{ __('Stock') }}</label><input id="mf-stock" type="number" class="input" placeholder="0"></div>
      </div>
    </div>
    <x-slot:footer><button type="button" class="btn btn-outline" data-modal-close>{{ __('Cancel') }}</button><button type="button" class="btn btn-primary" data-modal-close data-toast="success" data-toast-title="{{ __('Product added') }}">{{ __('Save') }}</button></x-slot:footer>
</x-ui.modal>

<x-ui.modal id="modal-info" title="{{ __('Scheduled maintenance') }}" description="{{ __('The system will undergo maintenance on Sunday from 01:00 to 03:00 MYT. The dashboard stays available.') }}" icon="info" tone="info" size="sm">
    <x-slot:footer><button type="button" class="btn btn-primary" data-modal-close>{{ __('Got it') }}</button></x-slot:footer>
</x-ui.modal>

<x-ui.modal id="modal-confirm" title="{{ __('Delete order?') }}" description="{{ __('Order #KK-2837 will be permanently deleted and cannot be recovered.') }}" icon="trash" tone="danger" size="sm">
    <x-slot:footer><button type="button" class="btn btn-outline" data-modal-close>{{ __('Cancel') }}</button><button type="button" class="btn btn-destructive" data-modal-close data-toast="danger" data-toast-title="{{ __('Order deleted') }}">{{ __('Yes, delete') }}</button></x-slot:footer>
</x-ui.modal>

<x-ui.drawer id="drawer-filter" title="{{ __('Filter orders') }}">
    <div class="space-y-5">
      <div class="space-y-2"><label class="label" for="df-status">{{ __('Status') }}</label><select id="df-status" class="select"><option>{{ __('All') }}</option><option>{{ __('Completed') }}</option><option>{{ __('Shipped') }}</option><option>{{ __('Processing') }}</option></select></div>
      <div class="space-y-2"><span class="label">{{ __('Date range') }}</span><div class="grid grid-cols-2 gap-2"><input type="date" class="input" aria-label="{{ __('From') }}"><input type="date" class="input" aria-label="{{ __('To') }}"></div></div>
      <div class="space-y-3"><span class="label">{{ __('Channel') }}</span>
        <label class="flex items-center gap-2"><input type="checkbox" checked> {{ __('Website') }}</label>
        <label class="flex items-center gap-2"><input type="checkbox" checked> Marketplace</label>
        <label class="flex items-center gap-2"><input type="checkbox"> WhatsApp</label>
      </div>
    </div>
    <x-slot:footer><button type="button" class="btn btn-outline flex-1" data-modal-close>{{ __('Reset') }}</button><button type="button" class="btn btn-primary flex-1" data-modal-close>{{ __('Apply') }}</button></x-slot:footer>
</x-ui.drawer>
</x-layouts.admin>
