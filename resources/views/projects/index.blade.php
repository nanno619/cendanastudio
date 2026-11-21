@php
    $isFiltered = collect($filters)->filter(fn ($value): bool => filled($value))->isNotEmpty();
    $removeFilter = fn (string ...$keys): string => request()->fullUrlWithoutQuery([...$keys, 'page']);
@endphp

<x-layouts.admin title="{{ __('Projects') }}" group="{{ __('Projects') }}">
<div class="flex flex-wrap items-end justify-between gap-4">
    <x-ui.page-header title="{{ __('Projects') }}" description="{{ __('Client projects you can see. Completed and cancelled projects are hidden unless you filter for them.') }}" />
    @if ($canCreate)
        <a href="{{ route('projects.create') }}" class="btn btn-primary"><x-ui.icon name="plus" />{{ __('Add project') }}</a>
    @endif
</div>

@if (session('status'))
    <x-ui.alert variant="success" role="status">{{ session('status') }}</x-ui.alert>
@endif

<x-ui.filter-bar :action="route('projects.index')" :search="$filters['q'] ?? null" :reset-url="$isFiltered ? route('projects.index') : null" placeholder="{{ __('Search code or name') }}">
    <div class="min-w-48 space-y-1.5">
        <label class="label" for="projects-client">{{ __('Client') }}</label>
        <x-ui.select id="projects-client" name="client" placeholder="{{ __('All clients') }}" :value="$filters['client'] ?? null" :options="$clients" :searchable="count($clients) > 15" />
    </div>
    <div class="min-w-40 space-y-1.5">
        <label class="label" for="projects-status">{{ __('Status') }}</label>
        <x-ui.select id="projects-status" name="status" placeholder="{{ __('Open projects') }}" :value="$filters['status'] ?? null" :options="$statuses" />
    </div>

    @if ($isFiltered)
        <x-slot:active>
            @if (filled($filters['q'] ?? null))
                <a href="{{ $removeFilter('q') }}" class="chip hover:bg-accent" aria-label="{{ __('Remove filter: :filter', ['filter' => __('Search')]) }}">{{ __('Search:') }} “{{ $filters['q'] }}”<x-ui.icon name="x" class="h-3.5 w-3.5 text-muted-foreground" /></a>
            @endif
            @if (filled($filters['client'] ?? null))
                <a href="{{ $removeFilter('client') }}" class="chip hover:bg-accent" aria-label="{{ __('Remove filter: :filter', ['filter' => __('Client')]) }}">{{ __('Client:') }} {{ $clients[$filters['client']] ?? '—' }}<x-ui.icon name="x" class="h-3.5 w-3.5 text-muted-foreground" /></a>
            @endif
            @if (filled($filters['status'] ?? null))
                <a href="{{ $removeFilter('status') }}" class="chip hover:bg-accent" aria-label="{{ __('Remove filter: :filter', ['filter' => __('Status')]) }}">{{ __('Status:') }} {{ $statuses[$filters['status']] }}<x-ui.icon name="x" class="h-3.5 w-3.5 text-muted-foreground" /></a>
            @endif
        </x-slot:active>
    @endif
</x-ui.filter-bar>

<section class="card" aria-labelledby="projects-heading">
    <div class="flex flex-wrap items-baseline justify-between gap-2 p-5 sm:px-6">
        <h2 id="projects-heading" class="card-title">{{ __('Projects') }}</h2>
        <p class="text-sm tabular-nums text-muted-foreground">{{ trans_choice(':count project|:count projects', $projects->total(), ['count' => number_format($projects->total())]) }}</p>
    </div>

    @if ($projects->isEmpty())
        <div class="border-t">
            @if ($isFiltered)
                <x-ui.table-state state="filtered" description="{{ __('No projects match these filters.') }}">
                    <a href="{{ route('projects.index') }}" class="btn btn-outline btn-sm">{{ __('Clear filters') }}</a>
                </x-ui.table-state>
            @else
                <x-ui.table-state state="empty" title="{{ __('No projects yet') }}" description="{{ $canCreate ? __('Add a client, then add their first project.') : __('Projects you manage will be listed here.') }}" />
            @endif
        </div>
    @else
        <div class="overflow-x-auto border-t">
            <table class="table min-w-210">
                <thead>
                    <tr>
                        <th scope="col" class="w-20"><span class="sr-only">{{ __('Actions') }}</span></th>
                        <th scope="col">{{ __('Project') }}</th>
                        <th scope="col">{{ __('Client') }}</th>
                        <th scope="col">{{ __('Project manager') }}</th>
                        <th scope="col">{{ __('Dates') }}</th>
                        <th scope="col">{{ __('Status') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($projects as $project)
                        <tr>
                            <td data-actions class="whitespace-nowrap">
                                <a href="{{ route('projects.show', $project) }}" class="btn btn-ghost btn-icon btn-sm text-muted-foreground" data-tip="{{ __('View') }}" data-tip-pos="right" aria-label="{{ __('View :name', ['name' => $project->name]) }}"><x-ui.icon name="eye" /></a>
                                @if ($project->isEditableBy(auth()->user()))
                                    <a href="{{ route('projects.edit', $project) }}" class="btn btn-ghost btn-icon btn-sm text-muted-foreground" data-tip="{{ __('Edit') }}" data-tip-pos="right" aria-label="{{ __('Edit :label', ['label' => $project->name]) }}"><x-ui.icon name="edit" /></a>
                                @endif
                            </td>
                            <td>
                                <p class="font-medium">{{ $project->name }}</p>
                                <p class="font-mono text-xs text-muted-foreground">{{ $project->code }}</p>
                            </td>
                            <td>{{ $project->client->name }}</td>
                            <td>{{ $project->projectManager?->user->name ?? '—' }}</td>
                            <td class="whitespace-nowrap text-sm tabular-nums">
                                {{ $project->start_date?->translatedFormat('j M Y') ?? '—' }}
                                <span class="text-muted-foreground">–</span>
                                {{ $project->end_date?->translatedFormat('j M Y') ?? '—' }}
                            </td>
                            <td><x-ui.badge :variant="$project->status->tone()">{{ __($project->status->label()) }}</x-ui.badge></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <x-ui.pagination :paginator="$projects" />
    @endif
</section>
</x-layouts.admin>
