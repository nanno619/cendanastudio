# 📎 File preview

`<x-ui.file-preview>` shows a choose/drag area, an image thumbnail, file metadata, a remove button and a local error message. Source: `resources/views/components/ui/file-preview.blade.php`; logic: `resources/js/admin/advanced-inputs.js`.

## 🧩 Props

| Prop | Default | Purpose |
| --- | --- | --- |
| `id` | required | File input/label ID |
| `label` | “Choose a file” (translated) | Main dropzone text |
| `accept` | `image/png,image/jpeg,application/pdf` | Hint for the browser's file dialog filter |
| `maxMb` | `5` | Local size limit in MB |

`class` and other attributes apply to the wrapper, **not the file input**. The built-in input has no `name` yet, so files aren't sent to Laravel unless you change the component.

## 🖼️ Size variants and preview

```blade
<x-ui.file-preview id="demo-attachment" label="{{ __('Choose an image or PDF') }}" />
<x-ui.file-preview id="large-attachment" label="{{ __('Choose an attachment') }}" :max-mb="10" class="mt-6" />
```

The JS currently only accepts PNG, JPG and PDF MIME types, regardless of the `accept` prop. If you change `accept` or the size limit, align the validation in `advanced-inputs.js` and in Laravel. Images are previewed through a local object URL; PDFs show as an icon and name, not their content. The remove button clears the input and revokes the object URL. Error and size messages are translated through the [JavaScript strings](/guide/theming#language).

## 📤 Enabling Laravel uploads

Add a `name` to the input in `file-preview.blade.php`; to make it configurable, add a `'name' => null` prop and output it as the `name` attribute on `<input type="file">`. After changing the component:

```blade
<form method="POST" action="{{ route('attachments.store') }}" enctype="multipart/form-data">
    @csrf
    <x-ui.file-preview id="attachment" name="attachment" label="{{ __('Entry attachment') }}" />
    <x-ui.button type="submit">{{ __('Upload') }}</x-ui.button>
</form>
```

The `attachments.store` route is an example route you'd create. In the controller, call `$request->validate(['attachment' => ['required', 'file', 'mimes:png,jpg,jpeg,pdf', 'max:5120']])`, then store the file on your app's disk. `accept` and `maxMb` in the browser are **not** server security validation. Full details are in the [upload guide](/integration/forms#file-uploads).
