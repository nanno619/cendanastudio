<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class RoleUserSeeder extends Seeder
{
    /**
     * Local/testing only: one sign-in per role (e.g. project-manager@demo.test / password).
     * Super Admin is skipped; the ADMIN_SEED_* admin already holds it and is never touched here.
     */
    public function run(): void
    {
        if (app()->isProduction()) {
            $this->command?->warn('Skipping role demo users in production.');

            return;
        }

        foreach (Role::cases() as $role) {
            $email = Str::slug($role->value).'@demo.test';

            if ($role === Role::SuperAdmin || $email === config('cendana.seed_admin.email')) {
                continue;
            }

            User::updateOrCreate(
                ['email' => $email],
                ['name' => $role->value.' User', 'password' => 'password', 'email_verified_at' => now()],
            )->syncRoles($role);
        }
    }
}
