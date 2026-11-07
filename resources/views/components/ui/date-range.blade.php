@props(['id', 'label' => null, 'fromName' => null, 'toName' => null, 'from' => null, 'to' => null])
@php $label ??= __('Date range'); @endphp

<fieldset data-date-range {{ $attributes->class('space-y-3') }}>
    <legend class="label">{{ $label }}</legend>
    <div class="flex flex-wrap gap-2">
        <button type="button" class="btn btn-outline btn-sm" data-range-days="7">{{ __('7 days') }}</button>
        <button type="button" class="btn btn-outline btn-sm" data-range-days="30">{{ __('30 days') }}</button>
        <button type="button" class="btn btn-ghost btn-sm" data-range-clear>{{ __('Delete') }}</button>
    </div>
    <div class="grid gap-3 sm:grid-cols-2">
        <div class="space-y-2"><label class="label" for="{{ $id }}-from">{{ __('From') }}</label><input id="{{ $id }}-from" @if($fromName) name="{{ $fromName }}" @endif type="text" class="input" value="{{ $fromName ? old($fromName, $from) : $from }}" placeholder="{{ __('Choose a start date') }}" autocomplete="off" data-date-picker data-range-from></div>
        <div class="space-y-2"><label class="label" for="{{ $id }}-to">{{ __('To') }}</label><input id="{{ $id }}-to" @if($toName) name="{{ $toName }}" @endif type="text" class="input" value="{{ $toName ? old($toName, $to) : $to }}" placeholder="{{ __('Choose an end date') }}" autocomplete="off" data-date-picker data-range-to></div>
    </div>
    <p class="field-help" role="status" aria-live="polite" data-range-status>{{ __('Pick a date or use a preset.') }}</p>
</fieldset>
