<x-layouts.admin title="{{ __('Edit entry') }}" group="{{ __('Sample pages') }}">
<div><a href="{{ route('examples.records.show') }}" class="mb-3 inline-flex items-center gap-1 text-sm text-muted-foreground hover:text-foreground"><x-ui.icon name="arrow-left" class="h-4 w-4" />{{ __('Back to details') }}</a><x-ui.page-header title="{{ __('Edit entry') }}" description="{{ __('A sample form with its data already filled in.') }}" /></div>
<x-examples.record-form mode="edit" />
</x-layouts.admin>
