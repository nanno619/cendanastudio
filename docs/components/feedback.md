# 💬 Feedback & dialogs

## 📢 Alert {#alert}

`x-ui.alert` takes `variant` (`info`, `success`, `warning`, `danger`), an optional `title`, a boolean `solid`, the content slot and an optional `action` slot. For `danger` error messages, the component adds `role="alert"`.

`title` renders bold text above the content. Use `action` to place a button or link on the right of the message, such as a reload action:

```blade
<x-ui.alert variant="warning" title="{{ __('Sync pending') }}">
    {{ __('Check your connection before continuing.') }}
    <x-slot:action>
        <a href="{{ request()->fullUrl() }}" class="btn btn-outline btn-sm">{{ __('Reload') }}</a>
    </x-slot:action>
</x-ui.alert>
```

`solid` gives a stronger background; use `:solid="true"` for the boolean prop. Ordinary informational messages don't get `role="alert"` automatically, so they don't interrupt screen readers.

```blade
<x-ui.alert variant="success" title="{{ __('Saved') }}">
    {{ __('The changes are available on the detail page.') }}
</x-ui.alert>
```

For flash messages from a Laravel redirect, render them on the server:

```blade
@if (session('status'))
    <x-ui.alert variant="success" title="{{ __('Success') }}">{{ session('status') }}</x-ui.alert>
@endif
```

## 🔔 Toast

Toasts are created with `window.App.toast({ title, description, tone, duration })` and shown in the layout's `#toasts` container. Tone options: `success`, `danger`, `warning`, `info`, `neutral`. They can also be triggered by attributes on a clicked element:

```blade
<button type="button" data-toast="info" data-toast-title="{{ __('Preview') }}" data-toast-desc="{{ __('Demo display only') }}">
    {{ __('Show message') }}
</button>
```

`data-toast` shows the message on click; it is **not** confirmation of a server save. For real actions, wait for the controller/redirect result before showing success. When building toast messages from user data, avoid inserting untrusted input directly: the current toast implementation builds its markup with `innerHTML`.

`duration` is in milliseconds; without it, the toast closes about 4.5 seconds after appearing. `window.App.toast` needs the `#toasts` container that the active layout provides. Don't make it the only place form errors appear; per-field messages through `x-ui.field` are still needed.

## 🪟 Modal & drawer

Both render an HTML `<dialog>` element. `x-ui.modal`: `id` and `title` are required; `description`, `icon`, `tone` and `size` (`sm`, `lg`, `xl`) are optional; a `footer` slot holds actions. `x-ui.drawer`: `id` and `title` are required; `description` is optional; a `footer` slot is available too. Open them with `data-modal-open="#id"` and close them with `data-modal-close` inside the dialog.

```blade
<button type="button" class="btn btn-outline" data-modal-open="#detail-info">{{ __('Information') }}</button>

<x-ui.modal id="detail-info" title="{{ __('Entry information') }}" description="{{ __('Review before continuing.') }}" size="sm">
    <p>{{ __('Dialog content.') }}</p>
    <x-slot:footer>
        <button type="button" class="btn btn-primary" data-modal-close>{{ __('Close') }}</button>
    </x-slot:footer>
</x-ui.modal>
```

To delete data, put a `method="POST"` form with `@csrf` and `@method('DELETE')` in the footer; the cancel button can use `data-modal-close`, but **don't give the submit button `data-modal-close` and a success toast**. The demo at `/components/feedback` only shows the interface interaction.

### 🧾 `x-ui.modal` {#x-ui-modal}

The modal takes `size="sm"` (`max-w-md`), the default `lg` (`max-w-lg`) and `xl` (`max-w-2xl`). The optional `icon` sits next to the title; `tone` sets the icon avatar class (default `info`). The `id` must be unique because it's used for the title and description labels and by `data-modal-open` to find the dialog. The main slot is the body, and `footer` places actions at the bottom. Closing with Esc, a backdrop click or a `data-modal-close` element is handled by `interactions.js`.

### 🗄️ `x-ui.drawer` {#x-ui-drawer}

The drawer takes `id`, `title`, `description` and a `footer` slot. Its content scrolls, so it suits long filters without pushing the action buttons off screen. Triggers and closing work the same as the modal; the built-in X button already has `data-modal-close` and a translated “Close …” label. The drawer is only a visual frame; filters inside it still need a GET form or your own request logic.

```blade
<button type="button" class="btn btn-outline" data-modal-open="#drawer-status">{{ __('Filter') }}</button>
<x-ui.drawer id="drawer-status" title="{{ __('Filter entries') }}">
    <p>{{ __('Choose the criteria to apply.') }}</p>
    <x-slot:footer>
        <button type="button" class="btn btn-outline" data-modal-close>{{ __('Cancel') }}</button>
    </x-slot:footer>
</x-ui.drawer>
```

## ⏳ Progress & skeletons

Progress bars (`.progress`/`.progress-bar`), spinners (`.spinner`) and skeletons (`.skeleton`) are CSS classes, not Blade components. Example markup and states are in `resources/views/showcase/feedback.blade.php`.
