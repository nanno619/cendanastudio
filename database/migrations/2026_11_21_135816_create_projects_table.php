<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->ulid()->unique();
            $table->string('code', 30)->unique();
            $table->string('name', 200);
            $table->foreignId('client_id')->constrained()->restrictOnDelete();
            $table->foreignId('project_manager_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->string('status', 20)->default('planning');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
