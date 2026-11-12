<?php

namespace Database\Factories;

use App\Models\FileSecuritySetting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FileSecuritySetting>
 */
class FileSecuritySettingFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return FileSecuritySetting::defaults();
    }
}
