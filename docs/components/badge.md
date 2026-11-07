# 🏷️ Badge

`<x-ui.badge>` shows a status label as a `<span>`; it isn't a link or a button. Source: `resources/views/components/ui/badge.blade.php`.

## 🧩 Props and variants

| Prop | Default | Purpose |
| --- | --- | --- |
| `variant` | `neutral` | `primary`, `soft`, `success`, `danger`, `warning`, `info`, `neutral`, `outline`, `solid-success`, `solid-danger`, `solid-warning`, `solid-info` |
| `pill` | `false` | Fully rounded ends |
| `dot` | `false` | Status dot |
| `icon` | `null` | Icon name from the sprite |
| Main slot | — | Label text |

Unknown variants fall back to `neutral`. `class`, `title` and other HTML attributes pass through to the `<span>`.

## 🎨 Every style family

```blade
<x-ui.badge variant="primary">{{ __('Primary') }}</x-ui.badge>
<x-ui.badge variant="soft">{{ __('Highlight') }}</x-ui.badge>
<x-ui.badge variant="success">{{ __('Active') }}</x-ui.badge>
<x-ui.badge variant="danger">{{ __('Failed') }}</x-ui.badge>
<x-ui.badge variant="warning">{{ __('Pending') }}</x-ui.badge>
<x-ui.badge variant="info">{{ __('In progress') }}</x-ui.badge>
<x-ui.badge variant="neutral">{{ __('Archived') }}</x-ui.badge>
<x-ui.badge variant="outline">{{ __('Draft') }}</x-ui.badge>
<x-ui.badge variant="solid-success">{{ __('Completed') }}</x-ui.badge>
<x-ui.badge variant="solid-danger">{{ __('Cancelled') }}</x-ui.badge>
<x-ui.badge variant="solid-warning">{{ __('Needs review') }}</x-ui.badge>
<x-ui.badge variant="solid-info">{{ __('Shipped') }}</x-ui.badge>
```

## ⚙️ Combining pill, dot and icon

```blade
<x-ui.badge variant="success" :dot="true">{{ __('Online') }}</x-ui.badge>
<x-ui.badge variant="warning" :pill="true">{{ __('Awaiting payment') }}</x-ui.badge>
<x-ui.badge variant="info" icon="truck" :pill="true">{{ __('Shipped') }}</x-ui.badge>
```

Use `:dot="true"` and `:pill="true"` so Blade passes a boolean, not the string `"false"`, which is still truthy in PHP.

## 🗃️ Statuses from the database

```blade
@php
    $tone = match ($record->status) {
        'active' => 'success',
        'in-review' => 'info',
        'draft' => 'warning',
        default => 'neutral',
    };
@endphp
<x-ui.badge :variant="$tone" :dot="true">{{ __(ucfirst(str_replace('-', ' ', $record->status))) }}</x-ui.badge>
```

Map domain values to existing variants; never use user input directly as a CSS class. Describe the status in text, not only with colour or a dot. See [`/components/buttons`](/guide/getting-started#available-pages) for the buttons and badges showcase.
