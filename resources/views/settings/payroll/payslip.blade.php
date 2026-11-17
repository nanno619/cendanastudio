<x-layouts.admin title="{{ __('Payslip Settings') }}" group="{{ __('Settings') }}">
<div class="flex flex-wrap items-end justify-between gap-4">
    <x-ui.page-header title="{{ __('Payslip Settings') }}" description="{{ __('What payslips show and how they reach staff. Company details and employer numbers come from the Company settings.') }}" />
    <a href="{{ route('settings.payroll.payslip.preview') }}" target="_blank" rel="noopener" class="btn btn-outline"><x-ui.icon name="eye" />{{ __('Preview payslip') }}</a>
</div>

@if (session('status'))
    <x-ui.alert variant="success" role="status">{{ session('status') }}</x-ui.alert>
@endif

<form method="POST" action="{{ route('settings.payroll.payslip.update') }}" class="space-y-4">
    @csrf
    @method('PUT')

    <section class="card">
        <div class="card-header">
            <h2 class="card-title">{{ __('Company details') }}</h2>
        </div>
        <div class="card-content grid gap-5 md:grid-cols-2">
            <div>
                <x-ui.switch name="show_company_logo" :checked="$setting->show_company_logo" label="{{ __('Company logo') }}" hint="{{ __('The logo uploaded in Company Basic Details.') }}" />
                @error('show_company_logo')<p class="field-error">{{ $message }}</p>@enderror
            </div>
            <div>
                <x-ui.switch name="show_employer_numbers" :checked="$setting->show_employer_numbers" label="{{ __('Employer numbers') }}" hint="{{ __('EPF, SOCSO, EIS and income tax employer numbers from Bank Account Details.') }}" />
                @error('show_employer_numbers')<p class="field-error">{{ $message }}</p>@enderror
            </div>
        </div>
    </section>

    <section class="card">
        <div class="card-header">
            <h2 class="card-title">{{ __('Staff details') }}</h2>
            <p class="card-desc">{{ __('Payslips are personal data under the PDPA; show only what staff need.') }}</p>
        </div>
        <div class="card-content grid gap-5 md:grid-cols-2">
            <x-ui.field label="{{ __('IC or passport number') }}" name="identity_display" required>
                <x-ui.select name="identity_display" :value="$setting->identity_display->value" :options="$identityDisplays" required />
            </x-ui.field>
            <div>
                <x-ui.switch name="mask_bank_account" :checked="$setting->mask_bank_account" label="{{ __('Mask bank account number') }}" hint="{{ __('Show only the last 4 digits of the staff member’s account.') }}" />
                @error('mask_bank_account')<p class="field-error">{{ $message }}</p>@enderror
            </div>
        </div>
    </section>

    <section class="card">
        <div class="card-header">
            <h2 class="card-title">{{ __('Sections') }}</h2>
            <p class="card-desc">{{ __('Earnings, deductions and net pay are always shown.') }}</p>
        </div>
        <div class="card-content grid gap-5 md:grid-cols-3">
            <div>
                <x-ui.switch name="show_employer_contributions" :checked="$setting->show_employer_contributions" label="{{ __('Employer contributions') }}" hint="{{ __('Employer EPF, SOCSO and EIS.') }}" />
                @error('show_employer_contributions')<p class="field-error">{{ $message }}</p>@enderror
            </div>
            <div>
                <x-ui.switch name="show_year_to_date" :checked="$setting->show_year_to_date" label="{{ __('Year-to-date totals') }}" hint="{{ __('Gross pay, EPF and PCB so far this year.') }}" />
                @error('show_year_to_date')<p class="field-error">{{ $message }}</p>@enderror
            </div>
            <div>
                <x-ui.switch name="show_leave_balance" :checked="$setting->show_leave_balance" label="{{ __('Leave balance') }}" hint="{{ __('Remaining days for each leave type.') }}" />
                @error('show_leave_balance')<p class="field-error">{{ $message }}</p>@enderror
            </div>
        </div>
    </section>

    <section class="card">
        <div class="card-header">
            <h2 class="card-title">{{ __('Language and footer') }}</h2>
        </div>
        <div class="card-content grid gap-5 md:grid-cols-2">
            <x-ui.field label="{{ __('Payslip language') }}" name="language" required>
                <x-ui.select name="language" :value="$setting->language" :options="$languages" required />
            </x-ui.field>
            <x-ui.field label="{{ __('Footer note') }}" name="footer_note" help="{{ __('Optional. Up to :count characters, shown at the bottom of every payslip.', ['count' => \App\Models\PayslipSetting::FOOTER_NOTE_MAX_LENGTH]) }}">
                <x-ui.textarea name="footer_note" rows="3" maxlength="{{ \App\Models\PayslipSetting::FOOTER_NOTE_MAX_LENGTH }}" :value="$setting->footer_note" />
            </x-ui.field>
        </div>
    </section>

    <section class="card">
        <div class="card-header">
            <h2 class="card-title">{{ __('Delivery') }}</h2>
        </div>
        <div class="card-content grid gap-5 md:grid-cols-2">
            <div>
                <x-ui.switch name="email_on_release" :checked="$setting->email_on_release" label="{{ __('Email payslips on release') }}" hint="{{ __('Each staff member is emailed their payslip when a payroll month is released.') }}" />
                @error('email_on_release')<p class="field-error">{{ $message }}</p>@enderror
            </div>
            <div>
                <x-ui.switch name="password_protect" :checked="$setting->password_protect" label="{{ __('Password-protect emailed payslips') }}" hint="{{ __('The PDF opens with the staff member’s IC or passport number, without dashes.') }}" />
                @error('password_protect')<p class="field-error">{{ $message }}</p>@enderror
            </div>
        </div>
    </section>

    <section class="card">
        <div class="card-header">
            <h2 class="card-title">{{ __('Fixed rules') }}</h2>
        </div>
        <div class="card-content">
            <ul class="list-disc space-y-1.5 pl-5 text-sm text-muted-foreground">
                <li>{{ __('Every payslip itemises earnings and deductions, as the Employment Act requires.') }}</li>
                <li>{{ __('Payslips are kept for at least 6 years with the payroll records.') }}</li>
                <li>{{ __('The preview uses sample figures with the saved settings; save first to see changes.') }}</li>
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
