@php
    $editing = $employee->exists;
    $title = $editing ? __('Edit :label', ['label' => $employee->user->name]) : __('Add staff');
    $date = fn (?\Carbon\CarbonInterface $value): ?string => $value?->toDateString();
    // Staff are added with an NRIC for now; only records saved earlier with a passport keep one.
    $hasPassport = $editing && $employee->identity_type === \App\Enums\IdentityType::Passport;
    // Adding staff is split into steps (resources/js/admin/stepper.js); editing stays one page.
    // Adding asks for only some personal details (gender, phone); the rest and emergency contacts are filled in later.
    $steps = $editing ? [] : [__('Personal details'), __('Employment'), __('Pay and statutory'), __('Review')];
    $step = fn (int $index): string => $editing ? '' : 'data-step="'.e($steps[$index]).'"';
@endphp
<x-layouts.admin :title="$title" group="{{ __('Staff') }}">
<div>
    <a href="{{ $editing ? route('staff.show', $employee) : route('staff.index') }}" class="mb-3 inline-flex items-center gap-1 text-sm text-muted-foreground hover:text-foreground">
        <x-ui.icon name="arrow-left" class="h-4 w-4" />{{ $editing ? __('Back to :name', ['name' => $employee->user->name]) : __('Back to Staff') }}
    </a>
    <x-ui.page-header :title="$title" description="{{ $editing ? __('Changes to the name and email also change how this person signs in.') : __('Creates the staff record and a sign-in account. They fill in the rest of their personal details and emergency contacts later.') }}" />
</div>

