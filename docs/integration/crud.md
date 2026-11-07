# 🗃️ Data & CRUD pages

This example turns the `examples/records` demo pages into saved entries. As an illustration, we use a `Record` model with `title`, `category`, `status` and `summary` columns. Adapt the schema, access policies and route names to your app. The old demo routes in `routes/web.php` sit inside an `if (config('cendana.showcase'))` block; remove or change the old `examples.records.*` route block before registering the same routes.

## 🧱 Set up the model and table

```bash
php artisan make:model Record -m
php artisan make:controller RecordController
```

In the new migration (`database/migrations/...create_records_table.php`), define the table:

```php
Schema::create('records', function (Blueprint $table) {
    $table->id();
    $table->string('title');
    $table->string('category');
    $table->string('status')->default('draft');
    $table->text('summary')->nullable();
    $table->timestamps();
});
```

Then run `php artisan migrate`. If you use `Record::create()` and `$record->update()`, declare the fillable fields in `app/Models/Record.php`:

```php
protected $fillable = ['title', 'category', 'status', 'summary'];
```

## 🛣️ Register the routes

Use the route names the sample navigation already uses so the sidebar links keep working. The static `/new` route is defined before the `{record}` parameter so it isn't captured as an ID; model binding returns a 404 for missing entries.

```php
use App\Http\Controllers\RecordController;

Route::prefix('examples/records')->name('examples.records.')->group(function () {
    Route::get('/', [RecordController::class, 'index'])->name('index');
    Route::get('/new', [RecordController::class, 'create'])->name('create');
    Route::post('/', [RecordController::class, 'store'])->name('store');
    Route::get('/{record}', [RecordController::class, 'show'])->name('show');
    Route::get('/{record}/edit', [RecordController::class, 'edit'])->name('edit');
    Route::put('/{record}', [RecordController::class, 'update'])->name('update');
    Route::delete('/{record}', [RecordController::class, 'destroy'])->name('destroy');
});
```

If this entry flow must stay available when the showcase is off, put the new routes **outside** the `if (config('cendana.showcase'))` block, and adjust the navigation config if the menu should show too.

## 📤 Pass data from the controller

```php
namespace App\Http\Controllers;

use App\Models\Record;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RecordController extends Controller
{
    public function index(Request $request): View
    {
        $records = Record::query()
            ->when($request->filled('q'), fn ($query) => $query->where('title', 'like', '%'.$request->input('q').'%'))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('examples.records.index', compact('records'));
    }

    public function create(): View
    {
        return view('examples.records.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'in:guides,reports,notes,archive'],
            'status' => ['required', 'string', 'in:draft,in-review,active,archived'],
            'summary' => ['nullable', 'string'],
        ]);

        $record = Record::create($data);

        return redirect()->route('examples.records.show', $record)
            ->with('status', __('Entry created.'));
    }

    public function show(Record $record): View
    {
        return view('examples.records.show', compact('record'));
    }

    public function edit(Record $record): View
    {
        return view('examples.records.edit', compact('record'));
    }

    public function update(Request $request, Record $record): RedirectResponse
    {
        $record->update($request->validate([
            'title' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'in:guides,reports,notes,archive'],
            'status' => ['required', 'string', 'in:draft,in-review,active,archived'],
            'summary' => ['nullable', 'string'],
        ]));

        return redirect()->route('examples.records.show', $record)
            ->with('status', __('Entry updated.'));
    }

    public function destroy(Record $record): RedirectResponse
    {
        $record->delete();

        return redirect()->route('examples.records.index')
            ->with('status', __('Entry deleted.'));
    }
}
```

The category and status values (`guides`, `draft`, `in-review`…) match the option keys the demo form already uses. Store these stable keys in the database and translate the label when you display it.

## 🧩 Adapt the demo Blade views

The built-in view uses `config('cendana.demo.records')` and a static `/detail` link. Replace the loop and links with `$records` and the model ID:

```blade
@forelse ($records as $record)
    <tr>
        <td>
            <a href="{{ route('examples.records.show', $record) }}">
                {{ $record->title }}
            </a>
        </td>
        <td>{{ __(ucfirst($record->category)) }}</td>
        <td>{{ __(ucfirst(str_replace('-', ' ', $record->status))) }}</td>
    </tr>
@empty
    <tr><td colspan="3"><x-ui.table-state state="empty" /></td></tr>
@endforelse
```

After the table, output `$records->links()` and remove `data-table` to use server pagination. In the detail/edit views, replace the static content and the `/detail`/`/edit` links with `$record` and `route('examples.records.edit', $record)`. A connected form example is in [forms & validation](/integration/forms).
