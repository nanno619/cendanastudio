# 📝 Textarea

`<x-ui.textarea>` shows a multi-line input with `old()` values and an automatic invalid state. Source: `resources/views/components/ui/textarea.blade.php`.

## 🧩 API

| Prop | Default | Purpose |
| --- | --- | --- |
| `name` | `null` | Request name and automatic ID |
| `value` | `null` | Initial content, overridden by `old(name)` |
| `rows` | `4` | Initial number of rows |
| `invalid` | `null` | Force the invalid state |

Add HTML attributes such as `placeholder`, `maxlength`, `required`, `readonly` or `class` directly on the component tag. The element content comes from the `value` prop, **not** the slot.

## 📝 Create and edit forms

```blade
<x-ui.field name="summary" label="{{ __('Summary') }}" help="{{ __('One or two sentences.') }}">
    <x-ui.textarea name="summary" rows="4" :value="$record?->summary"
        placeholder="{{ __('Write an entry summary…') }}" />
</x-ui.field>
```

When controller validation fails and redirects, `old('summary')` replaces `$record?->summary`, so the user's typing isn't lost.

## 📏 Long text and read-only

```blade
<x-ui.field name="notes" label="{{ __('Internal notes') }}">
    <x-ui.textarea name="notes" rows="8" maxlength="2000" :value="$record?->notes" />
</x-ui.field>
<x-ui.textarea name="preview" :value="$previewText" rows="5" readonly aria-label="{{ __('Preview') }}" />
```

`maxlength` in the browser is a convenience, not a replacement for server validation such as `'notes' => ['nullable', 'string', 'max:2000']`. When `$errors` has an error, the textarea gets `aria-invalid="true"`; the error text is shown by [field](/components/field).
