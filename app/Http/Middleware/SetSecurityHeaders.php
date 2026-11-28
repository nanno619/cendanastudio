<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Adds browser security headers to every response. X-Frame-Options and
 * X-Content-Type-Options are set by nginx (they cover static files too).
 */
class SetSecurityHeaders
{
    /**
     * One year; no includeSubDomains or preload until every subdomain serves HTTPS.
     */
    public const HSTS = 'max-age=31536000';

    public const REFERRER_POLICY = 'strict-origin-when-cross-origin';

    /**
     * Location stays available to our own pages for Hadir check-ins; allow the
     * camera here (camera=(self)) when Hadir selfies are built.
     */
    public const PERMISSIONS_POLICY = 'accelerometer=(), camera=(), geolocation=(self), gyroscope=(), magnetometer=(), microphone=(), payment=(), usb=()';

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('Referrer-Policy', self::REFERRER_POLICY, false);
        $response->headers->set('Permissions-Policy', self::PERMISSIONS_POLICY, false);

        if ($request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', self::HSTS, false);
        }

        return $response;
    }
}
