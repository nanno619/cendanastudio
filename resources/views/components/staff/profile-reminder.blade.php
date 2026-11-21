{{-- Shows a signed-in staff member how complete their profile is, and what's missing, until the checklist
     (Employee::profileChecklist()) is done. Until then EnsureProfileIsComplete keeps them on My profile. --}}
@props(['showLink' => true])
@php
    $employee = auth()->user()?->employee;
    $checklist = $employee?->profileChecklist() ?? [];
    $percent = $employee?->profileCompletion() ?? 100;
    $items = [
        'phone' => [__('Phone number'), 'f-phone'],
        'address' => [__('Home address (address, postcode, city and state)'), 'f-address_line_1'],
        'emergency_contact' => [__('At least one emergency contact'), 'f-contacts-0-name'],
    ];
    $base = $showLink ? route('my-profile.show') : '';
@endphp

@if ($employee && $percent < 100)
    <section {{ $attributes->class('card border-warning/40') }} aria-labelledby="profile-reminder-title">
        <div class="card-content space-y-4 pt-5">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div class="min-w-0">
                    <h2 id="profile-reminder-title" class="font-semibold">{{ __('Complete your profile to continue') }}</h2>
                    <p class="text-sm text-muted-foreground">{{ __('HR needs these details. Other pages open once your profile is complete.') }}</p>
                </div>
                <span class="text-2xl font-semibold tabular-nums">{{ $percent }}%</span>
            </div>
            <div class="progress" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $percent }}" aria-label="{{ __('Profile completion') }}">
                <div class="progress-bar" style="width: {{ $percent }}%"></div>
            </div>
            <ul class="grid gap-2 text-sm sm:grid-cols-3">
                @foreach ($items as $key => [$label, $anchor])
                    <li class="flex items-start gap-2">
                        @if ($checklist[$key] ?? false)
                            <x-ui.icon name="check-circle" class="mt-0.5 h-4 w-4 shrink-0 text-success" />
                            <span class="text-muted-foreground">{{ $label }} <span class="sr-only">({{ __('Done') }})</span></span>
                        @else
                            <x-ui.icon name="alert-circle" class="mt-0.5 h-4 w-4 shrink-0 text-warning" />
                            <a href="{{ $base }}#{{ $anchor }}" class="font-medium hover:underline">{{ $label }} <span class="sr-only">({{ __('Missing') }})</span></a>
                        @endif
                    </li>
                @endforeach
            </ul>
            @if ($showLink)
                <a href="{{ route('my-profile.show') }}" class="btn btn-primary btn-sm">{{ __('Complete my profile') }}</a>
            @endif
        </div>
    </section>
@endif
