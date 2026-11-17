{{-- Reminds a signed-in staff member to fill in their own details until a phone, address and emergency contact are on record. --}}
@props(['showLink' => true])
@php $employee = auth()->user()?->employee; @endphp

@if ($employee?->needsPersonalDetails())
    <x-ui.alert variant="info" {{ $attributes }}>
        {{ __('Please complete your profile: HR needs your phone number, home address and at least one emergency contact.') }}
        @if ($showLink)
            <a href="{{ route('my-profile.show') }}" class="font-medium underline">{{ __('Complete my profile') }}</a>
        @endif
    </x-ui.alert>
@endif
