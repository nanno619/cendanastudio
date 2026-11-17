<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Who approves leave, by the applicant's role (Settings → Leave → Leave Approval).
     * A null applicant_role is the default route for roles without their own; the app keeps it to one.
     */
    public function up(): void
    {
        Schema::create('leave_approval_routes', function (Blueprint $table) {
            $table->id();
            $table->ulid()->unique();
            $table->string('applicant_role', 50)->nullable()->unique();
            $table->json('levels');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_approval_routes');
    }
};
