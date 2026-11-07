# 🗂️ Tabs

`<x-ui.tabs>` provides Blade tabs whose panels are all server-rendered; switching is handled by `resources/js/admin/interactions.js`. Source: `resources/views/components/ui/tabs.blade.php`.

## 🧩 Props & named slots

| Prop | Default | Purpose |
| --- | --- | --- |
| `id` | required | Prefix for tab and panel IDs; must be unique |
| `tabs` | required | `key => label` array, e.g. `['summary' => __('Overview')]` |
| `variant` | `underline` | `underline`, `pill`, `soft` |
| `active` | first key | Panel key visible on first render |
| `aria-label` | “Tab navigation” (translated) | Tablist name |

Each key in `tabs` needs a `tab_{key}` slot. Use alphanumeric/underscore keys that are valid slot names and HTML IDs.

## 📖 Underline and pill

```blade
<x-ui.tabs id="detail" aria-label="{{ __('Entry details') }}" :tabs="['summary' => __('Overview'), 'history' => __('History')]">
    <x-slot:tab_summary><p>{{ __('Entry data overview.') }}</p></x-slot:tab_summary>
    <x-slot:tab_history><p>{{ __('Entry update history.') }}</p></x-slot:tab_history>
</x-ui.tabs>

<x-ui.tabs id="report" variant="pill" active="table" :tabs="['chart' => __('Charts'), 'table' => __('Tables')]">
    <x-slot:tab_chart><p>{{ __('Chart panel.') }}</p></x-slot:tab_chart>
    <x-slot:tab_table><p>{{ __('Table panel.') }}</p></x-slot:tab_table>
</x-ui.tabs>
```

## 🎨 The soft variant and interaction

```blade
<x-ui.tabs id="settings" variant="soft" :tabs="['profile' => __('Profile'), 'access' => __('Access')]">
    <x-slot:tab_profile><p>{{ __('Profile settings.') }}</p></x-slot:tab_profile>
    <x-slot:tab_access><p>{{ __('Access settings.') }}</p></x-slot:tab_access>
</x-ui.tabs>
```

Clicking and the left/right arrow keys switch panels; charts in a panel initialise once the tab is selected. All panel content is in the HTML from the start; there's no built-in lazy loading or AJAX. For sensitive data, make sure access is checked on the server before the view renders.
