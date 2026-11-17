@php
    $editing = $employee->exists;
    $title = $editing ? __('Edit :label', ['label' => $employee->user->name]) : __('Add staff');
    $date = fn (?\Carbon\CarbonInterface $value): ?string => $value?->toDateString();
    $isMyKad = old('identity_type', $employee->identity_type?->value ?? $employee->identity_type) === \App\Enums\IdentityType::MyKad->value;
    // Adding staff is split into steps (resources/js/admin/stepper.js); editing stays one page.
    $steps = $editing ? [] : [__('Account'), __('Personal details'), __('Employment'), __('Pay and statutory'), __('Review')];
    $step = fn (int $index): string => $editing ? '' : 'data-step="'.e($steps[$index]).'"';
@endphp
<x-layouts.admin :title="$title" group="{{ __('Staff') }}">
<div>
    <a href="{{ $editing ? route('staff.show', $employee) : route('staff.index') }}" class="mb-3 inline-flex items-center gap-1 text-sm text-muted-foreground hover:text-foreground">
        <x-ui.icon name="arrow-left" class="h-4 w-4" />{{ $editing ? __('Back to :name', ['name' => $employee->user->name]) : __('Back to Staff') }}
    </a>
    <x-ui.page-header :title="$title" description="{{ $editing ? __('Changes to the name and email also change how this person signs in.') : __('Creates the staff record and a sign-in account. They’ll get an email to set their password.') }}" />
</div>

