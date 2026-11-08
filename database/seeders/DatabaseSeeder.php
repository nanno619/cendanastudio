<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use RuntimeException;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $admin = config('cendana.seed_admin');

        if (blank($admin['email']) || blank($admin['password'])) {
            if (app()->isProduction()) {
                throw new RuntimeException('Set ADMIN_SEED_EMAIL and ADMIN_SEED_PASSWORD in .env before seeding production.');
            }

            // Local convenience only: a throwaway login so the dashboard can be opened right away.
            $admin = ['name' => 'Test User', 'email' => 'test@example.com', 'password' => 'password'];
            $this->command?->warn('ADMIN_SEED_EMAIL/ADMIN_SEED_PASSWORD not set; seeding test@example.com / password.');
        }

        if (mb_strlen($admin['password']) < 12 && app()->isProduction()) {
            throw new RuntimeException('ADMIN_SEED_PASSWORD must be at least 12 characters in production.');
        }

        $this->call(RoleSeeder::class);

        User::updateOrCreate(
            ['email' => $admin['email']],
            ['name' => $admin['name'] ?: 'Administrator', 'password' => $admin['password'], 'email_verified_at' => now()],
        )->syncRoles(Role::SuperAdmin);

        $this->call([RoleUserSeeder::class, AuditTrailDemoSeeder::class]);
    }
}
