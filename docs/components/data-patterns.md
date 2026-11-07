# 🧰 Data patterns

Five components for admin app flows: [filter bar](/components/filter-bar), [detail list](/components/detail-list), [timeline](/components/timeline), [tabs](/components/tabs) and [confirm action](/components/confirm-action). All are anonymous Blade components (`resources/views/components/ui/`).

## 🖥️ See the combined example

With `ADMIN_SHOWCASE=true`, open `/components/data-patterns`. The page shows all five components plus a searchable select; the confirmation preview route only redirects with a flash message and doesn't delete any data.

## 🔌 Using them for real

- On index pages, use the filter bar with `method="GET"` so the URL is shareable and the parameters are available through `request()`.
- On show pages, fill the detail list/timeline with controller data already formatted for display.
- Use tabs for server-rendered content; don't assume lazy loading.
- Put the confirm action next to the entry it affects, and point it at a route that checks permissions and performs the action.
- Reach for the [searchable select](/components/select#searchable-select-choices-js) and the [Flatpickr date range](/components/date-range) only when they help searching or scheduling.
