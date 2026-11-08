@php
    $eventLabel = fn (?string $event): string => $event ? __(\Illuminate\Support\Str::headline($event)) : __('Other');
    $today = now($timezone)->toDateString();
    $yesterday = now($timezone)->subDay()->toDateString();
    $isFiltered = collect($filters)->filter(fn ($value): bool => filled($value))->isNotEmpty();
    $selectedCauser = $causers->firstWhere('id', (int) ($filters['user'] ?? 0));
    $removeFilter = fn (string ...$keys): string => request()->fullUrlWithoutQuery([...$keys, 'page']);
    $lastDay = null;
@endphp

<x-layouts.admin title="{{ __('Audit Trail') }}" group="{{ __('Administrations') }}">
<div class="flex flex-wrap items-end justify-between gap-4">
    <x-ui.page-header title="{{ __('Audit Trail') }}" description="{{ __('Who did what, and when, across :app.', ['app' => config('cendana.name')]) }}" />
    <p class="flex items-center gap-1.5 text-xs text-muted-foreground">
        <x-ui.icon name="clock" class="h-3.5 w-3.5" />
        {{ __('Times shown in :zone', ['zone' => 'UTC'.now($timezone)->format('P')]) }}
    </p>
</div>

<x-ui.filter-bar :action="route('administrations.audit-trail.index')" :search="$filters['q'] ?? null" :reset-url="$isFiltered ? route('administrations.audit-trail.index') : null" placeholder="{{ __('Search description, name or email') }}">
    <div class="min-w-36 space-y-1.5">
        <label class="label" for="audit-event">{{ __('Event') }}</label>
        <x-ui.select id="audit-event" name="event" placeholder="{{ __('All events') }}" :value="$filters['event'] ?? null"
            :options="$events->mapWithKeys(fn (string $event): array => [$event => $eventLabel($event)])->all()" />
    </div>
    <div class="min-w-44 space-y-1.5">
        <label class="label" for="audit-user">{{ __('User') }}</label>
        <x-ui.select id="audit-user" name="user" placeholder="{{ __('All users') }}" :value="$filters['user'] ?? null"
            :options="$causers->pluck('name', 'id')->all()" :searchable="$causers->count() > 15" />
    </div>
    <div class="w-full space-y-1.5 sm:w-40">
        <label class="label" for="audit-from">{{ __('From') }}</label>
        <input id="audit-from" name="from" type="text" class="input" value="{{ $filters['from'] ?? '' }}" placeholder="{{ __('Any date') }}" autocomplete="off" data-date-picker>
    </div>
    <div class="w-full space-y-1.5 sm:w-40">
        <label class="label" for="audit-to">{{ __('To') }}</label>
        <input id="audit-to" name="to" type="text" @class(['input', 'input-invalid' => $errors->has('to')]) value="{{ $filters['to'] ?? '' }}" placeholder="{{ __('Any date') }}" autocomplete="off" data-date-picker @error('to') aria-invalid="true" aria-describedby="audit-to-error" @enderror>
    </div>

    @if ($isFiltered)
        <x-slot:active>
            @if (filled($filters['q'] ?? null))
                <a href="{{ $removeFilter('q') }}" class="chip hover:bg-accent" aria-label="{{ __('Remove filter: :filter', ['filter' => __('Search')]) }}">{{ __('Search:') }} “{{ $filters['q'] }}”<x-ui.icon name="x" class="h-3.5 w-3.5 text-muted-foreground" /></a>
            @endif
            @if (filled($filters['event'] ?? null))
                <a href="{{ $removeFilter('event') }}" class="chip hover:bg-accent" aria-label="{{ __('Remove filter: :filter', ['filter' => __('Event')]) }}">{{ __('Event:') }} {{ $eventLabel($filters['event']) }}<x-ui.icon name="x" class="h-3.5 w-3.5 text-muted-foreground" /></a>
            @endif
            @if ($selectedCauser)
                <a href="{{ $removeFilter('user') }}" class="chip hover:bg-accent" aria-label="{{ __('Remove filter: :filter', ['filter' => __('User')]) }}">{{ __('User:') }} {{ $selectedCauser->name }}<x-ui.icon name="x" class="h-3.5 w-3.5 text-muted-foreground" /></a>
            @endif
            @if (filled($filters['from'] ?? null) || filled($filters['to'] ?? null))
                <a href="{{ $removeFilter('from', 'to') }}" class="chip hover:bg-accent" aria-label="{{ __('Remove filter: :filter', ['filter' => __('Date range')]) }}">
                    {{ __('Date:') }}
                    {{ filled($filters['from'] ?? null) ? \Illuminate\Support\Carbon::parse($filters['from'])->translatedFormat('j M Y') : '…' }}
                    –
                    {{ filled($filters['to'] ?? null) ? \Illuminate\Support\Carbon::parse($filters['to'])->translatedFormat('j M Y') : '…' }}
                    <x-ui.icon name="x" class="h-3.5 w-3.5 text-muted-foreground" />
                </a>
            @endif
        </x-slot:active>
    @endif
</x-ui.filter-bar>

@error('to')
    <p id="audit-to-error" class="-mt-2 text-sm text-danger" role="alert">{{ $message }}</p>
