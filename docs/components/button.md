# 🔘 Button

`<x-ui.button>` renders a `<button>` with consistent styling. Source: `resources/views/components/ui/button.blade.php`; visual examples: `/components/buttons`.

## 🧩 API and rendered element

| Prop | Default | Purpose |
| --- | --- | --- |
| `variant` | `primary` | Visual style from the list below |
| `size` | `default` | `default`, `sm`, `lg` or `icon` |
| `type` | `button` | HTML type, e.g. `submit` or `reset` |
| Main slot | — | Text and/or icon inside the button |

Other attributes such as `disabled`, `name`, `value`, `aria-label`, `data-toast` and `data-modal-open` pass through to the `<button>`. Unknown `variant`/`size` names fall back to the default.

## 🎨 Every variant

```blade
<x-ui.button>{{ __('Primary') }}</x-ui.button>
<x-ui.button variant="secondary">{{ __('Secondary') }}</x-ui.button>
<x-ui.button variant="outline">{{ __('Outline') }}</x-ui.button>
<x-ui.button variant="ghost">{{ __('Ghost') }}</x-ui.button>
<x-ui.button variant="soft">{{ __('Soft highlight') }}</x-ui.button>
<x-ui.button variant="destructive">{{ __('Delete') }}</x-ui.button>
<x-ui.button variant="success">{{ __('Success') }}</x-ui.button>
<x-ui.button variant="warning">{{ __('Warning') }}</x-ui.button>
<x-ui.button variant="info">{{ __('Information') }}</x-ui.button>
<x-ui.button variant="link">{{ __('Link-styled action') }}</x-ui.button>
```

The `link` variant **is still a button**, not navigation. To change page, use `<a href="..." class="btn btn-primary">`.

## 📏 Sizes, icons and states

```blade
<x-ui.button size="sm">{{ __('Small') }}</x-ui.button>
<x-ui.button size="lg"><x-ui.icon name="plus" /> {{ __('Add data') }}</x-ui.button>
<x-ui.button size="icon" variant="outline" aria-label="{{ __('Open filters') }}" data-modal-open="#filter">
    <x-ui.icon name="filter" />
</x-ui.button>
<x-ui.button disabled>{{ __('Processing') }}</x-ui.button>
```

Icon-only buttons need an `aria-label`. The browser's `disabled` prevents clicks; to show real loading, tie the button state to the request, not the demo `data-load` that only waits on a timer.

## 📨 Laravel submit and delete confirmation

```blade
<form method="POST" action="{{ route('examples.records.store') }}">
    @csrf
    <x-ui.button type="submit" variant="primary">{{ __('Save entry') }}</x-ui.button>
</form>

<form method="POST" action="{{ route('examples.records.destroy', $record) }}">
    @csrf
    @method('DELETE')
    <x-ui.button type="submit" variant="destructive">{{ __('Delete entry') }}</x-ui.button>
</form>
```

The `examples.records.store` and `destroy` routes in this example need to be added by following the [CRUD guide](/integration/crud). `data-toast` and `data-load` are frontend-only behaviours; show success after the server confirms through a redirect/flash. See also [modal](/components/modal) for putting a delete form in a dialog.
