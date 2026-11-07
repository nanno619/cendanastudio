<x-layouts.admin title="{{ __('Forms') }}" group="{{ __('Components') }}">
<x-ui.page-header title="{{ __('Forms') }}" description="{{ __('Inputs, selects, checkboxes, radios, switches, sliders, file uploads and validation.') }}" />

<section class="grid gap-4 lg:grid-cols-2">
  <x-ui.card title="{{ __('Text inputs') }}" description="{{ __('Labels, help text, icons and input groups.') }}" class="min-w-0">
    <div class="space-y-4">
      <div class="space-y-2"><label class="label" for="f-name">{{ __('Full name') }}</label><input id="f-name" class="input" placeholder="Ayu Rahmawati"><p class="field-help">{{ __('The name shown on your public profile.') }}</p></div>
      <div class="space-y-2"><label class="label" for="f-email">{{ __('Email') }}</label>
        <div class="relative"><x-ui.icon name="mail" class="pointer-events-none absolute left-2.5 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" /><input id="f-email" type="email" class="input pl-8" placeholder="ayu@kopikenanga.id"></div></div>
      <div class="space-y-2"><label class="label" for="f-price">{{ __('Price') }}</label><div class="input-group"><span class="input-addon">RM</span><input id="f-price" class="input" placeholder="48.00"><span class="input-addon">/ kg</span></div></div>
      <div class="space-y-2"><label class="label" for="f-site">{{ __('Website') }}</label><div class="input-group"><span class="input-addon">https://</span><input id="f-site" class="input" placeholder="kopikenanga.id"></div></div>
      <div class="space-y-2"><label class="label" for="f-msg">{{ __('Notes') }}</label><textarea id="f-msg" class="textarea" placeholder="{{ __('Write a note for the customer') }}"></textarea></div>
    </div>
  </x-ui.card>

  <x-ui.card title="{{ __('Validation states') }}" description="{{ __('Valid, invalid, disabled and read-only.') }}" class="min-w-0">
    <div class="space-y-4">
      <div class="space-y-2"><label class="label" for="v-ok">{{ __('Username') }}</label><input id="v-ok" class="input input-valid" value="kopikenanga"><p class="field-ok">{{ __('Username is available.') }}</p></div>
      <div class="space-y-2"><label class="label" for="v-bad">{{ __('Email') }}</label><input id="v-bad" class="input input-invalid" value="ayu@kopikenanga" aria-invalid="true"><p class="field-error">{{ __('Invalid email format.') }}</p></div>
      <div class="space-y-2"><label class="label" for="v-dis">{{ __('Disabled') }}</label><input id="v-dis" class="input" value="{{ __('Cannot be changed') }}" disabled></div>
      <div class="space-y-2"><label class="label" for="v-ro">{{ __('Read-only') }}</label><input id="v-ro" class="input" value="KK-2841" readonly></div>
    </div>
  </x-ui.card>
</section>

<section class="grid gap-4 lg:grid-cols-2">
  <x-ui.card title="{{ __('Selects, dates and sliders') }}" class="min-w-0">
    <div class="space-y-4">
      <div class="space-y-2"><label class="label" for="s-cat">{{ __('Category') }}</label><select id="s-cat" class="select"><option>{{ __('Coffee beans') }}</option><option>{{ __('Equipment') }}</option><option>{{ __('Bundles') }}</option></select></div>
      <div class="grid grid-cols-2 gap-3">
        <div class="space-y-2"><label class="label" for="s-date">{{ __('Date') }}</label><input id="s-date" type="date" class="input"></div>
        <div class="space-y-2"><label class="label" for="s-time">{{ __('Time') }}</label><input id="s-time" type="time" class="input"></div>
      </div>
      <div class="space-y-2"><div class="flex justify-between"><label class="label" for="s-range">{{ __('Discount') }}</label><span class="text-sm text-muted-foreground">20%</span></div><input id="s-range" type="range" class="w-full" min="0" max="100" value="20"></div>
    </div>
  </x-ui.card>

  <x-ui.card title="{{ __('Checkboxes, radios and switches') }}" class="min-w-0">
    <div class="grid gap-6 sm:grid-cols-2">
      <fieldset class="space-y-3"><legend class="label mb-3">{{ __('Sales channels') }}</legend>
        <label class="flex items-center gap-2"><input type="checkbox" checked> {{ __('Website') }}</label>
        <label class="flex items-center gap-2"><input type="checkbox" checked> Marketplace</label>
        <label class="flex items-center gap-2"><input type="checkbox"> WhatsApp</label>
        <label class="flex items-center gap-2 opacity-50"><input type="checkbox" disabled> {{ __('Physical store') }}</label>
      </fieldset>
      <fieldset class="space-y-3"><legend class="label mb-3">{{ __('Delivery') }}</legend>
        <label class="flex items-center gap-2"><input type="radio" name="ship" checked> {{ __('Standard') }}</label>
        <label class="flex items-center gap-2"><input type="radio" name="ship"> {{ __('Express') }}</label>
        <label class="flex items-center gap-2"><input type="radio" name="ship"> {{ __('In-store pickup') }}</label>
      </fieldset>
    </div>
    <div class="mt-6 space-y-3 border-t pt-5">
      <div class="flex items-center justify-between"><span>{{ __('Accept orders automatically') }}</span><label class="switch"><input type="checkbox" class="peer sr-only" checked aria-label="{{ __('Accept orders automatically') }}"><span class="switch-track"></span></label></div>
      <div class="flex items-center justify-between"><span>{{ __('Send confirmation email') }}</span><label class="switch"><input type="checkbox" class="peer sr-only" aria-label="{{ __('Send confirmation email') }}"><span class="switch-track"></span></label></div>
      <div class="flex items-center justify-between opacity-60"><span>{{ __('Maintenance mode') }}</span><label class="switch"><input type="checkbox" class="peer sr-only" disabled aria-label="{{ __('Maintenance mode') }}"><span class="switch-track"></span></label></div>
    </div>
  </x-ui.card>
