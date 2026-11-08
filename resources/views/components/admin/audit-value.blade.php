@props(['value' => null])
{{-- One recorded field value in the audit trail; the caller's class tints it as before/after. --}}
@if (is_null($value) || $value === '')
    <span class="text-sm italic text-muted-foreground">{{ __('empty') }}</span>
@elseif (is_bool($value))
    <span {{ $attributes->class('rounded px-1.5 py-0.5 text-sm') }}>{{ $value ? __('Yes') : __('No') }}</span>
@else
    <span {{ $attributes->class('rounded px-1.5 py-0.5 text-sm [box-decoration-break:clone]') }}>{{ is_scalar($value) ? $value : json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</span>
@endif
