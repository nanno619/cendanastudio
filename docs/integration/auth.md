# 🔐 Authentication & production

Authentication uses [Laravel Fortify](https://laravel.com/docs/fortify) (headless: Fortify provides the routes and logic; this app provides the Blade views). Enabled features are set in `config/fortify.php`: login/logout, password reset, and profile and password updates from the settings page. Registration, email verification, two-factor authentication and passkeys are **disabled**.

## 🔑 Login and views

Views are registered in `app/Providers/FortifyServiceProvider.php`:

| View | Route | File |
| --- | --- | --- |
| Sign in | `login` / `login.store` | `resources/views/auth/login.blade.php` |
| Forgot password | `password.request` / `password.email` | `resources/views/auth/forgot-password.blade.php` |
| Reset password | `password.reset` / `password.update` | `resources/views/auth/reset-password.blade.php` |

All three use `<x-auth.shell>` (`resources/views/components/auth/shell.blade.php`), which also shows the compact EN/MS language switcher. Login is limited to 5 attempts per minute per email+IP. After signing in, users go to `/dashboard` (`home` in `config/fortify.php`). The **Sign out** button is in the sidebar and sends `POST /logout`.

Account settings (`/settings`) send `PUT user/profile-information` and `PUT user/password`. Errors are shown per error bag (`updateProfileInformation`, `updatePassword`). Validation, login and password-reset messages follow the visitor's language; the Malay versions are in `lang/ms/*.php`.

To create the first user, set `ADMIN_SEED_NAME`, `ADMIN_SEED_EMAIL` and `ADMIN_SEED_PASSWORD` in `.env` and run `php artisan db:seed`. The seeder uses `updateOrCreate`, so re-running it resets that user's name and password instead of creating a duplicate. Locally, empty values fall back to `test@example.com` / `password`; in production the seeder refuses to run without them and requires a password of at least 12 characters. Don't commit real values; set them only in the server's `.env`, and you can clear `ADMIN_SEED_PASSWORD` after seeding.

Password-reset emails use the `MAIL_MAILER` driver in `.env` (default `log`; see `storage/logs`).

## 🛡️ Admin routes

The dashboard, analytics, settings, component catalogue and sample entries sit inside `Route::middleware('auth')` in `routes/web.php`. New private CRUD routes belong in that group too. `/demo/404`, `/testing` and the `locale.update` route stay public (the login page needs the language switch); remove or protect `/testing` before production.

Also protect every write operation with authorization checks (Policy/Gate) that fit your project's rules.

## 🚢 Deploying

Prepare the production `.env` and database, run migrations for your schema, then build the Laravel assets with `npm run build`. The docs are built separately with `npm run docs:build` into static files in `docs/.vitepress/dist`; you can publish them as a static docs site without routing docs requests through Laravel. Use `ADMIN_SHOWCASE=false` if you don't want the sample catalogue in production, and check links to the showcase before turning it off.
