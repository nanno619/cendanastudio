---
paths:
  - 'resources/views/**'
---

# Views

## Use anonymous Blade components, not class components or partials
Build UI from anonymous Blade components in `resources/views/components` (`ui/`, `admin/`, `layouts/`), declaring options with `@props`. Don't create class-based components in `app/View` or `@include` partials; reuse an existing `<x-ui.*>` before writing raw markup.

## Translate text with English JSON keys
Wrap every user-facing string in `__('English sentence')` and add its Malay value to `lang/ms.json`, keyed by the English text. Don't use dotted short keys like `__('messages.save')`.

## Use x-ui.icon for icons
Use `<x-ui.icon name="..." />`, which reads the public `icons.svg` sprite. Don't use `<x-icon>`; it needs an inline sprite the layouts don't load. Icon-only buttons need an `aria-label`.

## Avoid Blade pitfalls with props and JSON attributes
For translated `@props` defaults, set the prop to `null` and fill it with a block `@php $x ??= __('...'); @endphp`, never inline `@php(...)`. For array literals in `data-*` attributes use `{{ json_encode([...]) }}`, not `@json()`, which splits on commas.

## Format money and numbers the Malaysian way
Show money as MYR (`RM 48.00`, `RM 16.7k`) and numbers with dot decimals and comma thousands (`1,284`, `+13.2%`).
