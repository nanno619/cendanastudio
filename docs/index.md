---
layout: home

hero:
  name: Cendana Studio
  text: 📚 Laravel Blade documentation
  tagline: From demo pages to a dashboard connected to your project's data. Learn the components, page patterns and backend integration steps.
  actions:
    - theme: brand
      text: Start with installation
      link: /guide/getting-started
    - theme: alt
      text: Explore components
      link: /components/

features:
  - title: 🧩 Blade as the foundation
    details: Build pages with <x-layouts.admin>, <x-ui.*> components, props and Blade slots.
    link: /guide/structure
    linkText: Understand the structure
  - title: 🧰 Components you can reference
    details: Usage examples and limits for each component, taken from the implementation and this project's showcase pages.
    link: /components/
    linkText: Open the reference
  - title: 🔌 A path to real data
    details: Turn Route::view, config data, demo forms, client-side tables and login into proper Laravel flows.
    link: /integration/
    linkText: Read the integration guide
---

## 🧭 Where to start?

New to the project? Follow [installing and running the app](/guide/getting-started), then [Blade page structure](/guide/structure). If you're already building your own pages, open the [component map](/components/) or the [backend integration guide](/integration/).

::: info Scope
This dashboard provides a Laravel Blade frontend. Sample data lives in `config/cendana.php`, demo routes use `Route::view`, and the sample save buttons only show a notification. The integration docs contain **example implementations for you to add**, not backend features that already exist.
:::
