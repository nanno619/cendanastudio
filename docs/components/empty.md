# 📭 Empty

`<x-ui.empty>` is a placeholder for sections or pages that genuinely have no content yet, outside of tables. Source: `resources/views/components/ui/empty.blade.php`.

## 🧩 API

| Prop | Default | Purpose |
| --- | --- | --- |
| `icon` | `file-text` | Icon in the circle |
| `title` | “No data yet” (translated) | Empty-state heading |
| `description` | `null` | Explanation of the next step |
| Main slot | empty | Optional actions |

There's no `variant`/`state`; the icon and text show a general status. The slot only renders when it isn't empty. Extra `class` attributes aren't applied to the root; if you need custom spacing, wrap the component in another element.

## 📦 No products yet

```blade
<x-ui.empty icon="package" title="{{ __('No products yet') }}"
    description="{{ __('Add your first product to see it in the catalogue.') }}">
    <a href="{{ route('products.create') }}" class="btn btn-primary">{{ __('Add product') }}</a>
</x-ui.empty>
```

The `products.create` route illustrates a route in your own app.

## 🗂️ No action, and conditional states

```blade
@forelse ($messages as $message)
    <p>{{ $message->subject }}</p>
@empty
    <x-ui.empty icon="mail" title="{{ __('Your inbox is empty') }}"
        description="{{ __('New messages will appear on this page.') }}" />
@endforelse
```

For loading, failed requests or empty search results, choose the [table state](/components/table-state), which provides the `loading`, `error` and `filtered` states.
