<x-layouts.admin title="{{ __('Bank Account Details') }}" group="{{ __('Settings') }}">
<x-ui.page-header title="{{ __('Bank Account Details') }}" description="{{ __('The company’s bank account and statutory employer numbers. They are used for payroll, statutory contributions and tax submissions.') }}" />

@if (session('status'))
    <x-ui.alert variant="success" role="status">{{ session('status') }}</x-ui.alert>
@endif

@unless ($company->exists)
    <x-ui.alert variant="warning" title="{{ __('Save the company’s basic details first.') }}">
        {{ __('Bank account details belong to the company, so its name must be saved before you can fill this page in.') }}
        <a href="{{ route('settings.company.basic-details') }}">{{ __('Go to Basic Details') }}</a>
    </x-ui.alert>
@endunless

<form method="POST" action="{{ route('settings.company.bank-account-details.update') }}" class="space-y-4">
    @csrf
    @method('PUT')

    <fieldset @disabled(! $company->exists) class="space-y-4 disabled:opacity-60">
        <section class="card">
            <div class="card-header">
                <h2 class="card-title">{{ __('Bank account') }}</h2>
                <p class="card-desc">{{ __('The account salaries and payments are made from.') }}</p>
            </div>
            <div class="card-content grid gap-5 md:grid-cols-2">
                <x-ui.field label="{{ __('Bank') }}" name="bank">
                    <x-ui.select name="bank" :value="$company->bank" :options="$banks" include-blank />
                </x-ui.field>
                <x-ui.field label="{{ __('Account holder name') }}" name="bank_account_holder" help="{{ __('As shown on the bank statement.') }}">
                    <x-ui.input name="bank_account_holder" :value="$company->bank_account_holder" maxlength="150" />
                </x-ui.field>
                <x-ui.field label="{{ __('Account number') }}" name="bank_account_no" help="{{ __('Digits only; spaces and dashes are removed.') }}">
                    <x-ui.input name="bank_account_no" :value="$company->bank_account_no" inputmode="numeric" maxlength="30" class="tabular-nums" />
                </x-ui.field>
            </div>
        </section>

        <section class="card">
            <div class="card-header">
                <h2 class="card-title">{{ __('Statutory numbers') }}</h2>
                <p class="card-desc">{{ __('Used for EPF, SOCSO, EIS, income tax, zakat, HRDF and PTPTN submissions.') }}</p>
            </div>
            <div class="card-content grid gap-5 md:grid-cols-2">
                <x-ui.field label="{{ __('EPF employer number (Malaysian)') }}" name="epf_no">
                    <x-ui.input name="epf_no" :value="$company->epf_no" maxlength="30" class="tabular-nums" />
                </x-ui.field>
                <x-ui.field label="{{ __('EPF employer number (Non-Malaysian)') }}" name="epf_foreign_no">
                    <x-ui.input name="epf_foreign_no" :value="$company->epf_foreign_no" maxlength="30" class="tabular-nums" />
                </x-ui.field>
                <x-ui.field label="{{ __('SOCSO employer code') }}" name="socso_no">
                    <x-ui.input name="socso_no" :value="$company->socso_no" maxlength="30" class="tabular-nums" />
                </x-ui.field>
                <x-ui.field label="{{ __('EIS employer code') }}" name="eis_no">
                    <x-ui.input name="eis_no" :value="$company->eis_no" maxlength="30" class="tabular-nums" />
                </x-ui.field>
                <x-ui.field label="{{ __('LHDN employer number') }}" name="income_tax_no" help="{{ __('E-number, e.g. E 1234567890.') }}">
                    <x-ui.input name="income_tax_no" :value="$company->income_tax_no" maxlength="30" class="tabular-nums" />
                </x-ui.field>
                <x-ui.field label="{{ __('Zakat number') }}" name="zakat_no" help="{{ __('Optional.') }}">
                    <x-ui.input name="zakat_no" :value="$company->zakat_no" maxlength="30" class="tabular-nums" />
                </x-ui.field>
                <x-ui.field label="{{ __('HRDF account number') }}" name="hrdf_no" help="{{ __('HRD Corp, formerly HRDF.') }}">
                    <x-ui.input name="hrdf_no" :value="$company->hrdf_no" maxlength="30" class="tabular-nums" />
                </x-ui.field>
                <x-ui.field label="{{ __('PTPTN employer code') }}" name="ptptn_no">
                    <x-ui.input name="ptptn_no" :value="$company->ptptn_no" maxlength="30" class="tabular-nums" />
                </x-ui.field>
            </div>
        </section>

        <div class="flex flex-wrap items-center justify-between gap-3">
            <p class="text-sm text-muted-foreground">
                @if ($lastChange)
                    {{ __('Last changed by :name on :date.', ['name' => $lastChange->causer?->name ?? __('System'), 'date' => $lastChange->created_at->timezone($timezone)->translatedFormat('j M Y, g:i A')]) }}
                    <a href="{{ route('administrations.audit-trail.show', $lastChange) }}" class="font-medium text-foreground underline">{{ __('View in Audit Trail') }}</a>
                @endif
            </p>
            <button type="submit" class="btn btn-primary">{{ __('Save changes') }}</button>
        </div>
    </fieldset>
</form>
</x-layouts.admin>
