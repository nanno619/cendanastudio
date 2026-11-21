{{-- Personal details fields, shared by the staff edit form and My profile. --}}
@props(['employee', 'genders', 'races', 'maritalStatuses', 'states', 'description' => null])

@php
    // NRIC holders' gender follows the number (MyKad::gender()), so it's shown, not chosen; on the HR edit
    // form it updates as the NRIC number is changed (resources/js/admin/nric.js).
    $isNricHolder = $employee->identity_type === \App\Enums\IdentityType::MyKad;
    $childrenStatuses = \App\Models\Employee::MARITAL_STATUSES_WITH_CHILDREN;
@endphp

<section {{ $attributes->class('card') }}>
    <div class="card-header">
        <h2 class="card-title">{{ __('Personal details') }}</h2>
        @if ($description)
            <p class="card-desc">{{ $description }}</p>
        @endif
    </div>
    <div class="card-content grid gap-5 md:grid-cols-3">
        @if ($isNricHolder)
            <x-ui.field label="{{ __('Gender') }}" name="gender" help="{{ __('Filled in from the NRIC number.') }}">
                <x-ui.input name="gender_from_nric" id="f-gender" :value="$genders[\App\Support\MyKad::gender($employee->identity_no) ?? ''] ?? null" readonly tabindex="-1" data-nric-fill="gender" data-nric-labels="{{ json_encode(array_intersect_key($genders, array_flip(['male', 'female']))) }}" />
            </x-ui.field>
        @else
            <x-ui.field label="{{ __('Gender') }}" name="gender">
                <x-ui.select name="gender" :value="$employee->gender" :options="$genders" include-blank />
            </x-ui.field>
        @endif
        <x-ui.field label="{{ __('Race') }}" name="race">
            <x-ui.select name="race" :value="$employee->race" :options="$races" include-blank />
        </x-ui.field>
        <x-ui.field label="{{ __('Marital status') }}" name="marital_status">
            <x-ui.select name="marital_status" :value="$employee->marital_status" :options="$maritalStatuses" include-blank />
        </x-ui.field>
        {{-- Children count only for married, divorced or widowed staff (PCB); others are saved with 0. --}}
        <div data-show-when="marital_status" data-show-values="{{ implode(',', $childrenStatuses) }}" @unless (in_array(old('marital_status', $employee->marital_status), $childrenStatuses, true)) hidden @endunless>
            <x-ui.field label="{{ __('Number of children') }}" name="children_count" required help="{{ __('Used for income tax (PCB) reliefs.') }}">
                <x-ui.input name="children_count" type="number" min="0" max="20" step="1" :value="$employee->children_count" class="tabular-nums" required />
            </x-ui.field>
        </div>
        {{-- Married staff only: it decides PCB category 2 (spouse not working) or 3. --}}
        <div data-show-when="marital_status" data-show-values="married" @unless (old('marital_status', $employee->marital_status) === 'married') hidden @endunless>
            <x-ui.field label="{{ __('Spouse is working') }}" name="spouse_working" help="{{ __('Affects PCB reliefs.') }}">
                <x-ui.select name="spouse_working" :value="is_null($employee->spouse_working) ? null : (int) $employee->spouse_working" :options="['1' => __('Yes'), '0' => __('No')]" include-blank />
            </x-ui.field>
        </div>
        <x-ui.field label="{{ __('Phone') }}" name="phone">
            <x-ui.input name="phone" type="tel" :value="$employee->phone" maxlength="20" placeholder="012-345 6789" />
        </x-ui.field>
        <x-ui.field label="{{ __('Personal email') }}" name="personal_email">
            <x-ui.input name="personal_email" type="email" :value="$employee->personal_email" />
        </x-ui.field>
    </div>
    <div class="card-content grid gap-5 border-t pt-5 md:grid-cols-3">
        <x-ui.field label="{{ __('Address line 1') }}" name="address_line_1" class="md:col-span-3">
            <x-ui.input name="address_line_1" :value="$employee->address_line_1" maxlength="255" />
        </x-ui.field>
        <x-ui.field label="{{ __('Address line 2') }}" name="address_line_2" class="md:col-span-3">
            <x-ui.input name="address_line_2" :value="$employee->address_line_2" maxlength="255" />
        </x-ui.field>
        <x-ui.field label="{{ __('Postcode') }}" name="postcode">
            <x-ui.input name="postcode" :value="$employee->postcode" inputmode="numeric" maxlength="5" class="tabular-nums" />
        </x-ui.field>
        <x-ui.field label="{{ __('City') }}" name="city">
            <x-ui.input name="city" :value="$employee->city" maxlength="100" />
        </x-ui.field>
        <x-ui.field label="{{ __('State') }}" name="state">
            <x-ui.select name="state" :value="$employee->state" :options="$states" include-blank />
        </x-ui.field>
    </div>
</section>
