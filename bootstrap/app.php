<?php

use App\Http\Middleware\EnsureModuleIsAvailable;
use App\Http\Middleware\EnsureProfileIsComplete;
use App\Http\Middleware\SetContentSecurityPolicy;
use App\Http\Middleware\SetLocale;
use App\Http\Middleware\SetSecurityHeaders;
use App\Http\Middleware\SignOutStaffWhoHaveLeft;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->append([SetSecurityHeaders::class, SetContentSecurityPolicy::class]);

        $middleware->web(append: [SetLocale::class, SignOutStaffWhoHaveLeft::class, EnsureProfileIsComplete::class]);

        $middleware->alias([
            'module' => EnsureModuleIsAvailable::class,
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
