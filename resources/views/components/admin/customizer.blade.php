<dialog id="customizer" class="drawer" aria-labelledby="customizer-title">
    <div class="flex h-full flex-col">
        <div class="flex items-start justify-between gap-4 border-b p-5">
            <div><h2 id="customizer-title" class="font-semibold">{{ __('Customize appearance') }}</h2><p class="text-sm text-muted-foreground">{{ __('Settings are saved in your browser.') }}</p></div>
            <button type="button" class="btn btn-ghost btn-icon btn-sm" data-modal-close aria-label="{{ __('Close') }}"><x-ui.icon name="x" /></button>
        </div>
        <div class="flex-1 space-y-6 overflow-y-auto p-5">
            <section>
                <h3 class="label mb-3">{{ __('Language') }}</h3>
                <x-locale.switcher />
            </section>
            <section>
                <h3 class="label mb-3">{{ __('Theme') }}</h3>
                <div class="grid grid-cols-3 gap-2">
                    <button type="button" class="pref-btn" data-pref="theme" data-value="light" aria-pressed="false"><x-ui.icon name="sun" />{{ __('Light') }}</button>
                    <button type="button" class="pref-btn" data-pref="theme" data-value="dark" aria-pressed="false"><x-ui.icon name="moon" />{{ __('Dark') }}</button>
                    <button type="button" class="pref-btn" data-pref="theme" data-value="system" aria-pressed="false"><x-ui.icon name="monitor" />{{ __('System') }}</button>
                </div>
            </section>
            <section>
                <h3 class="label mb-3">{{ __('Accent colour') }}</h3>
                <div class="grid grid-cols-6 gap-3">
                    @foreach (config('cendana.accents') as $accent)
                        <button type="button" class="swatch" style="background: {{ $accent['hex'] }}" data-pref="accent" data-value="{{ $accent['id'] }}" aria-label="{{ __($accent['name']) }}" title="{{ __($accent['name']) }}" aria-pressed="false"></button>
                    @endforeach
                </div>
            </section>
            <section>
                <h3 class="label mb-3">{{ __('Corner radius') }}</h3>
                <div class="grid grid-cols-5 gap-2">
                    @foreach (config('cendana.radii') as $radius)
                        <button type="button" class="pref-btn px-1 text-xs" data-pref="radius" data-value="{{ $radius['value'] }}" aria-pressed="false">{{ __($radius['name']) }}</button>
                    @endforeach
                </div>
            </section>
            <section class="hidden lg:block">
                <h3 class="label mb-1">{{ __('Sidebar') }}</h3><p class="field-help mb-3">{{ __('Icon mode expands when you hover over the sidebar.') }}</p>
                <div class="grid grid-cols-2 gap-2">
                    <button type="button" class="pref-btn" data-pref="sbmode" data-value="full" aria-pressed="false">{{ __('Full') }}</button>
                    <button type="button" class="pref-btn" data-pref="sbmode" data-value="mini" aria-pressed="false">{{ __('Icons') }}</button>
                </div>
            </section>
        </div>
        <div class="border-t p-5"><button type="button" class="btn btn-outline w-full" data-pref-reset><x-ui.icon name="refresh" />{{ __('Reset') }}</button></div>
    </div>
</dialog>
