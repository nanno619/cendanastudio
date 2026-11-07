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

## 🗺️ Suggested order of work

1. Read [structure & Blade](/guide/structure) to understand layouts and props.
2. Follow the [CRUD example](/integration/crud) for the route, model, controller and entry list.
3. Connect [forms & validation](/integration/forms).
4. For large data and visualisation, use [server-side tables & charts](/integration/data).
5. Finally, review [authentication & production readiness](/integration/auth).

The examples in this section are **sample code for you to add**. The model, table, column and route names don't exist in this app automatically. Match them to your own domain.
