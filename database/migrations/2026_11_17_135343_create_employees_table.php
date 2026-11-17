<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Staff records. Every staff member has one sign-in account (user_id); their name and work email live
     * on the user. Lookup columns hold Data Dictionary codes. Rows are never deleted: leaving is recorded
     * with last_working_date.
     */
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->ulid()->unique();
            $table->foreignId('user_id')->unique()->constrained()->restrictOnDelete();

            $table->string('staff_no', 20)->unique();
            $table->string('identity_type', 10);
            $table->string('identity_no', 20)->unique();
            $table->char('nationality', 2)->default('MY');
            $table->date('date_of_birth');
            $table->string('gender')->nullable();
            $table->string('race')->nullable();
            $table->string('marital_status')->nullable();
            $table->unsignedTinyInteger('children_count')->default(0);
            $table->boolean('spouse_working')->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('personal_email')->nullable();
            $table->string('address_line_1')->nullable();
            $table->string('address_line_2')->nullable();
            $table->string('postcode', 5)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('state')->nullable();

            $table->string('employment_type', 20);
            $table->string('department')->nullable();
            $table->string('position')->nullable();
            $table->foreignId('reporting_manager_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->date('join_date');
            $table->date('probation_end_date')->nullable();
            $table->boolean('is_manual_worker')->default(false);
            $table->date('last_working_date')->nullable();
            $table->string('leaving_reason', 20)->nullable();

            $table->decimal('basic_salary', 10, 2);
            $table->string('bank')->nullable();
            $table->string('bank_account_no', 20)->nullable();

            $table->string('epf_no', 20)->nullable();
            $table->string('socso_no', 20)->nullable();
            $table->string('income_tax_no', 20)->nullable();
            $table->decimal('epf_elected_rate', 5, 2)->nullable();

            $table->timestamps();

            $table->index('last_working_date');
            $table->index('department');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
