@php
    $at = $activity->created_at->timezone($timezone);
    $causer = $activity->causer;
    $subject = $activity->subject;
    $subjectType = $activity->subject_type ? __(\Illuminate\Support\Str::headline(class_basename($activity->subject_type))) : null;
    $initials = fn (string $name): string => collect(preg_split('/\s+/', trim($name)) ?: [])->take(2)->map(fn (string $part): string => mb_strtoupper(mb_substr($part, 0, 1)))->implode('');
    $context = collect($activity->properties ?? [])->reject(fn ($value): bool => blank($value));
    $contextLabels = ['ip' => __('IP address'), 'user_agent' => __('Browser / device')];
@endphp

<x-layouts.admin title="{{ __('Audit entry #:id', ['id' => $activity->id]) }}" group="{{ __('Administrations') }}">
<div>
    <a href="{{ $backUrl }}" class="mb-3 inline-flex items-center gap-1 text-sm text-muted-foreground hover:text-foreground">
        <x-ui.icon name="arrow-left" class="h-4 w-4" />{{ __('Back to Audit Trail') }}
    </a>
    <div class="flex flex-wrap items-center gap-x-3 gap-y-2">
        <h1 class="text-2xl font-semibold tracking-tight">{{ $activity->description }}</h1>
        <x-admin.audit-event :event="$activity->event" />
    </div>
    <p class="mt-1 text-muted-foreground">
        <time datetime="{{ $activity->created_at->toIso8601String() }}">{{ $at->translatedFormat('l, j F Y \· H:i:s') }}</time>
        <span class="text-xs">({{ 'UTC'.$at->format('P') }})</span>
        · {{ $activity->created_at->diffForHumans() }}
    </p>
</div>

