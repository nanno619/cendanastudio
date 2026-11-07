# 📝 Forms & inputs

Use the form components for presentation and to connect Laravel's errors; saving is still handled by your route/controller. `x-ui.field` shows the label, help text and the error from `$errors` for its `name`. Make sure the field and the control use the same `name`.

## 🧷 Fields and shared validation {#fields-and-shared-validation}

`x-ui.field` takes `label`, `name`, `help`, `required`, `class` and an `id` attribute. `name` is used to look up the error message in `$errors`; when there's an error, the `help` text is replaced by the first error message. The `required` prop **only** adds an asterisk to the label: still set the `required` attribute on the control and the `required` rule in the controller. The automatic ID is `f-{name}` on both the field and the matching input.

```blade
<x-ui.field label="{{ __('Email') }}" name="email" help="{{ __('Use your work email address.') }}" :required="true">
    <x-ui.input name="email" type="email" autocomplete="email" required />
</x-ui.field>
```

If you add a custom `id`, give the same value to `x-ui.field` and its control. `class` for the field wrapper is accepted both as a prop and as an HTML attribute.

## ⌨️ Input, select, textarea

```blade
<form method="POST" action="{{ route('examples.records.store') }}">
    @csrf
    <x-ui.field name="title" label="{{ __('Title') }}" :required="true" help="{{ __('A short entry title.') }}">
        <x-ui.input name="title" :value="$record?->title" required />
    </x-ui.field>

    <x-ui.field name="category" label="{{ __('Category') }}">
        <x-ui.select name="category" placeholder="{{ __('Choose a category') }}"
            :value="$record?->category"
            :options="['guides' => __('Guides'), 'reports' => __('Reports')]" />
    </x-ui.field>

    <x-ui.field name="summary" label="{{ __('Summary') }}">
        <x-ui.textarea name="summary" :value="$record?->summary" rows="4" />
    </x-ui.field>

    <x-ui.button type="submit">{{ __('Save') }}</x-ui.button>
</form>
```

### 📥 `x-ui.input` {#x-ui-input}

Props: `name`, `value`, `type` (default `text`), `invalid`, `addon`, `icon`. `icon="search"` puts an icon on the left and pads the text; `addon="RM"` puts a label at the start of the input group. Attributes such as `placeholder`, `min`, `max`, `autocomplete`, `readonly` and `required` pass through to the `<input>`. For `type="file"` inputs, the component never fills `value`, even when there's an old value.

```blade
<x-ui.input name="price" type="number" addon="RM" min="0" step="0.01" :value="$product?->price" />
```

### 🔽 `x-ui.select` {#x-ui-select}

Props: `name`, `value`, `options` as a `value => label` array, `placeholder`, `includeBlank`, `invalid`, and `searchable` (default `false`). `placeholder` or `includeBlank` inserts an `<option value="">`; without either, the first option can be submitted even if the user never consciously chose it. The slot can hold extra `<option>` elements. Turn on `:searchable="true"` for Choices.js; the [select reference](/components/select) has the details.

```blade
<x-ui.select name="status" :value="$record?->status" placeholder="{{ __('Choose a status') }}"
    :options="['draft' => __('Draft'), 'active' => __('Active')]" required />
```

### 📄 `x-ui.textarea` {#x-ui-textarea}

Props: `name`, `value`, `rows` (default 4), `invalid`. The `<textarea>` content comes from `old(name, value)`, **not** from the slot. Attributes such as `maxlength`, `placeholder` and `required` pass through to the HTML element.

```blade
<x-ui.textarea name="notes" :value="$record?->notes" rows="6" maxlength="1000"
    placeholder="{{ __('Notes for the team…') }}" />
```

All three prefer `old(name, value)` after a validation redirect. An error adds `aria-invalid` and the invalid class; `x-ui.field` shows the first message. Use the `invalid` prop to force the invalid state when validation comes from somewhere other than `$errors`.

This example uses route names from the [CRUD guide](/integration/crud) that you need to add; `$record` may be `null` on the create page.

::: tip IDs for labels
Without an explicit `id`, a field and an input with `name="title"` both use `f-title`. For array names (`items[0][title]`), give both components the same explicit `id` so the label points at the right control and the ID stays unique.
:::

## 🔀 Switch {#switch}

`x-ui.switch` needs a `name`; `label`, `hint`, `checked` (default false) and `value` (default `1`) are optional. The component renders a hidden input with value `0` and a checkbox with value `1` when on, so a switched-off control still sends a value in the request.

```blade
<x-ui.switch name="published" label="{{ __('Publish') }}" :checked="(bool) ($record->published ?? false)" />
```

On the server use `$request->boolean('published')`; don't assume the `checked` prop validates or saves anything.

`old(name, checked)` restores the choice after a redirect. The hidden input and the checkbox share the same `name` on purpose, so the HTML form always sends `0` or `1`. If you need a different value through the `value` prop, adjust the server-side conversion too.

## 🔎 Combobox {#combobox}

`x-ui.combobox` needs an `id` and a `label`; `name`, `placeholder` and `options` are optional. `options` are strings or `['value' => '...', 'label' => '...']`. The text input is for searching options; **the form value is in a hidden input**, filled once an option is chosen. On first render the component doesn't restore a choice from `old()` or an edit value; add that support if you use it on an edit page.

```blade
<x-ui.combobox id="category-search" label="{{ __('Category') }}" name="category_id"
    :options="[['value' => '1', 'label' => __('Guides')], ['value' => '2', 'label' => __('Reports')]]" />
```

The option list is filtered in the browser as you type. Users can choose with a click or with the arrow keys and Enter; once chosen, the label shows in the text input and the `value` moves to the hidden input. Changing the text after choosing clears the hidden value. For edit pages, the component doesn't yet provide an initial selected `value` or `old()` restore; extend it before using it to edit real data. The options are rendered when the server sends the HTML; this search doesn't fetch remote data.

## 📅 Date range {#date-range}

`x-ui.date-range` uses Flatpickr for the two dates plus 7/30-day preset buttons. Use `from-name="from"` and `to-name="to"` so ISO `Y-m-d` values are sent to Laravel; without input names it's demo-only. You still need to validate the order on the server. The calendar shows month and day names in the active language. See the [date range reference](/components/date-range).

```blade
<x-ui.date-range id="report-period" label="{{ __('Report period') }}" />
```

## 📎 File preview {#file-preview}

`x-ui.file-preview` needs an `id`; its props are `label`, `accept` and `maxMb` (default 5). It provides a choose/drag area, the file name and size, an image thumbnail or PDF icon, a remove button and an error message. The image/PDF preview and the type/size checks are done in JS. The built-in input **has no `name` yet**; add `name="attachment"` to the input in the component before using it in a form upload, then add `enctype="multipart/form-data"`, Laravel validation and server storage. The JS currently accepts PNG/JPG/PDF even if you change the `accept` prop; keep client and server rules aligned when you add file types. `maxMb` is client-side validation, not a PHP or Laravel upload size limit.

```blade
<x-ui.file-preview id="demo-file" label="{{ __('Choose a file') }}" :max-mb="5" />
```

An interactive example is at `/components/filters`; the backend guide is in [forms & uploads](/integration/forms).
