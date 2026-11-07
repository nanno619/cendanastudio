@php
    $orderSummary = __('Total orders: :count', ['count' => 120]);
@endphp
<x-layouts.blank title="{{ __('Dashboard') }}" group="{{ __('Platform') }}">

    <div class="max-w-xl mx-auto px-6 py-8 space-y-8">


        <h1 class="text-2xl font-bold">
            Component Testing
        </h1>

        <hr>
        <x-ui.card class="min-w-0">
            <x-slot:header>
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <h2 class="card-title">{{ __('Orders') }}</h2>
                        <p class="card-desc">{{ __('Current period') }}</p>
                    </div>
                    <a href="{{ route('admin.dashboard') }}" class="btn btn-outline btn-sm">{{ __('View all') }}</a>
                </div>
            </x-slot:header>
            <p class="text-muted-foreground">{{ $orderSummary }}</p>
        </x-ui.card >
    </div>

    </div>

</x-layouts.blank>
