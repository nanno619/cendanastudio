<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Data Dictionary values: one row per item, grouped by an App\Enums\LookupCategory value.
     */
    public function up(): void
    {
        Schema::create('lookup_items', function (Blueprint $table) {
            $table->id();
            $table->string('category', 50);
            $table->string('code', 50);
            $table->string('label_en');
            $table->string('label_ms')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->string('tone', 20)->nullable();
            $table->timestamps();

            $table->unique(['category', 'code']);
            $table->index(['category', 'is_active', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lookup_items');
    }
};
