@use('App\Enums\IdentityType')
@use('App\Support\MyKad')
<x-layouts.admin title="{{ __('My profile') }}" group="{{ __('Platform') }}">
<x-ui.page-header title="{{ __('My profile') }}" description="{{ __('Your staff record. Keep your personal details and emergency contacts up to date; ask HR to change anything else.') }}" />

@if ($employee === null)
    <section class="card">
        <x-ui.table-state state="empty" title="{{ __('No staff record') }}" description="{{ __('This account isn’t linked to a staff record. Your sign-in details are in Account settings.') }}">
            <a href="{{ route('admin.settings') }}" class="btn btn-outline btn-sm">{{ __('Account settings') }}</a>
        </x-ui.table-state>
    </section>
@else
    @php
        $status = $employee->status();
        $label = fn (string $category, ?string $code): string => $code === null ? '—' : ($labels[$category][$code] ?? $code);
        $value = fn ($value): string => filled($value) ? (string) $value : '—';
        $maskedAccount = filled($employee->bank_account_no) ? str_repeat('•', max(0, strlen($employee->bank_account_no) - 4)).substr($employee->bank_account_no, -4) : '—';
        $managed = [
            __('Employment') => [
                __('Employee number') => $employee->staff_no,
                ...($employee->identity_type === IdentityType::MyKad ? [__('NRIC number') => MyKad::format($employee->identity_no)] : [__('Passport number') => $employee->identity_no]),
                __('Position') => $label('position', $employee->position),
                __('Department') => $label('department', $employee->department),
                __('Subsidiary') => $label('subsidiary', $employee->subsidiary),
                __('Reports to') => $value($employee->reportingManager?->user->name),
                __('Employment type') => __($employee->employment_type->label()),
                __('Start date') => $employee->join_date->translatedFormat('j F Y'),
                __('Probation ends') => $value($employee->probation_end_date?->translatedFormat('j F Y')),
            ],
            __('Pay and statutory') => [
                __('Basic salary') => 'RM '.number_format((float) $employee->basic_salary, 2),
                __('Bank') => $label('banks', $employee->bank),
                __('Account number') => $maskedAccount,
                __('EPF number') => $value($employee->epf_no),
                __('SOCSO number') => $value($employee->socso_no),
                __('Income tax number') => $value($employee->income_tax_no),
            ],
        ];
    @endphp

    @if (session('status'))
        <x-ui.alert variant="success" role="status">{{ session('status') }}</x-ui.alert>
    @endif
    <x-staff.profile-reminder :show-link="false" />

    <div class="grid gap-4 lg:grid-cols-[minmax(0,1fr)_20rem]">
        <form method="POST" action="{{ route('my-profile.update') }}" class="min-w-0 space-y-4">
            @csrf
            @method('PUT')

            <x-staff.personal-details :employee="$employee" :genders="$genders" :races="$races" :marital-statuses="$maritalStatuses" :states="$states"
                description="{{ __('Used by HR, and for income tax (PCB) reliefs.') }}" />
            <x-staff.emergency-contacts :employee="$employee" :relationships="$relationships" />

            <div class="flex justify-end">
                <button type="submit" class="btn btn-primary">{{ __('Save my details') }}</button>
            </div>
        </form>

        <div class="min-w-0 space-y-4">
            <section class="card p-5">
                <div class="flex items-center gap-3">
                    <span class="avatar avatar-lg" aria-hidden="true">{{ auth()->user()->initials }}</span>
                    <div class="min-w-0">
                        <p class="truncate font-semibold">{{ auth()->user()->name }}</p>
                        <p class="truncate text-sm text-muted-foreground">{{ auth()->user()->email }}</p>
                        <x-ui.badge :variant="$status->tone()" class="mt-1">{{ __($status->label()) }}</x-ui.badge>
                    </div>
                </div>
            </section>

            @foreach ($managed as $heading => $fields)
                <section class="card p-5">
                    <h2 class="card-title">{{ $heading }}</h2>
                    <p class="mb-4 flex items-center gap-1.5 text-xs text-muted-foreground"><x-ui.icon name="lock" class="h-3.5 w-3.5" />{{ __('Managed by HR') }}</p>
                    <dl class="space-y-3 text-sm">
                        @foreach ($fields as $term => $detail)
                            <div>
                                <dt class="text-xs font-medium text-muted-foreground">{{ $term }}</dt>
                                <dd class="mt-0.5 break-words font-medium">{{ $detail }}</dd>
                            </div>
                        @endforeach
                    </dl>
                </section>
            @endforeach

            <section class="card p-5" aria-labelledby="my-documents-heading">
                <h2 id="my-documents-heading" class="card-title mb-4">{{ __('My documents') }}</h2>
                @if ($documents->isEmpty())
                    <p class="text-sm text-muted-foreground">{{ __('No documents yet. HR adds them to your record.') }}</p>
                @else
                    <ul class="space-y-3 text-sm">
                        @foreach ($documents as $document)
                            <li class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="truncate font-medium">{{ $document->file_name }}</p>
                                    <p class="text-xs text-muted-foreground">{{ $documentTypes[$document->getCustomProperty('type')] ?? __('Other') }} · {{ $document->created_at->timezone($timezone)->translatedFormat('j M Y') }}</p>
                                </div>
                                <a href="{{ route('my-profile.documents.download', $document->uuid) }}" class="btn btn-ghost btn-icon btn-sm text-muted-foreground" data-tip="{{ __('Download') }}" aria-label="{{ __('Download :file', ['file' => $document->file_name]) }}"><x-ui.icon name="download" /></a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>
        </div>
    </div>
@endif
</x-layouts.admin>
