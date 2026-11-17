@php
    $title = __('Edit :label', ['label' => $module->label()]);
    $selectedIcon = old('icon', $module->icon);
    $selectedRoles = collect(old('allowed_roles', $module->allowed_roles->map(fn ($role) => $role->value)->all()));
@endphp
<x-layouts.admin :title="$title" group="{{ __('Modules') }}">
<div>
    <a href="{{ route('administrations.modules.index') }}" class="mb-3 inline-flex items-center gap-1 text-sm text-muted-foreground hover:text-foreground">
        <x-ui.icon name="arrow-left" class="h-4 w-4" />{{ __('Back to Modules') }}
    </a>
    <x-ui.page-header :title="$title" description="{{ __($module->key->description()) }}" />
</div>

<form method="POST" action="{{ route('administrations.modules.update', $module->key) }}" class="space-y-4">
    @csrf
    @method('PUT')

    <section class="card">
        <div class="card-header">
            <h2 class="card-title">{{ __('Availability') }}</h2>
        </div>
        <div class="card-content space-y-5">
            <div>
                <x-ui.switch name="is_enabled" :checked="$module->is_enabled" label="{{ __('Module is on') }}" hint="{{ __('When off, the module and its settings disappear from the sidebar and its pages can’t be opened. Saved data is kept.') }}" />
                @error('is_enabled')<p class="field-error">{{ $message }}</p>@enderror
            </div>

            <fieldset class="space-y-2">
                <legend class="text-sm font-medium">{{ __('Who can use it') }}</legend>
                <p class="field-help">{{ __('Leave all unticked to open it to everyone. Super Admin can always use it.') }}</p>
                <div class="grid gap-3 sm:grid-cols-3 lg:grid-cols-5">
                    @foreach ($roles as $role)
                        <label class="flex cursor-pointer items-center gap-3 rounded-md border p-3 has-[:checked]:border-primary has-[:checked]:bg-soft">
                            <input type="checkbox" name="allowed_roles[]" value="{{ $role->value }}" class="mt-0.5" @checked($selectedRoles->contains($role->value))>
                            <span class="text-sm font-medium">{{ __($role->value) }}</span>
                        </label>
                    @endforeach
                </div>
                @error('allowed_roles')<p class="field-error">{{ $message }}</p>@enderror
                @error('allowed_roles.*')<p class="field-error">{{ $message }}</p>@enderror
            </fieldset>
        </div>
    </section>

    <section class="card">
        <div class="card-header">
            <h2 class="card-title">{{ __('In the sidebar') }}</h2>
        </div>
        <div class="card-content space-y-5">
            <div class="grid gap-5 md:grid-cols-3">
                <x-ui.field label="{{ __('Name (English)') }}" name="name_en" required>
                    <x-ui.input name="name_en" :value="$module->name_en" maxlength="{{ \App\Models\Module::MAX_NAME_LENGTH }}" required />
                </x-ui.field>
                <x-ui.field label="{{ __('Name (Bahasa Melayu)') }}" name="name_ms" help="{{ __('Optional. The English name is used when blank.') }}">
                    <x-ui.input name="name_ms" :value="$module->name_ms" maxlength="{{ \App\Models\Module::MAX_NAME_LENGTH }}" />
                </x-ui.field>
                <x-ui.field label="{{ __('Display order') }}" name="sort_order" required help="{{ __('Lower numbers appear first.') }}">
                    <x-ui.input name="sort_order" type="number" min="1" max="99" step="1" :value="$module->sort_order" class="tabular-nums" required />
                </x-ui.field>
            </div>

            <fieldset class="space-y-2">
                <legend class="text-sm font-medium">{{ __('Icon') }} <span class="text-destructive">*</span></legend>
                <div class="grid grid-cols-5 gap-2 sm:grid-cols-9">
                    @foreach (\App\Models\Module::ICONS as $icon)
                        <label class="flex cursor-pointer items-center justify-center rounded-md border p-3 has-[:checked]:border-primary has-[:checked]:bg-soft has-[:focus-visible]:ring-2 has-[:focus-visible]:ring-ring" data-tip="{{ $icon }}">
                            <input type="radio" name="icon" value="{{ $icon }}" class="sr-only" @checked($selectedIcon === $icon) aria-label="{{ $icon }}">
                            <x-ui.icon :name="$icon" class="h-5 w-5" />
                        </label>
                    @endforeach
                </div>
                @error('icon')<p class="field-error">{{ $message }}</p>@enderror
            </fieldset>
        </div>
    </section>

    <div class="flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-muted-foreground">
            @if ($lastChange)
                {{ __('Last changed by :name on :date.', ['name' => $lastChange->causer?->name ?? __('System'), 'date' => $lastChange->created_at->timezone($timezone)->translatedFormat('j M Y, g:i A')]) }}
                <a href="{{ route('administrations.audit-trail.show', $lastChange) }}" class="font-medium text-foreground underline">{{ __('View in Audit Trail') }}</a>
            @endif
        </p>
        <button type="submit" class="btn btn-primary">{{ __('Save changes') }}</button>
    </div>
</form>
</x-layouts.admin>
