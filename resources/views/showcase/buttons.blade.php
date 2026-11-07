<x-layouts.admin title="{{ __('Buttons and badges') }}" group="{{ __('Components') }}">
<x-ui.page-header title="{{ __('Buttons and badges') }}" description="{{ __('Buttons, button groups, badges, avatars, chips and tooltips.') }}" />

<x-ui.card title="{{ __('Button variants') }}" description="{{ __('Each variant has its own class, e.g. btn-primary.') }}">
  <div class="flex flex-wrap items-center gap-3">
    <button type="button" class="btn btn-primary">Primary</button>
    <button type="button" class="btn btn-secondary">Secondary</button>
    <button type="button" class="btn btn-outline">Outline</button>
    <button type="button" class="btn btn-ghost">Ghost</button>
    <button type="button" class="btn btn-soft">Soft</button>
    <button type="button" class="btn btn-destructive">Destructive</button>
    <button type="button" class="btn btn-success">Success</button>
    <button type="button" class="btn btn-warning">Warning</button>
    <button type="button" class="btn btn-info">Info</button>
    <button type="button" class="btn btn-link">Link</button>
  </div>
</x-ui.card>

<section class="grid gap-4 lg:grid-cols-2">
  <x-ui.card title="{{ __('Sizes') }}" description="{{ __('btn-sm, default and btn-lg.') }}">
    <div class="flex flex-wrap items-center gap-3">
      <button type="button" class="btn btn-primary btn-sm">{{ __('Small') }}</button>
      <button type="button" class="btn btn-primary">Default</button>
      <button type="button" class="btn btn-primary btn-lg">{{ __('Large') }}</button>
    </div>
    <div class="mt-4 flex flex-wrap items-center gap-3">
      <button type="button" class="btn btn-outline btn-icon btn-sm" aria-label="{{ __('Add') }}"><x-ui.icon name="plus" /></button>
      <button type="button" class="btn btn-outline btn-icon" aria-label="{{ __('Add') }}"><x-ui.icon name="plus" /></button>
      <button type="button" class="btn btn-outline btn-icon btn-lg" aria-label="{{ __('Add') }}"><x-ui.icon name="plus" class="h-5 w-5" /></button>
      <button type="button" class="btn btn-primary btn-icon rounded-full" aria-label="{{ __('Edit') }}"><x-ui.icon name="edit" /></button>
    </div>
  </x-ui.card>

  <x-ui.card title="{{ __('With icons') }}" description="{{ __('Icon on the left or right of the text.') }}">
    <div class="flex flex-wrap items-center gap-3">
      <button type="button" class="btn btn-primary"><x-ui.icon name="plus" />{{ __('Add product') }}</button>
      <button type="button" class="btn btn-outline"><x-ui.icon name="download" />{{ __('Download') }}</button>
      <button type="button" class="btn btn-secondary">{{ __('Continue') }}<x-ui.icon name="arrow-right" /></button>
      <button type="button" class="btn btn-destructive"><x-ui.icon name="trash" />{{ __('Delete') }}</button>
    </div>
  </x-ui.card>

  <x-ui.card title="{{ __('Status') }}" description="{{ __('Loading (click to try), disabled and full width.') }}">
    <div class="flex flex-wrap items-center gap-3">
      <button type="button" class="btn btn-primary" data-load><x-ui.icon name="refresh" />{{ __('Reload') }}</button>
      <button type="button" class="btn btn-primary" disabled><span class="spinner"></span>{{ __('Saving') }}</button>
      <button type="button" class="btn btn-outline" disabled>{{ __('Disabled') }}</button>
    </div>
    <button type="button" class="btn btn-primary mt-4 w-full">{{ __('Full width') }}</button>
  </x-ui.card>

  <x-ui.card title="{{ __('Button groups') }}" description="{{ __('btn-group, split buttons and buttons with a menu.') }}">
    <div class="flex flex-wrap items-center gap-4">
      <div class="btn-group">
        <button type="button" class="btn btn-outline">{{ __('Day') }}</button>
        <button type="button" class="btn btn-outline">{{ __('Week') }}</button>
        <button type="button" class="btn btn-outline">{{ __('Month') }}</button>
      </div>
      <div data-dropdown>
        <div class="btn-group">
          <button type="button" class="btn btn-primary">{{ __('Save') }}</button>
          <button type="button" class="btn btn-primary btn-icon border-l border-primary-foreground/25" data-dropdown-toggle aria-expanded="false" aria-label="{{ __('More options') }}"><x-ui.icon name="chevron-down" /></button>
        </div>
        <div class="menu" hidden>
          <button type="button" class="menu-item">{{ __('Save as draft') }}</button>
          <button type="button" class="menu-item">{{ __('Save and close') }}</button>
        </div>
      </div>
    </div>
  </x-ui.card>
</section>