</section>

<section class="grid gap-4 lg:grid-cols-2">
  <x-ui.card title="{{ __('Choice cards') }}" description="{{ __('Card-style radios for important choices.') }}" class="min-w-0">
    <div class="grid gap-3 sm:grid-cols-2">
      <label class="cursor-pointer"><input type="radio" name="plan" class="peer sr-only" checked><span class="block rounded-lg border p-4 peer-checked:border-primary peer-checked:bg-soft peer-focus-visible:ring-2 peer-focus-visible:ring-ring/50"><span class="flex items-center gap-2 font-medium"><x-ui.icon name="package" />{{ __('Standard') }}</span><span class="mt-1 block text-sm text-muted-foreground">{{ __('2–3 days · RM 6.00') }}</span></span></label>
      <label class="cursor-pointer"><input type="radio" name="plan" class="peer sr-only"><span class="block rounded-lg border p-4 peer-checked:border-primary peer-checked:bg-soft peer-focus-visible:ring-2 peer-focus-visible:ring-ring/50"><span class="flex items-center gap-2 font-medium"><x-ui.icon name="zap" />{{ __('Express') }}</span><span class="mt-1 block text-sm text-muted-foreground">{{ __('Arrives tomorrow · RM 13.00') }}</span></span></label>
    </div>
  </x-ui.card>

  <x-ui.card title="{{ __('File upload') }}" description="{{ __('A drag-and-drop area.') }}" class="min-w-0">
    <label class="dropzone cursor-pointer">
      <span class="flex h-10 w-10 items-center justify-center rounded-full bg-soft text-soft-foreground"><x-ui.icon name="upload" class="h-5 w-5" /></span>
      <span class="font-medium">{{ __('Click to choose or drag a file here') }}</span>
      <span class="field-help">{{ __('PNG, JPG or PDF. Max 5 MB.') }}</span>
      <input type="file" class="sr-only">
    </label>
  </x-ui.card>
</section>

<div class="card">
  <div class="card-header"><h2 class="card-title">{{ __('Full form example: add a product') }}</h2><p class="card-desc">{{ __('A two-column layout with actions at the bottom.') }}</p></div>
  <div class="card-content">
    <div class="grid gap-4 md:grid-cols-2">
      <div class="space-y-2"><label class="label" for="p-name">{{ __('Product name') }}</label><input id="p-name" class="input" placeholder="Arabika Gayo 1 kg"></div>
      <div class="space-y-2"><label class="label" for="p-sku">SKU</label><input id="p-sku" class="input" placeholder="KP-001"></div>
      <div class="space-y-2"><label class="label" for="p-cat">{{ __('Category') }}</label><select id="p-cat" class="select"><option>{{ __('Coffee beans') }}</option><option>{{ __('Equipment') }}</option><option>{{ __('Bundles') }}</option></select></div>
      <div class="space-y-2"><label class="label" for="p-price">{{ __('Price') }}</label><div class="input-group"><span class="input-addon">RM</span><input id="p-price" class="input" placeholder="48.00"></div></div>
      <div class="space-y-2 md:col-span-2"><label class="label" for="p-desc">{{ __('Description') }}</label><textarea id="p-desc" class="textarea" placeholder="{{ __('Short product description') }}"></textarea></div>
    </div>
  </div>
  <div class="card-footer justify-end"><button type="button" class="btn btn-outline">{{ __('Cancel') }}</button><button type="button" class="btn btn-primary" data-toast="success" data-toast-title="{{ __('Product saved') }}">{{ __('Save product') }}</button></div>
</div>
</x-layouts.admin>
