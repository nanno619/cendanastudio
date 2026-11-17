<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * One leave policy per Data Dictionary leave type, shown on Settings → Leave → Leave Policies.
     */
    public function up(): void
    {
        Schema::create('leave_policies', function (Blueprint $table) {
            $table->id();
            $table->ulid()->unique();
            $table->string('leave_type', 50)->unique();
            $table->boolean('is_paid')->default(true);
            $table->boolean('requires_attachment')->default(false);
            $table->json('entitlements');
            $table->boolean('allow_carry_forward')->default(false);
            $table->decimal('carry_forward_max_days', 4, 1)->nullable();
            $table->unsignedTinyInteger('carry_forward_expiry_months')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_policies');
    }
};
