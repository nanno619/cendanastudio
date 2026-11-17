<?php

namespace Database\Seeders;

use App\Enums\ModuleKey;
use App\Models\Module;
use Illuminate\Database\Seeder;

class ModuleSeeder extends Seeder
{
    /**
     * A row for every module (all environments), on and open to everyone, with Malay names. Existing rows
     * are left untouched, so changes saved by Super Admin survive re-seeding.
     */
    public function run(): void
    {
        $malayNames = [
            ModuleKey::Leave->value => 'Cuti',
            ModuleKey::Payroll->value => 'Gaji',
            ModuleKey::Claim->value => 'Tuntutan',
            ModuleKey::Hadir->value => 'Hadir',
        ];

        activity()->withoutLogging(function () use ($malayNames): void {
            foreach (ModuleKey::cases() as $key) {
                Module::query()->firstOrCreate(
                    ['key' => $key->value],
                    [...Module::defaults($key), 'name_ms' => $malayNames[$key->value] ?? null],
                );
            }
        });
    }
}
