@php
    $editing = $project->exists;
    $title = $editing ? __('Edit :label', ['label' => $project->name]) : __('Add project');
    $date = fn (?\Carbon\CarbonInterface $value): ?string => $value?->toDateString();
@endphp
<x-layouts.admin :title="$title" group="{{ __('Projects') }}">
<div>
    <a href="{{ $editing ? route('projects.show', $project) : route('projects.index') }}" class="mb-3 inline-flex items-center gap-1 text-sm text-muted-foreground hover:text-foreground">
        <x-ui.icon name="arrow-left" class="h-4 w-4" />{{ $editing ? __('Back to :name', ['name' => $project->name]) : __('Back to Projects') }}
    </a>
    <x-ui.page-header :title="$title" description="{{ __('Fields marked * are required.') }}" />
</div>

@if ($clients === [])
    <x-ui.alert variant="warning">
        {{ __('Add a client first; every project belongs to one.') }}
        @if (\App\Models\Client::isEditableBy(auth()->user()))
            <a href="{{ route('clients.create') }}">{{ __('Add client') }}</a>
        @endif
    </x-ui.alert>
@endif

<form method="POST" action="{{ $editing ? route('projects.update', $project) : route('projects.store') }}" class="space-y-4">
    @csrf
    @if ($editing) @method('PUT') @endif

    <section class="card">
        <div class="card-header">
            <h2 class="card-title">{{ __('Project') }}</h2>
        </div>
        <div class="card-content grid gap-5 md:grid-cols-3">
            <x-ui.field label="{{ __('Project code') }}" name="code" required help="{{ __('e.g. PRJ-2026-001') }}">
                <x-ui.input name="code" :value="$project->code" class="font-mono uppercase" maxlength="30" autocomplete="off" spellcheck="false" required />
            </x-ui.field>
            <x-ui.field label="{{ __('Project name') }}" name="name" required class="md:col-span-2">
                <x-ui.input name="name" :value="$project->name" maxlength="200" required />
            </x-ui.field>
            <x-ui.field label="{{ __('Client') }}" name="client" required>
                <x-ui.select name="client" :value="$selectedClient" :options="$clients" include-blank :searchable="count($clients) > 15" required />
            </x-ui.field>
            <x-ui.field label="{{ __('Project manager') }}" name="project_manager" help="{{ $canAssignManager ? null : __('You manage the projects you add.') }}">
                @if ($canAssignManager)
                    <x-ui.select name="project_manager" :value="$project->projectManager?->ulid" :options="$managers" include-blank :searchable="count($managers) > 15" />
                @else
                    <x-ui.input name="project_manager_name" id="f-project_manager" :value="$editing ? ($project->projectManager?->user->name ?? '—') : auth()->user()->name" readonly tabindex="-1" />
                @endif
            </x-ui.field>
            <x-ui.field label="{{ __('Status') }}" name="status" required>
                <x-ui.select name="status" :value="$project->status?->value" :options="$statuses" required />
            </x-ui.field>
            <x-ui.field label="{{ __('Start date') }}" name="start_date">
                <x-ui.input name="start_date" type="date" :value="$date($project->start_date)" />
            </x-ui.field>
            <x-ui.field label="{{ __('End date') }}" name="end_date">
                <x-ui.input name="end_date" type="date" :value="$date($project->end_date)" />
            </x-ui.field>
        </div>
        <div class="card-content border-t pt-5">
            <x-ui.field label="{{ __('Description') }}" name="description" help="{{ __('Scope, deliverables or anything the team should know.') }}">
                <x-ui.textarea name="description" :value="$project->description" rows="5" maxlength="5000" />
            </x-ui.field>
        </div>
    </section>

    <div class="flex justify-end">
        <button type="submit" class="btn btn-primary">{{ $editing ? __('Save changes') : __('Add project') }}</button>
    </div>
</form>
</x-layouts.admin>
