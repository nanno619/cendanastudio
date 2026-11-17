<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Up to Employee::MAX_EMERGENCY_CONTACTS people to call for a staff member, in order. `relationship`
     * holds a LookupCategory::Relationship code.
     */
    public function up(): void
    {
        Schema::create('emergency_contacts', function (Blueprint $table) {
            $table->id();
            $table->ulid()->unique();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->string('name', 150);
            $table->string('relationship');
            $table->string('phone', 20);
            $table->unsignedTinyInteger('sort_order');
            $table->timestamps();

            $table->index(['employee_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('emergency_contacts');
    }
};
