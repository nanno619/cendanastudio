<?php

use App\Enums\Role;
use App\Http\Controllers\Administration\AuditTrailController;
use App\Http\Controllers\Administration\AuditTrailEntryController;
use App\Http\Controllers\Administration\BackupController;
use App\Http\Controllers\Administration\CreateLookupItemController;
use App\Http\Controllers\Administration\DataDictionaryCategoryController;
use App\Http\Controllers\Administration\DataDictionaryController;
use App\Http\Controllers\Administration\DownloadBackupController;
use App\Http\Controllers\Administration\EditLookupItemController;
use App\Http\Controllers\Administration\FileSecurityController;
use App\Http\Controllers\Administration\RunBackupController;
use App\Http\Controllers\Administration\StoreLookupItemController;
use App\Http\Controllers\Administration\ToggleLookupItemController;
use App\Http\Controllers\Administration\UpdateFileSecurityController;
use App\Http\Controllers\Administration\UpdateLookupItemController;
use App\Http\Controllers\LocaleController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::post('/locale', LocaleController::class)->name('locale.update');

Route::middleware('auth')->group(function (): void {
    Route::view('/dashboard', 'admin.dashboard')->name('admin.dashboard');
    Route::view('/analytics', 'admin.analytics')->name('admin.analytics');
    Route::view('/settings', 'admin.settings')->name('admin.settings');

    Route::middleware('role:'.Role::SuperAdmin->value)->prefix('administrations')->name('administrations.')->group(function (): void {
        Route::get('/audit-trail', AuditTrailController::class)->name('audit-trail.index');
        Route::get('/audit-trail/{activity}', AuditTrailEntryController::class)->whereNumber('activity')->name('audit-trail.show');
        Route::get('/backups', BackupController::class)->name('backups.index');
        Route::post('/backups', RunBackupController::class)->middleware('throttle:3,10')->name('backups.store');
        Route::get('/backups/{disk}/{file}', DownloadBackupController::class)->where('file', '[A-Za-z0-9._-]+\.zip')->name('backups.download');
        Route::get('/file-security', FileSecurityController::class)->name('file-security.index');
        Route::put('/file-security', UpdateFileSecurityController::class)->name('file-security.update');
        Route::get('/data-dictionary', DataDictionaryController::class)->name('data-dictionary.index');
        Route::get('/data-dictionary/{category}', DataDictionaryCategoryController::class)->name('data-dictionary.show');
        Route::prefix('/data-dictionary/{category}/items')->name('data-dictionary.items.')->group(function (): void {
            Route::get('/create', CreateLookupItemController::class)->name('create');
            Route::post('/', StoreLookupItemController::class)->name('store');
            Route::get('/{item}/edit', EditLookupItemController::class)->whereNumber('item')->name('edit');
            Route::put('/{item}', UpdateLookupItemController::class)->whereNumber('item')->name('update');
            Route::patch('/{item}/status', ToggleLookupItemController::class)->whereNumber('item')->name('status');
        });
    });

    if (config('cendana.showcase')) {
        Route::prefix('components')->name('showcase.')->group(function (): void {
            Route::view('/cards', 'showcase.cards')->name('cards');
            Route::view('/tables', 'showcase.tables')->name('tables');
            Route::view('/table-states', 'showcase.table-states')->name('table-states');
            Route::view('/forms', 'showcase.forms')->name('forms');
            Route::view('/filters', 'showcase.filters')->name('filters');
            Route::view('/charts', 'showcase.charts')->name('charts');
            Route::view('/buttons', 'showcase.buttons')->name('buttons');
            Route::view('/feedback', 'showcase.feedback')->name('feedback');
            Route::view('/navigation', 'showcase.navigation')->name('navigation');
            Route::view('/data-patterns', 'showcase.data-patterns')->name('data-patterns');
            Route::post('/data-patterns/preview', fn () => redirect()->route('showcase.data-patterns')->with('status', __('Confirmation preview complete; no data was deleted.')))->name('data-patterns.preview');
        });

        Route::prefix('examples/records')->name('examples.records.')->group(function (): void {
            Route::view('/', 'examples.records.index')->name('index');
            Route::view('/new', 'examples.records.create')->name('create');
            Route::view('/detail', 'examples.records.show')->name('show');
            Route::view('/edit', 'examples.records.edit')->name('edit');
        });
    }
});

Route::view('/demo/404', 'errors.404')->name('demo.404');
Route::view('/testing', 'testing')->name('testing');
