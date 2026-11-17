@use('App\Enums\IdentityType')
@use('App\Enums\LookupCategory')
@use('App\Support\Countries')
@use('App\Support\MyKad')
@php
    $status = $employee->status();
    $label = fn (LookupCategory $category, ?string $code): string => $code === null ? '—' : ($labels[$category->value][$code] ?? $code);
    $value = fn ($value): string => filled($value) ? (string) $value : '—';
    $address = collect([$employee->address_line_1, $employee->address_line_2, trim($employee->postcode.' '.$employee->city), $employee->state ? $label(LookupCategory::MalaysianStates, $employee->state) : null])->filter()->implode(', ');
    $sections = [
        __('Personal details') => [
            __('IC or passport number') => $employee->identity_type === IdentityType::MyKad ? MyKad::format($employee->identity_no) : $employee->identity_no.' ('.__('Passport').')',
            __('Nationality') => Countries::name($employee->nationality),
            __('Date of birth') => $employee->date_of_birth->translatedFormat('j F Y').' · '.trans_choice(':count year old|:count years old', $employee->age()),
            __('Gender') => $label(LookupCategory::Gender, $employee->gender),
            __('Race') => $label(LookupCategory::Race, $employee->race),
            __('Marital status') => $label(LookupCategory::MaritalStatus, $employee->marital_status),
            __('Number of children') => (string) $employee->children_count,
            __('Spouse is working') => is_null($employee->spouse_working) ? '—' : ($employee->spouse_working ? __('Yes') : __('No')),
            __('Phone') => $value($employee->phone),
            __('Personal email') => $value($employee->personal_email),
            __('Address') => $value($address),
        ],
        __('Employment') => [
            __('Staff number') => $employee->staff_no,
            __('Employment type') => __($employee->employment_type->label()),
            __('Department') => $label(LookupCategory::Department, $employee->department),
            __('Position') => $label(LookupCategory::Position, $employee->position),
            __('Reports to') => $value($employee->reportingManager?->user->name),
            __('Join date') => $employee->join_date->translatedFormat('j F Y'),
            __('Probation ends') => $value($employee->probation_end_date?->translatedFormat('j F Y')),
            __('Manual worker') => $employee->is_manual_worker ? __('Yes') : __('No'),
        ],
        __('Pay and statutory') => [
            __('Basic salary') => 'RM '.number_format((float) $employee->basic_salary, 2),
            __('Bank') => $label(LookupCategory::Banks, $employee->bank),
            __('Account number') => $value($employee->bank_account_no),
            __('EPF number') => $value($employee->epf_no),
            __('SOCSO number') => $value($employee->socso_no),
            __('Income tax number') => $value($employee->income_tax_no),
            __('Elected EPF rate') => filled($employee->epf_elected_rate) ? $employee->epf_elected_rate.'%' : '—',
        ],
    ];
@endphp
<x-layouts.admin title="{{ $employee->user->name }}" group="{{ __('Staff') }}">
<div>
    <a href="{{ route('staff.index') }}" class="mb-3 inline-flex items-center gap-1 text-sm text-muted-foreground hover:text-foreground">
        <x-ui.icon name="arrow-left" class="h-4 w-4" />{{ __('Back to Staff') }}
    </a>
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div class="flex min-w-0 items-center gap-4">
            <span class="avatar h-14 w-14 text-lg" aria-hidden="true">{{ $employee->user->initials }}</span>
            <div class="min-w-0">
                <h1 class="truncate text-2xl font-semibold tracking-tight">{{ $employee->user->name }}</h1>
                <p class="flex flex-wrap items-center gap-2 text-muted-foreground">
                    <span>{{ $label(LookupCategory::Position, $employee->position) }}</span>
                    <x-ui.badge :variant="$status->tone()">{{ __($status->label()) }}</x-ui.badge>
                </p>
            </div>
        </div>
        @if ($canEdit)
            <a href="{{ route('staff.edit', $employee) }}" class="btn btn-outline"><x-ui.icon name="edit" />{{ __('Edit') }}</a>
        @endif
    </div>
</div>

@if (session('status'))
    <x-ui.alert variant="success" role="status">{{ session('status') }}</x-ui.alert>
@endif

@if ($employee->last_working_date)
    <x-ui.alert :variant="$employee->hasLeft() ? 'neutral' : 'warning'">
        {{ $employee->hasLeft()
            ? __('Left on :date (:reason). They can no longer sign in.', ['date' => $employee->last_working_date->translatedFormat('j F Y'), 'reason' => __($employee->leaving_reason?->label() ?? 'Other')])
            : __('Last working day is :date (:reason). They can sign in until then.', ['date' => $employee->last_working_date->translatedFormat('j F Y'), 'reason' => __($employee->leaving_reason?->label() ?? 'Other')]) }}
    </x-ui.alert>
@endif

<div class="grid gap-4 lg:grid-cols-[minmax(0,1fr)_20rem]">
    <div class="min-w-0 space-y-4">
        @foreach ($sections as $heading => $fields)
            <section class="card p-5 sm:p-6">
                <h2 class="card-title mb-5">{{ $heading }}</h2>
                <dl class="grid gap-x-8 gap-y-5 sm:grid-cols-2">
                    @foreach ($fields as $term => $detail)
                        <div class="min-w-0">
                            <dt class="text-xs font-medium text-muted-foreground">{{ $term }}</dt>
                            <dd class="mt-1 break-words text-sm font-medium">{{ $detail }}</dd>
                        </div>
                    @endforeach
                </dl>
            </section>
        @endforeach
    </div>

    <div class="min-w-0 space-y-4">
        <section class="card p-5">
            <h2 class="card-title mb-4">{{ __('Sign-in account') }}</h2>
            <dl class="space-y-4 text-sm">
                <div>
                    <dt class="text-xs font-medium text-muted-foreground">{{ __('Work email') }}</dt>
                    <dd class="mt-1 break-words font-medium">{{ $employee->user->email }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-muted-foreground">{{ __('Role') }}</dt>
                    <dd class="mt-1 font-medium">{{ $employee->user->roles->map(fn ($role) => __($role->name))->implode(', ') ?: '—' }}</dd>
                </div>
            </dl>
        </section>

        @if ($employee->directReports->isNotEmpty())
            <section class="card p-5">
                <h2 class="card-title mb-4">{{ __('Direct reports') }}</h2>
                <ul class="space-y-2 text-sm">
                    @foreach ($employee->directReports->sortBy('user.name') as $report)
                        <li><a href="{{ route('staff.show', $report) }}" class="font-medium hover:underline">{{ $report->user->name }}</a></li>
                    @endforeach
                </ul>
            </section>
        @endif

        @if ($lastChange)
            <p class="text-sm text-muted-foreground">
                {{ __('Last changed by :name on :date.', ['name' => $lastChange->causer?->name ?? __('System'), 'date' => $lastChange->created_at->timezone($timezone)->translatedFormat('j M Y, g:i A')]) }}
                @if (auth()->user()->hasRole(\App\Enums\Role::SuperAdmin))
                    <a href="{{ route('administrations.audit-trail.show', $lastChange) }}" class="font-medium text-foreground underline">{{ __('View in Audit Trail') }}</a>
                @endif
            </p>
        @endif
    </div>
</div>
</x-layouts.admin>
