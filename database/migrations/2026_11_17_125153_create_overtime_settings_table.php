<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Overtime pay rules, shown on Settings → Payroll → Overtime. Holds a single row.
     * Rates are multiples of the hourly rate of pay.
     */
    public function up(): void
    {
        Schema::create('overtime_settings', function (Blueprint $table) {
            $table->id();
            $table->ulid()->unique();

            $table->string('eligibility', 20)->default('employment-act');
            $table->decimal('eligibility_wage_limit', 10, 2)->nullable()->default(4000);

            $table->decimal('normal_day_rate', 4, 2)->default(1.5);
            $table->decimal('rest_day_rate', 4, 2)->default(2);
            $table->decimal('public_holiday_rate', 4, 2)->default(3);
            $table->decimal('normal_hours_per_day', 4, 2)->default(8);
            $table->unsignedSmallInteger('monthly_limit_hours')->default(104);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('overtime_settings');
    }
};
