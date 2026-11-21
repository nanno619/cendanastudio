@use('App\Enums\ClientStatus')
@php
    $isFiltered = collect($filters)->filter(fn ($value): bool => filled($value))->isNotEmpty();
    $removeFilter = fn (string ...$keys): string => request()->fullUrlWithoutQuery([...$keys, 'page']);
    $tiles = [
        ClientStatus::Active->value => ['check-circle', 'bg-success-soft text-success'],
        ClientStatus::Closure->value => ['power', 'bg-muted text-muted-foreground'],
        ClientStatus::Blacklist->value => ['alert-triangle', 'bg-danger-soft text-danger'],
    ];
@endphp

<x-layouts.admin title="{{ __('CRM') }}" group="{{ __('Modules') }}">
<div class="flex flex-wrap items-end justify-between gap-4">
    <x-ui.page-header title="{{ __('CRM') }}" description="{{ __('Your clients, the people to contact there, and where each client stands.') }}" />
    @if ($canEdit)
        <a href="{{ route('crm.clients.create') }}" class="btn btn-primary"><x-ui.icon name="plus" />{{ __('Add client') }}</a>
    @endif
</div>

@if (session('status'))
    <x-ui.alert variant="success" role="status">{{ session('status') }}</x-ui.alert>
@endif

<section aria-label="{{ __('Clients by status') }}" class="grid gap-4 sm:grid-cols-3">
    @foreach (ClientStatus::cases() as $status)
        @php
            [$icon, $tile] = $tiles[$status->value];
            $selected = ($filters['status'] ?? null) === $status->value;
        @endphp
        <a href="{{ $selected ? route('crm.index') : route('crm.index', ['status' => $status->value]) }}"
           @class(['card p-5 transition-colors hover:bg-accent/40 focus-ring', 'ring-2 ring-primary' => $selected])
           @if ($selected) aria-current="true" @endif
           aria-label="{{ __(':status: :count', ['status' => __($status->label()), 'count' => number_format($counts[$status->value])]) }}">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <h2 class="text-sm font-medium text-muted-foreground">{{ __($status->label()) }}</h2>
                    <p class="mt-2 text-2xl font-semibold tracking-tight tabular-nums">{{ number_format($counts[$status->value]) }}</p>
                </div>
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg {{ $tile }}" aria-hidden="true">
                    <x-ui.icon :name="$icon" class="h-5 w-5" />
                </span>
            </div>
            <p class="mt-3 text-xs text-muted-foreground">{{ __($status->description()) }}</p>
        </a>
    @endforeach
</section>

<x-ui.filter-bar :action="route('crm.index')" :search="$filters['q'] ?? null" :reset-url="$isFiltered ? route('crm.index') : null" placeholder="{{ __('Search name, SSM number or email') }}">
    <div class="min-w-40 space-y-1.5">
        <label class="label" for="crm-status">{{ __('Status') }}</label>
        <x-ui.select id="crm-status" name="status" placeholder="{{ __('All statuses') }}" :value="$filters['status'] ?? null" :options="$statuses" />
    </div>

    @if ($isFiltered)
        <x-slot:active>
            @if (filled($filters['q'] ?? null))
                <a href="{{ $removeFilter('q') }}" class="chip hover:bg-accent" aria-label="{{ __('Remove filter: :filter', ['filter' => __('Search')]) }}">{{ __('Search:') }} “{{ $filters['q'] }}”<x-ui.icon name="x" class="h-3.5 w-3.5 text-muted-foreground" /></a>
            @endif
            @if (filled($filters['status'] ?? null))
                <a href="{{ $removeFilter('status') }}" class="chip hover:bg-accent" aria-label="{{ __('Remove filter: :filter', ['filter' => __('Status')]) }}">{{ __('Status:') }} {{ $statuses[$filters['status']] }}<x-ui.icon name="x" class="h-3.5 w-3.5 text-muted-foreground" /></a>
            @endif
        </x-slot:active>
    @endif
</x-ui.filter-bar>

<section class="card" aria-labelledby="clients-heading">
    <div class="flex flex-wrap items-baseline justify-between gap-2 p-5 sm:px-6">
        <h2 id="clients-heading" class="card-title">{{ __('Clients') }}</h2>
        <p class="text-sm tabular-nums text-muted-foreground">{{ trans_choice(':count client|:count clients', $clients->total(), ['count' => number_format($clients->total())]) }}</p>
    </div>

    @if ($clients->isEmpty())
        <div class="border-t">
            @if ($isFiltered)
                <x-ui.table-state state="filtered" description="{{ __('No clients match these filters.') }}">
                    <a href="{{ route('crm.index') }}" class="btn btn-outline btn-sm">{{ __('Clear filters') }}</a>
                </x-ui.table-state>
            @else
                <x-ui.table-state state="empty" title="{{ __('No clients yet') }}" description="{{ __('Add your first client and the people to contact there.') }}">
                    @if ($canEdit)
                        <a href="{{ route('crm.clients.create') }}" class="btn btn-primary btn-sm"><x-ui.icon name="plus" />{{ __('Add client') }}</a>
                    @endif
                </x-ui.table-state>
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
                        <th scope="col">{{ __('Status') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($clients as $client)
                        @php $contact = $client->contacts->first(); @endphp
                        <tr>
                            <td data-actions class="whitespace-nowrap">
                                <a href="{{ route('crm.clients.show', $client) }}" class="btn btn-ghost btn-icon btn-sm text-muted-foreground" data-tip="{{ __('View') }}" data-tip-pos="right" aria-label="{{ __('View :name', ['name' => $client->name]) }}"><x-ui.icon name="eye" /></a>
                                @if ($canEdit)
                                    <a href="{{ route('crm.clients.edit', $client) }}" class="btn btn-ghost btn-icon btn-sm text-muted-foreground" data-tip="{{ __('Edit') }}" data-tip-pos="right" aria-label="{{ __('Edit :label', ['label' => $client->name]) }}"><x-ui.icon name="edit" /></a>
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
                            <td><x-ui.badge :variant="$client->status->tone()">{{ __($client->status->label()) }}</x-ui.badge></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <x-ui.pagination :paginator="$clients" />
    @endif
</section>
</x-layouts.admin>
