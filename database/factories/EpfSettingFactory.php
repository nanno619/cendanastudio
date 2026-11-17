<?php

namespace Database\Factories;

use App\Models\EpfSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EpfSetting>
 */
class EpfSettingFactory extends Factory
{
    /**
     * The statutory EPF rules.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return EpfSetting::defaults();
    }
}
