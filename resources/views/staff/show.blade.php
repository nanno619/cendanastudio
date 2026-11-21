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
            __('Preferred name') => $value($employee->preferred_name),
            ...($employee->identity_type === IdentityType::MyKad
                ? [__('NRIC number') => MyKad::format($employee->identity_no)]
                : [__('Passport number') => $employee->identity_no, __('Nationality') => Countries::name($employee->nationality)]),
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
            __('Employee number') => $employee->staff_no,
            __('Employment type') => __($employee->employment_type->label()),
            __('Department') => $label(LookupCategory::Department, $employee->department),
            __('Position') => $label(LookupCategory::Position, $employee->position),
            __('Subsidiary') => $label(LookupCategory::Subsidiary, $employee->subsidiary),
            __('Reports to') => $value($employee->reportingManager?->user->name),
            __('Start date') => $employee->join_date->translatedFormat('j F Y'),
            __('Probation ends') => $value($employee->probation_end_date?->translatedFormat('j F Y')),
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
                @if (filled($employee->preferred_name))
                    <p class="truncate text-sm text-muted-foreground">{{ __('Goes by :name', ['name' => $employee->preferred_name]) }}</p>
                @endif
                <p class="flex flex-wrap items-center gap-2 text-muted-foreground">
                    <span>{{ $label(LookupCategory::Position, $employee->position) }}</span>
                    <x-ui.badge :variant="$status->tone()">{{ __($status->label()) }}</x-ui.badge>
                </p>
            </div>
        </div>
        @if ($canEdit)
            <div class="flex flex-wrap gap-2">
                @unless ($employee->hasLeft())
                    <form method="POST" action="{{ route('staff.invitation', $employee) }}">
                        @csrf
                        <button type="submit" class="btn btn-outline" data-tip="{{ __('Emails a link to set their password. Any earlier link stops working.') }}"><x-ui.icon name="mail" />{{ __('Send invitation') }}</button>
                    </form>
                @endunless
                <a href="{{ route('staff.edit', $employee) }}" class="btn btn-outline"><x-ui.icon name="edit" />{{ __('Edit') }}</a>
            </div>
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

        <section id="documents" class="card scroll-mt-20" aria-labelledby="documents-heading">
            <div class="flex flex-wrap items-baseline justify-between gap-2 p-5 sm:px-6">
                <h2 id="documents-heading" class="card-title">{{ __('Documents') }}</h2>
                <p class="flex items-center gap-1.5 text-xs text-muted-foreground"><x-ui.icon name="lock" class="h-3.5 w-3.5" />{{ __('Private. Downloads are recorded in the audit trail.') }}</p>
            </div>

            @if ($documents->isEmpty())
                <div class="border-t">
                    <x-ui.table-state state="empty" title="{{ __('No documents yet') }}" description="{{ __('Keep copies of the IC or passport, offer letter and certificates here.') }}" />
                </div>
            @else
                <div class="overflow-x-auto border-t">
                    <table class="table min-w-160">
                        <thead>
                            <tr>
                                <th scope="col" class="w-20"><span class="sr-only">{{ __('Actions') }}</span></th>
                                <th scope="col">{{ __('Document') }}</th>
                                <th scope="col">{{ __('Type') }}</th>
                                <th scope="col">{{ __('Uploaded') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($documents as $document)
                                <tr>
                                    <td data-actions class="whitespace-nowrap">
                                        <a href="{{ route('staff.documents.download', [$employee, $document->uuid]) }}" class="btn btn-ghost btn-icon btn-sm text-muted-foreground" data-tip="{{ __('Download') }}" data-tip-pos="right" aria-label="{{ __('Download :file', ['file' => $document->file_name]) }}"><x-ui.icon name="download" /></a>
                                        @if ($canEdit)
                                            <button type="button" class="btn btn-ghost btn-icon btn-sm text-muted-foreground" data-modal-open="#delete-document-{{ $document->uuid }}" data-tip="{{ __('Delete') }}" data-tip-pos="right" aria-label="{{ __('Delete :file', ['file' => $document->file_name]) }}"><x-ui.icon name="trash" /></button>
                                        @endif
                                    </td>
                                    <td class="min-w-0">
                                        <p class="truncate font-medium">{{ $document->file_name }}</p>
                                        <p class="text-xs text-muted-foreground">
                                            {{ $document->human_readable_size }}
                                            @if (filled($document->getCustomProperty('note')))
                                                · {{ $document->getCustomProperty('note') }}
                                            @endif
                                        </p>
                                    </td>
                                    <td>{{ $documentTypes[$document->getCustomProperty('type')] ?? __('Other') }}</td>
                                    <td class="whitespace-nowrap">
                                        <p class="tabular-nums">{{ $document->created_at->timezone($timezone)->translatedFormat('j M Y') }}</p>
                                        <p class="text-xs text-muted-foreground">{{ $documentUploaders[$document->getCustomProperty('uploaded_by')] ?? __('System') }}</p>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            @if ($canEdit)
                <form method="POST" action="{{ route('staff.documents.store', $employee) }}" enctype="multipart/form-data" class="space-y-4 border-t p-5 sm:px-6">
                    @csrf
                    <h3 class="text-sm font-semibold">{{ __('Upload a document') }}</h3>
                    <x-ui.file-preview id="f-document" name="document" :accept="'.'.implode(',.', $uploadPolicy->allowedExtensions())" :max-mb="$uploadPolicy->max_file_size_mb"
                        label="{{ __('Choose a document') }}"
                        hint="{{ __('Drag here or click to choose. :types, up to :max MB.', ['types' => strtoupper(implode(', ', $uploadPolicy->allowedExtensions())), 'max' => $uploadPolicy->max_file_size_mb]) }}" />
                    @error('document')<p class="field-error">{{ $message }}</p>@enderror
                    <div class="grid gap-4 sm:grid-cols-[14rem_minmax(0,1fr)_auto] sm:items-end">
                        <x-ui.field label="{{ __('Document type') }}" name="type" required>
                            <x-ui.select name="type" :options="$documentTypes" required />
                        </x-ui.field>
                        <x-ui.field label="{{ __('Note') }}" name="note">
                            <x-ui.input name="note" maxlength="{{ \App\Models\Employee::DOCUMENT_NOTE_MAX_LENGTH }}" placeholder="{{ __('Optional, e.g. SPM certificate') }}" />
                        </x-ui.field>
                        <button type="submit" class="btn btn-primary"><x-ui.icon name="upload" />{{ __('Upload') }}</button>
                    </div>
                </form>
            @endif
        </section>
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

        <section class="card p-5">
            <h2 class="card-title mb-4">{{ __('Emergency contacts') }}</h2>
            @if ($employee->emergencyContacts->isEmpty())
                <p class="text-sm text-muted-foreground">{{ __('None added yet.') }}</p>
            @else
                <ol class="space-y-4 text-sm">
                    @foreach ($employee->emergencyContacts as $contact)
                        <li>
                            <p class="font-medium">{{ $contact->name }}</p>
                            <p class="text-muted-foreground">{{ $label(LookupCategory::Relationship, $contact->relationship) }}</p>
                            <a href="tel:{{ preg_replace('/[^0-9+]/', '', $contact->phone) }}" class="font-medium tabular-nums hover:underline">{{ $contact->phone }}</a>
                        </li>
                    @endforeach
                </ol>
            @endif
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
@if ($canEdit)
    @foreach ($documents as $document)
        <x-ui.confirm-action id="delete-document-{{ $document->uuid }}" title="{{ __('Delete this document?') }}"
            description="{{ __(':file will be removed for good. This can’t be undone.', ['file' => $document->file_name]) }}"
            :action="route('staff.documents.destroy', [$employee, $document->uuid])" />
    @endforeach
@endif
</x-layouts.admin>
