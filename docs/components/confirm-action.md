# 🛡️ Confirm action

`<x-ui.confirm-action>` wraps a [modal](/components/modal) around a Laravel form for actions that need confirmation. Source: `resources/views/components/ui/confirm-action.blade.php`.

## 🧩 Props

| Prop | Default | Purpose |
| --- | --- | --- |
| `id`, `title`, `description`, `action` | required | Dialog identity, text and form URL |
| `method` | `DELETE` | HTTP method via `@method` when not POST |
| `label` | “Delete” (translated) | Submit button text |
| `tone` | `destructive` | `destructive` or `primary` |
| Main slot | — | Extra explanation inside the dialog |

The form always uses the HTML POST method and `@csrf`; for DELETE/PUT/PATCH, Blade adds `@method` spoofing. Make sure the route matches the method and authorize in the controller.

## 🗑️ Delete confirmation

```blade
<x-ui.button variant="destructive" data-modal-open="#delete-record">{{ __('Delete') }}</x-ui.button>
<x-ui.confirm-action id="delete-record" title="{{ __('Delete entry?') }}"
    description="{{ __('This cannot be undone.') }}"
    :action="route('examples.records.destroy', $record)">
    <p>{{ __(':title will be permanently deleted.', ['title' => $record->title]) }}</p>
</x-ui.confirm-action>
```

## ✅ Confirming a non-destructive action

```blade
<x-ui.button data-modal-open="#publish-record">{{ __('Publish') }}</x-ui.button>
<x-ui.confirm-action id="publish-record" title="{{ __('Publish entry?') }}"
    description="{{ __('The entry will be visible to the team.') }}" :action="route('records.publish', $record)"
    method="POST" label="{{ __('Yes, publish') }}" tone="primary" />
```

The `records.publish` route is illustrative and needs to be created. Don't rely on the modal for permissions or validation; do those on the server. The `/components/data-patterns` demo POSTs to a preview route that only redirects and deletes nothing.
