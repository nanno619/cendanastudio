<x-layouts.admin title="{{ __('Payroll Settings') }}" group="{{ __('Settings') }}">
<x-ui.page-header title="{{ __('Payroll Settings') }}" description="{{ __('Company-wide payroll rules: when salaries are paid, statutory deductions, payment and approval. EPF, overtime, pro-rata and payslips have their own pages.') }}" />

@if (session('status'))
    <x-ui.alert variant="success" role="status">{{ session('status') }}</x-ui.alert>
@endif

<form method="POST" action="{{ route('settings.payroll.general.update') }}" class="space-y-4">
    @csrf
    @method('PUT')

    <section class="card">
        <div class="card-header">
            <h2 class="card-title">{{ __('Pay schedule') }}</h2>
            <p class="card-desc">{{ __('Salaries cover one calendar month. The Employment Act requires payment within 7 days after the month ends.') }}</p>
        </div>
        <div class="card-content grid gap-5 md:grid-cols-2">
            <x-ui.field label="{{ __('Pay day') }}" name="pay_day_rule" required>
                <x-ui.select name="pay_day_rule" :value="$setting->pay_day_rule->value" :options="$payDayRules" required />
            </x-ui.field>
            <x-ui.field label="{{ __('Day of the month') }}" name="pay_day" help="{{ __('1 to 31 in the same month (shorter months use their last day), or 1 to 7 of the following month. Not used for the last working day.') }}">
                <x-ui.input name="pay_day" type="number" min="1" max="31" step="1" :value="$setting->pay_day" class="tabular-nums" />
            </x-ui.field>
            <x-ui.field label="{{ __('If pay day is a rest day or public holiday') }}" name="pay_day_adjustment" required help="{{ __('Uses the working days and public holiday state in Leave → General.') }}">
                <x-ui.select name="pay_day_adjustment" :value="$setting->pay_day_adjustment->value" :options="$adjustments" required />
            </x-ui.field>
            <x-ui.field label="{{ __('Cut-off day') }}" name="cutoff_day" required help="{{ __('Overtime, claims and unpaid leave after this day go into next month’s payroll.') }}">
                <x-ui.input name="cutoff_day" type="number" min="1" max="31" step="1" :value="$setting->cutoff_day" class="tabular-nums" required />
            </x-ui.field>
        </div>
    </section>

    <section class="card">
        <div class="card-header">
            <h2 class="card-title">{{ __('Statutory deductions') }}</h2>
            <p class="card-desc">{{ __('SOCSO and EIS rates are set by law. EPF has its own page.') }}</p>
        </div>
        <div class="card-content grid gap-5 md:grid-cols-2">
            <div>
                <x-ui.switch name="socso_enabled" :checked="$setting->socso_enabled" label="{{ __('SOCSO') }}" hint="{{ __('Employment injury and invalidity scheme (PERKESO).') }}" />
                @error('socso_enabled')<p class="field-error">{{ $message }}</p>@enderror
            </div>
            <div>
                <x-ui.switch name="eis_enabled" :checked="$setting->eis_enabled" label="{{ __('EIS') }}" hint="{{ __('Employment Insurance System (SIP).') }}" />
                @error('eis_enabled')<p class="field-error">{{ $message }}</p>@enderror
            </div>
            <div>
                <x-ui.switch name="pcb_enabled" :checked="$setting->pcb_enabled" label="{{ __('PCB (monthly tax deduction)') }}" hint="{{ __('Income tax deducted from salary and paid to LHDN.') }}" />
                @error('pcb_enabled')<p class="field-error">{{ $message }}</p>@enderror
            </div>
            <x-ui.field label="{{ __('PCB method') }}" name="pcb_method" required>
                <x-ui.select name="pcb_method" :value="$setting->pcb_method->value" :options="$pcbMethods" required />
            </x-ui.field>
            <x-ui.field label="{{ __('HRDF levy') }}" name="hrdf_levy" required help="{{ __('1% is mandatory for 10 or more Malaysian employees in covered sectors; 0.5% is optional for 5 to 9.') }}">
                <x-ui.select name="hrdf_levy" :value="$setting->hrdf_levy->value" :options="$hrdfLevies" required />
            </x-ui.field>
            <div>
                <x-ui.switch name="zakat_via_payroll" :checked="$setting->zakat_via_payroll" label="{{ __('Zakat through payroll') }}" hint="{{ __('Staff who choose it pay zakat monthly from salary, offset against their PCB.') }}" />
                @error('zakat_via_payroll')<p class="field-error">{{ $message }}</p>@enderror
            </div>
        </div>
    </section>

    <section class="card">
        <div class="card-header">
            <h2 class="card-title">{{ __('Payment') }}</h2>
        </div>
        <div class="card-content grid gap-5 md:grid-cols-2">
            <x-ui.field label="{{ __('Default payment method') }}" name="payment_method" required>
                <x-ui.select name="payment_method" :value="$setting->payment_method->value" :options="$paymentMethods" required />
                <x-slot:help>
                    {{ __('Bank transfers are paid from the account in') }}
                    <a href="{{ route('settings.company.bank-account-details') }}" class="font-medium text-foreground underline">{{ __('Bank Account Details') }}</a>.
                </x-slot:help>
            </x-ui.field>
            <x-ui.field label="{{ __('Net pay rounding') }}" name="net_pay_rounding" required help="{{ __('Rounding to 5 sen follows Malaysia’s cash rounding.') }}">
                <x-ui.select name="net_pay_rounding" :value="$setting->net_pay_rounding->value" :options="$roundings" required />
            </x-ui.field>
        </div>
    </section>

    <section class="card">
        <div class="card-header">
            <h2 class="card-title">{{ __('Approval and locking') }}</h2>
        </div>
        <div class="card-content grid gap-5 md:grid-cols-2">
            <div>
                <x-ui.switch name="requires_approval" :checked="$setting->requires_approval" label="{{ __('Payroll needs approval') }}" hint="{{ __('A payroll run must be approved before salaries are released.') }}" />
                @error('requires_approval')<p class="field-error">{{ $message }}</p>@enderror
            </div>
            <x-ui.field label="{{ __('Approved by') }}" name="approver_role" help="{{ __('Anyone with this role can approve. Used only when approval is on.') }}">
                <x-ui.select name="approver_role" :value="$setting->approver_role?->value" :options="$roles" include-blank />
            </x-ui.field>
            <div>
                <x-ui.switch name="lock_after_release" :checked="$setting->lock_after_release" label="{{ __('Lock after release') }}" hint="{{ __('A released month becomes read-only. Only Super Admin can reopen it.') }}" />
                @error('lock_after_release')<p class="field-error">{{ $message }}</p>@enderror
            </div>
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
