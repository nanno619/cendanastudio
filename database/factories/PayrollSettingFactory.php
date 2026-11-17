<?php

namespace Database\Factories;

use App\Models\PayrollSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PayrollSetting>
 */
class PayrollSettingFactory extends Factory
{
    /**
     * The default payroll rules.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return PayrollSetting::defaults();
    }
}
