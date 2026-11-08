<x-layouts.admin title="{{ __($category->label()) }}" group="{{ __('Data Dictionary') }}">
<div class="flex flex-wrap items-end justify-between gap-4">
<div>
    <a href="{{ route('administrations.data-dictionary.index') }}" class="mb-3 inline-flex items-center gap-1 text-sm text-muted-foreground hover:text-foreground">
        <x-ui.icon name="arrow-left" class="h-4 w-4" />{{ __('Back to Data Dictionary') }}
    </a>
    <div class="flex flex-wrap items-center gap-x-3 gap-y-2">
        <h1 class="text-2xl font-semibold tracking-tight">{{ __($category->label()) }}</h1>
        <span class="kbd">{{ $category->value }}</span>
    </div>
    <p class="mt-1 text-muted-foreground">{{ __($category->description()) }}</p>
</div>
<a href="{{ route('administrations.data-dictionary.items.create', $category) }}" class="btn btn-primary"><x-ui.icon name="plus" />{{ __('Add item') }}</a>
</div>

@if (session('status'))
    <x-ui.alert variant="success" role="status">{{ session('status') }}</x-ui.alert>
@endif

<section class="card" aria-labelledby="items-heading" data-table data-page-size="25">
    <div class="flex flex-col gap-3 p-5 sm:flex-row sm:items-center sm:justify-between sm:px-6">
        <div>
            <h2 id="items-heading" class="card-title">{{ __('Items') }}</h2>
            <p class="card-desc mt-1.5">{{ __('Inactive items are hidden from dropdowns but kept, so records that use them still show a label.') }}</p>
        </div>
        @if ($items->isNotEmpty())
            <div class="flex flex-col gap-2 sm:flex-row">
                <input type="search" class="input sm:w-56" placeholder="{{ __('Search code or label') }}" aria-label="{{ __('Search items') }}" data-table-search>
                <select class="select sm:w-36" aria-label="{{ __('Filter by status') }}" data-table-filter>
                    <option value="">{{ __('All statuses') }}</option>
                    <option value="active">{{ __('Active') }}</option>
                    <option value="inactive">{{ __('Inactive') }}</option>
                </select>
            </div>
        @endif
    </div>

    @if ($items->isEmpty())
        <div class="border-t">
            <x-ui.table-state state="empty" title="{{ __('No items yet') }}" description="{{ __('Items added to this category will appear here.') }}" />
        </div>
    @else
        <div class="overflow-x-auto border-t">
            <table class="table min-w-180">
                <thead>
                    <tr>
                        <th scope="col" class="w-24"><span class="sr-only">{{ __('Actions') }}</span></th>
                        <th scope="col" data-sort="num" class="w-20 text-right">{{ __('Order') }}</th>
                        <th scope="col" data-sort>{{ __('Code') }}</th>
                        <th scope="col" data-sort>{{ __('Label (English)') }}</th>
                        <th scope="col" data-sort>{{ __('Label (Malay)') }}</th>
                        <th scope="col">{{ __('Status') }}</th>
                        <th scope="col" data-sort="num">{{ __('Last updated') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($items as $item)
                        <tr data-status="{{ $item->is_active ? 'active' : 'inactive' }}" @class(['text-muted-foreground' => ! $item->is_active])>
                            <td data-actions class="whitespace-nowrap">
                                <a href="{{ route('administrations.data-dictionary.items.edit', [$category, $item]) }}" class="btn btn-ghost btn-icon btn-sm text-muted-foreground" data-tip="{{ __('Edit') }}" data-tip-pos="right" aria-label="{{ __('Edit :label', ['label' => $item->label_en]) }}"><x-ui.icon name="edit" /></a>
                                <form method="POST" action="{{ route('administrations.data-dictionary.items.status', [$category, $item]) }}" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" @class(['btn btn-ghost btn-icon btn-sm', 'text-muted-foreground' => $item->is_active, 'text-success' => ! $item->is_active]) data-tip="{{ $item->is_active ? __('Deactivate') : __('Activate') }}" data-tip-pos="right" aria-label="{{ $item->is_active ? __('Deactivate :label', ['label' => $item->label_en]) : __('Activate :label', ['label' => $item->label_en]) }}"><x-ui.icon name="power" /></button>
                                </form>
                            </td>
                            <td class="text-right tabular-nums text-muted-foreground" data-value="{{ $item->sort_order }}">{{ $item->sort_order }}</td>
                            <td class="whitespace-nowrap font-mono text-xs">{{ $item->code }}</td>
                            <td class="font-medium">
                                @if ($item->tone)
                                    <x-ui.badge :variant="$item->tone">{{ $item->label_en }}</x-ui.badge>
                                @else
                                    {{ $item->label_en }}
                                @endif
                            </td>
                            <td>
                                @if (filled($item->label_ms))
                                    {{ $item->label_ms }}
                                @else
                                    <span class="text-xs italic text-muted-foreground">{{ __('Uses English') }}</span>
                                @endif
                            </td>
                            <td>
                                @if ($item->is_active)
                                    <x-ui.badge variant="success" dot>{{ __('Active') }}</x-ui.badge>
                                @else
                                    <x-ui.badge variant="neutral" dot>{{ __('Inactive') }}</x-ui.badge>
                                @endif
                            </td>
                            <td class="whitespace-nowrap text-muted-foreground" data-value="{{ $item->updated_at?->timestamp }}">
                                <time datetime="{{ $item->updated_at?->toIso8601String() }}">{{ $item->updated_at?->timezone($timezone)->translatedFormat('j M Y') }}</time>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div data-table-empty hidden><x-ui.table-state state="filtered" /></div>
        </div>
        <div class="flex flex-col items-center justify-between gap-3 border-t px-5 py-3 sm:flex-row sm:px-6"><p data-table-info class="text-sm text-muted-foreground"></p><div data-table-pager class="flex items-center gap-1"></div></div>
    @endif
</section>
</x-layouts.admin>
