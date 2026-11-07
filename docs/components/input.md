# ⌨️ Input

`<x-ui.input>` renders an `<input>` (or an input inside `.input-group` when there's an `icon`/`addon`) with `old()` and Laravel error support. Source: `resources/views/components/ui/input.blade.php`.

## 🧩 Props and attributes

| Prop | Default | Purpose |
| --- | --- | --- |
| `name` | `null` | Field name and base of the automatic ID |
| `value` | `null` | Initial value; replaced by `old(name)` after validation |
| `type` | `text` | HTML input type |
| `invalid` | `null` | Force the invalid state; when null, uses `$errors->has(name)` |
| `icon` | `null` | Icon on the left of the input |
| `addon` | `null` | Text label on the left of the input |

The `placeholder`, `autocomplete`, `min`, `max`, `step`, `required`, `readonly` and `disabled` attributes and CSS classes pass through to the `<input>`. Set an `id` if you don't want the default `f-{name}` ID.

## 📝 Text and email

```blade
<x-ui.field name="title" label="{{ __('Title') }}" :required="true">
    <x-ui.input name="title" :value="$record?->title" placeholder="{{ __('e.g. Team guide') }}" required />
</x-ui.field>
<x-ui.field name="email" label="{{ __('Email') }}">
    <x-ui.input name="email" type="email" autocomplete="email" icon="mail" />
</x-ui.field>
```

`icon` decorates the input; it isn't a label. You still need `<x-ui.field>` or a separate HTML label so the control has a clear name.

## 💰 Numbers, addons and validation

```blade
<x-ui.field name="price" label="{{ __('Price') }}">
    <x-ui.input name="price" type="number" addon="RM" min="0" step="0.01" :value="$product?->price" />
</x-ui.field>
<x-ui.input name="sku" value="KP-001" readonly />
<x-ui.input name="serial" value="ABC-001" disabled />
```

`addon` is only a visual label; the `price` request value must still be a number and validated on the server. `disabled` fields aren't submitted with the form, while `readonly` ones are. When `invalid` is true or `$errors` has a message, the component adds `aria-invalid="true"` and `input-invalid`; the message text is shown by [field](/components/field).

## 📎 Files and limits

```blade
<x-ui.input name="attachment" type="file" accept="image/png,image/jpeg" />
```

For `type="file"`, the component deliberately never fills `value`; browsers forbid pre-filling a file path. Make sure the form uses `enctype="multipart/form-data"` and validate on the server. For a drag area with a local thumbnail, use [file preview](/components/file-preview).
