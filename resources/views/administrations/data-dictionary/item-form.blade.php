@php
    $editing = $item->exists;
    $title = $editing ? __('Edit :label', ['label' => $item->label_en]) : __('Add item');
    $toneNames = ['primary' => __('Accent'), 'success' => __('Green'), 'info' => __('Blue'), 'warning' => __('Amber'), 'danger' => __('Red'), 'neutral' => __('Grey')];
    $selectedTone = old('tone', $item->tone);
@endphp

<x-layouts.admin title="{{ $title }}" group="{{ __($category->label()) }}">
<div>
    <a href="{{ route('administrations.data-dictionary.show', $category) }}" class="mb-3 inline-flex items-center gap-1 text-sm text-muted-foreground hover:text-foreground">
        <x-ui.icon name="arrow-left" class="h-4 w-4" />{{ __('Back to :category', ['category' => __($category->label())]) }}
    </a>
    <x-ui.page-header :title="$title" description="{{ $editing ? __('Changes apply everywhere this item is offered.') : __('New items appear in :category dropdowns once saved.', ['category' => __($category->label())]) }}" />
</div>

<form method="POST" action="{{ $editing ? route('administrations.data-dictionary.items.update', [$category, $item]) : route('administrations.data-dictionary.items.store', $category) }}" class="space-y-4">
    @csrf
    @if ($editing) @method('PUT') @endif

    <section class="card">
        <div class="card-header">
            <h2 class="card-title">{{ __('Item details') }}</h2>
            <p class="card-desc">{{ __('Fields marked * are required.') }}</p>
        </div>
        <div class="card-content grid gap-5 md:grid-cols-2">
            @if ($editing)
                <div class="space-y-1.5">
                    <p class="label">{{ __('Code') }}</p>
                    <p class="flex h-9 items-center gap-2 rounded-md border bg-muted/50 px-3 font-mono text-sm">
                        <x-ui.icon name="lock" class="h-3.5 w-3.5 text-muted-foreground" />{{ $item->code }}
                    </p>
                    <p class="field-help">{{ __('The code can’t change because records store it.') }}</p>
                </div>
            @else
                <x-ui.field label="{{ __('Code') }}" name="code" required help="{{ __('Lowercase letters, numbers and hyphens, e.g. negeri-sembilan. It can’t be changed later.') }}">
                    <x-ui.input name="code" class="font-mono" maxlength="50" autocomplete="off" spellcheck="false" placeholder="e.g. negeri-sembilan" required />
                </x-ui.field>
            @endif

            <x-ui.field label="{{ __('Display order') }}" name="sort_order" required help="{{ __('Lower numbers appear first.') }}">
                <x-ui.input name="sort_order" type="number" min="0" max="65535" step="1" :value="$item->sort_order" class="tabular-nums" required />
            </x-ui.field>

            <x-ui.field label="{{ __('Label (English)') }}" name="label_en" required>
                <x-ui.input name="label_en" :value="$item->label_en" maxlength="255" required />
            </x-ui.field>

            <x-ui.field label="{{ __('Label (Malay)') }}" name="label_ms" help="{{ __('Leave empty to show the English label in Malay too.') }}">
                <x-ui.input name="label_ms" :value="$item->label_ms" maxlength="255" />
            </x-ui.field>

            <fieldset class="space-y-2 md:col-span-2">
                <legend class="label">{{ __('Badge colour') }}</legend>
                <p class="field-help">{{ __('Optional. Used where the item is shown as a badge, e.g. a status.') }}</p>
                <div class="flex flex-wrap gap-2 pt-1">
                    <label class="filter-chip cursor-pointer has-[:checked]:border-primary has-[:checked]:bg-soft has-[:checked]:text-soft-foreground">
                        <input type="radio" name="tone" value="" class="sr-only" @checked(blank($selectedTone))>{{ __('None') }}
                    </label>
                    @foreach (\App\Models\LookupItem::TONES as $tone)
                        <label class="filter-chip cursor-pointer gap-2 has-[:checked]:border-primary has-[:checked]:bg-soft has-[:checked]:text-soft-foreground">
                            <input type="radio" name="tone" value="{{ $tone }}" class="sr-only" @checked($selectedTone === $tone)>
                            <x-ui.badge :variant="$tone">{{ $toneNames[$tone] }}</x-ui.badge>
                        </label>
                    @endforeach
                </div>
                @error('tone')<p class="field-error">{{ $message }}</p>@enderror
            </fieldset>

            <div class="md:col-span-2">
                <x-ui.switch name="is_active" :checked="$item->is_active" label="{{ __('Active') }}" hint="{{ __('Inactive items are hidden from dropdowns but kept for existing records.') }}" />
            </div>
        </div>
    </section>

    <div class="flex flex-wrap justify-end gap-2">
        <a href="{{ route('administrations.data-dictionary.show', $category) }}" class="btn btn-outline">{{ __('Cancel') }}</a>
        <button type="submit" class="btn btn-primary">{{ $editing ? __('Save changes') : __('Add item') }}</button>
    </div>
</form>
</x-layouts.admin>
