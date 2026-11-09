<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class TurnstileTest extends TestCase
{
    use RefreshDatabase;

    private const VERIFY_URL = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';

    private function enableTurnstile(): void
    {
        config(['services.turnstile' => ['site_key' => 'test-site-key', 'secret_key' => 'test-secret-key']]);
    }

    public function test_forms_show_no_widget_and_skip_the_check_without_keys(): void
    {
        Http::fake();
        $user = User::factory()->create();

        $this->get(route('login'))->assertDontSee('cf-turnstile', false);
        $this->post(route('login.store'), ['email' => $user->email, 'password' => 'password'])
            ->assertRedirect(route('admin.dashboard'));

        Http::assertNothingSent();
    }

    public function test_login_and_forgot_password_show_the_widget_when_configured(): void
    {
        $this->enableTurnstile();

        $this->get(route('login'))->assertSee('data-sitekey="test-site-key"', false)->assertSee('challenges.cloudflare.com/turnstile/v0/api.js');
        $this->get(route('password.request'))->assertSee('data-sitekey="test-site-key"', false);
    }

    public function test_sign_in_without_a_token_is_blocked_and_audited(): void
    {
        $this->enableTurnstile();
        Http::fake();
        $user = User::factory()->create();

        $this->from(route('login'))
            ->post(route('login.store'), ['email' => $user->email, 'password' => 'password'])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors(['email' => 'Please complete the security check and try again.']);

        $this->assertGuest();
        Http::assertNothingSent();
        $this->assertSame($user->email, Activity::query()->where('event', 'bot-check-failed')->sole()->properties['email']);
    }

    public function test_sign_in_with_a_rejected_token_is_blocked(): void
    {
        $this->enableTurnstile();
        Http::fake([self::VERIFY_URL => Http::response(['success' => false, 'error-codes' => ['invalid-input-response']])]);
        $user = User::factory()->create();

        $this->post(route('login.store'), ['email' => $user->email, 'password' => 'password', 'cf-turnstile-response' => 'bad-token'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_sign_in_with_a_valid_token_proceeds_and_sends_the_secret(): void
    {
        $this->enableTurnstile();
        Http::fake([self::VERIFY_URL => Http::response(['success' => true])]);
        $user = User::factory()->create();

        $this->post(route('login.store'), ['email' => $user->email, 'password' => 'password', 'cf-turnstile-response' => 'good-token'])
            ->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($user);
        Http::assertSent(fn (Request $request): bool => $request['secret'] === 'test-secret-key' && $request['response'] === 'good-token');
    }

    public function test_cloudflare_being_unreachable_blocks_sign_in(): void
    {
        $this->enableTurnstile();
        Http::fake([self::VERIFY_URL => Http::failedConnection()]);
        $user = User::factory()->create();

        $this->post(route('login.store'), ['email' => $user->email, 'password' => 'password', 'cf-turnstile-response' => 'token'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_forgot_password_requires_the_check(): void
    {
        $this->enableTurnstile();
        Http::fake();

        $this->post(route('password.email'), ['email' => 'someone@example.test'])
            ->assertSessionHasErrors('email');
    }
}
