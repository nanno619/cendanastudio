<x-layouts.admin title="{{ __('Claim Settings') }}" group="{{ __('Settings') }}">
<x-ui.page-header title="{{ __('Claim Settings') }}" description="{{ __('Company-wide rules for expense claims: deadlines, receipts, mileage and payment. Who approves claims is set in Approval Workflows.') }}" />

@if (session('status'))
    <x-ui.alert variant="success" role="status">{{ session('status') }}</x-ui.alert>
@endif

<form method="POST" action="{{ route('settings.claim.general.update') }}" class="space-y-4">
    @csrf
    @method('PUT')

    <section class="card">
        <div class="card-header">
            <h2 class="card-title">{{ __('Submitting claims') }}</h2>
        </div>
        <div class="card-content grid gap-5 md:grid-cols-3">
            <x-ui.field label="{{ __('Submission deadline') }}" name="submission_deadline_days" required help="{{ __('Days after the expense date. 0 means no deadline.') }}">
                <x-ui.input name="submission_deadline_days" type="number" min="0" max="{{ \App\Models\ClaimSetting::MAX_SUBMISSION_DEADLINE_DAYS }}" step="1" :value="$setting->submission_deadline_days" class="tabular-nums" required />
            </x-ui.field>
            <x-ui.field label="{{ __('Receipts needed above') }}" name="receipt_required_above" required help="{{ __('Smaller amounts, like tolls and parking, can be claimed without a receipt. 0 means a receipt is always needed.') }}">
                <x-ui.input name="receipt_required_above" type="number" min="0" max="10000" step="0.01" :value="$setting->receipt_required_above" addon="RM" class="tabular-nums" required />
            </x-ui.field>
            <div>
                <x-ui.switch name="allow_resubmission" :checked="$setting->allow_resubmission" label="{{ __('Allow resubmitting rejected claims') }}" hint="{{ __('Staff can correct a rejected claim and submit it again. When off, they start a new claim.') }}" />
                @error('allow_resubmission')<p class="field-error">{{ $message }}</p>@enderror
            </div>
        </div>
    </section>

    <section class="card">
        <div class="card-header">
            <h2 class="card-title">{{ __('Mileage') }}</h2>
        </div>
        <div class="card-content grid gap-5 md:grid-cols-3">
            <div>
                <x-ui.switch name="mileage_enabled" :checked="$setting->mileage_enabled" label="{{ __('Mileage claims') }}" hint="{{ __('Staff can claim distance driven in their own vehicle for work, instead of fuel receipts.') }}" />
                @error('mileage_enabled')<p class="field-error">{{ $message }}</p>@enderror
            </div>
            <x-ui.field label="{{ __('Mileage rate') }}" name="mileage_rate" help="{{ __('RM per km. Used only when mileage claims are on.') }}">
                <x-ui.input name="mileage_rate" type="number" min="0.01" max="10" step="0.01" :value="$setting->mileage_rate" addon="RM" class="tabular-nums" />
            </x-ui.field>
        </div>
    </section>

    <section class="card">
        <div class="card-header">
            <h2 class="card-title">{{ __('Payment') }}</h2>
        </div>
        <div class="card-content grid gap-5 md:grid-cols-2">
            <x-ui.field label="{{ __('How claims are paid') }}" name="payment_method" required help="{{ __('With the payroll, claims approved after the cut-off day are paid the following month.') }}">
                <x-ui.select name="payment_method" :value="$setting->payment_method->value" :options="$paymentMethods" required />
            </x-ui.field>
        </div>
    </section>

    <section class="card">
        <div class="card-header">
            <h2 class="card-title">{{ __('Fixed rules') }}</h2>
        </div>
        <div class="card-content">
            <ul class="list-disc space-y-1.5 pl-5 text-sm text-muted-foreground">
                <li>{{ __('Claims reimburse actual work expenses, so they aren’t wages: no EPF, SOCSO or EIS is deducted.') }}</li>
                <li>{{ __('Claims are in RM. Expenses paid in other currencies are entered at the amount actually paid.') }}</li>
                <li>
                    {{ __('The payroll cut-off day is set in') }}
                    <a href="{{ route('settings.payroll.general') }}" class="font-medium text-foreground underline">{{ __('Payroll General settings') }}</a>.
                </li>
                <li>
                    {{ __('Claim types are managed in the') }}
                    <a href="{{ route('administrations.data-dictionary.show', \App\Enums\LookupCategory::ClaimType) }}" class="font-medium text-foreground underline">{{ __('Data Dictionary') }}</a>.
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
