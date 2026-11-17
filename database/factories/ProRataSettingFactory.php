<?php

namespace Database\Factories;

use App\Models\ProRataSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProRataSetting>
 */
class ProRataSettingFactory extends Factory
{
    /**
     * The default pro-rata rules.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return ProRataSetting::defaults();
    }
}
