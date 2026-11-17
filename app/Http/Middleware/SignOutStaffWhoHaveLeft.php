<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Ends the session of a signed-in staff member whose last working day has passed, so access stops the
 * day after they leave even if they never sign out. Sign-in itself is refused in FortifyServiceProvider.
 */
class SignOutStaffWhoHaveLeft
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->employee?->hasLeft()) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors(['email' => __('This account is closed because its staff record shows the person has left.')]);
        }

        return $next($request);
    }
}
