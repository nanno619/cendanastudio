# 🧷 Field

`<x-ui.field>` combines a label, the control in its slot, help text and Laravel validation errors. Source: `resources/views/components/ui/field.blade.php`.

## 🧩 Prop contract

| Prop | Default | Purpose |
| --- | --- | --- |
| `label` | `null` | `<label>` above the control |
| `name` | `null` | Error key in `$errors` and source of the automatic ID |
| `help` | `null` | Hint shown when there's no error |
| `required` | `false` | Visual asterisk on the label |
| `class` | `''` | Extra wrapper classes |
| Main slot | — | The input/select/textarea control |

The automatic ID `f-{name}` turns `[`/`]` brackets into hyphens. If you set an `id` manually, use the same `id` on the field and the control inside it. Attributes other than `class` are **not** passed to the field wrapper, because the implementation uses `$attributes->only('class')`.

## ⌨️ A required input with help text

```blade
<x-ui.field name="email" label="{{ __('Email address') }}" help="{{ __("We'll send the receipt to this address.") }}" :required="true">
    <x-ui.input name="email" type="email" autocomplete="email" required />
</x-ui.field>
```

`required` on the field is only an asterisk. You still need the `required` attribute on the input and the `required` rule in the controller.

## 🔽 Select, textarea and grid

```blade
<div class="grid gap-4 sm:grid-cols-2">
    <x-ui.field name="category" label="{{ __('Category') }}">
        <x-ui.select name="category" placeholder="{{ __('Choose a category') }}"
            :options="['guides' => __('Guides'), 'notes' => __('Notes')]" />
    </x-ui.field>
    <x-ui.field name="summary" label="{{ __('Summary') }}" class="sm:col-span-2">
        <x-ui.textarea name="summary" rows="4" />
    </x-ui.field>
</div>
```

## ❗ Server validation messages

```php
$request->validate(['email' => ['required', 'email']]);
```

After a validation redirect, `x-ui.field name="email"` automatically shows `$errors->first('email')` **in place of** `help`; [input](/components/input) applies `aria-invalid` automatically. If the field's `name` doesn't match the control's `name`, the error isn't connected. For array inputs, use a unique explicit ID on the field/control. Error messages follow the interface language (the Malay ones come from `lang/ms/validation.php`).
