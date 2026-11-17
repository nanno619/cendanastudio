<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Company-wide leave rules, shown on Settings → Leave → General. Holds a single row.
     */
    public function up(): void
    {
        Schema::create('leave_settings', function (Blueprint $table) {
            $table->id();
            $table->ulid()->unique();

            $table->unsignedTinyInteger('leave_year_start_month')->default(1);
            $table->json('working_days');
            $table->unsignedTinyInteger('half_working_day')->nullable();
            $table->string('public_holiday_state', 50)->nullable();

            $table->boolean('allow_half_day')->default(true);
            $table->unsignedTinyInteger('annual_leave_notice_days')->default(3);
            $table->unsignedTinyInteger('backdate_days')->default(7);
            $table->boolean('allow_leave_during_probation')->default(false);

            $table->boolean('allow_negative_balance')->default(false);
            $table->decimal('max_negative_days', 4, 1)->nullable();
            $table->boolean('prorate_entitlement')->default(true);
            $table->string('proration_rounding', 20)->default('nearest-day');

            $table->string('resignation_unused_leave', 20)->default('offset-notice');
            $table->boolean('share_leave_calendar')->default(false);
            $table->string('hr_notification_email')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_settings');
    }
};
