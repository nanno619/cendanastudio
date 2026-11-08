@props(['item'])
@php
    $hasRoute = ! empty($item['route']);
    $active = $hasRoute && request()->routeIs($item['active'] ?? $item['route']);
@endphp
{{-- Items without a route are placeholders for pages not built yet: shown, but not linked. --}}
<a @if($hasRoute) href="{{ route($item['route']) }}" @else aria-disabled="true" @endif @class(['nav-link', 'active' => $active, 'cursor-default opacity-60' => ! $hasRoute]) @if($active) aria-current="page" @endif>
    @if (!empty($item['icon']))
        <x-ui.icon :name="$item['icon']" class="h-4 w-4 shrink-0" />
    @endif
    <span class="sb-label flex-1 truncate">{{ __($item['label']) }}</span>
    @if (!empty($item['badge']))
        <span class="sb-label rounded-full bg-soft px-1.5 py-0.5 text-xs font-medium tabular-nums text-soft-foreground">{{ __($item['badge']) }}</span>
    @endif
</a>
