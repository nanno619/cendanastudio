<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocaleTest extends TestCase
{
    use RefreshDatabase;

    public function test_interface_defaults_to_english(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('<html lang="en">', false)
            ->assertSee('Sign in to your account');
    }

    public function test_guest_can_switch_to_malay(): void
    {
        $this->from(route('login'))
            ->post(route('locale.update'), ['locale' => 'ms'])
            ->assertRedirect(route('login'))
            ->assertSessionHas('locale', 'ms');

        $this->get(route('login'))
            ->assertOk()
            ->assertSee('<html lang="ms">', false)
            ->assertSee('Log masuk ke akaun anda');
    }

    public function test_admin_pages_render_in_malay(): void
    {
        $this->actingAs(User::factory()->create())
            ->withSession(['locale' => 'ms'])
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Prestasi perniagaan anda sepanjang 30 hari lepas.')
            ->assertSee('Tetapan akaun')
            ->assertSee('"copied":"Disalin ke papan keratan"', false);
    }

    public function test_malay_validation_messages_are_used(): void
    {
        $this->withSession(['locale' => 'ms'])
            ->from(route('login'))
            ->post(route('login.store'), ['email' => '', 'password' => ''])
            ->assertSessionHasErrors(['email' => 'Medan e-mel diperlukan.']);
    }

    public function test_unsupported_locale_is_rejected(): void
    {
        $this->from(route('login'))
            ->post(route('locale.update'), ['locale' => 'id'])
            ->assertSessionHasErrors('locale')
            ->assertSessionMissing('locale');
    }
}
