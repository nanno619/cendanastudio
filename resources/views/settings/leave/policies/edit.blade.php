<x-layouts.admin title="{{ $leaveType->label }}" group="{{ __('Leave Policies') }}">
<div>
    <a href="{{ route('settings.leave.policies.index') }}" class="mb-3 inline-flex items-center gap-1 text-sm text-muted-foreground hover:text-foreground">
        <x-ui.icon name="arrow-left" class="h-4 w-4" />{{ __('Back to Leave Policies') }}
    </a>
    <div class="flex flex-wrap items-center gap-x-3 gap-y-2">
        <h1 class="text-2xl font-semibold tracking-tight">{{ $leaveType->label }}</h1>
        <span class="kbd">{{ $leaveType->code }}</span>
    </div>
    <p class="mt-1 text-muted-foreground">{{ __('The policy staff follow when applying for this leave.') }}</p>
</div>

@php
    $rowCount = 5;
    $tiers = old('entitlements', $policy->entitlements);
@endphp

<form method="POST" action="{{ route('settings.leave.policies.update', $leaveType->code) }}" class="space-y-4">
    @csrf
    @method('PUT')

    <section class="card">
        <div class="card-header">
            <h2 class="card-title">{{ __('Entitlement') }}</h2>
            <p class="card-desc">{{ __('Days a year by completed years of service. Leave a row blank if you don’t need it. Leave all rows blank for leave with no fixed entitlement, e.g. unpaid leave.') }}</p>
        </div>
        <div class="card-content space-y-3">
            @error('entitlements')<x-ui.alert variant="danger">{{ $message }}</x-ui.alert>@enderror
            <div class="hidden grid-cols-2 gap-3 text-sm font-medium sm:grid sm:max-w-md">
                <span>{{ __('From years of service') }}</span>
                <span>{{ __('Days a year') }}</span>
            </div>
            @for ($row = 0; $row < $rowCount; $row++)
                <div class="grid grid-cols-2 gap-3 sm:max-w-md">
                    <div>
                        <x-ui.input name="entitlements[{{ $row }}][min_years]" type="number" min="0" max="50" step="1" :value="$tiers[$row]['min_years'] ?? ''" :invalid="$errors->has('entitlements.'.$row.'.min_years')" addon="{{ __('years') }}" aria-label="{{ __('From years of service, row :row', ['row' => $row + 1]) }}" class="tabular-nums" />
                        @error('entitlements.'.$row.'.min_years')<p class="field-error">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <x-ui.input name="entitlements[{{ $row }}][days]" type="number" min="0.5" max="365" step="0.5" :value="$tiers[$row]['days'] ?? ''" :invalid="$errors->has('entitlements.'.$row.'.days')" addon="{{ __('days') }}" aria-label="{{ __('Days a year, row :row', ['row' => $row + 1]) }}" class="tabular-nums" />
                        @error('entitlements.'.$row.'.days')<p class="field-error">{{ $message }}</p>@enderror
                    </div>
                </div>
            @endfor
        </div>
    </section>

    <section class="card">
        <div class="card-header">
            <h2 class="card-title">{{ __('Payment and documents') }}</h2>
        </div>
        <div class="card-content grid gap-5 md:grid-cols-2">
            <div>
                <x-ui.switch name="is_paid" :checked="$policy->is_paid" label="{{ __('Paid leave') }}" hint="{{ __('When off, the days taken are deducted from salary.') }}" />
                @error('is_paid')<p class="field-error">{{ $message }}</p>@enderror
            </div>
            <div>
                <x-ui.switch name="requires_attachment" :checked="$policy->requires_attachment" label="{{ __('Attachment required') }}" hint="{{ __('e.g. a medical certificate or birth certificate.') }}" />
                @error('requires_attachment')<p class="field-error">{{ $message }}</p>@enderror
            </div>
        </div>
    </section>

    <section class="card">
        <div class="card-header">
            <h2 class="card-title">{{ __('Carry forward') }}</h2>
            <p class="card-desc">{{ __('Unused days that move into the next leave year.') }}</p>
        </div>
        <div class="card-content grid gap-5 md:grid-cols-3">
            <div>
                <x-ui.switch name="allow_carry_forward" :checked="$policy->allow_carry_forward" label="{{ __('Allow carry forward') }}" />
                @error('allow_carry_forward')<p class="field-error">{{ $message }}</p>@enderror
            </div>
            <x-ui.field label="{{ __('Maximum days carried forward') }}" name="carry_forward_max_days" help="{{ __('Used only when carry forward is on. Whole or half days.') }}">
                <x-ui.input name="carry_forward_max_days" type="number" min="0.5" max="365" step="0.5" :value="$policy->carry_forward_max_days" addon="{{ __('days') }}" class="tabular-nums" />
            </x-ui.field>
            <x-ui.field label="{{ __('Carried days expire after') }}" name="carry_forward_expiry_months" help="{{ __('Months into the new leave year, e.g. 3 = end of March when the year starts in January. Leave blank if they never expire.') }}">
                <x-ui.input name="carry_forward_expiry_months" type="number" min="1" max="12" step="1" :value="$policy->carry_forward_expiry_months" addon="{{ __('months') }}" class="tabular-nums" />
            </x-ui.field>
        </div>
    </section>

    <div class="flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-muted-foreground">
            @if ($lastChange)
                {{ __('Last changed by :name on :date.', ['name' => $lastChange->causer?->name ?? __('System'), 'date' => $lastChange->created_at->timezone($timezone)->translatedFormat('j M Y, g:i A')]) }}
                <a href="{{ route('administrations.audit-trail.show', $lastChange) }}" class="font-medium text-foreground underline">{{ __('View in Audit Trail') }}</a>
            @elseif (! $policy->exists)
                {{ __('Not set up yet.') }}
            @endif
        </p>
        <button type="submit" class="btn btn-primary">{{ __('Save changes') }}</button>
    </div>
</form>
</x-layouts.admin>
