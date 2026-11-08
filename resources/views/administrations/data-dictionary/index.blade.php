<x-layouts.admin title="{{ __('Data Dictionary') }}" group="{{ __('Administrations') }}">
<x-ui.page-header title="{{ __('Data Dictionary') }}" description="{{ __('Reference values used in dropdowns across :app.', ['app' => config('cendana.name')]) }}" />

<section class="card" aria-labelledby="categories-heading">
    <div class="flex flex-wrap items-baseline justify-between gap-2 p-5 sm:px-6">
        <h2 id="categories-heading" class="card-title">{{ __('Categories') }}</h2>
        <p class="text-sm tabular-nums text-muted-foreground">{{ trans_choice(':count category|:count categories', $categories->count(), ['count' => $categories->count()]) }}</p>
    </div>
    <div class="overflow-x-auto border-t">
        <table class="table min-w-160">
            <thead>
                <tr>
                    <th scope="col" class="w-12"><span class="sr-only">{{ __('Actions') }}</span></th>
                    <th scope="col">{{ __('Category') }}</th>
                    <th scope="col">{{ __('Code') }}</th>
                    <th scope="col" class="text-right">{{ __('Items') }}</th>
                    <th scope="col">{{ __('Last updated') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($categories as $row)
                    <tr>
                        <td data-actions>
                            <a href="{{ route('administrations.data-dictionary.show', $row['category']) }}" class="btn btn-ghost btn-icon btn-sm text-muted-foreground" data-tip="{{ __('View') }}" data-tip-pos="right" aria-label="{{ __('View :name', ['name' => __($row['category']->label())]) }}"><x-ui.icon name="eye" /></a>
                        </td>
                        <td>
                            <p class="font-medium">{{ __($row['category']->label()) }}</p>
                            <p class="mt-0.5 text-xs text-muted-foreground">{{ __($row['category']->description()) }}</p>
                        </td>
                        <td class="whitespace-nowrap font-mono text-xs text-muted-foreground">{{ $row['category']->value }}</td>
                        <td class="whitespace-nowrap text-right tabular-nums">
                            {{ number_format($row['active']) }} <span class="text-muted-foreground">{{ __('active') }}</span>
                            @if ($row['total'] > $row['active'])
                                <p class="text-xs text-muted-foreground">{{ trans_choice(':count inactive|:count inactive', $row['total'] - $row['active'], ['count' => $row['total'] - $row['active']]) }}</p>
                            @endif
                        </td>
                        <td class="whitespace-nowrap text-muted-foreground">
                            @if ($row['lastUpdated'])
                                <time datetime="{{ $row['lastUpdated']->toIso8601String() }}" title="{{ $row['lastUpdated']->timezone($timezone)->translatedFormat('j F Y, H:i') }}">{{ $row['lastUpdated']->timezone($timezone)->translatedFormat('j M Y') }}</time>
                            @else
                                <span aria-label="{{ __('None') }}">—</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</section>
</x-layouts.admin>
