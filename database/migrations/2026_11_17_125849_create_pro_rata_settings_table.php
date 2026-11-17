<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * How part-month wages are worked out, shown on Settings → Payroll → Pro-Rata. Holds a single row.
     */
    public function up(): void
    {
        Schema::create('pro_rata_settings', function (Blueprint $table) {
            $table->id();
            $table->ulid()->unique();

            $table->string('joiner_leaver_basis', 20)->default('calendar-days');
            $table->string('unpaid_leave_basis', 20)->default('fixed-days');
            $table->unsignedTinyInteger('fixed_days')->nullable()->default(26);
            $table->boolean('prorate_fixed_allowances')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pro_rata_settings');
    }
};
