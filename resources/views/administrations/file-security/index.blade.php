@php
    $selected = collect(old('allowed_types', $setting->allowed_types->map->value->all()));
@endphp

<x-layouts.admin title="{{ __('File Security') }}" group="{{ __('Administrations') }}">
<x-ui.page-header title="{{ __('File Security') }}" description="{{ __('Set which files people can upload and how large they can be. Every upload is checked against these rules.') }}" />

@if (session('status'))
    <x-ui.alert variant="success" role="status">{{ session('status') }}</x-ui.alert>
@endif

<form method="POST" action="{{ route('administrations.file-security.update') }}" class="space-y-4">
    @csrf
    @method('PUT')

    <section class="card">
        <div class="card-header">
            <h2 class="card-title">{{ __('Allowed file types') }}</h2>
            <p class="card-desc">{{ __('Uploads with any other extension are rejected.') }}</p>
        </div>
        <div class="card-content space-y-3">
            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($types as $type)
                    <label class="flex cursor-pointer items-start gap-3 rounded-md border p-3 has-[:checked]:border-primary has-[:checked]:bg-soft">
                        <input type="checkbox" name="allowed_types[]" value="{{ $type->value }}" class="mt-0.5" @checked($selected->contains($type->value))>
                        <span class="min-w-0">
                            <span class="block text-sm font-medium">{{ __($type->label()) }}</span>
                            <span class="mt-0.5 block font-mono text-xs text-muted-foreground">{{ implode(', ', $type->extensions()) }}</span>
                        </span>
                    </label>
                @endforeach
            </div>
            @error('allowed_types')<p class="field-error">{{ $message }}</p>@enderror
            @error('allowed_types.*')<p class="field-error">{{ $message }}</p>@enderror
        </div>
    </section>

    <section class="card">
        <div class="card-header">
            <h2 class="card-title">{{ __('Limits and checks') }}</h2>
        </div>
        <div class="card-content grid gap-5 md:grid-cols-2">
            <x-ui.field label="{{ __('Maximum file size') }}" name="max_file_size_mb" required help="{{ __('In MB. This server accepts up to :max MB.', ['max' => number_format($serverLimitMb)]) }}">
                <x-ui.input name="max_file_size_mb" type="number" min="1" max="{{ $serverLimitMb }}" step="1" :value="$setting->max_file_size_mb" addon="MB" class="tabular-nums" required />
            </x-ui.field>

            <div class="md:pt-6">
                <x-ui.switch name="verify_content" :checked="$setting->verify_content" label="{{ __('Check file content') }}" hint="{{ __('Reject files whose content doesn’t match their extension, e.g. a program renamed to .pdf.') }}" />
                @error('verify_content')<p class="field-error">{{ $message }}</p>@enderror
            </div>
        </div>
    </section>

    <section class="card">
        <div class="card-header">
            <h2 class="card-title flex items-center gap-2"><x-ui.icon name="lock" class="h-4 w-4 text-muted-foreground" />{{ __('Always blocked') }}</h2>
            <p class="card-desc">{{ __('Files with these extensions anywhere in their name are always rejected, e.g. invoice.php.pdf. This list can’t be changed here.') }}</p>
        </div>
        <div class="card-content flex flex-wrap gap-1.5">
            @foreach ($blockedExtensions as $extension)
                <span class="kbd">.{{ ltrim($extension, '.') }}</span>
            @endforeach
        </div>
    </section>

    <div class="flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-muted-foreground">
            @if ($lastChange)
                {{ __('Last changed by :name on :date.', ['name' => $lastChange->causer?->name ?? __('System'), 'date' => $lastChange->created_at->timezone($timezone)->translatedFormat('j M Y, g:i A')]) }}
                <a href="{{ route('administrations.audit-trail.show', $lastChange) }}" class="font-medium text-foreground underline">{{ __('View in Audit Trail') }}</a>
            @elseif (! $setting->exists)
                {{ __('Using the default settings until you save.') }}
            @endif
        </p>
        <button type="submit" class="btn btn-primary">{{ __('Save changes') }}</button>
    </div>
</form>
</x-layouts.admin>
