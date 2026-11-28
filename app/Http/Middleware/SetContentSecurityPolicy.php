<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Vite;
use Symfony\Component\HttpFoundation\Response;

/**
 * Sends a nonce-based Content-Security-Policy on HTML pages. Inline scripts carry
 * `nonce="{{ Vite::cspNonce() }}"`; @vite tags get it automatically. Report-only
 * until `cendana.csp.report_only` is turned off; violations go to the `csp.report` route.
 */
class SetContentSecurityPolicy
{
    /**
     * Cloudflare Turnstile loads its script and widget iframe from here.
     */
    public const TURNSTILE_ORIGIN = 'https://challenges.cloudflare.com';

    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! config('cendana.csp.enabled')) {
            return $next($request);
        }

        $nonce = Vite::useCspNonce();

        $response = $next($request);

        if ($this->appliesTo($request, $response)) {
            $header = config('cendana.csp.report_only') ? 'Content-Security-Policy-Report-Only' : 'Content-Security-Policy';

            $response->headers->set($header, $this->policy($nonce));
        }

        return $response;
    }

    /**
     * HTML pages only (PDFs and downloads have no scripts), and not Log Viewer,
     * whose package views use inline scripts without a nonce.
     */
    private function appliesTo(Request $request, Response $response): bool
    {
        return str_starts_with((string) $response->headers->get('Content-Type'), 'text/html')
            && ! $request->is(config('log-viewer.route_path'), config('log-viewer.route_path').'/*');
    }

    private function policy(string $nonce): string
    {
        [$devScripts, $devSockets] = $this->viteDevServerSources();

        $directives = [
            'default-src' => ["'self'"],
            'script-src' => ["'self'", "'nonce-{$nonce}'", self::TURNSTILE_ORIGIN, ...$devScripts],
            // Inline style attributes are used throughout the UI; a nonce here would disable 'unsafe-inline'.
            'style-src' => ["'self'", "'unsafe-inline'", ...$devScripts],
            'img-src' => ["'self'", 'data:', 'blob:'],
            'font-src' => ["'self'", 'data:', ...$devScripts],
            'connect-src' => ["'self'", ...$devScripts, ...$devSockets],
            'frame-src' => ["'self'", self::TURNSTILE_ORIGIN],
            'frame-ancestors' => ["'self'"],
            'object-src' => ["'none'"],
            'base-uri' => ["'self'"],
            'form-action' => ["'self'"],
            'report-uri' => [route('csp.report', absolute: false)],
        ];

        return collect($directives)
            ->map(fn (array $sources, string $directive): string => $directive.' '.implode(' ', $sources))
            ->implode('; ');
    }

    /**
     * While `npm run dev` runs, assets and hot reload come from the Vite dev server.
     *
     * @return array{0: list<string>, 1: list<string>}
     */
    private function viteDevServerSources(): array
    {
        if (! Vite::isRunningHot()) {
            return [[], []];
        }

        $origin = rtrim(trim((string) file_get_contents(Vite::hotFile())), '/');

        return [[$origin], [preg_replace('#^http#', 'ws', $origin)]];
    }
}
