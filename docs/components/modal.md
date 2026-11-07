# 🪟 Modal

`<x-ui.modal>` creates a `<dialog>` for confirmations or actions that need focused attention. Source: `resources/views/components/ui/modal.blade.php`; opening/closing is handled by `resources/js/admin/interactions.js`.

## 🧩 Props and slots

| Prop/slot | Default | Purpose |
| --- | --- | --- |
| `id` | required | Unique dialog ID and target of the open button |
| `title` | required | Dialog heading and label |
| `description` | `null` | Description, linked via `aria-describedby` |
| `icon` | `null` | Icon name next to the title |
| `tone` | `info` | Icon area colour, e.g. `info`, `danger`, `success` |
| `size` | `lg` | `sm` → `max-w-md`, `lg` → `max-w-lg`, `xl` → `max-w-2xl` |
| Main slot/`footer` | optional | Body content and the bottom button group |

Other attributes such as `class` apply to the `<dialog>`. `tone` is only used when there's an `icon`; sizes other than `sm`/`xl` use the default.

## 📖 Information modal and sizes

```blade
<x-ui.button variant="outline" data-modal-open="#info">{{ __('View information') }}</x-ui.button>
<x-ui.modal id="info" title="{{ __('Maintenance schedule') }}" description="{{ __('The app stays available.') }}" icon="info" size="sm">
    <p>{{ __('Maintenance is scheduled for Sunday at 01:00 MYT.') }}</p>
    <x-slot:footer>
        <x-ui.button data-modal-close>{{ __('Got it') }}</x-ui.button>
    </x-slot:footer>
</x-ui.modal>
```

## 🗑️ A real delete confirmation

```blade
<x-ui.button variant="destructive" data-modal-open="#delete-entry">{{ __('Delete') }}</x-ui.button>
<x-ui.modal id="delete-entry" title="{{ __('Delete entry?') }}" description="{{ __('This cannot be undone.') }}"
    icon="trash" tone="danger" size="sm">
    <p>{{ __(':title will be deleted.', ['title' => $record->title]) }}</p>
    <x-slot:footer>
        <x-ui.button variant="outline" data-modal-close>{{ __('Cancel') }}</x-ui.button>
        <form method="POST" action="{{ route('examples.records.destroy', $record) }}">
            @csrf
            @method('DELETE')
            <x-ui.button type="submit" variant="destructive">{{ __('Yes, delete') }}</x-ui.button>
        </form>
    </x-slot:footer>
</x-ui.modal>
```

The `destroy` route follows the [CRUD example](/integration/crud). The submit button gets **no** `data-modal-close` and no success toast before the server responds. After the redirect, show the controller's flash message.

## ⚙️ Dialog behaviour

Clicking an element with `data-modal-open="#id"` calls `showModal()`; `data-modal-close`, Esc and a backdrop click close it with an animation. Make sure the `id` is unique. The `<dialog>` element's built-in focus and closing behaviour comes from the browser; the dialog itself doesn't submit forms or validate on the server. For a side panel, use a [drawer](/components/drawer).
