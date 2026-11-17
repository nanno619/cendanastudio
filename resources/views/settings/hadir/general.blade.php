<x-layouts.admin title="{{ __('Attendance Settings') }}" group="{{ __('Settings') }}">
<x-ui.page-header title="{{ __('Attendance Settings') }}" description="{{ __('Company-wide Hadir rules: office hours, lateness and where staff can clock in. Overtime and visits have their own pages.') }}" />

@if (session('status'))
    <x-ui.alert variant="success" role="status">{{ session('status') }}</x-ui.alert>
@endif

<form method="POST" action="{{ route('settings.hadir.general.update') }}" class="space-y-4">
    @csrf
    @method('PUT')

    <section class="card">
        <div class="card-header">
            <h2 class="card-title">{{ __('Office hours') }}</h2>
            <p class="card-desc">{{ __('Times are in :timezone.', ['timezone' => $timezone]) }}</p>
        </div>
        <div class="card-content grid gap-5 md:grid-cols-4">
            <x-ui.field label="{{ __('Work starts') }}" name="work_start_time" required>
                <x-ui.input name="work_start_time" type="time" :value="$setting->work_start_time" class="tabular-nums" required />
            </x-ui.field>
            <x-ui.field label="{{ __('Work ends') }}" name="work_end_time" required>
                <x-ui.input name="work_end_time" type="time" :value="$setting->work_end_time" class="tabular-nums" required />
            </x-ui.field>
            <x-ui.field label="{{ __('Break') }}" name="break_minutes" required help="{{ __('Unpaid minutes inside the work day.') }}">
                <x-ui.input name="break_minutes" type="number" min="0" max="180" step="5" :value="$setting->break_minutes" class="tabular-nums" required />
            </x-ui.field>
            <x-ui.field label="{{ __('Late after') }}" name="late_grace_minutes" required help="{{ __('Minutes after the start time before a clock-in counts as late.') }}">
                <x-ui.input name="late_grace_minutes" type="number" min="0" max="60" step="1" :value="$setting->late_grace_minutes" class="tabular-nums" required />
            </x-ui.field>
        </div>
        <div class="card-content grid gap-5 border-t pt-5 md:grid-cols-2">
            <x-ui.field label="{{ __('When staff forget to clock out') }}" name="missed_clock_out" required>
                <x-ui.select name="missed_clock_out" :value="$setting->missed_clock_out->value" :options="$missedClockOutOptions" required />
            </x-ui.field>
        </div>
    </section>

    <section class="card">
        <div class="card-header">
            <h2 class="card-title">{{ __('Where staff can clock in') }}</h2>
        </div>
        <div class="card-content space-y-5">
            <div class="grid gap-5 md:grid-cols-3">
                <div>
                    <x-ui.switch name="record_location" :checked="$setting->record_location" label="{{ __('Record location') }}" hint="{{ __('Clock-ins and clock-outs save the device’s location. Staff are told before it’s recorded.') }}" />
                    @error('record_location')<p class="field-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <x-ui.switch name="geofence_enabled" :checked="$setting->geofence_enabled" label="{{ __('Geofence') }}" hint="{{ __('Staff can only clock in within the radius of the office. Work elsewhere is logged as a visit.') }}" />
                    @error('geofence_enabled')<p class="field-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <x-ui.switch name="require_selfie" :checked="$setting->require_selfie" label="{{ __('Selfie when clocking in') }}" hint="{{ __('A photo is taken with each clock-in to deter buddy punching.') }}" />
                    @error('require_selfie')<p class="field-error">{{ $message }}</p>@enderror
                </div>
            </div>
            <div class="grid gap-5 md:grid-cols-3">
                <x-ui.field label="{{ __('Office latitude') }}" name="office_latitude" help="{{ __('Used only while the geofence is on. Copy it from a map, e.g. 3.157800.') }}">
                    <x-ui.input name="office_latitude" type="number" min="-90" max="90" step="0.000001" :value="$setting->office_latitude" class="tabular-nums" />
                </x-ui.field>
                <x-ui.field label="{{ __('Office longitude') }}" name="office_longitude" help="{{ __('For example 101.711900.') }}">
                    <x-ui.input name="office_longitude" type="number" min="-180" max="180" step="0.000001" :value="$setting->office_longitude" class="tabular-nums" />
                </x-ui.field>
                <x-ui.field label="{{ __('Radius') }}" name="geofence_radius_m" help="{{ __('Metres around the office, 50 to 5,000.') }}">
                    <x-ui.input name="geofence_radius_m" type="number" min="50" max="5000" step="10" :value="$setting->geofence_radius_m" class="tabular-nums" />
                </x-ui.field>
            </div>
        </div>
    </section>

    <section class="card">
        <div class="card-header">
            <h2 class="card-title">{{ __('Fixed rules') }}</h2>
        </div>
        <div class="card-content">
            <ul class="list-disc space-y-1.5 pl-5 text-sm text-muted-foreground">
                <li>
                    {{ __('Working days and public holidays follow') }}
                    <a href="{{ route('settings.leave.general') }}" class="font-medium text-foreground underline">{{ __('Leave General settings') }}</a>.
                </li>
                <li>{{ __('The Employment Act limits normal hours to 8 a day and 45 a week; time beyond them is overtime.') }}</li>
                <li>{{ __('Location and selfies are personal data under the PDPA. They’re used only for attendance and kept with attendance records.') }}</li>
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
