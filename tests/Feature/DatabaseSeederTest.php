<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use RuntimeException;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeds_the_admin_from_env_config(): void
    {
        config(['cendana.seed_admin' => ['name' => 'Studio Owner', 'email' => 'owner@example.test', 'password' => 'a-long-secret-pass']]);

        $this->seed(DatabaseSeeder::class);
        $this->seed(DatabaseSeeder::class);

        $user = User::sole();
        $this->assertSame('owner@example.test', $user->email);
        $this->assertSame('Studio Owner', $user->name);
        $this->assertTrue(Hash::check('a-long-secret-pass', $user->password));
        $this->assertNotNull($user->email_verified_at);
    }

    public function test_falls_back_to_the_test_user_outside_production(): void
    {
        config(['cendana.seed_admin' => ['name' => null, 'email' => null, 'password' => null]]);

        $this->seed(DatabaseSeeder::class);

        $this->assertDatabaseHas('users', ['email' => 'test@example.com']);
    }

    public function test_refuses_to_seed_production_without_credentials(): void
    {
        config(['cendana.seed_admin' => ['name' => null, 'email' => null, 'password' => null]]);
        $this->app->detectEnvironment(fn () => 'production');

        $this->expectException(RuntimeException::class);

        (new DatabaseSeeder)->run();
    }

    public function test_refuses_a_short_password_in_production(): void
    {
        config(['cendana.seed_admin' => ['name' => 'Owner', 'email' => 'owner@example.test', 'password' => 'short']]);
        $this->app->detectEnvironment(fn () => 'production');

        $this->expectException(RuntimeException::class);

        (new DatabaseSeeder)->run();
    }
}
