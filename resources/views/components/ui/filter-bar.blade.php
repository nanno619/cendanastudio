@props(['action', 'searchName' => 'q', 'search' => null, 'placeholder' => null, 'resetUrl' => null])
@php $placeholder ??= __('Search data…'); @endphp

<div {{ $attributes->class('card p-4 sm:p-5') }}>
    <form method="GET" action="{{ $action }}" class="flex flex-wrap items-end gap-3">
        <div class="min-w-[12rem] flex-1 space-y-1.5">
            <label class="label" for="{{ $searchName }}-filter">{{ __('Keyword') }}</label>
            <x-ui.input :id="$searchName.'-filter'" type="search" :name="$searchName" :value="$search ?? request($searchName)" :placeholder="$placeholder" icon="search" />
        </div>
        {{ $slot }}
        <x-ui.button type="submit" size="sm">{{ __('Apply') }}</x-ui.button>
        @if($resetUrl)
            <a href="{{ $resetUrl }}" class="btn btn-outline btn-sm">{{ __('Reset') }}</a>
        @endif
    </form>
    @isset($active)
        <div class="mt-4 flex flex-wrap items-center gap-2 border-t pt-4 text-sm" aria-label="{{ __('Active filters') }}">
            <span class="text-muted-foreground">{{ __('Active filters:') }}</span>{{ $active }}
        </div>
    @endisset
</div>
