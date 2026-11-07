# 🎨 Theming, branding & assets

## 🏷️ Branding

`config/cendana.php` reads these values from `.env`:

```dotenv
ADMIN_NAME="Cendana Studio"
ADMIN_BRAND_NAME="Cendana Studio Admin"
ADMIN_WORKSPACE="Cendana Studio"
ADMIN_WORKSPACE_DESCRIPTION="Cendana Studio CRM"
ADMIN_FOOTER="© Cendana Studio"
ADMIN_SHOWCASE=true
```

`ADMIN_NAME` is used in the document title, `ADMIN_WORKSPACE` and its description appear in the sidebar and on the login page, and `ADMIN_FOOTER` appears in the footer. Some demo content (including the dashboard numbers and analytics charts) is still written directly in the views: change it when you replace the project's identity. After changing config on an app with cached config, run `php artisan optimize:clear`.

## 🌐 Language {#language}

The interface ships in English with a Bahasa Melayu translation. Supported languages are listed in `config('cendana.locales')`; `APP_LOCALE` sets the default.

- **Switching:** `<x-locale.switcher />` posts to the `locale.update` route, which stores the choice in the session. The `SetLocale` middleware (`app/Http/Middleware/SetLocale.php`) applies it on every web request. The switcher appears in the Customize drawer and, in compact form, on the login pages.
- **Blade text:** write English and wrap it in `__()`, e.g. <span v-pre>`{{ __('Save changes') }}`</span>. Add the Malay version to `lang/ms.json`, keyed by the English text. Missing keys fall back to English.
- **Config labels:** navigation labels, accent names and demo statuses in `config/cendana.php` are stored in English and passed through `__()` where they're rendered, because config is loaded before the visitor's language is known.
- **JavaScript text:** strings used by `resources/js/admin/*.js` live in `lang/en/js.php` and `lang/ms/js.php`. The layout head exposes the active set as `window.__i18n`, and scripts read it with `__t('key', { placeholder: value })`.
- **Laravel messages:** Malay validation, login, password-reset and pagination messages are in `lang/ms/*.php`. English uses Laravel's built-in files.

::: tip Adding a language
Add the code to `config('cendana.locales')`, create `lang/<code>.json` and `lang/<code>/*.php`, and the switcher picks it up automatically.
:::

## 🌈 Visual tokens

`resources/css/app.css` holds colour tokens as CSS variables, radius sizes, components such as `.btn`, `.badge` and `.table`, and the light/dark rules. Tailwind CSS 4 scans Blade and JavaScript through the `@source` directive. If you add dynamic classes, make sure the full class names are still discoverable by Tailwind's scanner.

The admin customizer offers system/light/dark themes, accent and radius options, and a full/mini sidebar. Browser choices are stored in `localStorage` (`theme`, `accent`, `radius`, `sbmode`). A small script in `<x-layouts.head>` applies them before CSS loads so page transitions don't flash.

```js
window.App.setPref('theme', 'dark')
window.App.setPref('accent', 'blue')
window.App.setPref('radius', '0.75')
```

Available accent values are listed in `config('cendana.accents')`; the CSS default is `indigo`. If you change the default or the accent list, keep the PHP config, the CSS tokens and the default value in `resources/js/admin/interactions.js` in sync.

## 🧿 Icons & fonts

Icons use the `public/icons.svg` sprite. Use `<x-ui.icon name="search" class="h-5 w-5" />`; the name must match `id="i-search"` in the sprite. Decorative icons get `aria-hidden`; buttons containing only an icon need an `aria-label` on the button.

The dashboard's Inter font is imported through `@fontsource/inter` in `resources/css/app.css`; dashboard assets are bundled by Vite through `<x-layouts.head>`. The VitePress docs have their own pipeline and theme and don't load the dashboard CSS directly.
