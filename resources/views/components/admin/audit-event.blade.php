@props(['event' => null])
@php
    $tone = ['created' => 'success', 'updated' => 'info', 'deleted' => 'danger', 'restored' => 'warning', 'login' => 'soft', 'logout' => 'neutral', 'failed-login' => 'danger', 'lockout' => 'solid-danger', 'password-reset' => 'warning', 'role-assigned' => 'success', 'role-removed' => 'warning', 'backup-created' => 'success', 'backup-failed' => 'danger', 'backup-downloaded' => 'info'][$event] ?? 'outline';
@endphp
<x-ui.badge :variant="$tone" dot {{ $attributes }}>{{ $event ? __(\Illuminate\Support\Str::headline($event)) : __('Other') }}</x-ui.badge>
