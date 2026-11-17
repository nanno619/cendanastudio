<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Closure;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Fortify;

/**
 * Sign-in pipeline step before Fortify's AttemptToAuthenticate: refuses the right password for a staff
 * member whose last working day has passed, without signing them in. Wrong passwords pass through, so
 * Fortify records the failed attempt as usual.
 */
class RejectStaffWhoHaveLeft
{
    public function __construct(protected StatefulGuard $guard) {}

    /**
     * @param  Closure(Request): mixed  $next
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $credentials = $request->only(Fortify::username(), 'password');
        $provider = $this->guard->getProvider();
        $user = $provider->retrieveByCredentials($credentials);

        if ($user instanceof User && $provider->validateCredentials($user, $credentials) && $user->employee?->hasLeft()) {
            throw ValidationException::withMessages([
                Fortify::username() => __('This account is closed because its staff record shows the person has left.'),
            ]);
        }

        return $next($request);
    }
}
