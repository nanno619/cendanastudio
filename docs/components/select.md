# 🔽 Select

`<x-ui.select>` wraps an HTML `<select>` and supports options from a PHP array, a placeholder, old form values and Laravel errors. Source: `resources/views/components/ui/select.blade.php`.

## 🧩 Props

| Prop | Default | Purpose |
| --- | --- | --- |
| `name` | `null` | Request name and automatic ID |
| `value` | `null` | Initial choice; overridden by `old(name)` |
| `options` | `[]` | Value → label map (`['active' => __('Active')]`) |
| `placeholder` | `null` | Empty option with its own text |
| `includeBlank` | `false` | Empty option reading “— Choose —” (translated) |
| `invalid` | `null` | Force the invalid look |
| `searchable` | `false` | Turn on Choices.js for search and a non-native dropdown |
| Main slot | empty | Extra manual `<option>` elements |

The automatic ID is `f-{name}`; the `required`, `disabled`, `multiple`, `aria-label`, `class` and `id` attributes pass through to the `<select>`. The component compares option values and `old()` as strings.

## 📝 Single choice and placeholder

```blade
<x-ui.field name="status" label="{{ __('Status') }}" :required="true">
    <x-ui.select name="status" placeholder="{{ __('Choose a status') }}" required
        :value="$record?->status"
        :options="['draft' => __('Draft'), 'in-review' => __('In review'), 'active' => __('Active')]" />
</x-ui.field>
```

With `required`, the empty option helps the browser detect a choice that hasn't been made. Without a placeholder/includeBlank, the first option becomes the browser's default choice.

## 🗃️ Options from the database

```php
$categories = Category::query()->orderBy('name')->pluck('name', 'id')->all();
return view('admin.products.create', compact('categories'));
```

```blade
<x-ui.select name="category_id" placeholder="{{ __('Choose a category') }}"
    :value="$product?->category_id" :options="$categories" />
```

You need to create the example `Category` model and view yourself. Use the `exists:categories,id` validator on the server; the options prop doesn't validate the request.

## 🧰 Option slot and limits

```blade
<x-ui.select name="period" :include-blank="true" aria-label="{{ __('Period') }}">
    <option value="week" @selected(old('period') === 'week')>{{ __('7 days') }}</option>
    <option value="month" @selected(old('period') === 'month')>{{ __('30 days') }}</option>
</x-ui.select>
```

The component doesn't add `@selected` to slot options: set it yourself. `multiple` only passes the HTML attribute through; the built-in `old()` selection is designed for **one** value. For a multi-select, adapt the component before using arrays of values.

## 🔎 Searchable select (Choices.js)

```blade
<x-ui.field name="category" label="{{ __('Category') }}">
    <x-ui.select name="category" placeholder="{{ __('Choose a category') }}" :searchable="true"
        :value="old('category', $record?->category)"
        :options="['guides' => __('Guides'), 'reports' => __('Reports'), 'archive' => __('Archive')]" />
</x-ui.field>
```

Choices.js enhances the select progressively: the options and `name` still come from the Blade `<select>`, so the Laravel request still contains `category`. Search happens in the browser, the dropdown uses the dashboard's light/dark tokens, and its “No results” and search texts are translated. Without JS, the HTML select still works. Inside a modal, the dropdown may need special positioning if the `<dialog>` clips it; test it where you place it.
