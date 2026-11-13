# 📎 File preview

`<x-ui.file-preview>` shows a choose/drag area, an image thumbnail, file metadata, a remove button and a local error message. Source: `resources/views/components/ui/file-preview.blade.php`; logic: `resources/js/admin/advanced-inputs.js`.

## 🧩 Props

| Prop | Default | Purpose |
| --- | --- | --- |
| `id` | required | File input/label ID |
| `name` | `null` | `name` of the file input; set it to send the file with the form |
| `label` | “Choose a file” (translated) | Main dropzone text |
| `hint` | “Drag here or click to choose. PNG, JPG, PDF · max N MB.” (translated) | Help text under the label; override it when `accept` differs |
| `accept` | `image/png,image/jpeg,application/pdf` | Hint for the browser's file dialog filter |
| `maxMb` | `5` | Local size limit in MB |

`class` and other attributes apply to the wrapper, **not the file input**. Without `name` the file isn't sent to Laravel.

## 🖼️ Size variants and preview

```blade
<x-ui.file-preview id="demo-attachment" label="{{ __('Choose an image or PDF') }}" />
<x-ui.file-preview id="large-attachment" label="{{ __('Choose an attachment') }}" :max-mb="10" class="mt-6" />
```

The JS currently only accepts PNG, JPG and PDF MIME types, regardless of the `accept` prop. If you change `accept` or the size limit, align the validation in `advanced-inputs.js` and in Laravel. Images are previewed through a local object URL; PDFs show as an icon and name, not their content. The remove button clears the input and revokes the object URL. Error and size messages are translated through the [JavaScript strings](/guide/theming#language).

## 📤 Laravel uploads

Pass `name` and put the component in a `multipart/form-data` form:

```blade
<form method="POST" action="{{ route('attachments.store') }}" enctype="multipart/form-data">
    @csrf
    <x-ui.file-preview id="attachment" name="attachment" label="{{ __('Entry attachment') }}" />
    <x-ui.button type="submit">{{ __('Upload') }}</x-ui.button>
</form>
```

The `attachments.store` route is an example route you'd create. Validate it with `['required', 'file', new App\Rules\AllowedUpload]` so the File Security policy applies, then store it (e.g. with Media Library). Settings → Company → Basic Details uses it this way for the logo. `accept` and `maxMb` in the browser are **not** server security validation. Full details are in the [upload guide](/integration/forms#file-uploads).
