<x-layouts.admin title="{{ __('Leave Policies') }}" group="{{ __('Settings') }}">
<x-ui.page-header title="{{ __('Leave Policies') }}" description="{{ __('Entitlement, payment, documents and carry forward for each leave type. Starter values follow the Employment Act 1955 minimums.') }}" />

@if (session('status'))
    <x-ui.alert variant="success" role="status">{{ session('status') }}</x-ui.alert>
@endif

@php
    $days = fn ($value): string => rtrim(rtrim(number_format((float) $value, 1, '.', ','), '0'), '.');
@endphp

<section class="card" aria-labelledby="policies-heading">
    <div class="flex flex-col gap-1.5 p-5 sm:px-6">
        <h2 id="policies-heading" class="card-title">{{ __('Leave types') }}</h2>
        <p class="card-desc">{{ __('Active leave types from the Data Dictionary.') }}
            <a href="{{ route('administrations.data-dictionary.show', \App\Enums\LookupCategory::LeaveType) }}" class="font-medium text-foreground underline">{{ __('Add or rename leave types') }}</a>
        </p>
    </div>

    @if ($leaveTypes->isEmpty())
        <div class="border-t">
            <x-ui.table-state state="empty" title="{{ __('No leave types') }}" description="{{ __('Add leave types in the Data Dictionary first.') }}" />
        </div>
    @else
        <div class="overflow-x-auto border-t">
            <table class="table min-w-180">
                <thead>
                    <tr>
                        <th scope="col" class="w-14"><span class="sr-only">{{ __('Actions') }}</span></th>
                        <th scope="col">{{ __('Leave type') }}</th>
                        <th scope="col">{{ __('Entitlement') }}</th>
                        <th scope="col">{{ __('Payment') }}</th>
                        <th scope="col">{{ __('Attachment') }}</th>
                        <th scope="col">{{ __('Carry forward') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($leaveTypes as $leaveType)
                        @php $policy = $policies->get($leaveType->code); @endphp
                        <tr>
                            <td data-actions class="whitespace-nowrap">
                                <a href="{{ route('settings.leave.policies.edit', $leaveType->code) }}" class="btn btn-ghost btn-icon btn-sm text-muted-foreground" data-tip="{{ __('Edit') }}" data-tip-pos="right" aria-label="{{ __('Edit :label', ['label' => $leaveType->label]) }}"><x-ui.icon name="edit" /></a>
                            </td>
                            <td class="font-medium">{{ $leaveType->label }}</td>
                            @if ($policy)
                                <td>
                                    @if ($policy->entitlements === [])
                                        <span class="text-muted-foreground">{{ __('No fixed entitlement') }}</span>
                                    @else
                                        <span class="tabular-nums">{{ __(':days days', ['days' => collect($policy->entitlements)->map(fn ($tier) => $days($tier['days']))->implode(' / ')]) }}</span>
                                        @if (count($policy->entitlements) > 1)
                                            <span class="block text-xs text-muted-foreground">{{ __('From :years years of service', ['years' => collect($policy->entitlements)->pluck('min_years')->implode(', ')]) }}</span>
                                        @endif
                                    @endif
                                </td>
                                <td><x-ui.badge :variant="$policy->is_paid ? 'success' : 'warning'">{{ $policy->is_paid ? __('Paid') : __('Unpaid') }}</x-ui.badge></td>
                                <td>{{ $policy->requires_attachment ? __('Required') : __('Not required') }}</td>
                                <td>
                                    @if (! $policy->allow_carry_forward)
                                        <span class="text-muted-foreground">{{ __('Off') }}</span>
                                    @elseif ($policy->carry_forward_expiry_months)
                                        {{ __('Up to :days days, expires after :months months', ['days' => $days($policy->carry_forward_max_days), 'months' => $policy->carry_forward_expiry_months]) }}
                                    @else
                                        {{ __('Up to :days days, never expires', ['days' => $days($policy->carry_forward_max_days)]) }}
                                    @endif
                                </td>
                            @else
                                <td colspan="4"><x-ui.badge variant="neutral">{{ __('Not set up') }}</x-ui.badge></td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</section>
</x-layouts.admin>
