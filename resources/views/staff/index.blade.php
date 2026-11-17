@php
    $isFiltered = collect($filters)->filter(fn ($value): bool => filled($value))->isNotEmpty();
    $removeFilter = fn (string ...$keys): string => request()->fullUrlWithoutQuery([...$keys, 'page']);
@endphp

<x-layouts.admin title="{{ __('Staff') }}" group="{{ __('Modules') }}">
<div class="flex flex-wrap items-end justify-between gap-4">
    <x-ui.page-header title="{{ __('Staff') }}" description="{{ __('Staff records and their sign-in accounts. Staff who have left are hidden unless you filter for them.') }}" />
    <a href="{{ route('staff.create') }}" class="btn btn-primary"><x-ui.icon name="userplus" />{{ __('Add staff') }}</a>
</div>

@if (session('status'))
    <x-ui.alert variant="success" role="status">{{ session('status') }}</x-ui.alert>
@endif

<x-ui.filter-bar :action="route('staff.index')" :search="$filters['q'] ?? null" :reset-url="$isFiltered ? route('staff.index') : null" placeholder="{{ __('Search name, email, staff no or IC') }}">
    <div class="min-w-44 space-y-1.5">
        <label class="label" for="staff-department">{{ __('Department') }}</label>
        <x-ui.select id="staff-department" name="department" placeholder="{{ __('All departments') }}" :value="$filters['department'] ?? null" :options="$departments" />
    </div>
    <div class="min-w-40 space-y-1.5">
        <label class="label" for="staff-status">{{ __('Status') }}</label>
        <x-ui.select id="staff-status" name="status" placeholder="{{ __('Current staff') }}" :value="$filters['status'] ?? null" :options="$statuses" />
    </div>

    @if ($isFiltered)
        <x-slot:active>
            @if (filled($filters['q'] ?? null))
                <a href="{{ $removeFilter('q') }}" class="chip hover:bg-accent" aria-label="{{ __('Remove filter: :filter', ['filter' => __('Search')]) }}">{{ __('Search:') }} “{{ $filters['q'] }}”<x-ui.icon name="x" class="h-3.5 w-3.5 text-muted-foreground" /></a>
            @endif
            @if (filled($filters['department'] ?? null))
                <a href="{{ $removeFilter('department') }}" class="chip hover:bg-accent" aria-label="{{ __('Remove filter: :filter', ['filter' => __('Department')]) }}">{{ __('Department:') }} {{ $departments[$filters['department']] ?? $filters['department'] }}<x-ui.icon name="x" class="h-3.5 w-3.5 text-muted-foreground" /></a>
            @endif
            @if (filled($filters['status'] ?? null))
                <a href="{{ $removeFilter('status') }}" class="chip hover:bg-accent" aria-label="{{ __('Remove filter: :filter', ['filter' => __('Status')]) }}">{{ __('Status:') }} {{ $statuses[$filters['status']] }}<x-ui.icon name="x" class="h-3.5 w-3.5 text-muted-foreground" /></a>
            @endif
        </x-slot:active>
    @endif
</x-ui.filter-bar>

<section class="card" aria-labelledby="staff-heading">
    <div class="flex flex-wrap items-baseline justify-between gap-2 p-5 sm:px-6">
        <h2 id="staff-heading" class="card-title">{{ __('Staff') }}</h2>
        <p class="text-sm tabular-nums text-muted-foreground">
            {{ trans_choice(':count person|:count people', $employees->total(), ['count' => number_format($employees->total())]) }}
        </p>
    </div>

    @if ($employees->isEmpty())
        <div class="border-t">
            @if ($isFiltered)
                <x-ui.table-state state="filtered" description="{{ __('No staff match these filters.') }}">
                    <a href="{{ route('staff.index') }}" class="btn btn-outline btn-sm">{{ __('Clear filters') }}</a>
                </x-ui.table-state>
            @else
                <x-ui.table-state state="empty" title="{{ __('No staff yet') }}" description="{{ __('Add your first staff member to create their record and sign-in account.') }}" />
            @endif
        </div>
    @else
        <div class="overflow-x-auto border-t">
            <table class="table min-w-210">
                <thead>
                    <tr>
                        <th scope="col" class="w-20"><span class="sr-only">{{ __('Actions') }}</span></th>
                        <th scope="col">{{ __('Name') }}</th>
                        <th scope="col">{{ __('Staff number') }}</th>
                        <th scope="col">{{ __('Department') }}</th>
                        <th scope="col">{{ __('Join date') }}</th>
                        <th scope="col">{{ __('Status') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($employees as $employee)
                        @php $status = $employee->status(); @endphp
                        <tr>
                            <td data-actions class="whitespace-nowrap">
                                <a href="{{ route('staff.show', $employee) }}" class="btn btn-ghost btn-icon btn-sm text-muted-foreground" data-tip="{{ __('View') }}" data-tip-pos="right" aria-label="{{ __('View :name', ['name' => $employee->user->name]) }}"><x-ui.icon name="eye" /></a>
                                <a href="{{ route('staff.edit', $employee) }}" class="btn btn-ghost btn-icon btn-sm text-muted-foreground" data-tip="{{ __('Edit') }}" data-tip-pos="right" aria-label="{{ __('Edit :label', ['label' => $employee->user->name]) }}"><x-ui.icon name="edit" /></a>
                            </td>
                            <td>
                                <div class="flex min-w-0 items-center gap-2.5">
                                    <span class="avatar avatar-sm" aria-hidden="true">{{ $employee->user->initials }}</span>
                                    <div class="min-w-0">
                                        <p class="truncate font-medium">{{ $employee->user->name }}</p>
                                        <p class="truncate text-xs text-muted-foreground">{{ $employee->user->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="font-mono text-sm">{{ $employee->staff_no }}</td>
                            <td>
                                <p>{{ $departments[$employee->department] ?? '—' }}</p>
                                @if ($employee->position)
                                    <p class="text-xs text-muted-foreground">{{ $positions[$employee->position] ?? $employee->position }}</p>
                                @endif
                            </td>
                            <td class="whitespace-nowrap tabular-nums">{{ $employee->join_date->translatedFormat('j M Y') }}</td>
                            <td><x-ui.badge :variant="$status->tone()">{{ __($status->label()) }}</x-ui.badge></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <x-ui.pagination :paginator="$employees" />
    @endif
</section>
</x-layouts.admin>
