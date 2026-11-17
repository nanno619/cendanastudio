# ⚡ JavaScript interactions

`resources/js/app.js` imports `bootstrap.js`, `admin/charts.js`, `admin/interactions.js`, `admin/advanced-inputs.js`, `admin/enhanced-controls.js` and `admin/stepper.js`. No frontend framework is needed. Most clicks are handled through event delegation on the document; tables and charts initialise on page load.

| Behaviour | Markup hook | Location |
| --- | --- | --- |
| Modal/drawer | `data-modal-open="#id"`, `data-modal-close` | `interactions.js` |
| Dropdown | `data-dropdown`, `data-dropdown-toggle`, `.menu` | `interactions.js` |
| Tabs | `role="tablist"`, `role="tab"`, `aria-controls` | `interactions.js` |
| Sidebar/submenu | `#sidebarToggle`, `data-collapse="#id"` | `interactions.js` |
| Toast, dismiss, copy | `data-toast`, `data-dismissible`/`data-dismiss`, `data-copy` | `interactions.js` |
| Tables | `data-table` and the other table attributes | `interactions.js` |
| Charts | `data-chart` and the JSON data attributes | `charts.js` |
| Combobox, date range, file preview | `data-combobox`, `data-date-range`, `data-file-preview` | `advanced-inputs.js` |
| Searchable select, date picker | `data-enhanced-select`, `data-date-picker` | `enhanced-controls.js` (Choices.js/Flatpickr) |
| Step-by-step form | `data-stepper`, `data-step="Label"`, `data-stepper-nav`/`-go`/`-prev`/`-next`/`-submit`, `data-stepper-review` + `data-stepper-summary` | `stepper.js` |
| Demo forms | `data-demo-form` | `advanced-inputs.js` |

## 🪜 Step-by-step forms

Wrap groups of fields in elements with `data-step="Label"` inside a `<form data-stepper>`; the Add staff form (`resources/views/staff/form.blade.php`) is the reference. Without JavaScript every step shows and the form posts as one page. With it, one step shows at a time: **Next** checks that step's fields with the browser's own validation (`required`, `type`, `min`/`max`), the step bar (`data-stepper-go="0"`, using the `.step` styles) jumps back or forward once the earlier steps are valid, and Enter moves to the next step instead of submitting early. A last step marked `data-stepper-review` fills its `data-stepper-summary` with every answer before submitting. After a failed server-side save the form opens on the first step containing an error. The form is still sent once, so the controller and Form Request don't change.

## 🌐 Translated text in scripts

Scripts never hardcode user-facing text. They call `__t('key', { placeholder: value })`, which reads `window.__i18n`, set by `<x-layouts.head>` from `lang/<locale>/js.php`. To add a message, add the key to both `lang/en/js.php` and `lang/ms/js.php`. The Flatpickr calendar switches to its Malay locale when `<html lang="ms">`, and table sorting compares text in the page's language.

## 🧪 Demo pages vs real forms

`data-demo-form` intercepts the submit (`preventDefault`) and only shows a toast. When you connect a form to Laravel, remove that attribute, set `method` and `action`, and include `@csrf`/`@method` as needed.

`data-load` on a button only simulates loading for about 1.6 seconds; it doesn't wait for a server response. Buttons that really submit a form should manage their loading state from the request result. Filter chips and the table-state buttons also only change the browser view.

## 🔄 Dynamically added content

After inserting new table or chart elements into the DOM, call `window.App.initPage(container)`; it initialises tables/charts and syncs preferences. `advanced-inputs.js` queries the page when the script first loads, so a combobox, date range or file preview inserted later needs extra initialisation in your code.

For searchable selects and date pickers inserted later, call `window.EnhancedControls.init(container)`; elements already initialised aren't set up again.

For ordinary multi-page Blade, the browser reloads the page so the initial setup runs automatically. The single-file preview router (`window.__SPA__`) in `interactions.js` comes from the original HTML template and isn't used by the Laravel Blade routes.
