<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * One row per App\Enums\ModuleKey case: display name, icon and sidebar order, whether the module is on,
     * and which roles can use it (empty = everyone). Managed on Administrations → Modules.
     */
    public function up(): void
    {
        Schema::create('modules', function (Blueprint $table) {
            $table->id();
            $table->ulid()->unique();
            $table->string('key', 30)->unique();
            $table->string('name_en', 40);
            $table->string('name_ms', 40)->nullable();
            $table->string('icon', 30);
            $table->unsignedTinyInteger('sort_order');
            $table->boolean('is_enabled')->default(true);
            $table->json('allowed_roles');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('modules');
    }
};
