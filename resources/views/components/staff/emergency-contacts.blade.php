{{-- Emergency contact rows, shared by the staff edit form and My profile. Untouched rows are ignored when saving. --}}
@props(['employee', 'relationships'])
@php
    $contacts = old('contacts', $employee->exists ? $employee->emergencyContacts->map(fn ($contact) => $contact->only(['name', 'relationship', 'phone']))->all() : []);
@endphp

<section {{ $attributes->class('card') }}>
    <div class="card-header">
        <h2 class="card-title">{{ __('Emergency contacts') }}</h2>
        <p class="card-desc">{{ __('Up to :count people to call, in order. Leave unused rows blank.', ['count' => \App\Models\Employee::MAX_EMERGENCY_CONTACTS]) }}</p>
    </div>
    <div class="card-content space-y-4">
        @error('contacts')<x-ui.alert variant="danger">{{ $message }}</x-ui.alert>@enderror
        <div class="hidden gap-3 text-sm font-medium sm:grid sm:grid-cols-[2rem_minmax(0,1fr)_12rem_12rem]" aria-hidden="true">
            <span></span><span>{{ __('Name') }}</span><span>{{ __('Relationship') }}</span><span>{{ __('Phone') }}</span>
        </div>
        @for ($row = 0; $row < \App\Models\Employee::MAX_EMERGENCY_CONTACTS; $row++)
            @php $contact = $contacts[$row] ?? []; @endphp
            <fieldset class="grid items-start gap-3 sm:grid-cols-[2rem_minmax(0,1fr)_12rem_12rem]">
                <legend class="sr-only">{{ __('Contact :n', ['n' => $row + 1]) }}</legend>
                <span class="pt-2 text-sm tabular-nums text-muted-foreground" aria-hidden="true">{{ $row + 1 }}.</span>
                <div>
                    <label for="f-contacts-{{ $row }}-name" class="label sm:sr-only">{{ __('Contact :n: name', ['n' => $row + 1]) }}</label>
                    <x-ui.input id="f-contacts-{{ $row }}-name" name="contacts[{{ $row }}][name]" :value="$contact['name'] ?? null" maxlength="150" :invalid="$errors->has('contacts.'.$row.'.name')" />
                    @error('contacts.'.$row.'.name')<p class="field-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="f-contacts-{{ $row }}-relationship" class="label sm:sr-only">{{ __('Contact :n: relationship', ['n' => $row + 1]) }}</label>
                    <x-ui.select id="f-contacts-{{ $row }}-relationship" name="contacts[{{ $row }}][relationship]" :value="$contact['relationship'] ?? null" :options="$relationships" include-blank :invalid="$errors->has('contacts.'.$row.'.relationship')" />
                    @error('contacts.'.$row.'.relationship')<p class="field-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="f-contacts-{{ $row }}-phone" class="label sm:sr-only">{{ __('Contact :n: phone', ['n' => $row + 1]) }}</label>
                    <x-ui.input id="f-contacts-{{ $row }}-phone" name="contacts[{{ $row }}][phone]" type="tel" :value="$contact['phone'] ?? null" maxlength="20" placeholder="012-345 6789" :invalid="$errors->has('contacts.'.$row.'.phone')" />
                    @error('contacts.'.$row.'.phone')<p class="field-error">{{ $message }}</p>@enderror
                </div>
            </fieldset>
        @endfor
    </div>
</section>
