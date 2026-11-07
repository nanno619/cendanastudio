<x-layouts.admin title="{{ __('Data patterns') }}" group="{{ __('Components') }}">
<x-ui.page-header title="{{ __('Data patterns') }}" description="{{ __('Reusable filters, details, history, tabs and action confirmations.') }}" />
@if(session('status'))<x-ui.alert variant="info">{{ session('status') }}</x-ui.alert>@endif

<x-ui.filter-bar :action="route('showcase.data-patterns')" :search="request('q')" :reset-url="route('showcase.data-patterns')" placeholder="{{ __('Search entries…') }}">
    <div class="min-w-40 space-y-1.5">
        <label class="label" for="pattern-status">{{ __('Status') }}</label>
        <x-ui.select id="pattern-status" name="status" placeholder="{{ __('All statuses') }}" :value="request('status')"
            :options="['active' => __('Active'), 'draft' => __('Draft'), 'archived' => __('Archived')]" :searchable="true" />
    </div>
    <x-slot:active>
        @if(request()->filled('q'))<x-ui.badge variant="soft">{{ __('Search:') }} {{ request('q') }}</x-ui.badge>@endif
        @if(request()->filled('status'))<x-ui.badge variant="info">{{ __('Status:') }} {{ request('status') }}</x-ui.badge>@endif
        @if(!request()->filled('q') && !request()->filled('status'))<span class="text-muted-foreground">{{ __('No filters yet.') }}</span>@endif
    </x-slot:active>
</x-ui.filter-bar>

<div class="grid gap-4 lg:grid-cols-2">
    <x-ui.detail-list title="{{ __('Entry information') }}" :items="[
        ['label' => __('Code'), 'value' => 'ENT-001'],
        ['label' => __('Title'), 'value' => __('Onboarding guide')],
        ['label' => __('Owner'), 'value' => 'Ayu Rahmawati'],
        ['label' => __('Category'), 'value' => __('Guides')],
    ]" />

    <x-ui.card title="{{ __('Entry history') }}" description="{{ __('An easy-to-scan sequence of events.') }}">
        <x-ui.timeline :items="[
            ['title' => __('Entry published'), 'time' => __('Today, :time', ['time' => '10:30']), 'datetime' => '2026-10-03T10:30:00', 'description' => __('The latest version is available to the team.'), 'tone' => 'success'],
            ['title' => __('Content reviewed'), 'time' => __('Yesterday, :time', ['time' => '15:20']), 'description' => __('Bagas approved the document.')],
            ['title' => __('Entry created'), 'time' => '1 Oct 2026', 'description' => __('Ayu wrote the first draft.')],
        ]" />
    </x-ui.card>
</div>

<x-ui.card title="{{ __('Tabs and confirmation') }}" description="{{ __('Tab content is rendered on the server; the delete action uses a Laravel form.') }}">
    <x-ui.tabs id="record-tabs" aria-label="{{ __('Entry information') }}" :tabs="['summary' => __('Overview'), 'history' => __('History'), 'settings' => __('Settings')]" variant="pill">
        <x-slot:tab_summary><p class="text-sm text-muted-foreground">{{ __('An orientation guide for new team members.') }}</p></x-slot:tab_summary>
        <x-slot:tab_history><p class="text-sm text-muted-foreground">{{ __('Last changed by Bagas yesterday.') }}</p></x-slot:tab_history>
        <x-slot:tab_settings><p class="text-sm text-muted-foreground">{{ __('The action below is a frontend demonstration only.') }}</p></x-slot:tab_settings>
    </x-ui.tabs>
    <div class="mt-5 border-t pt-5">
        <x-ui.button variant="destructive" size="sm" data-modal-open="#demo-confirm">{{ __('Show confirmation') }}</x-ui.button>
    </div>
</x-ui.card>

<x-ui.confirm-action id="demo-confirm" title="{{ __('Delete entry?') }}" description="{{ __('A sample confirmation form; no data is deleted.') }}"
    :action="route('showcase.data-patterns.preview')" method="POST" label="{{ __('Confirm') }}">
    <p class="text-sm text-muted-foreground">{{ __('With real data, point the action at a DELETE route and add server-side authorization.') }}</p>
</x-ui.confirm-action>
</x-layouts.admin>
