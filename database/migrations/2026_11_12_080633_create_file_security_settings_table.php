<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The upload policy set on Administrations → File Security. Holds a single row.
     */
    public function up(): void
    {
        Schema::create('file_security_settings', function (Blueprint $table) {
            $table->id();
            $table->json('allowed_types');
            $table->unsignedSmallInteger('max_file_size_mb');
            $table->boolean('verify_content')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('file_security_settings');
    }
};
