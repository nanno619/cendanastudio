# 🧭 Pages & navigation

## 📝 Create an admin page

Say you're adding a category list. Create `resources/views/admin/categories.blade.php`:

```blade
<x-layouts.admin title="{{ __('Categories') }}" group="{{ __('Catalogue') }}">
    <x-ui.page-header title="{{ __('Categories') }}" description="{{ __('Manage product categories.') }}" />
    <x-ui.card title="{{ __('Category list') }}">
        @forelse ($categories as $category)
            <p>{{ $category->name }}</p>
        @empty
            <x-ui.table-state state="empty" title="{{ __('No categories yet') }}" />
        @endforelse
    </x-ui.card>
</x-layouts.admin>
```

If the data comes from the database, create a controller and register the route in `routes/web.php` (this example needs a `Category` model that you create yourself):

```php
use App\Http\Controllers\CategoryController;

Route::get('/categories', [CategoryController::class, 'index'])
    ->name('admin.categories.index');
```

```php
namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        return view('admin.categories', [
            'categories' => Category::query()->orderBy('name')->get(),
        ]);
    }
}
```

If a page is fully static, you can follow the current routes: `Route::view('/categories', 'admin.categories')->name('admin.categories.index')`. Don't use `Route::view` for an example that needs `$categories` from the database.

## 🗺️ Add a sidebar menu item

In `config/cendana.php`, `navigation` is a list of groups and items. `x-admin.sidebar` reads this config, and `x-admin.nav-link` builds links with `route(...)` and marks the active one with `request()->routeIs(...)`.

```php
['label' => 'Catalogue', 'items' => [
    [
        'label' => 'Categories',
        'route' => 'admin.categories.index',
        'active' => 'admin.categories.*',
        'icon' => 'grid',
    ],
]],
```

Labels are written in English and translated when rendered, so add `"Catalogue"` and `"Categories"` to `lang/ms.json` for the Malay interface. Make sure the route name actually exists; a menu item pointing at an unregistered route fails to render. For submenus, see the `Authentication` item in the config: the parent item has an `id`, an `icon` and `children` holding route items. Icons must have a matching symbol in `public/icons.svg`.

### 🧭 Admin shell and active state

`x-admin.sidebar` and `x-admin.header` are rendered automatically by `x-layouts.admin`. The sidebar builds its groups from `config('cendana.navigation')`. Each item needs a `label` and a `route`; the optional `active` can be a route-name pattern such as `admin.categories.*`. `icon` and `badge` are optional too. Nested items use `children` and open/close through `data-collapse`. The header breadcrumb comes from the layout's `title`/`group` props, so renaming a menu item doesn't change the breadcrumb.

The workspace branding from config appears at the top of the sidebar, and the profile at the bottom shows the signed-in user's name and email from `auth()->user()`. The header notification menu shows an empty state; connect it to your own notification source when you need one. The customizer is already part of the layout, so you don't need to include it in each view.

## 🧪 Manage the demo pages

The `/components/*` and `/examples/records/*` routes are only registered when `config('cendana.showcase')` is true. To hide the showcase and demo menus, set `ADMIN_SHOWCASE=false` in `.env`. The dashboard, analytics, settings, login and demo 404 pages stay, as defined in `routes/web.php`.

::: warning Watch for links to the showcase
Before turning the showcase off, check any custom links to `showcase.*` routes. The built-in dashboard already checks `config('cendana.showcase')` for its “View all” link.
:::
