<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Rules for work away from the office (client and site visits), shown on Settings → Hadir → Visits.
     * Holds a single row.
     */
    public function up(): void
    {
        Schema::create('visit_settings', function (Blueprint $table) {
            $table->id();
            $table->ulid()->unique();

            $table->boolean('visits_enabled')->default(true);
            $table->json('allowed_roles');
            $table->boolean('counts_as_attendance')->default(true);

            $table->boolean('require_client')->default(true);
            $table->boolean('require_purpose')->default(true);
            $table->boolean('require_photo')->default(false);

            $table->boolean('requires_approval')->default(false);
            $table->string('approver_role')->nullable();
            $table->boolean('suggest_mileage_claim')->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visit_settings');
    }
};
