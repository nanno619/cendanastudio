<x-layouts.admin title="{{ __('Filters and inputs') }}" group="{{ __('Components') }}">
<x-ui.page-header title="{{ __('Filters and inputs') }}" description="{{ __('Search and selection patterns that work without a backend.') }}" />

<section class="grid gap-4 lg:grid-cols-2">
    <x-ui.card title="Combobox" description="{{ __('Type to filter options; use the arrow keys and Enter.') }}">
        <x-ui.combobox id="demo-category" label="{{ __('Category') }}" name="category" placeholder="{{ __('Search categories…') }}" :options="[
            ['value' => 'documents', 'label' => __('Documents')],
            ['value' => 'reports', 'label' => __('Reports')],
            ['value' => 'guides', 'label' => __('Guides')],
            ['value' => 'notes', 'label' => __('Team notes')],
            ['value' => 'archive', 'label' => __('Archive')],
        ]" />
        <p class="field-help mt-3">{{ __('The selected value is kept in a hidden input for use in a Laravel form.') }}</p>
    </x-ui.card>

    <x-ui.card title="{{ __('Date range') }}" description="{{ __('Quick presets and manual date picking with order validation.') }}">
        <x-ui.date-range id="demo-period" from-name="from" to-name="to" />
    </x-ui.card>

    <x-ui.card title="{{ __('Searchable select') }}" description="{{ __('Choices.js enhances the select without changing the input name Laravel receives.') }}">
        <x-ui.field label="{{ __('Document category') }}" name="category">
            <x-ui.select name="category" placeholder="{{ __('Choose a category') }}" :searchable="true"
                :options="['guides' => __('Guides'), 'reports' => __('Reports'), 'notes' => __('Notes'), 'archive' => __('Archive')]" />
        </x-ui.field>
    </x-ui.card>

    <x-ui.card title="{{ __('Filter chips') }}" description="{{ __('Combine several criteria and see the active selection.') }}">
        <div data-filter-chips>
            <p class="label mb-3">{{ __('Status') }}</p>
            <div class="flex flex-wrap gap-2" role="group" aria-label="{{ __('Filter by status') }}">
                <button type="button" class="filter-chip" aria-pressed="true" data-filter-chip>{{ __('All') }}</button>
                <button type="button" class="filter-chip" aria-pressed="false" data-filter-chip>{{ __('Active') }}</button>
                <button type="button" class="filter-chip" aria-pressed="false" data-filter-chip>{{ __('Pending') }}</button>
                <button type="button" class="filter-chip" aria-pressed="false" data-filter-chip>{{ __('Archived') }}</button>
            </div>
            <p class="mt-4 text-sm text-muted-foreground" role="status" aria-live="polite" data-filter-summary>{{ __('Showing all statuses.') }}</p>
        </div>
    </x-ui.card>

    <x-ui.card title="{{ __('File preview') }}" description="{{ __('Choose or drag a file; validation happens in the browser with no upload to the server.') }}">
        <x-ui.file-preview id="demo-upload" label="{{ __('Choose an image or PDF') }}" />
    </x-ui.card>
</section>
</x-layouts.admin>
