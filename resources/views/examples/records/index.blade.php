<x-layouts.admin title="{{ __('Entry list') }}" group="{{ __('Sample pages') }}">
<div class="flex flex-wrap items-start justify-between gap-4">
    <x-ui.page-header title="{{ __('Entry list') }}" description="{{ __('A list page pattern you can adapt to any project data.') }}" />
    <a href="{{ route('examples.records.create') }}" class="btn btn-primary"><x-ui.icon name="plus" />{{ __('Add entry') }}</a>
</div>

<div class="card" data-table data-page-size="6">
    <div class="flex flex-col gap-3 p-5 sm:flex-row sm:items-center sm:justify-between sm:p-6">
        <div><h2 class="card-title">{{ __('All entries') }}</h2><p class="card-desc mt-1.5">{{ __('Search and filter the sample data in the browser.') }}</p></div>
        <div class="flex flex-col gap-2 sm:flex-row">
            <input type="search" class="input sm:w-56" placeholder="{{ __('Search title or owner') }}" aria-label="{{ __('Search entries') }}" data-table-search>
            <select class="select sm:w-36" aria-label="{{ __('Filter by status') }}" data-table-filter><option value="">{{ __('All statuses') }}</option><option value="active">{{ __('Active') }}</option><option value="draft">{{ __('Draft') }}</option><option value="in-review">{{ __('In review') }}</option><option value="archived">{{ __('Archived') }}</option></select>
        </div>
    </div>
    <div class="overflow-x-auto border-t">
        <table class="table min-w-155"><thead><tr><th class="w-12" scope="col"><span class="sr-only">{{ __('Actions') }}</span></th><th data-sort>{{ __('Title') }}</th><th data-sort>{{ __('Category') }}</th><th data-sort>{{ __('Owner') }}</th><th>{{ __('Status') }}</th><th data-sort>{{ __('Updated') }}</th></tr></thead>
            <tbody>
                @foreach (config('cendana.demo.records') as $record)
                    <tr data-status="{{ $record['statusKey'] }}">
                        <td data-actions><a href="{{ route('examples.records.show') }}" class="btn btn-ghost btn-icon btn-sm text-muted-foreground" data-tip="{{ __('View') }}" data-tip-pos="right" aria-label="{{ __('View :name', ['name' => __($record['title'])]) }}"><x-ui.icon name="eye" /></a></td>
                        <td><a href="{{ route('examples.records.show') }}" class="font-medium text-primary hover:underline">{{ __($record['title']) }}</a><p class="mt-0.5 text-xs text-muted-foreground">{{ $record['code'] }}</p></td>
                        <td>{{ __($record['category']) }}</td><td>{{ $record['owner'] }}</td>
                        <td><span class="badge badge-{{ $record['tone'] }}">{{ __($record['status']) }}</span></td>
                        <td class="text-muted-foreground">{{ __($record['updated']) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div data-table-empty hidden><x-ui.table-state state="filtered"><button type="button" class="btn btn-outline" data-demo-reset-table>{{ __('Clear search') }}</button></x-ui.table-state></div>
    </div>
    <div class="flex flex-col items-center justify-between gap-3 border-t px-5 py-3 sm:flex-row sm:px-6"><p data-table-info class="text-sm text-muted-foreground"></p><div data-table-pager class="flex items-center gap-1"></div></div>
</div>
</x-layouts.admin>
