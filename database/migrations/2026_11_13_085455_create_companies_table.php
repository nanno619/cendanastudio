<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The app's own company, shown on Settings → Company → Basic Details. Holds a single row.
     */
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->ulid()->unique();

            $table->string('name', 150);
            $table->string('registration_no', 20)->nullable();
            $table->string('old_registration_no', 20)->nullable();

            $table->string('email')->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('website')->nullable();

            $table->string('address_line_1')->nullable();
            $table->string('address_line_2')->nullable();
            $table->string('postcode', 10)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('state', 50)->nullable();
            $table->char('country', 2)->default('MY');

            $table->string('epf_no', 30)->nullable();
            $table->string('socso_no', 30)->nullable();
            $table->string('eis_no', 30)->nullable();
            $table->string('income_tax_no', 30)->nullable();
            $table->string('zakat_no', 30)->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
