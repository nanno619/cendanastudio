<?php

namespace Tests\Feature;

use App\Http\Middleware\SetSecurityHeaders;
use Tests\TestCase;

class SecurityHeadersTest extends TestCase
{
    public function test_https_responses_carry_the_security_headers(): void
    {
        $this->get('https://localhost/login')
            ->assertOk()
            ->assertHeader('Strict-Transport-Security', SetSecurityHeaders::HSTS)
            ->assertHeader('Referrer-Policy', SetSecurityHeaders::REFERRER_POLICY)
            ->assertHeader('Permissions-Policy', SetSecurityHeaders::PERMISSIONS_POLICY);
    }

    public function test_plain_http_responses_skip_hsts(): void
    {
        $this->get('http://localhost/login')
            ->assertOk()
            ->assertHeaderMissing('Strict-Transport-Security')
            ->assertHeader('Referrer-Policy', SetSecurityHeaders::REFERRER_POLICY);
    }

    public function test_redirects_and_errors_carry_the_headers_too(): void
    {
        $this->get('https://localhost/dashboard')
            ->assertRedirect()
            ->assertHeader('Referrer-Policy', SetSecurityHeaders::REFERRER_POLICY);

        $this->get('https://localhost/no-such-page')
            ->assertNotFound()
            ->assertHeader('Strict-Transport-Security', SetSecurityHeaders::HSTS);
    }
}
