# 🗂️ Card

`<x-ui.card>` wraps content in a `<section class="card">`. Source file: `resources/views/components/ui/card.blade.php`; a variety of cards is at `/components/cards`.

## 🧩 Props and slots

| Input | Default | Output |
| --- | --- | --- |
| `title` | `null` | `<h2>` heading in the header |
| `description` | `null` | Text under the heading |
| Main slot | required for content | `<div class="card-content">` |
| `header` | none | Custom header; replaces the automatic title and description |
| `footer` | none | `<div class="card-footer">` |

`class`, `id` and other attributes apply to the `<section>` through `$attributes`. The header isn't shown when `title`, `description` and the `header` slot are all empty.

## 📝 Basic card with a footer

```blade
<x-ui.card title="{{ __('Recent activity') }}" description="{{ __('Changes this week.') }}">
    <p>{{ $activitySummary }}</p>
    <x-slot:footer>
        <a href="{{ route('admin.analytics') }}" class="btn btn-outline btn-sm">{{ __('Open analytics') }}</a>
    </x-slot:footer>
</x-ui.card>
```

## 🛠️ Custom header

```blade
<x-ui.card class="min-w-0">
    <x-slot:header>
        <div class="flex items-center justify-between gap-3">
            <div><h2 class="card-title">{{ __('Orders') }}</h2><p class="card-desc">{{ __('Current period') }}</p></div>
            <a href="{{ route('admin.dashboard') }}" class="btn btn-outline btn-sm">{{ __('View all') }}</a>
        </div>
    </x-slot:header>
    <p class="text-muted-foreground">{{ $orderSummary }}</p>
</x-ui.card>
```

Don't expect the `title` prop to appear when you use a custom `header`: the `header` slot takes over the whole header markup.

## 🧱 Cards without a header, in a grid

```blade
<div class="grid gap-4 md:grid-cols-2">
    <x-ui.card class="min-w-0"><strong>{{ __('Active products') }}</strong><p>{{ $activeProducts }}</p></x-ui.card>
    <x-ui.card class="min-w-0"><strong>{{ __('Out of stock') }}</strong><p>{{ $outOfStock }}</p></x-ui.card>
</div>
```

A card whose `<table>` must reach the edges is better written as `<div class="card">` with its own header/overflow; `x-ui.card` always adds `.card-content`. Use a [stat card](/components/stat-card) for numbers with a delta and icon, rather than forcing every card into the stats pattern.
