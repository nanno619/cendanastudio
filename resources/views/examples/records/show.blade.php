<x-layouts.admin title="{{ __('Entry details') }}" group="{{ __('Sample pages') }}">
@php($record = config('cendana.demo.records.0'))
<div class="flex flex-wrap items-start justify-between gap-4">
    <div><a href="{{ route('examples.records.index') }}" class="mb-3 inline-flex items-center gap-1 text-sm text-muted-foreground hover:text-foreground"><x-ui.icon name="arrow-left" class="h-4 w-4" />{{ __('Back to list') }}</a><h1 class="text-2xl font-semibold tracking-tight">{{ __($record['title']) }}</h1><p class="mt-1 text-muted-foreground">{{ __('A sample detail page with key information and history.') }}</p></div>
    <a href="{{ route('examples.records.edit') }}" class="btn btn-primary"><x-ui.icon name="edit" />{{ __('Edit entry') }}</a>
</div>

<div class="grid gap-4 lg:grid-cols-[minmax(0,2fr)_minmax(16rem,1fr)]">
    <x-ui.card title="{{ __('Overview') }}" description="{{ __('The information most often needed on a detail page.') }}">
        <p class="leading-relaxed">{{ __('First steps for new team members. This sample entry shows how a title, metadata and notes can be laid out without tying the template to one kind of project.') }}</p>
        <dl class="mt-6 grid gap-5 border-t pt-5 sm:grid-cols-2">
            <div><dt class="text-sm text-muted-foreground">{{ __('Code') }}</dt><dd class="mt-1 font-medium">{{ $record['code'] }}</dd></div>
            <div><dt class="text-sm text-muted-foreground">{{ __('Category') }}</dt><dd class="mt-1 font-medium">{{ __($record['category']) }}</dd></div>
            <div><dt class="text-sm text-muted-foreground">{{ __('Owner') }}</dt><dd class="mt-1 font-medium">{{ $record['owner'] }}</dd></div>
            <div><dt class="text-sm text-muted-foreground">{{ __('Status') }}</dt><dd class="mt-1"><span class="badge badge-{{ $record['tone'] }}">{{ __($record['status']) }}</span></dd></div>
        </dl>
    </x-ui.card>
    <x-ui.card title="{{ __('Activity') }}" description="{{ __('A short change history for context.') }}">
        <ol class="space-y-5 text-sm">
            <li class="flex gap-3"><span class="mt-1 h-2 w-2 shrink-0 rounded-full bg-primary"></span><div><p class="font-medium">{{ __('Entry updated') }}</p><p class="text-muted-foreground">{{ __('Ayu Rahmawati · Today, 09:24') }}</p></div></li>
            <li class="flex gap-3"><span class="mt-1 h-2 w-2 shrink-0 rounded-full bg-muted-foreground"></span><div><p class="font-medium">{{ __('Status set to active') }}</p><p class="text-muted-foreground">{{ __('Bagas Prasetyo · Yesterday') }}</p></div></li>
            <li class="flex gap-3"><span class="mt-1 h-2 w-2 shrink-0 rounded-full bg-muted-foreground"></span><div><p class="font-medium">{{ __('Entry created') }}</p><p class="text-muted-foreground">Ayu Rahmawati · 26 Sep 2026</p></div></li>
        </ol>
    </x-ui.card>
</div>
</x-layouts.admin>
