# 📝 Forms, validation & uploads

## 🔄 Turn the preview form into a Laravel form

`resources/views/components/examples/record-form.blade.php` currently renders `<form data-demo-form>` with no `method`/`action`. `advanced-inputs.js` intercepts the submit and shows a toast. To save entries, replace the form tag in that component:

```blade
@props(['mode' => 'create', 'record' => null])

@php($editing = $mode === 'edit')

<form method="POST"
    action="{{ $editing ? route('examples.records.update', $record) : route('examples.records.store') }}"
    class="space-y-6">
    @csrf
    @if ($editing)
        @method('PUT')
    @endif

    <x-ui.field name="title" label="{{ __('Entry title') }}" :required="true">
        <x-ui.input name="title" :value="$record?->title" required />
    </x-ui.field>
    <x-ui.field name="category" label="{{ __('Category') }}" :required="true">
        <x-ui.select name="category" :value="$record?->category"
            :options="['guides' => __('Guides'), 'reports' => __('Reports'), 'notes' => __('Notes'), 'archive' => __('Archive')]"
            placeholder="{{ __('Choose a category') }}" required />
    </x-ui.field>
    <x-ui.field name="status" label="{{ __('Status') }}" :required="true">
        <x-ui.select name="status" :value="$record?->status ?? 'draft'"
            :options="['draft' => __('Draft'), 'in-review' => __('In review'), 'active' => __('Active'), 'archived' => __('Archived')]"
            required />
    </x-ui.field>
    <x-ui.field name="summary" label="{{ __('Summary') }}">
        <x-ui.textarea name="summary" :value="$record?->summary" />
    </x-ui.field>
    <x-ui.button type="submit">{{ $editing ? __('Save changes') : __('Create entry') }}</x-ui.button>
</form>
```

This is a **replacement** for the demo form markup, not an extra component that already exists. Merge in the fields and card structure from the old component if you want to keep its layout. On the edit page call `<x-examples.record-form mode="edit" :record="$record" />`; on the create page call `<x-examples.record-form />`.

`x-ui.input`, `x-ui.select` and `x-ui.textarea` use `old($name, $value)`, so values that fail validation are kept; `x-ui.field` shows the first error from `$errors`. Leave out `data-demo-form` so JavaScript doesn't block the submit. Give every real form an `action` and `method`, and handle authorization and validation on the server.

## ✅ Flash message after a redirect

In the target view, for example `examples/records/show.blade.php`:

```blade
@if (session('status'))
    <x-ui.alert variant="success" title="{{ __('Success') }}">
        {{ session('status') }}
    </x-ui.alert>
@endif
```

## 📎 File uploads

The built-in `x-ui.file-preview` only previews and validates in the browser. Edit `resources/views/components/ui/file-preview.blade.php` to accept a `name` prop and set the `name` attribute on `<input type="file">` from it. Usage after that change:

```blade
<form method="POST" action="{{ route('attachments.store') }}" enctype="multipart/form-data">
    @csrf
    <x-ui.file-preview id="attachment" name="attachment" label="{{ __('Attachment') }}" />
    <x-ui.button type="submit">{{ __('Upload') }}</x-ui.button>
</form>
```

In the controller, validate and store the file on the server, for example:

```php
$data = $request->validate([
    'attachment' => ['required', 'file', 'mimes:png,jpg,jpeg,pdf', 'max:5120'],
]);

$path = $data['attachment']->store('attachments', 'public');
```

Make sure the public disk is set up (`php artisan storage:link` if files must be reachable from the web). Store `$path` in your app's table if you need to refer to it again. The input's `accept` prop only helps file selection; the built-in JS still limits types to PNG/JPG/PDF and size to `maxMb`. The backend rules remain authoritative.
