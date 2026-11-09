<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Laravel\Fortify\Fortify;
use Symfony\Component\HttpFoundation\Response;

/**
 * Requires a valid Cloudflare Turnstile token on the sign-in and forgot-password forms.
 * Does nothing until TURNSTILE_SITE_KEY and TURNSTILE_SECRET_KEY are set (local, tests).
 */
class VerifyTurnstile
{
    private const VERIFY_URL = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';

    /** Fortify routes that must pass the check. */
    private const PROTECTED_ROUTES = ['login.store', 'password.email'];

    public static function enabled(): bool
    {
        return filled(config('services.turnstile.site_key')) && filled(config('services.turnstile.secret_key'));
    }

    public function handle(Request $request, Closure $next): Response
    {
        if (! self::enabled() || ! $request->isMethod('POST') || ! $request->routeIs(self::PROTECTED_ROUTES)) {
            return $next($request);
        }

        if ($this->passes($request)) {
            return $next($request);
        }

        activity()->causedByAnonymous()->event('bot-check-failed')
            ->withProperties(array_filter([
                'email' => $request->input(Fortify::username()),
                'ip' => $request->ip(),
                'user_agent' => Str::limit((string) $request->userAgent(), 255, ''),
            ]))
            ->log('Blocked by the security check on '.($request->routeIs('login.store') ? 'sign-in' : 'forgot password'));

        return back()
            ->withInput($request->except('password', 'cf-turnstile-response'))
            ->withErrors([Fortify::username() => __('Please complete the security check and try again.')]);
    }

    /**
     * Ask Cloudflare whether the token is valid. Fails closed: no token, an invalid token or no answer all block.
     */
    private function passes(Request $request): bool
    {
        $token = $request->string('cf-turnstile-response')->toString();

        if ($token === '') {
            return false;
        }

        try {
            return Http::asForm()->timeout(5)->post(self::VERIFY_URL, [
                'secret' => config('services.turnstile.secret_key'),
                'response' => $token,
                'remoteip' => $request->ip(),
            ])->json('success') === true;
        } catch (ConnectionException) {
            return false;
        }
    }
}
