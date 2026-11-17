<x-layouts.admin title="{{ __('Pro-Rata Settings') }}" group="{{ __('Settings') }}">
<x-ui.page-header title="{{ __('Pro-Rata Settings') }}" description="{{ __('How wages are worked out for part of a month. A part-month wage is the monthly wage divided by the days below, times the days paid.') }}" />

@if (session('status'))
    <x-ui.alert variant="success" role="status">{{ session('status') }}</x-ui.alert>
@endif

<form method="POST" action="{{ route('settings.payroll.pro-rata.update') }}" class="space-y-4">
    @csrf
    @method('PUT')

    <section class="card">
        <div class="card-header">
            <h2 class="card-title">{{ __('Days in a month') }}</h2>
            <p class="card-desc">{{ __('What the monthly wage is divided by to get one day’s pay.') }}</p>
        </div>
        <div class="card-content grid gap-5 md:grid-cols-3">
            <x-ui.field label="{{ __('Joiners and leavers') }}" name="joiner_leaver_basis" required help="{{ __('The Employment Act uses calendar days for an incomplete month of service.') }}">
                <x-ui.select name="joiner_leaver_basis" :value="$setting->joiner_leaver_basis->value" :options="$bases" required />
            </x-ui.field>
            <x-ui.field label="{{ __('Unpaid leave and absence') }}" name="unpaid_leave_basis" required help="{{ __('The Employment Act’s ordinary rate of pay divides monthly wages by 26.') }}">
                <x-ui.select name="unpaid_leave_basis" :value="$setting->unpaid_leave_basis->value" :options="$bases" required />
            </x-ui.field>
            <x-ui.field label="{{ __('Fixed number of days') }}" name="fixed_days" help="{{ __('Used only by a fixed-days basis. 20 to 31 days.') }}">
                <x-ui.input name="fixed_days" type="number" min="20" max="31" step="1" :value="$setting->fixed_days" class="tabular-nums" />
            </x-ui.field>
        </div>
    </section>

    <section class="card">
        <div class="card-header">
            <h2 class="card-title">{{ __('Allowances') }}</h2>
        </div>
        <div class="card-content">
            <x-ui.switch name="prorate_fixed_allowances" :checked="$setting->prorate_fixed_allowances" label="{{ __('Prorate fixed allowances') }}" hint="{{ __('Fixed monthly allowances are prorated the same way as the basic wage. When off, they’re paid in full for any part of a month.') }}" />
            @error('prorate_fixed_allowances')<p class="field-error">{{ $message }}</p>@enderror
        </div>
    </section>

    <section class="card">
        <div class="card-header">
            <h2 class="card-title">{{ __('Fixed rules') }}</h2>
        </div>
        <div class="card-content">
            <ul class="list-disc space-y-1.5 pl-5 text-sm text-muted-foreground">
                <li>{{ __('The joining day and the last working day are both paid.') }}</li>
                <li>{{ __('Unpaid half days deduct half a day’s pay.') }}</li>
                <li>{{ __('Prorated amounts are rounded to the sen; net pay rounding is set in Payroll General settings.') }}</li>
                <li>{{ __('EPF, SOCSO and EIS are worked out on the prorated wages.') }}</li>
                <li>
                    {{ __('Working days follow the working week in') }}
                    <a href="{{ route('settings.leave.general') }}" class="font-medium text-foreground underline">{{ __('Leave General settings') }}</a>. {{ __('Leave entitlement proration is set there too.') }}
                </li>
            </ul>
        </div>
    </section>

    <div class="flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-muted-foreground">
            @if ($lastChange)
                {{ __('Last changed by :name on :date.', ['name' => $lastChange->causer?->name ?? __('System'), 'date' => $lastChange->created_at->timezone($timezone)->translatedFormat('j M Y, g:i A')]) }}
                <a href="{{ route('administrations.audit-trail.show', $lastChange) }}" class="font-medium text-foreground underline">{{ __('View in Audit Trail') }}</a>
            @elseif (! $setting->exists)
                {{ __('Using the default settings until you save.') }}
            @endif
        </p>
        <button type="submit" class="btn btn-primary">{{ __('Save changes') }}</button>
    </div>
</form>
</x-layouts.admin>
