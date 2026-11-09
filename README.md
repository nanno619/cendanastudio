# Cendana Studio — Admin Dashboard

Cendana Studio is an admin dashboard built on Laravel 13 and Blade. Every main page uses Blade layouts, Blade components, Vite, Tailwind CSS 4, modular JavaScript, light/dark themes, accent options and a responsive layout. The interface is in English with a Bahasa Melayu translation.

The full guide is a VitePress site in `docs/`: run `npm run docs:dev` after `npm install`, and build it with `npm run docs:build`. It covers the `<x-ui.*>` component reference and how to connect the demo pages to a Laravel backend.

## Running the project

Requirements: PHP 8.3+, Composer, Node.js, and MySQL (or change `DB_CONNECTION` in `.env`, for example to `sqlite`).

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
```

Create an empty database matching `DB_DATABASE` in `.env` (default `laravel`), adjust `APP_URL`, `DB_USERNAME` and `DB_PASSWORD` if needed, then:

```bash
php artisan migrate --seed
npm run dev
php artisan serve
```

With Laravel Herd or Valet, skip `php artisan serve` and set `APP_URL` to your site address, for example `https://cendanastudio.test`.

The seeder creates the first admin from `ADMIN_SEED_NAME`, `ADMIN_SEED_EMAIL` and `ADMIN_SEED_PASSWORD` in `.env`. Locally, if those are empty, it falls back to `test@example.com` / `password`; in production it refuses to run without them and requires a password of at least 12 characters. Sign in at `/login`.

For a production build:

```bash
npm run build
php artisan optimize
```

## Main structure

- `resources/views/components/layouts` — admin, guest and blank layouts.
- `resources/views/components/admin` — sidebar, header, footer, navigation and customizer.
- `resources/views/components/ui` — reusable UI components such as card, button, field, badge, modal, drawer, combobox, date range, file preview, table state and chart.
- `resources/views/components/locale` — the English/Malay language switcher.
- `resources/views/admin` — dashboard, analytics and account settings.
- `resources/views/showcase` — component catalogue to reference when building new pages.
- `resources/views/examples/records` — fully frontend list, detail, create and edit flows.
- `resources/js/admin` — SVG charts and dashboard interactions.
- `resources/css/app.css` — design tokens, themes and component styles.
- `config/cendana.php` — branding, navigation, languages, theme options and demo data.
- `lang/` — Malay translations (`ms.json`, `ms/*.php`) and JavaScript strings (`en/js.php`, `ms/js.php`).

## Built-in pages

| URL | Route name | Description |
| --- | --- | --- |
| `/dashboard` | `admin.dashboard` | Main dashboard |
| `/analytics` | `admin.analytics` | Analytics |
| `/settings` | `admin.settings` | Account settings |
| `/administrations/audit-trail` | `administrations.audit-trail.index` | Audit trail of sign-ins and changes (Super Admin only) |
| `/administrations/audit-trail/{id}` | `administrations.audit-trail.show` | One audit entry: before/after values, context, related activity |
| `/administrations/backups` | `administrations.backups.index` | Backup health, Back up now, download backups (Super Admin only) |
| `/administrations/data-dictionary` | `administrations.data-dictionary.index` | Data Dictionary categories (Super Admin only) |
| `/administrations/data-dictionary/{category}` | `administrations.data-dictionary.show` | Items in one category (code, English/Malay labels, order, status) |
| `/administrations/data-dictionary/{category}/items/create` | `administrations.data-dictionary.items.create` | Add an item; edit at `…/items/{id}/edit`, activate/deactivate via `…/items/{id}/status` |
| `/components/*` | `showcase.*` | Component catalogue |
| `/components/filters` | `showcase.filters` | Combobox, date range, filter chips and file upload |
| `/components/table-states` | `showcase.table-states` | Ready, loading, empty, no results and error table states |
| `/components/charts` | `showcase.charts` | SVG chart variations and empty state |
| `/examples/records` | `examples.records.index` | Sample list with search, filters and pagination |
| `/examples/records/new` | `examples.records.create` | Sample create form |
| `/examples/records/detail` | `examples.records.show` | Sample detail page |
| `/examples/records/edit` | `examples.records.edit` | Sample edit form |
| `/login` | `login` | Sign in (Laravel Fortify) |
| `/forgot-password` | `password.request` | Forgot password |
| `/demo/404` | `demo.404` | Demo 404 page |

