@php
    $selectedRoles = collect(old('allowed_roles', $setting->allowed_roles->map(fn ($role) => $role->value)->all()));
@endphp
<x-layouts.admin title="{{ __('Visit Settings') }}" group="{{ __('Settings') }}">
<x-ui.page-header title="{{ __('Visit Settings') }}" description="{{ __('Rules for work away from the office, such as client and site visits. Staff check in and out at the place they visit.') }}" />

@if (session('status'))
    <x-ui.alert variant="success" role="status">{{ session('status') }}</x-ui.alert>
@endif

<form method="POST" action="{{ route('settings.hadir.visits.update') }}" class="space-y-4">
    @csrf
    @method('PUT')

    <section class="card">
        <div class="card-header">
            <h2 class="card-title">{{ __('Who can log visits') }}</h2>
        </div>
        <div class="card-content space-y-5">
            <div class="grid gap-5 md:grid-cols-2">
                <div>
                    <x-ui.switch name="visits_enabled" :checked="$setting->visits_enabled" label="{{ __('Visits') }}" hint="{{ __('When off, nobody can log a visit.') }}" />
                    @error('visits_enabled')<p class="field-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <x-ui.switch name="counts_as_attendance" :checked="$setting->counts_as_attendance" label="{{ __('Counts as attendance') }}" hint="{{ __('Checking in at a visit counts as clocking in, so staff who go straight to a client aren’t marked absent.') }}" />
                    @error('counts_as_attendance')<p class="field-error">{{ $message }}</p>@enderror
                </div>
            </div>

            <fieldset class="space-y-2">
                <legend class="text-sm font-medium">{{ __('Roles') }}</legend>
                <p class="field-help">{{ __('Super Admin can always log visits.') }}</p>
                <div class="grid gap-3 sm:grid-cols-3 lg:grid-cols-5">
                    @foreach ($roles as $role)
                        @continue($role === \App\Enums\Role::SuperAdmin)
                        <label class="flex cursor-pointer items-center gap-3 rounded-md border p-3 has-[:checked]:border-primary has-[:checked]:bg-soft">
                            <input type="checkbox" name="allowed_roles[]" value="{{ $role->value }}" class="mt-0.5" @checked($selectedRoles->contains($role->value))>
                            <span class="text-sm font-medium">{{ __($role->value) }}</span>
                        </label>
                    @endforeach
                </div>
                @error('allowed_roles')<p class="field-error">{{ $message }}</p>@enderror
                @error('allowed_roles.*')<p class="field-error">{{ $message }}</p>@enderror
            </fieldset>
        </div>
    </section>

    <section class="card">
        <div class="card-header">
            <h2 class="card-title">{{ __('What a visit records') }}</h2>
            <p class="card-desc">{{ __('The location is always recorded at check-in and check-out.') }}</p>
        </div>
        <div class="card-content grid gap-5 md:grid-cols-3">
            <div>
                <x-ui.switch name="require_client" :checked="$setting->require_client" label="{{ __('Client or site name') }}" hint="{{ __('Required at check-in.') }}" />
                @error('require_client')<p class="field-error">{{ $message }}</p>@enderror
            </div>
            <div>
                <x-ui.switch name="require_purpose" :checked="$setting->require_purpose" label="{{ __('Purpose') }}" hint="{{ __('A short note on why the visit was made.') }}" />
                @error('require_purpose')<p class="field-error">{{ $message }}</p>@enderror
            </div>
            <div>
                <x-ui.switch name="require_photo" :checked="$setting->require_photo" label="{{ __('Photo at check-in') }}" hint="{{ __('A photo of the site or meeting as proof of the visit.') }}" />
                @error('require_photo')<p class="field-error">{{ $message }}</p>@enderror
            </div>
        </div>
    </section>

    <section class="card">
        <div class="card-header">
            <h2 class="card-title">{{ __('Approval and claims') }}</h2>
        </div>
        <div class="card-content grid gap-5 md:grid-cols-3">
            <div>
                <x-ui.switch name="requires_approval" :checked="$setting->requires_approval" label="{{ __('Visits need approval') }}" hint="{{ __('Visits are confirmed by an approver before they count as attendance.') }}" />
                @error('requires_approval')<p class="field-error">{{ $message }}</p>@enderror
            </div>
            <x-ui.field label="{{ __('Approved by') }}" name="approver_role" help="{{ __('Anyone with this role can approve. Used only when approval is on.') }}">
                <x-ui.select name="approver_role" :value="$setting->approver_role?->value" :options="$roleOptions" include-blank />
            </x-ui.field>
            <div>
                <x-ui.switch name="suggest_mileage_claim" :checked="$setting->suggest_mileage_claim" label="{{ __('Offer a mileage claim') }}" hint="{{ __('After a visit, staff can turn the distance into a mileage claim.') }}" />
                @error('suggest_mileage_claim')<p class="field-error">{{ $message }}</p>@enderror
                @unless ($mileageEnabled)
                    <p class="field-help mt-2">
                        {{ __('Mileage claims are off in') }}
                        <a href="{{ route('settings.claim.general') }}" class="font-medium text-foreground underline">{{ __('Claim Settings') }}</a>. {{ __('This has no effect until they’re on.') }}
                    </p>
                @endunless
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
