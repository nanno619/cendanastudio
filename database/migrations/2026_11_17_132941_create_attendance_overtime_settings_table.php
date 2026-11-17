<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * How overtime time is recorded and approved, shown on Settings → Hadir → Overtime. Holds a single row.
     * Overtime pay rates are on Payroll → Overtime (overtime_settings).
     */
    public function up(): void
    {
        Schema::create('attendance_overtime_settings', function (Blueprint $table) {
            $table->id();
            $table->ulid()->unique();

            $table->string('capture_method', 20)->default('pre-approval');
            $table->unsignedSmallInteger('minimum_minutes')->default(30);
            $table->string('rounding', 10)->default('down-15');
            $table->boolean('allow_on_rest_days')->default(true);
            $table->string('approver_role')->default('Admin');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_overtime_settings');
    }
};
