<x-layouts.guest title="{{ __('Error 404') }}">
<div class="relative flex min-h-screen flex-col items-center justify-center px-6 text-center">
  <button type="button" data-theme-toggle aria-label="{{ __('Toggle theme') }}" class="btn btn-outline btn-icon absolute right-4 top-4"><svg class="h-4 w-4" aria-hidden="true"><use class="js-theme-icon" href="{{ asset('icons.svg') }}#i-moon"/></svg></button>
  <p class="text-7xl font-semibold tracking-tight text-primary">404</p>
  <h1 class="mt-4 text-2xl font-semibold tracking-tight">{{ __('Page not found') }}</h1>
  <p class="mt-2 max-w-md text-muted-foreground">{{ __('The address may be mistyped, may have moved, or may no longer exist.') }}</p>
  <div class="mt-6 flex gap-3">
    <a href="{{ route('admin.dashboard') }}" class="btn btn-primary"><x-ui.icon name="home" />{{ __('Go to dashboard') }}</a>
    <a href="#" class="btn btn-outline"><x-ui.icon name="help" />{{ __('Contact support') }}</a>
  </div>
</div>
</x-layouts.guest>
