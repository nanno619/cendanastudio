# 🗄️ Drawer

`<x-ui.drawer>` is a side-panel dialog with a header, a scrollable content area and an optional footer. Source: `resources/views/components/ui/drawer.blade.php`.

## 🧩 API

| Prop/slot | Default | Purpose |
| --- | --- | --- |
| `id` | required | Dialog ID / trigger selector |
| `title` | required | Heading linked through `aria-labelledby` |
| `description` | `null` | Text under the heading |
| Main slot | — | Scrollable content |
| `footer` | empty | Bottom action area |

The drawer already has an X close button in the header, with `data-modal-close` and an accessible label. There are no `size`/`tone` props like the [modal](/components/modal) has.

## 🔎 A simple filter

```blade
<x-ui.button variant="outline" data-modal-open="#entry-filter">{{ __('Set filters') }}</x-ui.button>
<x-ui.drawer id="entry-filter" title="{{ __('Filter entries') }}" description="{{ __('Choose your search criteria.') }}">
    <p>{{ __('Use a form to apply filters on the server.') }}</p>
    <x-slot:footer>
        <x-ui.button variant="outline" data-modal-close>{{ __('Cancel') }}</x-ui.button>
    </x-slot:footer>
</x-ui.drawer>
```

## 📝 A GET form inside the drawer

```blade
<x-ui.drawer id="data-filter" title="{{ __('Filter data') }}">
    <form id="filter-records" method="GET" action="{{ route('examples.records.index') }}" class="space-y-4">
        <x-ui.field name="status" label="{{ __('Status') }}">
            <x-ui.select name="status" placeholder="{{ __('All statuses') }}" :value="request('status')"
                :options="['active' => __('Active'), 'draft' => __('Draft')]" />
        </x-ui.field>
    </form>
    <x-slot:footer>
        <a href="{{ route('examples.records.index') }}" class="btn btn-outline">{{ __('Clear filters') }}</a>
        <x-ui.button type="submit" form="filter-records">{{ __('Apply') }}</x-ui.button>
    </x-slot:footer>
</x-ui.drawer>
```

`form="filter-records"` connects the footer button, which sits outside the `<form>` tag, to the form. Don't give the Apply button `data-modal-close`, so the GET request completes normally. The route and form variables follow the [server-side tables guide](/integration/data).

## ⚙️ Opening, closing and limits

It works the same way as the modal: `data-modal-open="#data-filter"`, `data-modal-close`, Esc and a backdrop click. Content isn't saved automatically; the URL query or your JS state is the real source of the filters. The `id` must be unique on the page.
