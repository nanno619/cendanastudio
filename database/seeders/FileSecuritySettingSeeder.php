<?php

namespace Database\Seeders;

use App\Models\FileSecuritySetting;
use Illuminate\Database\Seeder;

class FileSecuritySettingSeeder extends Seeder
{
    /**
     * The default upload policy (all environments). An existing policy is left untouched,
     * so settings saved by Super Admin survive re-seeding.
     */
    public function run(): void
    {
        activity()->withoutLogging(function (): void {
            if (FileSecuritySetting::query()->doesntExist()) {
                FileSecuritySetting::create(FileSecuritySetting::defaults());
            }
        });
    }
}
