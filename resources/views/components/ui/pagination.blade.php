@props(['paginator'])
@php
    // Same page window Laravel's own pagination views receive as $elements.
    $window = \Illuminate\Pagination\UrlWindow::make($paginator);
    $elements = array_filter([
        $window['first'],
        is_array($window['slider']) ? '...' : null,
        $window['slider'],
        is_array($window['last']) ? '...' : null,
        $window['last'],
    ]);
@endphp

@if ($paginator->hasPages())
    <div class="flex flex-wrap items-center justify-between gap-3 border-t px-6 py-3">
        <p class="text-xs text-muted-foreground">
            {{ __('Showing') }} {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }}
            {{ __('of') }} {{ $paginator->total() }}
        </p>

        <nav class="flex items-center gap-1" role="navigation" aria-label="{{ __('Page navigation') }}">
            @if ($paginator->onFirstPage())
                <span class="page-btn pointer-events-none opacity-40" aria-hidden="true">
                    <x-ui.icon name="chevron-left" class="h-4 w-4" />
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="page-btn" aria-label="{{ __('Previous page') }}">
                    <x-ui.icon name="chevron-left" class="h-4 w-4" />
                </a>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="page-btn pointer-events-none opacity-40">{{ $element }}</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="page-btn" aria-current="page">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="page-btn">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="page-btn" aria-label="{{ __('Next page') }}">
                    <x-ui.icon name="chevron-right" class="h-4 w-4" />
                </a>
            @else
                <span class="page-btn pointer-events-none opacity-40" aria-hidden="true">
                    <x-ui.icon name="chevron-right" class="h-4 w-4" />
                </span>
            @endif
        </nav>
    </div>
@endif
