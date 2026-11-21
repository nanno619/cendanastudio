@php
    $isFiltered = filled($filters['q'] ?? null);
@endphp

<x-layouts.admin title="{{ __('Clients') }}" group="{{ __('Projects') }}">
<div class="flex flex-wrap items-end justify-between gap-4">
    <x-ui.page-header title="{{ __('Clients') }}" description="{{ __('Companies you do projects for, and the people to contact there.') }}" />
    @if ($canEdit)
        <a href="{{ route('clients.create') }}" class="btn btn-primary"><x-ui.icon name="plus" />{{ __('Add client') }}</a>
    @endif
</div>

@if (session('status'))
    <x-ui.alert variant="success" role="status">{{ session('status') }}</x-ui.alert>
@endif

<x-ui.filter-bar :action="route('clients.index')" :search="$filters['q'] ?? null" :reset-url="$isFiltered ? route('clients.index') : null" placeholder="{{ __('Search name, SSM number or email') }}" />

<section class="card" aria-labelledby="clients-heading">
    <div class="flex flex-wrap items-baseline justify-between gap-2 p-5 sm:px-6">
        <h2 id="clients-heading" class="card-title">{{ __('Clients') }}</h2>
        <p class="text-sm tabular-nums text-muted-foreground">{{ trans_choice(':count client|:count clients', $clients->total(), ['count' => number_format($clients->total())]) }}</p>
    </div>

    @if ($clients->isEmpty())
        <div class="border-t">
            @if ($isFiltered)
                <x-ui.table-state state="filtered" description="{{ __('No clients match this search.') }}">
                    <a href="{{ route('clients.index') }}" class="btn btn-outline btn-sm">{{ __('Clear filters') }}</a>
                </x-ui.table-state>
            @else
                <x-ui.table-state state="empty" title="{{ __('No clients yet') }}" description="{{ __('Add a client before adding their projects.') }}" />
            @endif
        </div>
    @else
        <div class="overflow-x-auto border-t">
            <table class="table min-w-180">
                <thead>
                    <tr>
                        <th scope="col" class="w-20"><span class="sr-only">{{ __('Actions') }}</span></th>
                        <th scope="col">{{ __('Company') }}</th>
                        <th scope="col">{{ __('Main contact') }}</th>
                        <th scope="col">{{ __('Phone') }}</th>
                        <th scope="col" class="text-right">{{ __('Projects') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($clients as $client)
                        @php $contact = $client->contacts->first(); @endphp
                        <tr>
                            <td data-actions class="whitespace-nowrap">
                                <a href="{{ route('clients.show', $client) }}" class="btn btn-ghost btn-icon btn-sm text-muted-foreground" data-tip="{{ __('View') }}" data-tip-pos="right" aria-label="{{ __('View :name', ['name' => $client->name]) }}"><x-ui.icon name="eye" /></a>
                                @if ($canEdit)
                                    <a href="{{ route('clients.edit', $client) }}" class="btn btn-ghost btn-icon btn-sm text-muted-foreground" data-tip="{{ __('Edit') }}" data-tip-pos="right" aria-label="{{ __('Edit :label', ['label' => $client->name]) }}"><x-ui.icon name="edit" /></a>
                                @endif
                            </td>
                            <td>
                                <p class="font-medium">{{ $client->name }}</p>
                                @if ($client->registration_no)
                                    <p class="font-mono text-xs text-muted-foreground">{{ $client->registration_no }}</p>
                                @endif
                            </td>
                            <td>
                                @if ($contact)
                                    <p>{{ $contact->name }}</p>
                                    @if ($contact->position)<p class="text-xs text-muted-foreground">{{ $contact->position }}</p>@endif
                                @else
                                    <span class="text-muted-foreground">—</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap tabular-nums">{{ $client->phone ?? '—' }}</td>
                            <td class="text-right tabular-nums">{{ number_format($client->projects_count) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <x-ui.pagination :paginator="$clients" />
    @endif
</section>
</x-layouts.admin>
