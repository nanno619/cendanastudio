<?php

namespace Tests\Feature;

use App\Http\Middleware\SetContentSecurityPolicy;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class ContentSecurityPolicyTest extends TestCase
{
    use RefreshDatabase;

    private function nonceFrom(TestResponse $response, string $header): string
    {
        preg_match("/'nonce-([A-Za-z0-9]+)'/", (string) $response->headers->get($header), $matches);

        $this->assertNotEmpty($matches, "No nonce in {$header}.");

        return $matches[1];
    }

    public function test_pages_send_a_report_only_policy_by_default(): void
    {
        $response = $this->get(route('login'))
            ->assertOk()
            ->assertHeaderMissing('Content-Security-Policy');

        $policy = $response->headers->get('Content-Security-Policy-Report-Only');

        $this->assertStringContainsString("object-src 'none'", $policy);
        $this->assertStringContainsString("frame-ancestors 'self'", $policy);
        $this->assertStringContainsString('report-uri /csp-report', $policy);
    }

    public function test_every_inline_script_carries_the_request_nonce(): void
    {
        config(['cendana.csp.report_only' => false]);

        $response = $this->actingAs(User::factory()->create())->get(route('admin.dashboard'))->assertOk();
        $nonce = $this->nonceFrom($response, 'Content-Security-Policy');

        preg_match_all('/<script\b[^>]*>/', $response->getContent(), $scripts);

        $this->assertNotEmpty($scripts[0]);

        foreach ($scripts[0] as $tag) {
            $this->assertStringContainsString("nonce=\"{$nonce}\"", $tag);
        }
    }

    public function test_each_request_gets_a_new_nonce(): void
    {
        $first = $this->nonceFrom($this->get(route('login')), 'Content-Security-Policy-Report-Only');
        $second = $this->nonceFrom($this->get(route('login')), 'Content-Security-Policy-Report-Only');

        $this->assertNotSame($first, $second);
    }

    public function test_turnstile_is_allowed_when_enabled(): void
    {
        config(['services.turnstile.site_key' => 'site-key', 'services.turnstile.secret_key' => 'secret-key']);

        $response = $this->get(route('login'))->assertOk()->assertSee('challenges.cloudflare.com/turnstile', false);
        $policy = $response->headers->get('Content-Security-Policy-Report-Only');

        $this->assertMatchesRegularExpression('/script-src [^;]*'.preg_quote(SetContentSecurityPolicy::TURNSTILE_ORIGIN, '/').'/', $policy);
        $this->assertMatchesRegularExpression('/frame-src [^;]*'.preg_quote(SetContentSecurityPolicy::TURNSTILE_ORIGIN, '/').'/', $policy);
    }

    public function test_log_viewer_and_non_html_responses_are_left_alone(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/log-viewer')
            ->assertHeaderMissing('Content-Security-Policy-Report-Only');

        $this->get('/up')->assertHeader('Content-Security-Policy-Report-Only');

        $this->postJson(route('locale.update'), ['locale' => 'xx'])
            ->assertUnprocessable()
            ->assertHeaderMissing('Content-Security-Policy-Report-Only');
    }

    public function test_the_policy_can_be_switched_off(): void
    {
        config(['cendana.csp.enabled' => false]);

        $this->get(route('login'))
            ->assertOk()
            ->assertHeaderMissing('Content-Security-Policy')
            ->assertHeaderMissing('Content-Security-Policy-Report-Only');
    }

    public function test_violation_reports_are_logged_with_known_fields_only(): void
    {
        $channel = Log::spy();
        Log::shouldReceive('channel')->with('csp')->andReturn($channel);

        $this->call('POST', route('csp.report'), server: ['CONTENT_TYPE' => 'application/csp-report'], content: json_encode([
            'csp-report' => [
                'document-uri' => 'https://example.test/dashboard',
                'effective-directive' => 'script-src-elem',
                'blocked-uri' => 'inline',
                'cookie' => 'secret',
            ],
        ]))->assertNoContent();

        $channel->shouldHaveReceived('warning')->once()->with('CSP violation', [
            'document-uri' => 'https://example.test/dashboard',
            'effective-directive' => 'script-src-elem',
            'blocked-uri' => 'inline',
        ]);
    }

    public function test_malformed_reports_are_ignored(): void
    {
        $channel = Log::spy();
        Log::shouldReceive('channel')->with('csp')->andReturn($channel);

        $this->call('POST', route('csp.report'), content: 'not json')->assertNoContent();
        $this->call('POST', route('csp.report'), content: json_encode(['csp-report' => ['cookie' => 'secret']]))->assertNoContent();

        $channel->shouldNotHaveReceived('warning');
    }
}
