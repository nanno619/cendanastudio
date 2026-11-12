<?php

namespace App\Providers;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::before(fn (User $user): ?bool => $user->hasRole(Role::SuperAdmin) ? true : null);

        // Log Viewer (opcodesio/log-viewer) at /log-viewer, embedded in Administrations → Log Viewer.
        // Everyone but Super Admin (allowed by Gate::before) is denied, in every environment.
        Gate::define('viewLogViewer', fn (User $user): bool => false);
    }
}