<form method="POST" action="{{ $editing ? route('staff.update', $employee) : route('staff.store') }}" class="space-y-4" @unless ($editing) data-stepper @endunless>
    @csrf
    @if ($editing) @method('PUT') @endif

    @unless ($editing)
        <nav class="card p-4 sm:px-6" aria-label="{{ __('Steps') }}" data-stepper-nav hidden>
            <ol class="flex items-center gap-2">
                @foreach ($steps as $index => $label)
                    <li @class(['flex items-center gap-2', 'flex-1' => ! $loop->last])>
                        <button type="button" class="flex items-center gap-2 rounded-md p-1 text-left focus-ring" data-stepper-go="{{ $index }}">
                            <span class="step step-todo">{{ $index + 1 }}</span>
                            <span class="hidden text-sm font-medium lg:block">{{ $label }}</span>
                        </button>
                        @unless ($loop->last)<span class="h-px flex-1 bg-border" aria-hidden="true"></span>@endunless
                    </li>
                @endforeach
            </ol>
            <p class="mt-3 text-sm text-muted-foreground lg:sr-only" data-stepper-status aria-live="polite"></p>
        </nav>
    @endunless

    <section class="card" {!! $step(0) !!}>
        <div class="card-header">
            <h2 class="card-title">{{ __('Sign-in account') }}</h2>
            <p class="card-desc">{{ __('Fields marked * are required.') }}</p>
        </div>
        <div class="card-content grid gap-5 md:grid-cols-3">
            <x-ui.field label="{{ __('Full name') }}" name="name" required help="{{ __('As shown on the MyKad or passport.') }}">
                <x-ui.input name="name" :value="$employee->user?->name" maxlength="150" autocomplete="off" required />
            </x-ui.field>
            <x-ui.field label="{{ __('Work email') }}" name="email" required help="{{ __('Used to sign in.') }}">
                <x-ui.input name="email" type="email" :value="$employee->user?->email" autocomplete="off" required />
            </x-ui.field>
            <x-ui.field label="{{ __('Role') }}" name="role" required help="{{ __('What they can do in the system.') }}">
                <x-ui.select name="role" :value="$employee->user?->roles->first()?->name ?? \App\Enums\Role::Staff->value" :options="$roles" required />
            </x-ui.field>
        </div>
    </section>

    <section class="card" {!! $step(1) !!}>
        <div class="card-header">
            <h2 class="card-title">{{ __('Personal details') }}</h2>
        </div>
        <div class="card-content grid gap-5 md:grid-cols-3">
            <x-ui.field label="{{ __('Identity document') }}" name="identity_type" required>
                <x-ui.select name="identity_type" :value="$employee->identity_type?->value ?? $employee->identity_type" :options="$identityTypes" required />
            </x-ui.field>
            <x-ui.field label="{{ __('IC or passport number') }}" name="identity_no" required help="{{ __('For a MyKad, the date of birth is taken from the number.') }}">
                <x-ui.input name="identity_no" :value="$employee->identity_no && $employee->identity_type === \App\Enums\IdentityType::MyKad ? \App\Support\MyKad::format($employee->identity_no) : $employee->identity_no" class="font-mono" maxlength="20" autocomplete="off" spellcheck="false" required />
            </x-ui.field>
            <x-ui.field label="{{ __('Nationality') }}" name="nationality" required>
                <x-ui.select name="nationality" :value="$employee->nationality" :options="$countries" required />
            </x-ui.field>
            <x-ui.field label="{{ __('Date of birth') }}" name="date_of_birth" help="{{ __('Needed for passport holders only.') }}">
                <x-ui.input name="date_of_birth" type="date" :value="$isMyKad ? null : $date($employee->date_of_birth)" />
            </x-ui.field>
            <x-ui.field label="{{ __('Gender') }}" name="gender">
                <x-ui.select name="gender" :value="$employee->gender" :options="$genders" include-blank />
            </x-ui.field>
            <x-ui.field label="{{ __('Race') }}" name="race">
                <x-ui.select name="race" :value="$employee->race" :options="$races" include-blank />
            </x-ui.field>
            <x-ui.field label="{{ __('Marital status') }}" name="marital_status">
                <x-ui.select name="marital_status" :value="$employee->marital_status" :options="$maritalStatuses" include-blank />
            </x-ui.field>
            <x-ui.field label="{{ __('Number of children') }}" name="children_count" required help="{{ __('Used for income tax (PCB) reliefs.') }}">
                <x-ui.input name="children_count" type="number" min="0" max="20" step="1" :value="$employee->children_count" class="tabular-nums" required />
            </x-ui.field>
            <x-ui.field label="{{ __('Spouse is working') }}" name="spouse_working" help="{{ __('For married staff; affects PCB reliefs.') }}">
                <x-ui.select name="spouse_working" :value="is_null($employee->spouse_working) ? null : (int) $employee->spouse_working" :options="['1' => __('Yes'), '0' => __('No')]" include-blank />
            </x-ui.field>
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

    <section class="card" {!! $step(2) !!}>
        <div class="card-header">
            <h2 class="card-title">{{ __('Employment') }}</h2>
        </div>
        <div class="card-content grid gap-5 md:grid-cols-3">
            <x-ui.field label="{{ __('Staff number') }}" name="staff_no" required>
                <x-ui.input name="staff_no" :value="$employee->staff_no" class="font-mono uppercase" maxlength="20" autocomplete="off" spellcheck="false" required />
            </x-ui.field>
            <x-ui.field label="{{ __('Employment type') }}" name="employment_type" required>
                <x-ui.select name="employment_type" :value="$employee->employment_type?->value ?? $employee->employment_type" :options="$employmentTypes" required />
            </x-ui.field>
            <x-ui.field label="{{ __('Reports to') }}" name="reporting_manager">
                <x-ui.select name="reporting_manager" :value="$employee->reportingManager?->ulid" :options="$managers" include-blank :searchable="count($managers) > 15" />
            </x-ui.field>
            <x-ui.field label="{{ __('Department') }}" name="department">
                <x-ui.select name="department" :value="$employee->department" :options="$departments" include-blank />
            </x-ui.field>
            <x-ui.field label="{{ __('Position') }}" name="position">
                <x-ui.select name="position" :value="$employee->position" :options="$positions" include-blank />
            </x-ui.field>
            <div>
                <x-ui.switch name="is_manual_worker" :checked="$employee->is_manual_worker" label="{{ __('Manual worker') }}" hint="{{ __('Covered by the Employment Act whatever they earn.') }}" />
                @error('is_manual_worker')<p class="field-error">{{ $message }}</p>@enderror
            </div>
            <x-ui.field label="{{ __('Join date') }}" name="join_date" required>
                <x-ui.input name="join_date" type="date" :value="$date($employee->join_date)" required />
            </x-ui.field>
            <x-ui.field label="{{ __('Probation ends') }}" name="probation_end_date" help="{{ __('Leave blank if there’s no probation.') }}">
                <x-ui.input name="probation_end_date" type="date" :value="$date($employee->probation_end_date)" />
            </x-ui.field>
        </div>
    </section>

    <section class="card" {!! $step(3) !!}>
        <div class="card-header">
            <h2 class="card-title">{{ __('Pay and statutory') }}</h2>
        </div>
        <div class="card-content grid gap-5 md:grid-cols-3">
            <x-ui.field label="{{ __('Basic salary') }}" name="basic_salary" required help="{{ __('Monthly.') }}">
                <x-ui.input name="basic_salary" type="number" min="0" step="0.01" :value="$employee->basic_salary" addon="RM" class="tabular-nums" required />
            </x-ui.field>
            <x-ui.field label="{{ __('Bank') }}" name="bank">
                <x-ui.select name="bank" :value="$employee->bank" :options="$banks" include-blank />
            </x-ui.field>
            <x-ui.field label="{{ __('Account number') }}" name="bank_account_no">
                <x-ui.input name="bank_account_no" :value="$employee->bank_account_no" inputmode="numeric" maxlength="24" class="font-mono" autocomplete="off" />
            </x-ui.field>
            <x-ui.field label="{{ __('EPF number') }}" name="epf_no">
                <x-ui.input name="epf_no" :value="$employee->epf_no" maxlength="20" class="font-mono" autocomplete="off" />
            </x-ui.field>
            <x-ui.field label="{{ __('SOCSO number') }}" name="socso_no" help="{{ __('Usually the MyKad number.') }}">
                <x-ui.input name="socso_no" :value="$employee->socso_no" maxlength="20" class="font-mono" autocomplete="off" />
            </x-ui.field>
            <x-ui.field label="{{ __('Income tax number') }}" name="income_tax_no" help="{{ __('LHDN number, e.g. IG 12345678090.') }}">
                <x-ui.input name="income_tax_no" :value="$employee->income_tax_no" maxlength="20" class="font-mono" autocomplete="off" />
            </x-ui.field>
            @if ($electedRateAllowed || filled($employee->epf_elected_rate))
                <x-ui.field label="{{ __('Elected EPF rate') }}" name="epf_elected_rate" help="{{ __('Optional. Above the statutory :rate% employee rate.', ['rate' => $statutoryEmployeeRate]) }}">
                    <x-ui.input name="epf_elected_rate" type="number" min="0" max="30" step="0.01" :value="$employee->epf_elected_rate" addon="%" class="tabular-nums" />
                </x-ui.field>
            @endif
        </div>
    </section>

    @if ($editing)
        <section class="card">
            <div class="card-header">
                <h2 class="card-title">{{ __('Leaving') }}</h2>
                <p class="card-desc">{{ __('Staff can’t sign in after their last working day. Their record is kept.') }}</p>
            </div>
            <div class="card-content grid gap-5 md:grid-cols-3">
                @if ($employee->user_id === auth()->id())
                    <p class="text-sm text-muted-foreground md:col-span-3">{{ __('You can’t set a last working day on your own record.') }}</p>
                @else
                    <x-ui.field label="{{ __('Last working day') }}" name="last_working_date">
                        <x-ui.input name="last_working_date" type="date" :value="$date($employee->last_working_date)" />
                    </x-ui.field>
                    <x-ui.field label="{{ __('Reason for leaving') }}" name="leaving_reason">
                        <x-ui.select name="leaving_reason" :value="$employee->leaving_reason?->value" :options="$leavingReasons" include-blank />
                    </x-ui.field>
                @endif
            </div>
        </section>
    @endif

    @unless ($editing)
        <section class="card" {!! $step(4) !!} data-stepper-review hidden>
            <div class="card-header">
                <h2 class="card-title">{{ __('Review') }}</h2>
                <p class="card-desc">{{ __('Check the details before adding. Their password email is sent once you add them.') }}</p>
            </div>
            <div class="card-content space-y-6" data-stepper-summary></div>
        </section>
    @endunless

    <div class="flex flex-wrap items-center justify-end gap-2">
        @unless ($editing)
            <button type="button" class="btn btn-outline mr-auto" data-stepper-prev hidden><x-ui.icon name="arrow-left" />{{ __('Back') }}</button>
            <button type="button" class="btn btn-primary" data-stepper-next hidden>{{ __('Next') }}<x-ui.icon name="arrow-right" /></button>
        @endunless
        <button type="submit" class="btn btn-primary" data-stepper-submit>{{ $editing ? __('Save changes') : __('Add staff') }}</button>
    </div>
</form>
</x-layouts.admin>