Authentication (login, logout, password reset, profile and password updates) is wired up through Laravel Fortify, with an optional Cloudflare Turnstile bot check on sign-in and forgot password (set `TURNSTILE_SITE_KEY` and `TURNSTILE_SECRET_KEY`), and every admin page is protected by the `auth` middleware; see `docs/integration/auth.md`. Roles and permissions use `spatie/laravel-permission` (`HasRoles` on `User`, `role`/`permission` route middleware). The roles (Super Admin, Director, Project Manager, Consultant, Finance, Admin, Staff, Support, Trainee, Sales) are defined in `app/Enums/Role.php` and created by `php artisan db:seed`; the seeded admin is Super Admin, which passes every permission check. Locally the seeder also adds one user per other role for testing access, signing in as `{role}@demo.test` with password `password` (for example `finance@demo.test`, `project-manager@demo.test`); these are never created in production. Permissions per role aren't defined yet. All sample data and sample forms are still presentational UI: the sample forms only show a toast and don't save data. Connect server validation and persistence to fit your real project.

## Packages

Beyond Laravel and Fortify, these are installed and ready to use:

- `spatie/laravel-permission` — roles and permissions
- `spatie/laravel-activitylog` — audit trail of who changed what: sign-ins, sign-outs, failed sign-ins, lockouts, account and password changes, and role changes are recorded and shown at `/administrations/audit-trail`
- `spatie/laravel-medialibrary` — files and images attached to models (public disk; run `php artisan storage:link`)
- `maatwebsite/excel` — CSV/XLSX exports and imports
- `spatie/laravel-backup` — nightly database + upload backups to the server and optionally an S3-compatible bucket, managed at `/administrations/backups` (needs `mysqldump` and the scheduler cron; see `.env.example` for `BACKUP_*` settings)
- `barryvdh/laravel-dompdf` — PDF generation from Blade views
- `barryvdh/laravel-debugbar` — development-only debug toolbar (shown when `APP_DEBUG=true`)

## Customization

Basic identity is set in `.env`:

```dotenv
ADMIN_NAME="Cendana Studio"
ADMIN_BRAND_NAME="Cendana Studio Admin"
ADMIN_WORKSPACE="Cendana Studio"
ADMIN_WORKSPACE_DESCRIPTION="Cendana Studio CRM"
ADMIN_FOOTER="© Cendana Studio"
ADMIN_SHOWCASE=true
ADMIN_TIMEZONE=Asia/Kuala_Lumpur
```

The sidebar menu is defined in `config/cendana.php`. Add a route and a view, then add a new item to the `navigation` array. To hide the component catalogue and sample pages in production, set `ADMIN_SHOWCASE=false`.

Example of a new page:

```blade
<x-layouts.admin title="{{ __('Products') }}" group="{{ __('Catalogue') }}">
    <x-ui.page-header
        title="{{ __('Products') }}"
        description="{{ __('Manage your business products.') }}"
    />

    <x-ui.card>
        {{ __('Page content') }}
    </x-ui.card>
</x-layouts.admin>
```

## Language

English is the default (`APP_LOCALE=en`); users switch to Bahasa Melayu from the Customize drawer or the EN/MS buttons on the login page, and the choice is kept in the session. Write user-facing text in English inside `__()` and add the Malay version to `lang/ms.json`. Strings used by JavaScript go in `lang/en/js.php` and `lang/ms/js.php`. See `docs/guide/theming.md` for details.

## Theme and preferences

The customizer on the right of the header supports system/light/dark modes, 12 accent colours, radius sizes and a full/mini sidebar. Preferences are stored in `localStorage`, so they need no backend.

## AI tooling (Laravel Boost)

[Laravel Boost](https://laravel.com/docs/boost) is installed as a dev dependency for Claude Code. Project rules live in `.ai/guidelines/cendana.md`; `php artisan boost:update` (run automatically after `composer update`) composes them with Boost's Laravel guidelines into `CLAUDE.md` and installs skills into `.claude/skills/`. Both are generated and git-ignored, so after cloning run:

```bash
php artisan boost:install
```

`.mcp.json` registers the Boost MCP server (`php artisan boost:mcp`) so Claude Code can read docs, routes, config and the database schema.

## Validation

```bash
php artisan test
php artisan view:cache
npm run build
```

The local icon sprite is in `public/icons.svg`; the app doesn't rely on a CDN for icons or the Inter font.
