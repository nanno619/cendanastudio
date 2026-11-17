<?php

namespace Database\Factories;

use App\Models\PayslipSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PayslipSetting>
 */
class PayslipSettingFactory extends Factory
{
    /**
     * The default payslip options.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return PayslipSetting::defaults();
    }
}
