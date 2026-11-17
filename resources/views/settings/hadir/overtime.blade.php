<x-layouts.admin title="{{ __('Hadir Overtime Settings') }}" group="{{ __('Settings') }}">
<x-ui.page-header title="{{ __('Hadir Overtime Settings') }}" description="{{ __('How overtime is recorded, counted and approved. Who is paid overtime and at what rate is set in Payroll Overtime settings.') }}" />

@if (session('status'))
    <x-ui.alert variant="success" role="status">{{ session('status') }}</x-ui.alert>
@endif

<form method="POST" action="{{ route('settings.hadir.overtime.update') }}" class="space-y-4">
    @csrf
    @method('PUT')

    <section class="card">
        <div class="card-header">
            <h2 class="card-title">{{ __('Recording overtime') }}</h2>
        </div>
        <div class="card-content grid gap-5 md:grid-cols-2">
            <x-ui.field label="{{ __('How overtime is recorded') }}" name="capture_method" required help="{{ __('From clock-outs, time worked past the end of the work day becomes an overtime claim for approval.') }}">
                <x-ui.select name="capture_method" :value="$setting->capture_method->value" :options="$captureMethods" required />
            </x-ui.field>
            <div>
                <x-ui.switch name="allow_on_rest_days" :checked="$setting->allow_on_rest_days" label="{{ __('Overtime on rest days and public holidays') }}" hint="{{ __('When off, overtime can only be recorded on working days.') }}" />
                @error('allow_on_rest_days')<p class="field-error">{{ $message }}</p>@enderror
            </div>
        </div>
    </section>

    <section class="card">
        <div class="card-header">
            <h2 class="card-title">{{ __('Counting overtime') }}</h2>
        </div>
        <div class="card-content grid gap-5 md:grid-cols-2">
            <x-ui.field label="{{ __('Minimum overtime') }}" name="minimum_minutes" required help="{{ __('Minutes. Shorter overtime in a day isn’t counted. 0 counts every minute.') }}">
                <x-ui.input name="minimum_minutes" type="number" min="0" max="120" step="5" :value="$setting->minimum_minutes" class="tabular-nums" required />
            </x-ui.field>
            <x-ui.field label="{{ __('Rounding') }}" name="rounding" required help="{{ __('Always rounded down, so staff are paid only for time worked.') }}">
                <x-ui.select name="rounding" :value="$setting->rounding->value" :options="$roundings" required />
            </x-ui.field>
        </div>
    </section>

    <section class="card">
        <div class="card-header">
            <h2 class="card-title">{{ __('Approval') }}</h2>
        </div>
        <div class="card-content grid gap-5 md:grid-cols-2">
            <x-ui.field label="{{ __('Approved by') }}" name="approver_role" required help="{{ __('Anyone with this role can approve overtime.') }}">
                <x-ui.select name="approver_role" :value="$setting->approver_role->value" :options="$roles" required />
            </x-ui.field>
        </div>
    </section>

    <section class="card">
        <div class="card-header">
            <h2 class="card-title">{{ __('Fixed rules') }}</h2>
        </div>
        <div class="card-content">
            <ul class="list-disc space-y-1.5 pl-5 text-sm text-muted-foreground">
                <li>{{ __('Only approved overtime is paid.') }}</li>
                <li>{{ __('Overtime beyond the monthly limit can’t be approved.') }}</li>
                <li>
                    {{ __('Eligibility, rates and the monthly limit are set in') }}
                    <a href="{{ route('settings.payroll.overtime') }}" class="font-medium text-foreground underline">{{ __('Payroll Overtime settings') }}</a>.
                </li>
                <li>
                    {{ __('The end of the work day is set in') }}
                    <a href="{{ route('settings.hadir.general') }}" class="font-medium text-foreground underline">{{ __('Attendance Settings') }}</a>.
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
