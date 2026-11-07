<x-layouts.admin title="{{ __('Table states') }}" group="{{ __('Components') }}">
<x-ui.page-header title="{{ __('Table states') }}" description="{{ __('How a table looks when data is ready, loading, empty, not found or failed to load.') }}" />

<div class="card" data-table-state-demo>
    <div class="card-header"><h2 class="card-title">{{ __('Entry list') }}</h2><p class="card-desc">{{ __('Choose a state below to see how the interface responds.') }}</p></div>
    <div class="flex flex-wrap gap-2 px-6 pb-5" role="group" aria-label="{{ __('Sample table states') }}">
        @foreach (['ready' => __('Ready'), 'loading' => __('Loading'), 'empty' => __('Empty'), 'filtered' => __('Not found'), 'error' => __('Error')] as $state => $label)
            <button type="button" class="filter-chip" data-table-state-set="{{ $state }}" aria-pressed="{{ $state === 'ready' ? 'true' : 'false' }}">{{ $label }}</button>
        @endforeach
    </div>
    <div class="border-t" data-table-state-view="ready">
        <div class="overflow-x-auto">
            <table class="table min-w-[520px]"><thead><tr><th>{{ __('Title') }}</th><th>{{ __('Owner') }}</th><th>{{ __('Status') }}</th><th class="text-right">{{ __('Updated') }}</th></tr></thead>
                <tbody>
                    <tr><td class="font-medium">{{ __('Onboarding guide') }}</td><td>Ayu Rahmawati</td><td><span class="badge badge-success">{{ __('Active') }}</span></td><td class="text-right">{{ __('Today') }}</td></tr>
                    <tr><td class="font-medium">{{ __('Team meeting notes') }}</td><td>Bagas Prasetyo</td><td><span class="badge badge-warning">{{ __('Draft') }}</span></td><td class="text-right">{{ __('Yesterday') }}</td></tr>
                    <tr><td class="font-medium">{{ __('Launch checklist') }}</td><td>Maya Kusuma</td><td><span class="badge badge-info">{{ __('In review') }}</span></td><td class="text-right">28 Sep 2026</td></tr>
                </tbody>
            </table>
        </div>
        <p class="border-t px-6 py-3 text-sm text-muted-foreground">{{ __('Showing 3 of 3 entries.') }}</p>
    </div>
    <div class="border-t" data-table-state-view="loading" hidden><x-ui.table-state state="loading" /></div>
    <div class="border-t" data-table-state-view="empty" hidden><x-ui.table-state state="empty"><a href="{{ route('examples.records.create') }}" class="btn btn-primary"><x-ui.icon name="plus" />{{ __('Add entry') }}</a></x-ui.table-state></div>
    <div class="border-t" data-table-state-view="filtered" hidden><x-ui.table-state state="filtered"><button type="button" class="btn btn-outline" data-table-state-action="ready">{{ __('Clear filters') }}</button></x-ui.table-state></div>
    <div class="border-t" data-table-state-view="error" hidden><x-ui.table-state state="error"><button type="button" class="btn btn-primary" data-table-state-action="ready">{{ __('Try again') }}</button></x-ui.table-state></div>
</div>

<x-ui.card title="{{ __('Guidelines') }}" description="{{ __('Use the same component on real project tables.') }}">
    <ul class="grid gap-3 text-sm sm:grid-cols-2">
        <li class="rounded-lg bg-muted/50 p-4"><span class="font-medium">{{ __('Loading') }}</span><p class="mt-1 text-muted-foreground">{{ __('Skeletons keep the table shape stable while data loads.') }}</p></li>
        <li class="rounded-lg bg-muted/50 p-4"><span class="font-medium">{{ __('Empty') }}</span><p class="mt-1 text-muted-foreground">{{ __('Invite users to create their first record instead of showing blank space.') }}</p></li>
        <li class="rounded-lg bg-muted/50 p-4"><span class="font-medium">{{ __('Not found') }}</span><p class="mt-1 text-muted-foreground">{{ __('Offer a quick way to clear the filters.') }}</p></li>
        <li class="rounded-lg bg-muted/50 p-4"><span class="font-medium">{{ __('Error') }}</span><p class="mt-1 text-muted-foreground">{{ __('Explain the problem and offer a way to try again.') }}</p></li>
    </ul>
</x-ui.card>
</x-layouts.admin>
