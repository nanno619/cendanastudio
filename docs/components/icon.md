# 🧿 Icon

`<x-ui.icon>` renders an SVG `<use>` pointing to `public/icons.svg#i-{name}`. Source: `resources/views/components/ui/icon.blade.php`.

## 🧩 Props and symbol source

| Prop | Default | Purpose |
| --- | --- | --- |
| `name` | required | Sprite ID suffix (`search` → `i-search`) |
| `class` | `h-4 w-4` | SVG size and colour/position classes |

The SVG has `aria-hidden="true"` and `focusable="false"`. An icon carries no alternative text of its own; the action's label must be on the parent button or link.

## 📐 Icons in text and cards

```blade
<span class="inline-flex items-center gap-2">
    <x-ui.icon name="check-circle" class="h-4 w-4 text-success" />
    {{ __('Order completed') }}
</span>
<x-ui.card title="{{ __('Recent orders') }}">
    <x-ui.icon name="cart" class="h-6 w-6 text-primary" />
</x-ui.card>
```

## 🖱️ Icon-only buttons

```blade
<x-ui.button size="icon" variant="outline" aria-label="{{ __('Open search') }}">
    <x-ui.icon name="search" />
</x-ui.button>
```

Without an `aria-label`, the button only contains a decorative SVG and has no clear name for screen readers.

## 🧭 Adding a new icon

Add a `<symbol id="i-name" ...>` to `public/icons.svg` with a consistent `viewBox` size and stroke style, then use `name="name"`. To see the available icons, look at the `i-...` IDs in that file; examples used in the project: `search`, `plus`, `settings`, `chart`, `layers` (the logo), `check-circle`.

There's also `<x-icon>`, which points to `#i-{name}` **without a file path**, so it needs the inline `<x-icon.sprite>` in the document. The active admin layout uses the public sprite and `x-ui.icon`; don't mix the two mechanisms without loading the matching sprite.