@enderror

<section class="card" aria-labelledby="audit-heading">
    <div class="flex flex-wrap items-baseline justify-between gap-2 p-5 sm:px-6">
        <h2 id="audit-heading" class="card-title">{{ __('Activity') }}</h2>
        <p class="text-sm tabular-nums text-muted-foreground">
            {{ trans_choice(':count entry|:count entries', $activities->total(), ['count' => number_format($activities->total())]) }}
        </p>
    </div>

    @if ($activities->isEmpty())
        <div class="border-t">
            @if ($isFiltered)
                <x-ui.table-state state="filtered" description="{{ __('No activity matches these filters. Try a wider date range or clear them.') }}">
                    <a href="{{ route('administrations.audit-trail.index') }}" class="btn btn-outline btn-sm">{{ __('Clear filters') }}</a>
                </x-ui.table-state>
            @else
                <x-ui.table-state state="empty" title="{{ __('No activity recorded yet') }}" description="{{ __('Sign-ins and changes will appear here as people use the system.') }}" />
            @endif
        </div>
    @else
        <div class="overflow-x-auto border-t">
            <table class="table min-w-210">
                <thead>
                    <tr>
                        <th scope="col" class="w-12"><span class="sr-only">{{ __('Actions') }}</span></th>
                        <th scope="col" class="w-24">{{ __('Time') }}</th>
                        <th scope="col">{{ __('User') }}</th>
                        <th scope="col" class="w-28">{{ __('Event') }}</th>
                        <th scope="col">{{ __('Description') }}</th>
                        <th scope="col">{{ __('Subject') }}</th>
                        <th scope="col" class="text-right">{{ __('Changes') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($activities as $activity)
                        @php
                            $at = $activity->created_at->timezone($timezone);
                            $day = $at->toDateString();
                            $changedFields = count($activity->attribute_changes?->get('attributes') ?? []);
                        @endphp
                        @if ($day !== $lastDay)
                            @php $lastDay = $day; @endphp
                            <tr class="bg-muted/50 hover:bg-muted/50">
                                <th scope="colgroup" colspan="7" class="h-8 text-xs font-medium">
                                    <time datetime="{{ $day }}">
                                        @if ($day === $today)
                                            {{ __('Today') }} <span class="font-normal text-muted-foreground">· {{ $at->translatedFormat('j F Y') }}</span>
                                        @elseif ($day === $yesterday)
                                            {{ __('Yesterday') }} <span class="font-normal text-muted-foreground">· {{ $at->translatedFormat('j F Y') }}</span>
                                        @else
                                            {{ $at->translatedFormat('l, j F Y') }}
                                        @endif
                                    </time>
                                </th>
                            </tr>
                        @endif
                        <tr>
                            <td data-actions>
                                <a href="{{ route('administrations.audit-trail.show', $activity) }}" class="btn btn-ghost btn-icon btn-sm text-muted-foreground" data-tip="{{ __('View') }}" data-tip-pos="right" aria-label="{{ __('View entry #:id', ['id' => $activity->id]) }}"><x-ui.icon name="eye" /></a>
                            </td>
                            <td class="whitespace-nowrap tabular-nums text-muted-foreground">
                                <time datetime="{{ $activity->created_at->toIso8601String() }}" title="{{ $at->translatedFormat('j F Y, H:i:s') }}">{{ $at->format('H:i') }}</time>
                            </td>
                            <td>
                                @if ($activity->causer)
                                    <div class="flex min-w-0 items-center gap-2.5">
                                        <span class="avatar avatar-sm" aria-hidden="true">{{ $activity->causer->initials }}</span>
                                        <div class="min-w-0">
                                            <p class="truncate font-medium">{{ $activity->causer->name }}</p>
                                            <p class="truncate text-xs text-muted-foreground">{{ $activity->causer->email }}</p>
                                        </div>
                                    </div>
                                @else
                                    <div class="flex items-center gap-2.5 text-muted-foreground">
                                        <span class="avatar avatar-sm bg-muted text-muted-foreground" aria-hidden="true"><x-ui.icon name="zap" class="h-3.5 w-3.5" /></span>
                                        {{ $activity->causer_id ? __('Deleted user') : __('System') }}
                                    </div>
                                @endif
                            </td>
                            <td><x-admin.audit-event :event="$activity->event" /></td>
                            <td class="max-w-sm">{{ $activity->description }}</td>
                            <td class="whitespace-nowrap text-muted-foreground">
                                @if ($activity->subject_type)
                                    {{ __(\Illuminate\Support\Str::headline(class_basename($activity->subject_type))) }}
                                    <span class="tabular-nums">#{{ $activity->subject_id }}</span>
                                @else
                                    <span aria-label="{{ __('None') }}">—</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap text-right tabular-nums text-muted-foreground">
                                @if ($changedFields > 0)
                                    {{ trans_choice(':count field|:count fields', $changedFields, ['count' => $changedFields]) }}
                                @else
                                    <span aria-label="{{ __('None') }}">—</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <x-ui.pagination :paginator="$activities" />
    @endif
</section>
</x-layouts.admin>
