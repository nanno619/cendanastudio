<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Who approves claims, per applicant role (Settings → Claim → Approval Workflows). A null role is the
     * default route. Levels are JSON [{type: role|user, value, above: RM amount or null}].
     */
    public function up(): void
    {
        Schema::create('claim_approval_routes', function (Blueprint $table) {
            $table->id();
            $table->ulid()->unique();
            $table->string('applicant_role')->nullable()->unique();
            $table->json('levels');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('claim_approval_routes');
    }
};
