# 🔎 Combobox

`<x-ui.combobox>` provides a local option search with a text input, plus a hidden input for the form value. Blade source: `resources/views/components/ui/combobox.blade.php`; behaviour: `resources/js/admin/advanced-inputs.js`.

## 🧩 Props and option shape

| Prop | Default | Purpose |
| --- | --- | --- |
| `id` | required | Unique ID for the input, list and options |
| `label` | required | Label for the input and listbox |
| `options` | `[]` | Array of strings, or arrays with `value`/`label` |
| `name` | `null` | Name of the hidden input sent to the server |
| `placeholder` | “Search and choose…” (translated) | Search input text |

`class` and other root attributes pass through to the `data-combobox` wrapper. A `required` attribute on the root does **not** validate the hidden input; server validation is still required.

## 📝 String options or value/label

```blade
<x-ui.combobox id="city" label="{{ __('City') }}" name="city"
    :options="['Kuala Lumpur', 'Penang', 'Johor Bahru']" />

<x-ui.combobox id="category" label="{{ __('Category') }}" name="category_id"
    placeholder="{{ __('Search categories…') }}" :options="[
        ['value' => '1', 'label' => __('Guides')],
        ['value' => '2', 'label' => __('Reports')],
    ]" />
```

With an array of strings, the text is both the value and the label. With PHP arrays, `value` goes into the hidden input and `label` shows in the text input. Don't reuse the same `id` for two comboboxes on one page.

## ⌨️ Keyboard navigation and forms

Focusing or typing opens the list. Type to filter, use Arrow Up/Down to move through options, Enter to choose, and Esc/Tab to close. Choosing an option fires `change` on the hidden input; typing again clears the previous value. On the server, a category validation example: `['category_id' => ['required', 'exists:categories,id']]`.

## 🛠️ Database options and editing limits

```php
$options = Category::query()->orderBy('name')->get(['id', 'name'])
    ->map(fn ($category) => ['value' => (string) $category->id, 'label' => $category->name])
    ->all();
```

```blade
<x-ui.combobox id="product-category" label="{{ __('Category') }}" name="category_id" :options="$options" />
```

You need to create the example `Category` model. The component doesn't accept an initial `value`/`old()` yet; for edit pages, extend the Blade and script so the initial label and hidden value stay in sync. All options are rendered in the HTML and filtered in the browser, not fetched from an API; for thousands of options, consider a dedicated server-side search.
