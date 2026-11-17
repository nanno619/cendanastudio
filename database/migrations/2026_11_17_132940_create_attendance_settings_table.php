<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Company-wide attendance (Hadir) rules, shown on Settings → Hadir → General. Holds a single row.
     */
    public function up(): void
    {
        Schema::create('attendance_settings', function (Blueprint $table) {
            $table->id();
            $table->ulid()->unique();

            $table->string('work_start_time', 5)->default('09:00');
            $table->string('work_end_time', 5)->default('18:00');
            $table->unsignedSmallInteger('break_minutes')->default(60);
            $table->unsignedSmallInteger('late_grace_minutes')->default(10);
            $table->string('missed_clock_out', 20)->default('flag-for-review');

            $table->boolean('record_location')->default(true);
            $table->boolean('geofence_enabled')->default(false);
            $table->decimal('office_latitude', 9, 6)->nullable();
            $table->decimal('office_longitude', 9, 6)->nullable();
            $table->unsignedSmallInteger('geofence_radius_m')->nullable();
            $table->boolean('require_selfie')->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_settings');
    }
};