<div class="grid gap-4 lg:grid-cols-[minmax(0,2fr)_minmax(18rem,1fr)]">
    <div class="min-w-0 space-y-4">
        <section class="card" aria-labelledby="changes-heading">
            <div class="flex flex-wrap items-baseline justify-between gap-2 p-5 sm:px-6">
                <h2 id="changes-heading" class="card-title">{{ __('Changes') }}</h2>
                @if ($changes)
                    <p class="text-sm tabular-nums text-muted-foreground">{{ trans_choice(':count field|:count fields', count($changes), ['count' => count($changes)]) }}</p>
                @endif
            </div>
            @if ($changes)
                <div class="overflow-x-auto border-t">
                    <table class="table min-w-120">
                        <thead>
                            <tr>
                                <th scope="col" class="w-1/4">{{ __('Field') }}</th>
                                <th scope="col">{{ __('Before') }}</th>
                                <th scope="col" class="w-8" aria-hidden="true"></th>
                                <th scope="col">{{ __('After') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($changes as $change)
                                <tr>
                                    <th scope="row" class="h-auto whitespace-normal font-medium text-foreground">
                                        {{ __(\Illuminate\Support\Str::headline($change['field'])) }}
                                        <p class="mt-0.5 font-mono text-xs font-normal text-muted-foreground">{{ $change['field'] }}</p>
                                    </th>
                                    @if ($change['hidden'])
                                        <td colspan="3" class="text-muted-foreground">
                                            <span class="inline-flex items-center gap-1.5"><x-ui.icon name="lock" class="h-3.5 w-3.5" />{{ __('Changed (value hidden)') }}</span>
                                        </td>
                                    @else
                                        <td class="break-all">
                                            @if (! $change['hasOld'])
                                                <span class="text-muted-foreground">{{ __('Not recorded') }}</span>
                                            @else
                                                <x-admin.audit-value :value="$change['old']" class="bg-danger-soft text-danger" />
                                            @endif
                                        </td>
                                        <td class="px-0 text-muted-foreground" aria-hidden="true"><x-ui.icon name="arrow-right" class="h-4 w-4" /></td>
                                        <td class="break-all"><x-admin.audit-value :value="$change['new']" class="bg-success-soft text-success" /></td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="border-t">
                    <x-ui.table-state state="empty" title="{{ __('No field changes') }}" description="{{ __('This event was recorded without before and after values, which is normal for sign-ins and similar actions.') }}" />
                </div>
            @endif
        </section>

        @if ($context->isNotEmpty())
            <section class="card p-5 sm:p-6" aria-labelledby="context-heading">
                <h2 id="context-heading" class="card-title mb-5">{{ __('Context') }}</h2>
                <dl class="grid gap-x-8 gap-y-5 sm:grid-cols-2">
                    @foreach ($context as $key => $value)
                        <div class="min-w-0">
                            <dt class="text-xs font-medium text-muted-foreground">{{ $contextLabels[$key] ?? __(\Illuminate\Support\Str::headline($key)) }}</dt>
                            <dd class="mt-1 break-words text-sm font-medium @if ($key === 'ip') font-mono @endif">{{ is_scalar($value) ? $value : json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</dd>
                        </div>
                    @endforeach
                </dl>
            </section>
        @endif
    </div>

    <div class="min-w-0 space-y-4">
        <section class="card p-5 sm:p-6" aria-labelledby="details-heading">
            <h2 id="details-heading" class="card-title mb-5">{{ __('Details') }}</h2>
            <dl class="space-y-5 text-sm">
                <div>
                    <dt class="text-xs font-medium text-muted-foreground">{{ __('Performed by') }}</dt>
                    <dd class="mt-2">
                        @if ($causer)
                            <div class="flex min-w-0 items-center gap-2.5">
                                <span class="avatar" aria-hidden="true">{{ $initials($causer->name) }}</span>
                                <div class="min-w-0">
                                    <p class="truncate font-medium">{{ $causer->name }}</p>
                                    <p class="truncate text-xs text-muted-foreground">{{ $causer->email }}</p>
                                </div>
                            </div>
                            @if ($causer instanceof \App\Models\User && $causer->getRoleNames()->isNotEmpty())
                                <div class="mt-2 flex flex-wrap gap-1.5">
                                    @foreach ($causer->getRoleNames() as $role)
                                        <x-ui.badge variant="outline">{{ __($role) }}</x-ui.badge>
                                    @endforeach
                                </div>
                            @endif
                        @else
                            <span class="font-medium text-muted-foreground">{{ $activity->causer_id ? __('Deleted user') : __('System') }}</span>
                        @endif
                    </dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-muted-foreground">{{ __('Subject') }}</dt>
                    <dd class="mt-1 font-medium">
                        @if ($subjectType)
                            {{ $subjectType }} <span class="tabular-nums text-muted-foreground">#{{ $activity->subject_id }}</span>
                            @if ($subject?->name)
                                <p class="font-normal text-muted-foreground">{{ $subject->name }}</p>
                            @elseif (! $subject)
                                <p class="font-normal text-muted-foreground">{{ __('This record no longer exists.') }}</p>
                            @endif
                        @else
                            <span class="text-muted-foreground">{{ __('None') }}</span>
                        @endif
                    </dd>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <dt class="text-xs font-medium text-muted-foreground">{{ __('Log') }}</dt>
                        <dd class="mt-1 font-medium">{{ $activity->log_name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-muted-foreground">{{ __('Entry ID') }}</dt>
                        <dd class="mt-1 font-medium tabular-nums">#{{ $activity->id }}</dd>
                    </div>
                </div>
            </dl>
        </section>

        <section class="card p-5 sm:p-6" aria-labelledby="related-heading">
            <h2 id="related-heading" class="card-title">{{ $activity->subject_type ? __('Other activity on this record') : __('Other activity by this user') }}</h2>
            @if ($related->isEmpty())
                <p class="mt-3 text-sm text-muted-foreground">{{ __('Nothing else has been recorded yet.') }}</p>
            @else
                <ol class="mt-4 space-y-1">
                    @foreach ($related as $entry)
                        <li>
                            <a href="{{ route('administrations.audit-trail.show', $entry) }}" class="-mx-2 flex items-start gap-3 rounded-md p-2 hover:bg-accent focus-ring">
                                <x-admin.audit-event :event="$entry->event" class="mt-0.5 shrink-0" />
                                <span class="min-w-0 flex-1">
                                    <span class="block truncate text-sm font-medium">{{ $entry->description }}</span>
                                    <span class="block text-xs text-muted-foreground">
                                        {{ $entry->causer?->name ?? __('System') }} · <time datetime="{{ $entry->created_at->toIso8601String() }}">{{ $entry->created_at->timezone($timezone)->translatedFormat('j M Y, H:i') }}</time>
                                    </span>
                                </span>
                            </a>
                        </li>
                    @endforeach
                </ol>
            @endif
        </section>
    </div>
</div>
</x-layouts.admin>
