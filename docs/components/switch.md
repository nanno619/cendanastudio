# 🔀 Switch

`<x-ui.switch>` is a checkbox styled as a toggle for on/off values. Source: `resources/views/components/ui/switch.blade.php`.

## 🧩 Props and submitted HTML

| Prop | Default | Purpose |
| --- | --- | --- |
| `name` | required | Name of the hidden input and checkbox |
| `label` | `null` | Clickable label text |
| `checked` | `false` | Initial state; `old(name)` takes priority |
| `value` | `1` | Checkbox value when on |
| `hint` | `null` | Explanation under the label |

The component renders `<input type="hidden" name="..." value="0">` followed by a checkbox with the same `name`. When off, the server receives `0`; when on, the browser sends `0` and the checkbox value (`1` by default), and Laravel reads the last value for that name. The automatic ID `f-{name}` can be replaced with an `id` attribute.

## ✅ Basics and state from a model

```blade
<x-ui.switch name="notifications" label="{{ __('Receive notifications') }}" hint="{{ __("We'll send important updates.") }}" />
<x-ui.switch name="published" label="{{ __('Publish entry') }}" :checked="(bool) $record->published" />
```

In the controller:

```php
$data = $request->validate(['published' => ['required', 'boolean']]);
$record->update(['published' => $request->boolean('published')]);
```

## 🧾 In a form, with a custom ID

```blade
<form method="POST" action="{{ route('settings.update') }}">
    @csrf
    <x-ui.switch id="email-updates" name="email_updates" label="{{ __('Email updates') }}"
        :checked="(bool) ($settings->email_updates ?? false)" />
    <x-ui.button type="submit">{{ __('Save') }}</x-ui.button>
</form>
```

The `settings.update` route is an example you'd need to create; the built-in settings route is currently `Route::view` (GET). To disable the control, note that a `disabled` attribute on the component only passes to the checkbox; the hidden input still sends `0`. Handle that explicitly when you build the form.
