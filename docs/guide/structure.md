# 🧩 Structure & Blade patterns

This project uses **anonymous Blade components** in `resources/views/components`. The file name sets the tag: `components/ui/card.blade.php` becomes `<x-ui.card>`, and `components/layouts/admin.blade.php` becomes `<x-layouts.admin>`.

## 🗂️ Main directories

| Location | Contents |
| --- | --- |
| `resources/views/components/layouts/` | `admin`, `guest` and `head` layouts; `blank` is an alternative file not used by the main routes |
| `resources/views/components/admin/` | Sidebar, header, footer, customizer, navigation |
| `resources/views/components/ui/` | UI components you assemble into pages |
| `resources/views/components/locale/` | The interface language switcher |
| `resources/views/admin/` | Dashboard, analytics, settings |
| `resources/views/showcase/` | Component usage examples |
| `resources/views/examples/records/` | Sample frontend entry flows |
| `resources/js/admin/` | Interactions, SVG charts, advanced inputs |
| `resources/css/app.css` | Tailwind, theme tokens and UI classes |
| `config/cendana.php` | Name, branding, sidebar, languages, accent options and demo data |
| `lang/` | Malay translations (`ms.json`, `ms/*.php`) and JavaScript strings (`*/js.php`) |

## 🧱 Layouts and slots

Admin pages use a layout component; `title` also feeds the browser title and breadcrumb, while `group` feeds the breadcrumb. Content between the tags goes into `$slot` inside `<main id="page-root">`.

```blade
<x-layouts.admin title="{{ __('Products') }}" group="{{ __('Catalogue') }}">
    <x-ui.page-header title="{{ __('Products') }}" description="{{ __('Manage your business products.') }}" />

    <x-ui.card title="{{ __('Latest products') }}">
        <p>{{ __('Page content.') }}</p>
    </x-ui.card>
</x-layouts.admin>
```

For pages without a sidebar, use <span v-pre>`<x-layouts.guest title="{{ __('Sign in') }}">…</x-layouts.guest>`</span> as in `resources/views/auth/login.blade.php`. The `<x-layouts.head>` component outputs the CSRF meta tag, applies theme preferences before the page paints, exposes JavaScript translations, and loads `@vite(['resources/css/app.css', 'resources/js/app.js'])`.

::: tip Translatable text
Write user-facing text in English and wrap it in `__()`, then add the Malay version to `lang/ms.json`. See [language](/guide/theming#language).
:::

## 🏷️ Props, attributes and named slots

Components declare props with `@props`. Other HTML attributes (such as `class`, `id`, `disabled` or `aria-label`) are usually passed through via `$attributes`. PHP expressions are prefixed with `:`; plain strings are not.

```blade
<x-ui.card title="{{ __('Overview') }}" class="min-w-0">
    <x-slot:header>
        <h2 class="card-title">{{ __("Today's overview") }}</h2>
    </x-slot:header>

    {{ __('Main content') }}

    <x-slot:footer>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-outline">{{ __('Dashboard') }}</a>
    </x-slot:footer>
</x-ui.card>
```

On `x-ui.card`, the `header` slot replaces the default header built from `title`/`description`; the `footer` slot appears at the bottom. Examples of dynamic props: `:options="$categories"`, `:checked="(bool) $user->active"`.

### 🧪 Reading props from a component file

When a component feels unclear, open its Blade file and start with the `@props([...])` declaration: that's the current list of options and defaults. Next, check the root element for `$attributes->class(...)` or `$attributes->merge(...)` to see which HTML attributes pass through, then look for <span v-pre>`{{ $slot }}`</span>/`@isset($footer)` to find slot positions. For example, `x-ui.button` always renders a `<button>`, while `x-ui.modal` renders a `<dialog>`. That difference decides whether you need `type="submit"`, `aria-label` or `data-modal-open`.

::: tip Patterns used
Use `<x-...>` tags and slots to compose pages. Some examples still use Blade directives such as `@foreach` and `@if` for data and conditions; that's still modern Blade.
:::

## 📦 Where data comes from today

The admin and showcase pages read `config('cendana.demo.*')` or write sample values directly in the view. JavaScript uses `data-*` attributes for browser interactions. See the [backend migration map](/integration/) to replace that data with values from a controller.
