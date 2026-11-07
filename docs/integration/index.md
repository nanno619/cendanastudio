# 🔌 Backend integration map

This app is a Laravel Blade frontend: `routes/web.php` currently uses `Route::view`, sample data comes from `config/cendana.php`, and some forms only show a toast. You can keep the display components while adding your own backend.

| What exists today | When using real data |
| --- | --- |
| `Route::view` for demo pages | Route to a controller and pass variables to the view |
| `config('cendana.demo.records')` | A model query or your app's data source |
| Forms with `data-demo-form` | A `POST`/`PUT` form + `@csrf` + validation + redirect |
| `data-table` tables | Database pagination/filtering for large datasets |
| Charts with static arrays | Controller aggregates passed to the `labels`/`series` props |
| Login via Laravel Fortify | Already wired up: see [authentication](/integration/auth) |
| Local upload preview | Input names, file validation, storage on a disk |

## 📦 Available packages

These are installed but not yet wired into app models, so each feature decides how to use them:

| Need | Package | Starting point |
| --- | --- | --- |
| Roles & permissions | `spatie/laravel-permission` | [Authentication & production](/integration/auth#roles-permissions) |
| Audit trail | `spatie/laravel-activitylog` | `activity()->causedBy($user)->performedOn($record)->log('updated')`, or the `LogsActivity` trait |
| Files on records | `spatie/laravel-medialibrary` | Add `HasMedia` + `InteractsWithMedia` to the model, then `$record->addMediaFromRequest('file')->toMediaCollection('documents')` |
| CSV / Excel | `maatwebsite/excel` | An export class in `app/Exports`, then `Excel::download(new RecordsExport, 'records.xlsx')` |
| PDF | `barryvdh/laravel-dompdf` | `Pdf::loadView('pdf.quote', $data)->download('quote.pdf')`, with inline CSS in the PDF view |
| Debugging (dev only) | `barryvdh/laravel-debugbar` | Appears automatically when `APP_DEBUG=true`; never deploy dev dependencies |

Media uploads still need server-side validation (see [forms & uploads](/integration/forms#file-uploads)), and every export or PDF route needs the same authorization as the page it comes from.

## 🗺️ Suggested order of work

1. Read [structure & Blade](/guide/structure) to understand layouts and props.
2. Follow the [CRUD example](/integration/crud) for the route, model, controller and entry list.
3. Connect [forms & validation](/integration/forms).
4. For large data and visualisation, use [server-side tables & charts](/integration/data).
5. Finally, review [authentication & production readiness](/integration/auth).

The examples in this section are **sample code for you to add**. The model, table, column and route names don't exist in this app automatically. Match them to your own domain.
