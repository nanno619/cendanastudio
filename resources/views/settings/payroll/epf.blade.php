<x-layouts.admin title="{{ __('EPF Settings') }}" group="{{ __('Settings') }}">
<x-ui.page-header title="{{ __('EPF Settings') }}" description="{{ __('KWSP contribution rates and how they are calculated. Starter values are the statutory rates; check them against KWSP’s latest Third Schedule.') }}" />

@if (session('status'))
    <x-ui.alert variant="success" role="status">{{ session('status') }}</x-ui.alert>
@endif

<form method="POST" action="{{ route('settings.payroll.epf.update') }}" class="space-y-4">
    @csrf
    @method('PUT')

    <section class="card">
        <div class="card-header">
            <h2 class="card-title">{{ __('Contribution rates') }}</h2>
            <p class="card-desc">{{ __('Percentages of monthly wages. Budget announcements can change them.') }}</p>
        </div>
        <div class="card-content space-y-6">
            <fieldset class="space-y-3">
                <legend class="text-sm font-semibold">{{ __('Malaysian staff under 60') }}</legend>
                <div class="grid gap-5 md:grid-cols-4">
                    <x-ui.field label="{{ __('Employee') }}" name="malaysian_employee_rate" required>
                        <x-ui.input name="malaysian_employee_rate" type="number" min="0" max="30" step="0.01" :value="$setting->malaysian_employee_rate" addon="%" class="tabular-nums" required />
                    </x-ui.field>
                    <x-ui.field label="{{ __('Employer, up to the threshold') }}" name="malaysian_employer_rate" required>
                        <x-ui.input name="malaysian_employer_rate" type="number" min="0" max="30" step="0.01" :value="$setting->malaysian_employer_rate" addon="%" class="tabular-nums" required />
                    </x-ui.field>
                    <x-ui.field label="{{ __('Employer, above the threshold') }}" name="malaysian_employer_rate_above_threshold" required>
                        <x-ui.input name="malaysian_employer_rate_above_threshold" type="number" min="0" max="30" step="0.01" :value="$setting->malaysian_employer_rate_above_threshold" addon="%" class="tabular-nums" required />
                    </x-ui.field>
                    <x-ui.field label="{{ __('Wage threshold') }}" name="wage_threshold" required help="{{ __('Monthly wages.') }}">
                        <x-ui.input name="wage_threshold" type="number" min="1" max="100000" step="0.01" :value="$setting->wage_threshold" addon="RM" class="tabular-nums" required />
                    </x-ui.field>
                </div>
            </fieldset>

            <fieldset class="space-y-3">
                <legend class="text-sm font-semibold">{{ __('Malaysian staff 60 and over') }}</legend>
                <div class="grid gap-5 md:grid-cols-4">
                    <x-ui.field label="{{ __('Employee') }}" name="senior_employee_rate" required>
                        <x-ui.input name="senior_employee_rate" type="number" min="0" max="30" step="0.01" :value="$setting->senior_employee_rate" addon="%" class="tabular-nums" required />
                    </x-ui.field>
                    <x-ui.field label="{{ __('Employer') }}" name="senior_employer_rate" required>
                        <x-ui.input name="senior_employer_rate" type="number" min="0" max="30" step="0.01" :value="$setting->senior_employer_rate" addon="%" class="tabular-nums" required />
                    </x-ui.field>
                </div>
            </fieldset>

            <fieldset class="space-y-3">
                <legend class="text-sm font-semibold">{{ __('Non-Malaysian staff') }}</legend>
                <div class="grid gap-5 md:grid-cols-4">
                    <x-ui.field label="{{ __('Employee') }}" name="foreign_employee_rate" required>
                        <x-ui.input name="foreign_employee_rate" type="number" min="0" max="30" step="0.01" :value="$setting->foreign_employee_rate" addon="%" class="tabular-nums" required />
                    </x-ui.field>
                    <x-ui.field label="{{ __('Employer') }}" name="foreign_employer_rate" required>
                        <x-ui.input name="foreign_employer_rate" type="number" min="0" max="30" step="0.01" :value="$setting->foreign_employer_rate" addon="%" class="tabular-nums" required />
                    </x-ui.field>
                </div>
            </fieldset>
        </div>
    </section>

    <section class="card">
        <div class="card-header">
            <h2 class="card-title">{{ __('Calculation') }}</h2>
        </div>
        <div class="card-content grid gap-5 md:grid-cols-2">
            <x-ui.field label="{{ __('Calculation method') }}" name="calculation_method" required help="{{ __('The Third Schedule uses KWSP’s wage bands. Both methods round contributions up to the next ringgit.') }}">
                <x-ui.select name="calculation_method" :value="$setting->calculation_method->value" :options="$methods" required />
            </x-ui.field>
        </div>
    </section>

    <section class="card">
        <div class="card-header">
            <h2 class="card-title">{{ __('Company options') }}</h2>
        </div>
        <div class="card-content grid gap-5 md:grid-cols-3">
            <div>
                <x-ui.switch name="allow_employer_top_up" :checked="$setting->allow_employer_top_up" label="{{ __('Employer top-up') }}" hint="{{ __('The company contributes more than the statutory employer rate for Malaysian staff under 60.') }}" />
                @error('allow_employer_top_up')<p class="field-error">{{ $message }}</p>@enderror
            </div>
            <x-ui.field label="{{ __('Top-up employer rate') }}" name="employer_top_up_rate" help="{{ __('Used only when top-up is on. Must be higher than the statutory employer rate.') }}">
                <x-ui.input name="employer_top_up_rate" type="number" min="0" max="30" step="0.01" :value="$setting->employer_top_up_rate" addon="%" class="tabular-nums" />
            </x-ui.field>
            <div>
                <x-ui.switch name="allow_employee_elected_rate" :checked="$setting->allow_employee_elected_rate" label="{{ __('Employees can elect a higher rate') }}" hint="{{ __('Staff may contribute more than the statutory employee rate. Each person’s rate is set in their staff record.') }}" />
                @error('allow_employee_elected_rate')<p class="field-error">{{ $message }}</p>@enderror
            </div>
        </div>
    </section>

    <section class="card">
        <div class="card-header">
            <h2 class="card-title">{{ __('Fixed KWSP rules') }}</h2>
        </div>
        <div class="card-content">
            <ul class="list-disc space-y-1.5 pl-5 text-sm text-muted-foreground">
                <li>{{ __('The 60-and-over rates apply from the month after an employee’s 60th birthday.') }}</li>
                <li>{{ __('Contributions must reach KWSP by the 15th of the following month.') }}</li>
                <li>
                    {{ __('Employer EPF numbers (Malaysian and non-Malaysian) are kept in') }}
                    <a href="{{ route('settings.company.bank-account-details') }}" class="font-medium text-foreground underline">{{ __('Bank Account Details') }}</a>.
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
