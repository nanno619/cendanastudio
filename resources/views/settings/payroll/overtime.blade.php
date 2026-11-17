<x-layouts.admin title="{{ __('Overtime Settings') }}" group="{{ __('Settings') }}">
<x-ui.page-header title="{{ __('Overtime Settings') }}" description="{{ __('Who is paid overtime and at what rate. Starter values are the Employment Act 1955 minimums.') }}" />

@if (session('status'))
    <x-ui.alert variant="success" role="status">{{ session('status') }}</x-ui.alert>
@endif

<form method="POST" action="{{ route('settings.payroll.overtime.update') }}" class="space-y-4">
    @csrf
    @method('PUT')

    <section class="card">
        <div class="card-header">
            <h2 class="card-title">{{ __('Eligibility') }}</h2>
        </div>
        <div class="card-content grid gap-5 md:grid-cols-2">
            <x-ui.field label="{{ __('Who gets overtime pay') }}" name="eligibility" required help="{{ __('The Employment Act covers staff earning up to the wage limit, and manual workers whatever they earn.') }}">
                <x-ui.select name="eligibility" :value="$setting->eligibility->value" :options="$eligibilities" required />
            </x-ui.field>
            <x-ui.field label="{{ __('Wage limit') }}" name="eligibility_wage_limit" help="{{ __('Monthly wages. Used only for Employment Act staff.') }}">
                <x-ui.input name="eligibility_wage_limit" type="number" min="1" max="100000" step="0.01" :value="$setting->eligibility_wage_limit" addon="RM" class="tabular-nums" />
            </x-ui.field>
        </div>
    </section>

    <section class="card">
        <div class="card-header">
            <h2 class="card-title">{{ __('Overtime rates') }}</h2>
            <p class="card-desc">{{ __('Multiples of the hourly rate of pay. They can’t be lower than the Employment Act minimums.') }}</p>
        </div>
        <div class="card-content grid gap-5 md:grid-cols-4">
            <x-ui.field label="{{ __('Normal working day') }}" name="normal_day_rate" required help="{{ __('Minimum :rate×.', ['rate' => '1.5']) }}">
                <x-ui.input name="normal_day_rate" type="number" min="{{ \App\Models\OvertimeSetting::MIN_NORMAL_DAY_RATE }}" max="10" step="0.01" :value="$setting->normal_day_rate" addon="×" class="tabular-nums" required />
            </x-ui.field>
            <x-ui.field label="{{ __('Rest day') }}" name="rest_day_rate" required help="{{ __('Hours beyond normal hours. Minimum :rate×.', ['rate' => '2']) }}">
                <x-ui.input name="rest_day_rate" type="number" min="{{ \App\Models\OvertimeSetting::MIN_REST_DAY_RATE }}" max="10" step="0.01" :value="$setting->rest_day_rate" addon="×" class="tabular-nums" required />
            </x-ui.field>
            <x-ui.field label="{{ __('Public holiday') }}" name="public_holiday_rate" required help="{{ __('Hours beyond normal hours. Minimum :rate×.', ['rate' => '3']) }}">
                <x-ui.input name="public_holiday_rate" type="number" min="{{ \App\Models\OvertimeSetting::MIN_PUBLIC_HOLIDAY_RATE }}" max="10" step="0.01" :value="$setting->public_holiday_rate" addon="×" class="tabular-nums" required />
            </x-ui.field>
            <x-ui.field label="{{ __('Normal hours per day') }}" name="normal_hours_per_day" required help="{{ __('Divides the daily rate into the hourly rate.') }}">
                <x-ui.input name="normal_hours_per_day" type="number" min="1" max="10" step="0.5" :value="$setting->normal_hours_per_day" class="tabular-nums" required />
            </x-ui.field>
        </div>
    </section>

    <section class="card">
        <div class="card-header">
            <h2 class="card-title">{{ __('Limits') }}</h2>
        </div>
        <div class="card-content grid gap-5 md:grid-cols-4">
            <x-ui.field label="{{ __('Monthly limit') }}" name="monthly_limit_hours" required help="{{ __('The legal maximum is :hours hours a month.', ['hours' => \App\Models\OvertimeSetting::MAX_MONTHLY_HOURS]) }}">
                <x-ui.input name="monthly_limit_hours" type="number" min="1" max="{{ \App\Models\OvertimeSetting::MAX_MONTHLY_HOURS }}" step="1" :value="$setting->monthly_limit_hours" class="tabular-nums" required />
            </x-ui.field>
        </div>
    </section>

    <section class="card">
        <div class="card-header">
            <h2 class="card-title">{{ __('Fixed Employment Act rules') }}</h2>
        </div>
        <div class="card-content">
            <ul class="list-disc space-y-1.5 pl-5 text-sm text-muted-foreground">
                <li>{{ __('The hourly rate of pay is the monthly wage divided by 26, then by the normal hours per day.') }}</li>
                <li>{{ __('Work within normal hours on a rest day is paid half a day’s wages for up to half the normal hours, or a full day’s wages for more.') }}</li>
                <li>{{ __('Work within normal hours on a public holiday is paid two extra days’ wages.') }}</li>
                <li>{{ __('Overtime pay is not subject to EPF. Which pay items count towards the hourly rate is set with pay items.') }}</li>
                <li>
                    {{ __('Overtime after the cut-off day goes into the next month’s payroll; the cut-off day is set in') }}
                    <a href="{{ route('settings.payroll.general') }}" class="font-medium text-foreground underline">{{ __('Payroll General settings') }}</a>.
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
