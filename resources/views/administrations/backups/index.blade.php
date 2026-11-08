@php
    $diskLabels = ['backups' => __('This server'), 'backups-s3' => __('Off-site (S3)')];
    $formatBytes = fn (float $bytes): string => match (true) {
        $bytes >= 1073741824 => number_format($bytes / 1073741824, 2).' GB',
        $bytes >= 1048576 => number_format($bytes / 1048576, 1).' MB',
        default => number_format(max($bytes, 0) / 1024, 1).' KB',
    };
@endphp

<x-layouts.admin title="{{ __('Backups') }}" group="{{ __('Administrations') }}">
<div class="flex flex-wrap items-end justify-between gap-4">
    <x-ui.page-header title="{{ __('Backups') }}" description="{{ __('Database and uploaded files, backed up every night at 1:00 am and kept on a rolling schedule.') }}" />
    <form method="POST" action="{{ route('administrations.backups.store') }}">
        @csrf
        <button type="submit" class="btn btn-primary" data-busy-text="{{ __('Backing up…') }}"><x-ui.icon name="download" />{{ __('Back up now') }}</button>
    </form>
</div>

@if (session('status'))
    <x-ui.alert variant="success" role="status">{{ session('status') }}</x-ui.alert>
@endif
@if (session('error'))
    <x-ui.alert variant="danger">{{ session('error') }}</x-ui.alert>
@endif

<div @class(['grid gap-4', 'md:grid-cols-2' => $statuses->count() > 1])>
    @foreach ($statuses as $status)
        @php
            $destination = $status->backupDestination();
            $reachable = $destination->isReachable();
            $newest = $reachable ? $destination->newestBackup() : null;
            $healthy = $reachable && $status->isHealthy();
        @endphp
        <section class="card p-5 sm:p-6" aria-labelledby="disk-{{ $destination->diskName() }}">
            <div class="flex items-start justify-between gap-3">
                <div class="flex items-center gap-3">
                    <span @class(['flex h-10 w-10 items-center justify-center rounded-lg', 'bg-success-soft text-success' => $healthy, 'bg-danger-soft text-danger' => ! $healthy]) aria-hidden="true">
                        <x-ui.icon :name="$destination->diskName() === 'backups' ? 'monitor' : 'globe'" class="h-5 w-5" />
                    </span>
                    <div>
                        <h2 id="disk-{{ $destination->diskName() }}" class="card-title">{{ $diskLabels[$destination->diskName()] ?? $destination->diskName() }}</h2>
                        <p class="mt-1 font-mono text-xs text-muted-foreground">{{ $destination->diskName() }}</p>
                    </div>
                </div>
                @if (! $reachable)
                    <x-ui.badge variant="danger" dot>{{ __('Unreachable') }}</x-ui.badge>
                @elseif ($healthy)
                    <x-ui.badge variant="success" dot>{{ __('Healthy') }}</x-ui.badge>
                @else
                    <x-ui.badge variant="warning" dot>{{ __('Needs attention') }}</x-ui.badge>
                @endif
            </div>

            @if ($reachable)
                <dl class="mt-5 grid grid-cols-3 gap-4 text-sm">
                    <div>
                        <dt class="text-xs font-medium text-muted-foreground">{{ __('Latest backup') }}</dt>
                        <dd class="mt-1 font-medium">
                            @if ($newest)
                                <time datetime="{{ $newest->date()->toIso8601String() }}" title="{{ $newest->date()->timezone($timezone)->translatedFormat('j F Y, H:i') }}">{{ $newest->date()->diffForHumans() }}</time>
                            @else
                                {{ __('Never') }}
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-muted-foreground">{{ __('Backups kept') }}</dt>
                        <dd class="mt-1 font-medium tabular-nums">{{ number_format($destination->backups()->count()) }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-muted-foreground">{{ __('Space used') }}</dt>
                        <dd class="mt-1 font-medium tabular-nums">{{ $formatBytes($destination->usedStorage()) }}</dd>
                    </div>
                </dl>
                @foreach ($status->failureMessages() as $message)
                    <p class="mt-4 text-sm text-warning">{{ $message['message'] }}</p>
                @endforeach
            @else
                <p class="mt-4 text-sm text-muted-foreground">{{ __('Couldn’t connect to this location. Check its settings in the server’s .env file.') }}</p>
            @endif
        </section>
    @endforeach
</div>

@if ($statuses->count() < 2)
    <x-ui.alert variant="warning" title="{{ __('No off-site copy yet') }}">
        {{ __('Backups are only on this server, so they would be lost with it. Add S3 settings to the server’s .env to also keep a copy elsewhere.') }}
    </x-ui.alert>
@endif

<section class="card" aria-labelledby="backups-heading">
    <div class="flex flex-wrap items-baseline justify-between gap-2 p-5 sm:px-6">
        <div>
            <h2 id="backups-heading" class="card-title">{{ __('Backup files') }}</h2>
            <p class="card-desc mt-1.5">{{ __('Old backups are removed automatically: all kept for 7 days, then one a day for 16 days, one a week for 8 weeks and one a month for 4 months.') }}</p>
        </div>
        <p class="text-sm tabular-nums text-muted-foreground">{{ trans_choice(':count backup|:count backups', $backups->count(), ['count' => $backups->count()]) }}</p>
    </div>

    @if ($backups->isEmpty())
        <div class="border-t">
            <x-ui.table-state state="empty" title="{{ __('No backups yet') }}" description="{{ __('The first one runs tonight at 1:00 am, or use Back up now.') }}" />
        </div>
    @else
        <div class="overflow-x-auto border-t">
            <table class="table min-w-150">
                <thead>
                    <tr>
                        <th scope="col" class="w-12"><span class="sr-only">{{ __('Actions') }}</span></th>
                        <th scope="col">{{ __('Created') }}</th>
                        <th scope="col">{{ __('File') }}</th>
                        <th scope="col" class="text-right">{{ __('Size') }}</th>
                        @if ($offsiteNames !== null)
                            <th scope="col">{{ __('Off-site copy') }}</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach ($backups as $backup)
                        @php $file = basename($backup->path()); @endphp
                        <tr>
                            <td data-actions>
                                <a href="{{ route('administrations.backups.download', [$primaryDisk, $file]) }}" class="btn btn-ghost btn-icon btn-sm text-muted-foreground" data-tip="{{ __('Download') }}" data-tip-pos="right" aria-label="{{ __('Download :file', ['file' => $file]) }}"><x-ui.icon name="download" /></a>
                            </td>
                            <td class="whitespace-nowrap">
                                <time datetime="{{ $backup->date()->toIso8601String() }}">{{ $backup->date()->timezone($timezone)->translatedFormat('j M Y, H:i') }}</time>
                                <p class="text-xs text-muted-foreground">{{ $backup->date()->diffForHumans() }}</p>
                            </td>
                            <td class="font-mono text-xs text-muted-foreground">{{ $file }}</td>
                            <td class="whitespace-nowrap text-right tabular-nums">{{ $formatBytes($backup->sizeInBytes()) }}</td>
                            @if ($offsiteNames !== null)
                                <td>
                                    @if ($offsiteNames->contains($file))
                                        <x-ui.badge variant="success" dot>{{ __('Copied') }}</x-ui.badge>
                                    @else
                                        <x-ui.badge variant="warning" dot>{{ __('Missing') }}</x-ui.badge>
                                    @endif
                                </td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</section>
</x-layouts.admin>
