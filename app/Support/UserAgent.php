<?php

namespace App\Support;

/**
 * Turns a raw User-Agent header into a short label such as "Chrome 142 on macOS" for display.
 *
 * Covers the browsers and systems staff realistically use; anything unrecognised returns null
 * so callers can fall back to the raw string. Display only: never use it for security decisions.
 */
class UserAgent
{
    /**
     * Browser patterns, checked in order (Edge and Opera also contain "Chrome", Chrome also contains "Safari").
     *
     * @var array<string, string>
     */
    private const BROWSERS = [
        'Edge' => '/\b(?:Edg|EdgA|EdgiOS)\/(\d+)/',
        'Opera' => '/\b(?:OPR|OPiOS)\/(\d+)/',
        'Samsung Internet' => '/\bSamsungBrowser\/(\d+)/',
        'Firefox' => '/\b(?:Firefox|FxiOS)\/(\d+)/',
        'Chrome' => '/\b(?:Chrome|CriOS)\/(\d+)/',
        'Safari' => '/\bVersion\/(\d+)[\d.]* (?:Mobile\/\S+ )?Safari\//',
        'curl' => '/^curl\/(\d+)/',
        'Postman' => '/^PostmanRuntime\/(\d+)/',
    ];

    /**
     * Operating system patterns, checked in order (iOS and Android user agents also mention macOS and Linux).
     *
     * @var array<string, string>
     */
    private const SYSTEMS = [
        'iOS' => '/\biPhone\b|\biPod\b/',
        'iPadOS' => '/\biPad\b/',
        'Android' => '/\bAndroid\b/',
        'ChromeOS' => '/\bCrOS\b/',
        'Windows' => '/\bWindows\b/',
        'macOS' => '/\bMac OS X\b|\bMacintosh\b/',
        'Linux' => '/\bLinux\b/',
    ];

    public static function describe(?string $userAgent): ?string
    {
        if (blank($userAgent)) {
            return null;
        }

        $browser = self::match(self::BROWSERS, $userAgent);
        $system = self::match(self::SYSTEMS, $userAgent);

        return match (true) {
            $browser !== null && $system !== null => __(':browser on :system', ['browser' => $browser, 'system' => $system]),
            default => $browser ?? $system,
        };
    }

    /**
     * @param  array<string, string>  $patterns
     */
    private static function match(array $patterns, string $userAgent): ?string
    {
        foreach ($patterns as $name => $pattern) {
            if (preg_match($pattern, $userAgent, $matches)) {
                return isset($matches[1]) ? "{$name} {$matches[1]}" : $name;
            }
        }

        return null;
    }
}
