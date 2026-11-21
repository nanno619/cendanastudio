@php
    $value = fn ($value): string => filled($value) ? (string) $value : '—';
    $address = collect([$client->address_line_1, $client->address_line_2, trim($client->postcode.' '.$client->city), $stateLabel])->filter()->implode(', ');
@endphp
<x-layouts.admin title="{{ $client->name }}" group="{{ __('Projects') }}">
<div>
    <a href="{{ route('clients.index') }}" class="mb-3 inline-flex items-center gap-1 text-sm text-muted-foreground hover:text-foreground">
        <x-ui.icon name="arrow-left" class="h-4 w-4" />{{ __('Back to Clients') }}
    </a>
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div class="min-w-0">
            <h1 class="truncate text-2xl font-semibold tracking-tight">{{ $client->name }}</h1>
            @if ($client->registration_no)
                <p class="font-mono text-sm text-muted-foreground">{{ __('SSM') }} {{ $client->registration_no }}</p>
            @endif
        </div>
        <div class="flex flex-wrap gap-2">
            @if ($canCreateProject)
                <a href="{{ route('projects.create', ['client' => $client->ulid]) }}" class="btn btn-outline"><x-ui.icon name="plus" />{{ __('Add project') }}</a>
            @endif
            @if ($canEdit)
                <a href="{{ route('clients.edit', $client) }}" class="btn btn-outline"><x-ui.icon name="edit" />{{ __('Edit') }}</a>
            @endif
        </div>
    </div>
</div>

@if (session('status'))
    <x-ui.alert variant="success" role="status">{{ session('status') }}</x-ui.alert>
@endif

<div class="grid gap-4 lg:grid-cols-[minmax(0,1fr)_20rem]">
    <div class="min-w-0 space-y-4">
        <section class="card p-5 sm:p-6">
            <h2 class="card-title mb-5">{{ __('Company') }}</h2>
            <dl class="grid gap-x-8 gap-y-5 sm:grid-cols-2">
                @foreach ([__('Phone') => $value($client->phone), __('Email') => $value($client->email), __('Address') => $value($address)] as $term => $detail)
                    <div class="min-w-0">
                        <dt class="text-xs font-medium text-muted-foreground">{{ $term }}</dt>
                        <dd class="mt-1 break-words text-sm font-medium">{{ $detail }}</dd>
                    </div>
                @endforeach
                @if (filled($client->notes))
                    <div class="min-w-0 sm:col-span-2">
                        <dt class="text-xs font-medium text-muted-foreground">{{ __('Notes') }}</dt>
                        <dd class="mt-1 whitespace-pre-line break-words text-sm">{{ $client->notes }}</dd>
                    </div>
                @endif
            </dl>
        </section>

        <section class="card" aria-labelledby="client-projects-heading">
            <div class="p-5 sm:px-6">
                <h2 id="client-projects-heading" class="card-title">{{ __('Projects') }}</h2>
            </div>
            @if ($projects->isEmpty())
                <div class="border-t">
                    <x-ui.table-state state="empty" title="{{ __('No projects yet') }}" description="{{ __('Projects for this client will be listed here.') }}" />
                </div>
            @else
                <div class="overflow-x-auto border-t">
                    <table class="table min-w-160">
                        <thead>
                            <tr>
                                <th scope="col">{{ __('Project') }}</th>
                                <th scope="col">{{ __('Project manager') }}</th>
                                <th scope="col">{{ __('Start date') }}</th>
                                <th scope="col">{{ __('Status') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($projects as $project)
                                <tr>
                                    <td>
                                        <a href="{{ route('projects.show', $project) }}" class="font-medium hover:underline">{{ $project->name }}</a>
                                        <p class="font-mono text-xs text-muted-foreground">{{ $project->code }}</p>
                                    </td>
                                    <td>{{ $project->projectManager?->user->name ?? '—' }}</td>
                                    <td class="whitespace-nowrap tabular-nums">{{ $project->start_date?->translatedFormat('j M Y') ?? '—' }}</td>
                                    <td><x-ui.badge :variant="$project->status->tone()">{{ __($project->status->label()) }}</x-ui.badge></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
    </div>

    <div class="min-w-0 space-y-4">
        <section class="card p-5">
            <h2 class="card-title mb-4">{{ __('Contact persons') }}</h2>
            @if ($client->contacts->isEmpty())
                <p class="text-sm text-muted-foreground">{{ __('None added yet.') }}</p>
            @else
                <ol class="space-y-4 text-sm">
                    @foreach ($client->contacts as $contact)
                        <li>
                            <p class="font-medium">{{ $contact->name }}</p>
                            @if ($contact->position)<p class="text-muted-foreground">{{ $contact->position }}</p>@endif
                            @if ($contact->phone)<a href="tel:{{ preg_replace('/[^0-9+]/', '', $contact->phone) }}" class="block font-medium tabular-nums hover:underline">{{ $contact->phone }}</a>@endif
                            @if ($contact->email)<a href="mailto:{{ $contact->email }}" class="block break-all hover:underline">{{ $contact->email }}</a>@endif
                        </li>
                    @endforeach
                </ol>
            @endif
        </section>
    </div>
</div>
</x-layouts.admin>
