@php
    $date = fn (?\Carbon\CarbonInterface $value): string => $value?->translatedFormat('j F Y') ?? '—';
    $mainContact = $project->client->contacts->first();
@endphp
<x-layouts.admin title="{{ $project->name }}" group="{{ __('Projects') }}">
<div>
    <a href="{{ route('projects.index') }}" class="mb-3 inline-flex items-center gap-1 text-sm text-muted-foreground hover:text-foreground">
        <x-ui.icon name="arrow-left" class="h-4 w-4" />{{ __('Back to Projects') }}
    </a>
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div class="min-w-0">
            <p class="font-mono text-sm text-muted-foreground">{{ $project->code }}</p>
            <h1 class="truncate text-2xl font-semibold tracking-tight">{{ $project->name }}</h1>
            <p class="mt-1 flex flex-wrap items-center gap-2 text-muted-foreground">
                <span>{{ $project->client->name }}</span>
                <x-ui.badge :variant="$project->status->tone()">{{ __($project->status->label()) }}</x-ui.badge>
            </p>
        </div>
        @if ($canEdit)
            <a href="{{ route('projects.edit', $project) }}" class="btn btn-outline"><x-ui.icon name="edit" />{{ __('Edit') }}</a>
        @endif
    </div>
</div>

@if (session('status'))
    <x-ui.alert variant="success" role="status">{{ session('status') }}</x-ui.alert>
@endif

<div class="grid gap-4 lg:grid-cols-[minmax(0,1fr)_20rem]">
    <div class="min-w-0 space-y-4">
        <section class="card p-5 sm:p-6">
            <h2 class="card-title mb-5">{{ __('Details') }}</h2>
            <dl class="grid gap-x-8 gap-y-5 sm:grid-cols-2">
                @foreach ([__('Project manager') => $project->projectManager?->user->name ?? '—', __('Start date') => $date($project->start_date), __('End date') => $date($project->end_date)] as $term => $detail)
                    <div class="min-w-0">
                        <dt class="text-xs font-medium text-muted-foreground">{{ $term }}</dt>
                        <dd class="mt-1 break-words text-sm font-medium">{{ $detail }}</dd>
                    </div>
                @endforeach
            </dl>
            @if (filled($project->description))
                <div class="mt-5 border-t pt-5">
                    <h3 class="text-xs font-medium text-muted-foreground">{{ __('Description') }}</h3>
                    <p class="mt-1 whitespace-pre-line break-words text-sm">{{ $project->description }}</p>
                </div>
            @endif
        </section>
    </div>

    <div class="min-w-0 space-y-4">
        <section class="card p-5">
            <h2 class="card-title mb-4">{{ __('Client') }}</h2>
            <p class="font-medium">
                @if ($canViewClient)
                    <a href="{{ route('clients.show', $project->client) }}" class="hover:underline">{{ $project->client->name }}</a>
                @else
                    {{ $project->client->name }}
                @endif
            </p>
            @if ($mainContact)
                <div class="mt-4 text-sm">
                    <p class="text-xs font-medium text-muted-foreground">{{ __('Main contact') }}</p>
                    <p class="mt-1 font-medium">{{ $mainContact->name }}</p>
                    @if ($mainContact->position)<p class="text-muted-foreground">{{ $mainContact->position }}</p>@endif
                    @if ($mainContact->phone)<a href="tel:{{ preg_replace('/[^0-9+]/', '', $mainContact->phone) }}" class="block font-medium tabular-nums hover:underline">{{ $mainContact->phone }}</a>@endif
                    @if ($mainContact->email)<a href="mailto:{{ $mainContact->email }}" class="block break-all hover:underline">{{ $mainContact->email }}</a>@endif
                </div>
            @endif
        </section>
    </div>
</div>
</x-layouts.admin>