<form method="POST" action="{{ $editing ? route('staff.update', $employee) : route('staff.store') }}" class="space-y-4" @unless ($editing) data-stepper @endunless>
    @csrf
    @if ($editing) @method('PUT') @endif

    @unless ($editing)
        <nav class="card p-4 sm:px-6" aria-label="{{ __('Steps') }}" data-stepper-nav hidden>
            {{-- Equal columns, so every step is the same distance apart whatever its label's length. --}}
            <ol class="grid" style="grid-template-columns: repeat({{ count($steps) }}, minmax(0, 1fr))">
                @foreach ($steps as $index => $label)
                    <li class="relative flex justify-center">
                        @unless ($loop->last)
                            <span class="absolute left-1/2 top-5 h-px w-full bg-border" aria-hidden="true"></span>
                        @endunless
                        <button type="button" class="relative flex min-w-0 flex-col items-center gap-1.5 rounded-md p-1 text-center focus-ring" data-stepper-go="{{ $index }}">
                            <span class="rounded-full bg-card"><span class="step step-todo">{{ $index + 1 }}</span></span>
                            <span class="hidden text-xs font-medium leading-tight sm:block">{{ $label }}</span>
                        </button>
                    </li>
                @endforeach
            </ol>
            <p class="mt-3 text-center text-sm text-muted-foreground sm:sr-only" data-stepper-status aria-live="polite"></p>
        </nav>
    @endunless

    <section class="card" {!! $step(0) !!}>
        <div class="card-header">
            <h2 class="card-title">{{ $editing ? __('Account and identity') : __('Personal details') }}</h2>
            <p class="card-desc">{{ __('The work email is used to sign in. The NRIC number is needed for EPF, SOCSO and income tax, and stops the same person being added twice. Fields marked * are required.') }}</p>
        </div>
        <div class="card-content grid gap-5 md:grid-cols-3">
            <x-ui.field label="{{ __('Official full name') }}" name="name" required help="{{ $hasPassport ? __('As shown on the passport.') : __('As shown on the NRIC.') }}">
                <x-ui.input name="name" :value="$employee->user?->name" class="uppercase" maxlength="150" autocomplete="off" required />
            </x-ui.field>
            <x-ui.field label="{{ __('Preferred name') }}" name="preferred_name" help="{{ __('What they like to be called.') }}">
                <x-ui.input name="preferred_name" :value="$employee->preferred_name" maxlength="100" autocomplete="off" />
            </x-ui.field>
            @if ($hasPassport)
                <x-ui.field label="{{ __('Passport number') }}" name="identity_no" required>
                    <x-ui.input name="identity_no" :value="$employee->identity_no" class="font-mono" maxlength="20" autocomplete="off" spellcheck="false" required />
                </x-ui.field>
            @else
                <x-ui.field label="{{ __('NRIC number') }}" name="identity_no" required help="{{ __('12 digits without dashes, e.g. 960109045295.') }}">
                    <x-ui.input name="identity_no" :value="$employee->identity_no" class="font-mono tabular-nums" inputmode="numeric" pattern="\d{12}" minlength="12" maxlength="12" placeholder="960109045295" title="{{ __('12 digits without dashes, e.g. 960109045295.') }}" autocomplete="off" spellcheck="false" required data-nric />
                </x-ui.field>
            @endif
            @unless ($editing)
                {{-- Shows the gender filled in from the NRIC (resources/js/admin/nric.js); the server saves it from the NRIC, not this field. --}}
                <x-ui.field label="{{ __('Gender') }}" name="gender" help="{{ __('Filled in from the NRIC number.') }}">
                    <x-ui.input name="gender_from_nric" id="f-gender" :value="$genders[\App\Support\MyKad::gender((string) old('identity_no')) ?? ''] ?? null" readonly tabindex="-1" data-nric-fill="gender" data-nric-labels="{{ json_encode(array_intersect_key($genders, array_flip(['male', 'female']))) }}" />
                </x-ui.field>
            @endunless
            <x-ui.field label="{{ __('Work email') }}" name="email" required help="{{ __('Used to sign in.') }}">
                <x-ui.input name="email" type="email" :value="$employee->user?->email" autocomplete="off" required />
            </x-ui.field>
            @unless ($editing)
                {{-- On the edit form phone and gender come from <x-staff.personal-details>. --}}
                <x-ui.field label="{{ __('Phone') }}" name="phone">
                    <x-ui.input name="phone" type="tel" :value="$employee->phone" maxlength="20" placeholder="012-345 6789" />
                </x-ui.field>
            @endunless
            @if ($hasPassport)
                <x-ui.field label="{{ __('Date of birth') }}" name="date_of_birth" required>
                    <x-ui.input name="date_of_birth" type="date" :value="$date($employee->date_of_birth)" required />
                </x-ui.field>
            @else
                <x-ui.field label="{{ __('Date of birth') }}" name="date_of_birth" help="{{ __('Filled in from the NRIC number.') }}">
                    <x-ui.input name="date_of_birth" type="date" :value="$date($employee->date_of_birth)" readonly tabindex="-1" data-nric-fill="date_of_birth" />
                </x-ui.field>
            @endif
        </div>
    </section>

    @if ($editing)
        <x-staff.personal-details :employee="$employee" :genders="$genders" :races="$races" :marital-statuses="$maritalStatuses" :states="$states" />
        <x-staff.emergency-contacts :employee="$employee" :relationships="$relationships" />
    @endif

    <section class="card" {!! $step(1) !!}>
        <div class="card-header">
            <h2 class="card-title">{{ __('Employment') }}</h2>
        </div>
        <div class="card-content grid gap-5 md:grid-cols-3">
            <x-ui.field label="{{ __('Employee number') }}" name="staff_no" required>
                <x-ui.input name="staff_no" :value="$employee->staff_no" class="font-mono uppercase" maxlength="20" autocomplete="off" spellcheck="false" required />
            </x-ui.field>
            <x-ui.field label="{{ __('Start date') }}" name="join_date" required>
                <x-ui.input name="join_date" type="date" :value="$date($employee->join_date)" required />
            </x-ui.field>
            <x-ui.field label="{{ __('Reports to') }}" name="reporting_manager">
                <x-ui.select name="reporting_manager" :value="$employee->reportingManager?->ulid" :options="$managers" include-blank :searchable="count($managers) > 15" />
            </x-ui.field>
            <x-ui.field label="{{ __('Position') }}" name="position">
                <x-ui.select name="position" :value="$employee->position" :options="$positions" include-blank />
            </x-ui.field>
            <x-ui.field label="{{ __('Department') }}" name="department">
                <x-ui.select name="department" :value="$employee->department" :options="$departments" include-blank />
            </x-ui.field>
            <x-ui.field label="{{ __('Subsidiary') }}" name="subsidiary" help="{{ $subsidiaries === [] ? __('Add subsidiaries in Administrations → Data Dictionary.') : null }}">
                <x-ui.select name="subsidiary" :value="$employee->subsidiary" :options="$subsidiaries" include-blank />
            </x-ui.field>
            <x-ui.field label="{{ __('Employment type') }}" name="employment_type" required>
                <x-ui.select name="employment_type" :value="$employee->employment_type?->value ?? $employee->employment_type" :options="$employmentTypes" required />
            </x-ui.field>
            <x-ui.field label="{{ __('Role') }}" name="role" required help="{{ __('What they can do in the system.') }}">
                <x-ui.select name="role" :value="$employee->user?->roles->first()?->name ?? \App\Enums\Role::Staff->value" :options="$roles" required />
            </x-ui.field>
            @if ($editing)
                <x-ui.field label="{{ __('Probation ends') }}" name="probation_end_date" help="{{ __('Leave blank if there’s no probation.') }}">
                    <x-ui.input name="probation_end_date" type="date" :value="$date($employee->probation_end_date)" />
                </x-ui.field>
            @endif
        </div>
    </section>

    <section class="card" {!! $step(2) !!}>
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
        <section class="card" {!! $step(3) !!} data-stepper-review>
            <div class="card-header">
                <h2 class="card-title">{{ __('Review') }}</h2>
                <p class="card-desc">{{ __('Check the details, then choose whether to invite them now.') }}</p>
            </div>
            <div class="card-content space-y-6" data-stepper-summary></div>
            <div class="card-content border-t pt-5">
                <input type="hidden" name="send_invitation" value="0">
                <label class="flex items-start gap-3 text-sm">
                    <input type="checkbox" name="send_invitation" value="1" class="mt-0.5" @checked(old('send_invitation', '1') === '1')>
                    <span>
                        <span class="block font-medium">{{ __('Send invitation email to employee?') }}</span>
                        <span class="block text-muted-foreground">{{ __('They get a link to set their password and sign in. Leave unticked to invite them later from their profile.') }}</span>
                    </span>
                </label>
                @error('send_invitation')<p class="field-error">{{ $message }}</p>@enderror
            </div>
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
