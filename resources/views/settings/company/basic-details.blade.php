<x-layouts.admin title="{{ __('Basic Details') }}" group="{{ __('Settings') }}">
<x-ui.page-header title="{{ __('Basic Details') }}" description="{{ __('Your company’s registered name, contact details, address and employer numbers. They appear on letters, payslips and statutory forms.') }}" />

@if (session('status'))
    <x-ui.alert variant="success" role="status">{{ session('status') }}</x-ui.alert>
@endif

<form method="POST" action="{{ route('settings.company.basic-details.update') }}" enctype="multipart/form-data" class="space-y-4">
    @csrf
    @method('PUT')

    <section class="card">
        <div class="card-header">
            <h2 class="card-title">{{ __('Company') }}</h2>
            <p class="card-desc">{{ __('As registered with SSM.') }}</p>
        </div>
        <div class="card-content grid gap-5 md:grid-cols-2">
            <x-ui.field label="{{ __('Company name') }}" name="name" required class="md:col-span-2">
                <x-ui.input name="name" :value="$company->name" maxlength="150" required autocomplete="organization" />
            </x-ui.field>
            <x-ui.field label="{{ __('SSM registration number') }}" name="registration_no" help="{{ __('12 digits, e.g. 202301012345.') }}">
                <x-ui.input name="registration_no" :value="$company->registration_no" inputmode="numeric" maxlength="14" class="tabular-nums" />
            </x-ui.field>
            <x-ui.field label="{{ __('Old SSM number') }}" name="old_registration_no" help="{{ __('Optional, e.g. 1234567-A.') }}">
                <x-ui.input name="old_registration_no" :value="$company->old_registration_no" maxlength="20" class="tabular-nums" />
            </x-ui.field>
        </div>
    </section>

    <section class="card">
        <div class="card-header">
            <h2 class="card-title">{{ __('Logo') }}</h2>
            <p class="card-desc">{{ __('Shown on letters and payslips.') }}</p>
        </div>
        <div class="card-content grid gap-5 md:grid-cols-2">
            @if ($logoUrl)
                <div class="space-y-3">
                    <img src="{{ $logoUrl }}" alt="{{ __('Current logo') }}" class="h-20 max-w-full rounded-md border bg-card object-contain p-2">
                    <x-ui.switch name="remove_logo" label="{{ __('Remove logo') }}" hint="{{ __('Takes effect when you save. Choosing a new file replaces the logo instead.') }}" />
                </div>
            @endif
            <div @class(['space-y-1.5', 'md:col-span-2' => ! $logoUrl])>
                <x-ui.file-preview id="f-logo" name="logo" accept="image/png,image/jpeg" :max-mb="$maxUploadMb"
                    label="{{ $logoUrl ? __('Choose a new logo') : __('Choose a logo') }}"
                    hint="{{ __('Drag here or click to choose. PNG or JPG, up to :max MB.', ['max' => $maxUploadMb]) }}" />
                @error('logo')<p class="field-error">{{ $message }}</p>@enderror
            </div>
        </div>
    </section>

    <section class="card">
        <div class="card-header">
            <h2 class="card-title">{{ __('Contact') }}</h2>
        </div>
        <div class="card-content grid gap-5 md:grid-cols-3">
            <x-ui.field label="{{ __('Email') }}" name="email">
                <x-ui.input name="email" type="email" :value="$company->email" autocomplete="email" />
            </x-ui.field>
            <x-ui.field label="{{ __('Phone') }}" name="phone">
                <x-ui.input name="phone" type="tel" :value="$company->phone" maxlength="20" autocomplete="tel" placeholder="03-1234 5678" />
            </x-ui.field>
            <x-ui.field label="{{ __('Website') }}" name="website">
                <x-ui.input name="website" type="url" :value="$company->website" autocomplete="url" placeholder="https://" />
            </x-ui.field>
        </div>
    </section>

    <section class="card">
        <div class="card-header">
            <h2 class="card-title">{{ __('Address') }}</h2>
            <p class="card-desc">{{ __('The registered business address in Malaysia.') }}</p>
        </div>
        <div class="card-content grid gap-5 md:grid-cols-3">
            <x-ui.field label="{{ __('Address line 1') }}" name="address_line_1" class="md:col-span-3">
                <x-ui.input name="address_line_1" :value="$company->address_line_1" autocomplete="address-line1" />
            </x-ui.field>
            <x-ui.field label="{{ __('Address line 2') }}" name="address_line_2" class="md:col-span-3">
                <x-ui.input name="address_line_2" :value="$company->address_line_2" autocomplete="address-line2" />
            </x-ui.field>
            <x-ui.field label="{{ __('Postcode') }}" name="postcode">
                <x-ui.input name="postcode" :value="$company->postcode" inputmode="numeric" maxlength="5" autocomplete="postal-code" class="tabular-nums" />
            </x-ui.field>
            <x-ui.field label="{{ __('City') }}" name="city">
                <x-ui.input name="city" :value="$company->city" maxlength="100" autocomplete="address-level2" />
            </x-ui.field>
            <x-ui.field label="{{ __('State') }}" name="state">
                <x-ui.select name="state" :value="$company->state" :options="$states" include-blank />
            </x-ui.field>
        </div>
    </section>

    <section class="card">
        <div class="card-header">
            <h2 class="card-title">{{ __('Employer numbers') }}</h2>
            <p class="card-desc">{{ __('Used for EPF, SOCSO, EIS, income tax and zakat submissions.') }}</p>
        </div>
        <div class="card-content grid gap-5 md:grid-cols-2">
            <x-ui.field label="{{ __('EPF employer number') }}" name="epf_no">
                <x-ui.input name="epf_no" :value="$company->epf_no" maxlength="30" class="tabular-nums" />
            </x-ui.field>
            <x-ui.field label="{{ __('SOCSO employer code') }}" name="socso_no">
                <x-ui.input name="socso_no" :value="$company->socso_no" maxlength="30" class="tabular-nums" />
            </x-ui.field>
            <x-ui.field label="{{ __('EIS employer code') }}" name="eis_no">
                <x-ui.input name="eis_no" :value="$company->eis_no" maxlength="30" class="tabular-nums" />
            </x-ui.field>
            <x-ui.field label="{{ __('Income tax employer number') }}" name="income_tax_no" help="{{ __('LHDN E-number.') }}">
                <x-ui.input name="income_tax_no" :value="$company->income_tax_no" maxlength="30" class="tabular-nums" />
            </x-ui.field>
            <x-ui.field label="{{ __('Zakat number') }}" name="zakat_no" help="{{ __('Optional.') }}">
                <x-ui.input name="zakat_no" :value="$company->zakat_no" maxlength="30" class="tabular-nums" />
            </x-ui.field>
        </div>
    </section>

    <div class="flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-muted-foreground">
            @if ($lastChange)
                {{ __('Last changed by :name on :date.', ['name' => $lastChange->causer?->name ?? __('System'), 'date' => $lastChange->created_at->timezone($timezone)->translatedFormat('j M Y, g:i A')]) }}
                <a href="{{ route('administrations.audit-trail.show', $lastChange) }}" class="font-medium text-foreground underline">{{ __('View in Audit Trail') }}</a>
            @elseif (! $company->exists)
                {{ __('Not saved yet.') }}
            @endif
        </p>
        <button type="submit" class="btn btn-primary">{{ __('Save changes') }}</button>
    </div>
</form>
</x-layouts.admin>
