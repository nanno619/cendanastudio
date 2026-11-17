<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * EPF (KWSP) contribution rates and options, shown on Settings → Payroll → EPF. Holds a single row.
     * Rates are percentages of monthly wages.
     */
    public function up(): void
    {
        Schema::create('epf_settings', function (Blueprint $table) {
            $table->id();
            $table->ulid()->unique();

            $table->decimal('malaysian_employee_rate', 5, 2)->default(11);
            $table->decimal('malaysian_employer_rate', 5, 2)->default(13);
            $table->decimal('malaysian_employer_rate_above_threshold', 5, 2)->default(12);
            $table->decimal('wage_threshold', 10, 2)->default(5000);
            $table->decimal('senior_employee_rate', 5, 2)->default(0);
            $table->decimal('senior_employer_rate', 5, 2)->default(4);
            $table->decimal('foreign_employee_rate', 5, 2)->default(2);
            $table->decimal('foreign_employer_rate', 5, 2)->default(2);

            $table->string('calculation_method', 20)->default('third-schedule');
            $table->boolean('allow_employer_top_up')->default(false);
            $table->decimal('employer_top_up_rate', 5, 2)->nullable();
            $table->boolean('allow_employee_elected_rate')->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('epf_settings');
    }
};
