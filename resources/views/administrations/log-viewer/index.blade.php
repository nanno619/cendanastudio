<x-layouts.admin title="{{ __('Log Viewer') }}" group="{{ __('Administrations') }}">
<div class="flex flex-wrap items-end justify-between gap-4">
    <x-ui.page-header title="{{ __('Log Viewer') }}" description="{{ __('Application errors and messages from the log files on this server. Times are shown in :timezone.', ['timezone' => config('cendana.timezone')]) }}" />
    <a href="{{ route('log-viewer.index') }}" target="_blank" rel="noopener" class="btn btn-outline"><x-ui.icon name="external" />{{ __('Open in new tab') }}</a>
</div>

<section class="card overflow-hidden">
    <iframe src="{{ route('log-viewer.index') }}" title="{{ __('Log Viewer') }}" class="block h-[calc(100dvh-14rem)] min-h-[32rem] w-full border-0" loading="lazy"></iframe>
</section>
</x-layouts.admin>
