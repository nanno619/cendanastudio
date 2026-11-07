<x-layouts.admin title="{{ __('Add entry') }}" group="{{ __('Sample pages') }}">
<div><a href="{{ route('examples.records.index') }}" class="mb-3 inline-flex items-center gap-1 text-sm text-muted-foreground hover:text-foreground"><x-ui.icon name="arrow-left" class="h-4 w-4" />{{ __('Back to list') }}</a><x-ui.page-header title="{{ __('Add entry') }}" description="{{ __('A sample create form with no backend storage.') }}" /></div>
<x-examples.record-form />
</x-layouts.admin>
