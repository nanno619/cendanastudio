# 🧱 Layout & display

## 🗂️ Layouts and page titles

`x-layouts.admin` takes `title` (required), `group` (default `Platform`) and the page content slot. `x-layouts.guest` takes `title` for pages without a sidebar. The content heading usually uses `x-ui.page-header` with a `title` and optional `description`.

```blade
<x-layouts.admin title="{{ __('Orders') }}" group="{{ __('Sales') }}">
    <x-ui.page-header title="{{ __('Orders') }}" description="{{ __('Track the latest orders.') }}" />
    {{-- Page content --}}
</x-layouts.admin>
```

### 🧭 `x-layouts.admin` and `x-layouts.guest`

`x-layouts.admin` builds the full document: `<head>` with `@vite`, sidebar, header, `<main>`, footer, customizer and the toast container. The content slot goes into an already-responsive content area; don't add another `<html>` or `<body>` in child pages. `group` only changes the breadcrumb label, not the sidebar navigation group. `x-layouts.guest` uses the same head and toast container without a sidebar; the built-in login uses this layout. Both set `<html lang>` from the active locale.

### 🏷️ `x-ui.page-header` {#x-ui-page-header}

`title` is required, `description` optional. The component renders an `<h1>` for the content heading. Use one main heading per page; the text in the admin header is a breadcrumb, not a replacement for the content heading. Extra attributes such as `class="mb-6"` pass through to the wrapper via `$attributes`.

## 🧩 Cards and slots {#cards-and-slots}

`x-ui.card` takes optional `title` and `description`, plus the default slot and the `header` and `footer` slots. If `header` is filled, it replaces the standard title/description header.

```blade
<x-ui.card title="{{ __('Customers') }}" description="{{ __('Latest list') }}" class="min-w-0">
    <p>{{ __('Card content') }}</p>
    <x-slot:footer>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-outline">{{ __('Go to dashboard') }}</a>
    </x-slot:footer>
</x-ui.card>
```

The card header only renders when there's a `title`, `description` or a named `header` slot. The body is always wrapped in `.card-content`, and `footer` only renders when provided. Don't nest another `.card` inside the content just for padding: the component already provides the card structure. If you need a layout without the default content padding (for example a table that reaches the card edges), use `<div class="card">` markup directly, as the tables showcase does.

## 🖱️ Buttons, badges and icons

### 🔘 `x-ui.button` {#x-ui-button}

`x-ui.button` renders a `<button>`; the `type` prop defaults to `button` (use `submit` inside a form). `variant`: `primary`, `secondary`, `outline`, `ghost`, `soft`, `destructive`, `success`, `warning`, `info`, `link`. `size`: `default`, `sm`, `lg`, `icon`. Unrecognised variant/size values fall back to the default. For navigation use `<a class="btn btn-primary">`, because this component does **not** render links.

The component passes standard HTML attributes through to the button element, such as `disabled`, `name`, `value`, `aria-label`, `data-modal-open` or `data-toast`. `type="button"` is a safe default inside forms. To submit a form, **set `type="submit"`**. `size="icon"` only sets the dimensions; a button without text still needs an `aria-label`.

```blade
<x-ui.button variant="outline" size="icon" aria-label="{{ __('Open settings') }}" data-modal-open="#settings-modal">
    <x-ui.icon name="settings" />
</x-ui.button>
```

### 🏷️ `x-ui.badge` {#x-ui-badge}

`x-ui.badge` takes `variant` (default `neutral`), `pill`, `dot` and `icon`. Variant options: `primary`, `soft`, `success`, `danger`, `warning`, `info`, `neutral`, `outline`, `solid-success`, `solid-danger`, `solid-warning`, `solid-info`. A badge renders a `<span>`, not a button; use it to show status, not as an interactive element. `pill` and `dot` must be Blade boolean expressions, e.g. `:pill="true"`. For statuses from the database, map each status value to a trusted variant before displaying it.

### 🧿 `x-ui.icon` {#x-ui-icon}

`name` is required and refers to the `i-{name}` ID in `public/icons.svg`; for example `name="check-circle"` points to `i-check-circle`. The default `h-4 w-4` size can be overridden with `class`. Icons use `aria-hidden="true"`, so important text must still be visible or set as the control's `aria-label`. This differs from the older `<x-icon>` component, which uses the inline `#i-...` sprite; for active admin pages use `<x-ui.icon>`.

```blade
<x-ui.button type="submit" variant="primary" size="sm">{{ __('Save') }}</x-ui.button>
<x-ui.badge variant="success" :dot="true">{{ __('Active') }}</x-ui.badge>
<x-ui.icon name="check-circle" class="h-4 w-4" />
```

`x-ui.icon` takes its symbol from `public/icons.svg`. Don't build icon names from user input; pick a symbol name that actually exists. For icon-only buttons, put an `aria-label` on the button.

## 📈 Stat cards {#stat-cards}

`x-ui.stat-card` needs `title`, `value`, `icon` and `delta`; the options are `tone` (default `success`), `direction` (`up`/`down`), `note`, `tile` and `spark` (a JSON string of numbers). `tile` is the CSS class for the icon area. The sparkline is drawn by `resources/js/admin/charts.js`.

```blade
<x-ui.stat-card
    title="{{ __('Active orders') }}" :value="number_format($activeOrders)"
    icon="cart" delta="+8%" tone="success" direction="up"
    spark="[12,16,14,19,22]" />
```

Comparison values and stat text must be calculated from your data; the component doesn't do it for you. See the example page `resources/views/admin/dashboard.blade.php`.

`spark` is only drawn when truthy. Pass a JSON string such as `"[12,16,14]"`; for an array from a controller use `:spark="json_encode($weeklyOrders)"`. The array path in the implementation uses `Js::from()`, while the chart JS reads `JSON.parse`, so passing an array directly needs a component change. `direction` picks the `trend-up`/`trend-down` icon, while `tone` sets the badge/sparkline colour. Details are in the [stat card reference](/components/stat-card). `note` defaults to “vs last month” (translated); change it when the period differs.

::: info Two stat card versions
There's also `resources/views/components/admin/stat-card.blade.php`, with a **different** API (`label`, `value`, `trend`, `href` and a `spark` array). The active dashboard uses `x-ui.stat-card`; don't copy `x-admin.stat-card` props into `x-ui.stat-card`.
:::
