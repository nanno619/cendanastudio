# 🧭 Component map

The components below live in `resources/views/components/ui/`; the `<x-ui.name>` tag points to `name.blade.php`. Some showcase patterns are plain HTML + CSS classes and `data-*` attributes rather than their own Blade components.

| Need | Components | Reference |
| --- | --- | --- |
| Page frame | `x-layouts.admin`, `x-layouts.guest`, `x-ui.page-header` | [Layout](/components/layout) |
| Content & actions | `x-ui.card`, `x-ui.stat-card`, `x-ui.button`, `x-ui.badge`, `x-ui.icon` | [Layout](/components/layout) |
| Forms | `x-ui.field`, `x-ui.input`, `x-ui.select`, `x-ui.textarea`, `x-ui.switch` | [Forms](/components/forms) |
| Advanced inputs | `x-ui.combobox`, `x-ui.date-range`, `x-ui.file-preview` | [Forms](/components/forms) |
| Data & states | `x-ui.table-state`, `x-ui.empty`, `x-ui.pagination` | [Tables](/components/tables) |
| Visualisation | `x-ui.chart`, `x-ui.stat-card` | [Charts](/components/charts) |
| Notifications & overlays | `x-ui.alert`, `x-ui.modal`, `x-ui.drawer` | [Feedback](/components/feedback) |
| Language | `x-locale.switcher` | [Language](/guide/theming#language) |

Some page markup, such as `.table` tables, `.menu` dropdowns, tabs, spinners and filter chips, is driven by CSS and JavaScript with no `<x-ui.*>` wrapper. See [JavaScript interactions](/components/interactions) for the attribute contract.

## 🧰 UI component index

| Component | What it's for | Usage details |
| --- | --- | --- |
| `alert` | Inline success/warning/error messages | [Alert](/components/alert) |
| `badge` | Non-interactive status labels | [Badge](/components/badge) |
| `button` | HTML action buttons | [Button](/components/button) |
| `card` | Container with header/body/footer | [Card](/components/card) |
| `chart` | SVG charts from PHP data | [Chart](/components/chart) |
| `combobox` | Search and pick a local option | [Combobox](/components/combobox) |
| `date-range` | Presets and date range inputs | [Date range](/components/date-range) |
| `drawer` | Side dialog panel | [Drawer](/components/drawer) |
| `filter-bar` | GET search form with active filters | [Filter bar](/components/filter-bar) |
| `detail-list` | Label/value pairs for detail pages | [Detail list](/components/detail-list) |
| `timeline` | Chronological event history | [Timeline](/components/timeline) |
| `tabs` | Underline, pill and soft tabs | [Tabs](/components/tabs) |
| `confirm-action` | Confirmation dialog with a POST/DELETE form | [Confirm action](/components/confirm-action) |
| `empty` | Generic empty state | [Empty](/components/empty) |
| `field` | Label, hint and error for a control | [Field](/components/field) |
| `file-preview` | Choose/drag a file with a local preview | [File preview](/components/file-preview) |
| `icon` | Icons from the public sprite | [Icon](/components/icon) |
| `input` | HTML input with optional icon/addon | [Input](/components/input) |
| `modal` | Centred dialog for focused tasks | [Modal](/components/modal) |
| `page-header` | Main page heading and description | [Page header](/components/page-header) |
| `pagination` | Page navigation draft (not production-ready) | [Pagination](/components/pagination) |
| `select` | Select with options from an array | [Select](/components/select) |
| `stat-card` | Number, delta, icon & sparkline | [Stat card](/components/stat-card) |
| `switch` | Checkbox styled as a toggle | [Switch](/components/switch) |
| `table-state` | Loading, empty, filtered, error | [Table state](/components/table-state) |
| `textarea` | Multi-line text with old input/errors | [Textarea](/components/textarea) |

::: info Live catalogue
With `ADMIN_SHOWCASE=true`, `/components/cards`, `/components/forms`, `/components/tables`, `/components/charts` and the other component pages show the rendered output in the Laravel app.
:::

The extra components in `resources/views/components/admin/` are specific to the admin shell (sidebar, header, customizer). Use `<x-layouts.admin>` rather than calling them one by one.

A combined example of the five data-pattern components is at `/components/data-patterns`; the flow is explained in [data patterns](/components/data-patterns).
