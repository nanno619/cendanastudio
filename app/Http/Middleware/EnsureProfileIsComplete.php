<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Keeps a signed-in staff member on My profile until their profile checklist is done
 * (Employee::needsPersonalDetails()). They can still sign out, switch language and manage their account
 * on Settings. Accounts without a staff record are never held back.
 */
class EnsureProfileIsComplete
{
    /**
     * Routes still open while the profile is incomplete.
     *
     * @var list<string>
     */
    public const array ALLOWED_ROUTES = [
        'my-profile.*',
        'logout',
        'locale.update',
        'admin.settings',
        'user-password.update',
        'user-profile-information.update',
        'password.confirm',
        'password.confirm.store',
        'password.confirmation',
        'boost.browser-logs',
    ];

    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->routeIs(...self::ALLOWED_ROUTES) || ! $request->user()?->employee?->needsPersonalDetails()) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            abort(403, __('Complete your profile to continue.'));
        }

        return redirect()->route('my-profile.show');
    }
}
