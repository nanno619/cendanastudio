@props(['event' => null])
@php
    $tone = ['created' => 'success', 'updated' => 'info', 'deleted' => 'danger', 'restored' => 'warning', 'login' => 'soft', 'logout' => 'neutral'][$event] ?? 'outline';
@endphp
<x-ui.badge :variant="$tone" dot {{ $attributes }}>{{ $event ? __(\Illuminate\Support\Str::headline($event)) : __('Other') }}</x-ui.badge>
