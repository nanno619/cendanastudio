---
paths:
  - 'resources/views/**'
---

# Views

## Use anonymous Blade components, not class components or partials
Build UI from anonymous Blade components in `resources/views/components` (`ui/`, `admin/`, `layouts/`), declaring options with `@props`. Don't create class-based components in `app/View` or `@include` partials; reuse an existing `<x-ui.*>` before writing raw markup.

## Translate text with English JSON keys
Wrap every user-facing string in `__('English sentence')` and add its Malay value to `lang/ms.json`, keyed by the English text. Don't use dotted short keys like `__('messages.save')`.
