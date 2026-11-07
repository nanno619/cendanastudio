# 📢 Alert

`<x-ui.alert>` is an **inline** message that stays on the page, unlike a toast that disappears after a few seconds. Source: `resources/views/components/ui/alert.blade.php`.

## 🧩 Props and slots

| Input | Default | Purpose |
| --- | --- | --- |
| `variant` | `info` | `info`, `success`, `warning`, `danger` |
| `title` | `null` | Bold heading above the content |
| `solid` | `false` | Stronger filled style |
| Main slot | — | Message content; may contain links |
| `action` | none | Button/link on the right |

The variant sets the icon and the `alert-{variant}` class; only `danger` gets `role="alert"` automatically. Use one of the available variants, because the class is built from the prop.

## 🎨 Four semantic tones

```blade
<x-ui.alert variant="info" title="{{ __('Information') }}">{{ __('Settings apply after the page reloads.') }}</x-ui.alert>
<x-ui.alert variant="success" title="{{ __('Saved') }}">{{ __('Profile changes saved.') }}</x-ui.alert>
<x-ui.alert variant="warning" title="{{ __('Low stock') }}">{{ __('Check the products that need restocking.') }}</x-ui.alert>
<x-ui.alert variant="danger" title="{{ __('Failed to load') }}">{{ __('Try again after checking your connection.') }}</x-ui.alert>
```

## 🖱️ Actions and the solid style

```blade
<x-ui.alert variant="warning" title="{{ __('Sync pending') }}" :solid="true">
    {{ __('The latest data is not available yet.') }}
    <x-slot:action>
        <a href="{{ request()->fullUrl() }}" class="btn btn-outline btn-sm">{{ __('Reload') }}</a>
    </x-slot:action>
</x-ui.alert>
```

The `action` slot doesn't add click behaviour; connect the link to a route or the button to your own action. The component doesn't add a dismiss button either; if you need one, see the `data-dismissible`/`data-dismiss` pattern at `/components/feedback`.

## ✅ Messages from a controller

```php
return redirect()->route('admin.settings')->with('status', __('Settings saved.'));
```

```blade
@if (session('status'))
    <x-ui.alert variant="success" title="{{ __('Success') }}">{{ session('status') }}</x-ui.alert>
@endif
```

Blade escapes output expressions in templates. For per-field form errors, use [field](/components/field); for failures that must stay readable on the page, an alert is a better fit than a toast.
