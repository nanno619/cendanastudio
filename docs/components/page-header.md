# 📰 Page header

`<x-ui.page-header>` renders a wrapper with an `<h1>` and an optional description. Source: `resources/views/components/ui/page-header.blade.php`.

## 🧩 API

| Prop | Required | Purpose |
| --- | --- | --- |
| `title` | required | `<h1>` heading |
| `description` | optional | Supporting paragraph |
| HTML attributes | optional | Passed to the wrapper `<div>` |

This component has no actions slot and no visual variants. For a toolbar or actions, put it in a flex layout alongside links/buttons.

## 📝 Simple use

```blade
<x-layouts.admin title="{{ __('Products') }}" group="{{ __('Catalogue') }}">
    <x-ui.page-header title="{{ __('Products') }}" description="{{ __('Manage prices and stock.') }}" />
</x-layouts.admin>
```

## 🖱️ With actions and extra classes

```blade
<div class="flex flex-wrap items-start justify-between gap-4">
    <x-ui.page-header title="{{ __('Entry list') }}" description="{{ __('Search and manage team notes.') }}" class="min-w-0" />
    <a href="{{ route('examples.records.create') }}" class="btn btn-primary">{{ __('Add entry') }}</a>
</div>
```

## 🗃️ A title from server data

```blade
<x-layouts.admin :title="$record->title" group="{{ __('Entries') }}">
    <x-ui.page-header :title="$record->title" :description="__('Category: :name', ['name' => __($record->category)])" />
</x-layouts.admin>
```

The layout's `title` is used for the browser tab and breadcrumb, while the page header's `title` is for the page content. Text from the model is escaped by Blade. Make sure each page has only one `<h1>` in its content.
