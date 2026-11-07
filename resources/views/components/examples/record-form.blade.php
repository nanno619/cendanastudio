@props(['mode' => 'create'])

@php
    $editing = $mode === 'edit';
@endphp

<form data-demo-form data-demo-message="{{ $editing ? __('Change preview complete. No data was saved.') : __('New entry preview complete. No data was saved.') }}" class="space-y-6">
    <div class="card">
        <div class="card-header"><h2 class="card-title">{{ __('Main information') }}</h2><p class="card-desc">{{ __('A sample form structure for many kinds of data.') }}</p></div>
        <div class="card-content grid gap-5 md:grid-cols-2">
            <x-ui.field label="{{ __('Entry title') }}" name="title" required class="md:col-span-2">
                <x-ui.input name="title" :value="$editing ? __('Onboarding guide') : null" placeholder="{{ __('e.g. Onboarding guide') }}" required />
            </x-ui.field>
            <x-ui.field label="{{ __('Category') }}" name="category" required>
                <x-ui.select name="category" placeholder="{{ __('Choose a category') }}" :value="$editing ? 'guides' : null" :options="['guides' => __('Guides'), 'reports' => __('Reports'), 'notes' => __('Notes'), 'archive' => __('Archive')]" required />
            </x-ui.field>
            <x-ui.field label="{{ __('Status') }}" name="status" required>
                <x-ui.select name="status" :value="$editing ? 'active' : 'draft'" :options="['draft' => __('Draft'), 'in-review' => __('In review'), 'active' => __('Active'), 'archived' => __('Archived')]" required />
            </x-ui.field>
            <x-ui.field label="{{ __('Overview') }}" name="summary" help="{{ __('One or two sentences describing the entry.') }}" class="md:col-span-2">
                <x-ui.textarea name="summary" :value="$editing ? __('First steps for new team members.') : null" rows="4" placeholder="{{ __('Write a short summary…') }}" />
            </x-ui.field>
        </div>
    </div>
    <div class="card p-5 sm:p-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div><p class="font-medium">{{ __('Frontend preview') }}</p><p class="mt-1 text-sm text-muted-foreground">{{ __('This form does not send or save data to the server.') }}</p></div>
            <div class="flex flex-wrap gap-2"><a href="{{ route('examples.records.index') }}" class="btn btn-outline">{{ __('Cancel') }}</a><button type="submit" class="btn btn-primary">{{ $editing ? __('Save changes') : __('Create entry') }}</button></div>
        </div>
    </div>
</form>
