<x-layouts.admin title="{{ __('Leave Settings') }}" group="{{ __('Settings') }}">
<x-ui.page-header title="{{ __('Leave Settings') }}" description="{{ __('Company-wide leave rules: the leave year, working days, how leave is applied for, balances and what staff can see.') }}" />

@if (session('status'))
    <x-ui.alert variant="success" role="status">{{ session('status') }}</x-ui.alert>
@endif

@php
    $weekdayOptions = collect($weekdays)->mapWithKeys(fn ($day) => [$day->value => __($day->label())])->all();
    $selectedDays = collect(old('working_days', $setting->working_days->map(fn ($day) => $day->value)->all()))->map(fn ($day) => (string) $day);
@endphp

<form method="POST" action="{{ route('settings.leave.general.update') }}" class="space-y-4">
    @csrf
    @method('PUT')

    <section class="card">
        <div class="card-header">
            <h2 class="card-title">{{ __('Leave year and working week') }}</h2>
            <p class="card-desc">{{ __('Leave is counted in working days only. Rest days and public holidays inside a leave period are not deducted.') }}</p>
        </div>
        <div class="card-content space-y-5">
            <div class="grid gap-5 md:grid-cols-3">
                <x-ui.field label="{{ __('Leave year starts in') }}" name="leave_year_start_month" required help="{{ __('Balances reset on the 1st of this month.') }}">
                    <x-ui.select name="leave_year_start_month" :value="$setting->leave_year_start_month" :options="$months" required />
                </x-ui.field>
                <x-ui.field label="{{ __('Half working day') }}" name="half_working_day" help="{{ __('Optional, e.g. Saturday when staff work half a day.') }}">
                    <x-ui.select name="half_working_day" :value="$setting->half_working_day?->value" :options="$weekdayOptions" placeholder="{{ __('None') }}" />
                </x-ui.field>
                <x-ui.field label="{{ __('Public holidays follow') }}" name="public_holiday_state" help="{{ __('State holidays differ, e.g. Kedah, Kelantan and Terengganu.') }}">
                    <x-ui.select name="public_holiday_state" :value="$holidayState" :options="$states" include-blank />
                </x-ui.field>
            </div>

            <fieldset class="space-y-2">
                <legend class="text-sm font-medium">{{ __('Working days') }} <span class="text-destructive">*</span></legend>
                <div class="grid gap-3 sm:grid-cols-4 lg:grid-cols-7">
                    @foreach ($weekdays as $day)
                        <label class="flex cursor-pointer items-center gap-3 rounded-md border p-3 has-[:checked]:border-primary has-[:checked]:bg-soft">
                            <input type="checkbox" name="working_days[]" value="{{ $day->value }}" class="mt-0.5" @checked($selectedDays->contains((string) $day->value))>
                            <span class="text-sm font-medium">{{ __($day->label()) }}</span>
                        </label>
                    @endforeach
                </div>
                @error('working_days')<p class="field-error">{{ $message }}</p>@enderror
                @error('working_days.*')<p class="field-error">{{ $message }}</p>@enderror
            </fieldset>
        </div>
    </section>

    <section class="card">
        <div class="card-header">
            <h2 class="card-title">{{ __('Applying for leave') }}</h2>
        </div>
        <div class="card-content grid gap-5 md:grid-cols-2">
            <x-ui.field label="{{ __('Annual leave notice') }}" name="annual_leave_notice_days" required help="{{ __('Days in advance annual leave must be applied for. 0 means no notice needed.') }}">
                <x-ui.input name="annual_leave_notice_days" type="number" min="0" max="90" step="1" :value="$setting->annual_leave_notice_days" addon="{{ __('days') }}" class="tabular-nums" required />
            </x-ui.field>
            <x-ui.field label="{{ __('Backdated applications') }}" name="backdate_days" required help="{{ __('Days after the leave date staff can still apply, e.g. for sick leave. 0 means not allowed.') }}">
                <x-ui.input name="backdate_days" type="number" min="0" max="90" step="1" :value="$setting->backdate_days" addon="{{ __('days') }}" class="tabular-nums" required />
            </x-ui.field>
            <div>
                <x-ui.switch name="allow_half_day" :checked="$setting->allow_half_day" label="{{ __('Allow half-day leave') }}" hint="{{ __('Staff can apply for the morning or the afternoon only.') }}" />
                @error('allow_half_day')<p class="field-error">{{ $message }}</p>@enderror
            </div>
            <div>
                <x-ui.switch name="allow_leave_during_probation" :checked="$setting->allow_leave_during_probation" label="{{ __('Annual leave during probation') }}" hint="{{ __('When off, staff still earn annual leave and take it after probation. Sick and statutory leave are always allowed.') }}" />
                @error('allow_leave_during_probation')<p class="field-error">{{ $message }}</p>@enderror
            </div>
        </div>
    </section>

    <section class="card">
        <div class="card-header">
            <h2 class="card-title">{{ __('Balance and proration') }}</h2>
        </div>
        <div class="card-content grid gap-5 md:grid-cols-2">
            <div>
                <x-ui.switch name="allow_negative_balance" :checked="$setting->allow_negative_balance" label="{{ __('Allow negative balance') }}" hint="{{ __('Staff can borrow from next year’s entitlement.') }}" />
                @error('allow_negative_balance')<p class="field-error">{{ $message }}</p>@enderror
            </div>
            <x-ui.field label="{{ __('Days staff can borrow') }}" name="max_negative_days" help="{{ __('Used only when negative balance is on. Whole or half days, up to 30.') }}">
                <x-ui.input name="max_negative_days" type="number" min="0.5" max="30" step="0.5" :value="$setting->max_negative_days" addon="{{ __('days') }}" class="tabular-nums" />
            </x-ui.field>
            <div>
                <x-ui.switch name="prorate_entitlement" :checked="$setting->prorate_entitlement" label="{{ __('Prorate for joiners and leavers') }}" hint="{{ __('Entitlement follows completed months of service in the leave year.') }}" />
                @error('prorate_entitlement')<p class="field-error">{{ $message }}</p>@enderror
            </div>
            <x-ui.field label="{{ __('Rounding') }}" name="proration_rounding" required help="{{ __('Employment Act: under half a day is dropped, half a day or more counts as a full day.') }}">
                <x-ui.select name="proration_rounding" :value="$setting->proration_rounding->value" :options="$roundings" required />
            </x-ui.field>
        </div>
    </section>

    <section class="card">
        <div class="card-header">
            <h2 class="card-title">{{ __('Resignation') }}</h2>
        </div>
        <div class="card-content grid gap-5 md:grid-cols-2">
            <x-ui.field label="{{ __('Unused leave on resignation') }}" name="resignation_unused_leave" required>
                <x-ui.select name="resignation_unused_leave" :value="$setting->resignation_unused_leave->value" :options="$treatments" required />
            </x-ui.field>
        </div>
    </section>

    <section class="card">
        <div class="card-header">
            <h2 class="card-title">{{ __('Visibility and notifications') }}</h2>
        </div>
        <div class="card-content grid gap-5 md:grid-cols-2">
            <div>
                <x-ui.switch name="share_leave_calendar" :checked="$setting->share_leave_calendar" label="{{ __('Shared leave calendar') }}" hint="{{ __('Staff see who is on leave, never the leave type. Super Admin and Admin always see full details.') }}" />
                @error('share_leave_calendar')<p class="field-error">{{ $message }}</p>@enderror
            </div>
            <x-ui.field label="{{ __('HR copy email') }}" name="hr_notification_email" help="{{ __('Optional. Copied on every leave application and decision.') }}">
                <x-ui.input name="hr_notification_email" type="email" :value="$setting->hr_notification_email" autocomplete="email" />
            </x-ui.field>
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
