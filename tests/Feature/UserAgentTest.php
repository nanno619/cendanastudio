<?php

namespace Tests\Feature;

use App\Support\UserAgent;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class UserAgentTest extends TestCase
{
    /**
     * @return array<string, array{string, string}>
     */
    public static function userAgents(): array
    {
        return [
            'Chrome on macOS' => ['Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome 142 on macOS'],
            'Edge on Windows' => ['Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', 'Edge 142 on Windows'],
            'Opera on Windows' => ['Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 OPR/124.0.0.0', 'Opera 124 on Windows'],
            'Firefox on Windows' => ['Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0', 'Firefox 144 on Windows'],
            'Firefox on Linux' => ['Mozilla/5.0 (X11; Linux x86_64; rv:144.0) Gecko/20100101 Firefox/144.0', 'Firefox 144 on Linux'],
            'Safari on macOS' => ['Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.1 Safari/605.1.15', 'Safari 18 on macOS'],
            'Safari on iPhone' => ['Mozilla/5.0 (iPhone; CPU iPhone OS 18_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.1 Mobile/15E148 Safari/604.1', 'Safari 18 on iOS'],
            'Chrome on iPad' => ['Mozilla/5.0 (iPad; CPU OS 18_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/142.0.0.0 Mobile/15E148 Safari/604.1', 'Chrome 142 on iPadOS'],
            'Samsung Internet on Android' => ['Mozilla/5.0 (Linux; Android 15; SM-S928B) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/27.0 Chrome/125.0.0.0 Mobile Safari/537.36', 'Samsung Internet 27 on Android'],
            'Chrome on ChromeOS' => ['Mozilla/5.0 (X11; CrOS x86_64 14541.0.0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome 142 on ChromeOS'],
            'curl' => ['curl/8.7.1', 'curl 8'],
        ];
    }

    #[DataProvider('userAgents')]
    public function test_describes_common_browsers(string $userAgent, string $expected): void
    {
        $this->assertSame($expected, UserAgent::describe($userAgent));
    }

    public function test_returns_null_for_missing_or_unrecognised_agents(): void
    {
        $this->assertNull(UserAgent::describe(null));
        $this->assertNull(UserAgent::describe(''));
        $this->assertNull(UserAgent::describe('SomeInternalBot'));
    }

    public function test_label_is_translated(): void
    {
        $this->app->setLocale('ms');

        $this->assertSame('Firefox 144 pada Linux', UserAgent::describe('Mozilla/5.0 (X11; Linux x86_64; rv:144.0) Gecko/20100101 Firefox/144.0'));
    }
}