<section class="grid gap-4 lg:grid-cols-2">
  <x-ui.card title="{{ __('Badges') }}" description="{{ __('Soft, solid, outline, dot and pill.') }}">
    <div class="space-y-4">
      <div class="flex flex-wrap items-center gap-2">
        <span class="badge badge-primary">Primary</span><span class="badge badge-soft">Soft</span><span class="badge badge-neutral">Neutral</span>
        <span class="badge badge-success">Success</span><span class="badge badge-warning">Warning</span><span class="badge badge-danger">Danger</span><span class="badge badge-info">Info</span>
      </div>
      <div class="flex flex-wrap items-center gap-2">
        <span class="badge badge-solid-success">Solid</span><span class="badge badge-solid-warning">Solid</span><span class="badge badge-solid-danger">Solid</span><span class="badge badge-solid-info">Solid</span><span class="badge badge-outline">Outline</span>
      </div>
      <div class="flex flex-wrap items-center gap-2">
        <span class="badge badge-success badge-dot">{{ __('Active') }}</span><span class="badge badge-warning badge-dot">{{ __('Pending') }}</span><span class="badge badge-danger badge-dot">{{ __('Failed') }}</span>
        <span class="badge badge-soft badge-pill">{{ __('Pill') }}</span><span class="badge badge-primary badge-pill">12</span>
      </div>
      <div class="flex flex-wrap items-center gap-3">
        <button type="button" class="btn btn-outline">{{ __('Message') }} <span class="badge badge-primary badge-pill">8</span></button>
        <button type="button" class="btn btn-secondary">{{ __('Notifications') }} <span class="badge badge-solid-danger badge-pill">3</span></button>
      </div>
    </div>
  </x-ui.card>

  <x-ui.card title="{{ __('Avatars') }}" description="{{ __('Sizes, colours, status and stacked groups.') }}">
    <div class="space-y-4">
      <div class="flex flex-wrap items-center gap-3">
        <span class="avatar avatar-sm">AR</span><span class="avatar">AR</span><span class="avatar avatar-lg">AR</span><span class="avatar avatar-xl">AR</span>
        <span class="avatar avatar-square avatar-lg avatar-info">KK</span>
      </div>
      <div class="flex flex-wrap items-center gap-3">
        <span class="avatar avatar-lg avatar-success">DA<span class="avatar-dot"></span></span>
        <span class="avatar avatar-lg avatar-warning">BP</span><span class="avatar avatar-lg avatar-danger">MK</span><span class="avatar avatar-lg avatar-neutral"><x-ui.icon name="user" /></span>
      </div>
      <div class="flex items-center gap-3">
        <div class="avatar-group"><span class="avatar avatar-soft">DA</span><span class="avatar avatar-info">BP</span><span class="avatar avatar-success">MK</span><span class="avatar avatar-warning">RH</span><span class="avatar avatar-neutral">+4</span></div>
        <span class="text-sm text-muted-foreground">{{ __('8 team members') }}</span>
      </div>
    </div>
  </x-ui.card>
</section>

<section class="grid gap-4 lg:grid-cols-2">
  <x-ui.card title="{{ __('Chips and tags') }}" description="{{ __('Chips with a remove button (click to remove).') }}">
    <div class="flex flex-wrap gap-2" data-dismissible-group>
      <span class="chip" data-dismissible>{{ __('Arabica') }}<button type="button" data-dismiss class="rounded-full p-0.5 text-muted-foreground hover:bg-muted hover:text-foreground" aria-label="{{ __('Remove Arabica') }}"><x-ui.icon name="x" class="h-3 w-3" /></button></span>
      <span class="chip" data-dismissible>Robusta<button type="button" data-dismiss class="rounded-full p-0.5 text-muted-foreground hover:bg-muted hover:text-foreground" aria-label="{{ __('Remove Robusta') }}"><x-ui.icon name="x" class="h-3 w-3" /></button></span>
      <span class="chip" data-dismissible>Single origin<button type="button" data-dismiss class="rounded-full p-0.5 text-muted-foreground hover:bg-muted hover:text-foreground" aria-label="{{ __('Remove Single origin') }}"><x-ui.icon name="x" class="h-3 w-3" /></button></span>
      <span class="chip" data-dismissible>Medium roast<button type="button" data-dismiss class="rounded-full p-0.5 text-muted-foreground hover:bg-muted hover:text-foreground" aria-label="{{ __('Remove Medium roast') }}"><x-ui.icon name="x" class="h-3 w-3" /></button></span>
    </div>
    <p class="mt-4 text-sm text-muted-foreground">{{ __('Keyboard shortcuts:') }} <span class="kbd">Ctrl</span> <span class="kbd">K</span> {{ __('to search,') }} <span class="kbd">Esc</span> {{ __('to close.') }}</p>
  </x-ui.card>

  <x-ui.card title="Tooltip" description="{{ __('No JavaScript, just a data-tip attribute.') }}">
    <div class="flex flex-wrap items-center gap-3">
      <button type="button" class="btn btn-outline btn-icon" data-tip="{{ __('Edit') }}" aria-label="{{ __('Edit') }}"><x-ui.icon name="edit" /></button>
      <button type="button" class="btn btn-outline btn-icon" data-tip="{{ __('Copy link') }}" aria-label="{{ __('Copy link') }}"><x-ui.icon name="copy" /></button>
      <button type="button" class="btn btn-outline btn-icon" data-tip="{{ __('Download') }}" aria-label="{{ __('Download') }}"><x-ui.icon name="download" /></button>
      <button type="button" class="btn btn-outline" data-tip="{{ __('Tooltip on a text button') }}">{{ __('Hover me') }}</button>
    </div>
  </x-ui.card>
</section>
</x-layouts.admin>
