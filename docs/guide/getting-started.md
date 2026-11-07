# 🚀 Install & run

Cendana Studio is an admin dashboard built on Laravel 13 (PHP 8.3+), Blade components, Tailwind CSS 4 and Vite. You need Composer, Node.js, npm, and PHP with the extensions Laravel requires.

Searchable selects use Choices.js and the date picker uses Flatpickr; both are installed through `npm install`, with no CDN.

## 🖥️ Run the dashboard

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
```

On Windows, use `copy .env.example .env` instead of `cp`. `.env.example` uses MySQL: create an empty database matching `DB_DATABASE` first, or switch `DB_CONNECTION` to `sqlite`. The migrations create Laravel's default tables, **not** dashboard data; the seeder adds the first admin from `ADMIN_SEED_EMAIL`/`ADMIN_SEED_PASSWORD` in `.env` (locally it falls back to `test@example.com` / `password` when those are empty).

Run the Laravel server and the asset server in separate terminals:

```bash
php artisan serve
```

```bash
npm run dev
```

Then open `/login` at the address Laravel prints and sign in. With Laravel Herd or Valet, skip `php artisan serve` and use your site's `.test` address. To run the server, Vite, a queue worker and the log viewer together, use `composer dev`.

## 🌐 Interface language

The interface is in English by default, with a Bahasa Melayu translation. Visitors switch language from the **Customize** drawer (or the EN/MS buttons on the login page); the choice is stored in the session. See [theming, branding & language](/guide/theming#language) for how translations are organised.

## 📚 Run the documentation

The VitePress docs run separately from the Laravel app:

```bash
npm run docs:dev
```

Open the local URL VitePress prints. To build and preview the static site:

```bash
npm run docs:build
npm run docs:preview
```

Building the app and building the docs are separate commands: `npm run build` builds the Laravel assets, while `npm run docs:build` builds the documentation site. The docs output goes to `docs/.vitepress/dist`.

## 🗺️ Available pages

| URL | Purpose |
| --- | --- |
| `/dashboard`, `/analytics`, `/settings` | Main admin pages |
| `/components/*` | Showcase of Blade components and HTML/CSS patterns |
| `/components/data-patterns` | Filter bar, detail list, timeline, tabs, action confirmation |
| `/examples/records/*` | Sample list, detail, create and edit pages |
| `/login`, `/demo/404` | Guest views and the error page |

The showcase and sample entries are only available when `ADMIN_SHOWCASE=true`. Routes are defined in `routes/web.php` and explained in [pages & navigation](/guide/pages).

## 🛠️ When you're ready to build

Start with [creating Blade pages](/guide/pages), [choosing components](/components/), then [connecting data to Laravel](/integration/). Don't rely on the demo data for persistence.
