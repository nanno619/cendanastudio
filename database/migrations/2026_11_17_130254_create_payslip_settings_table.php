<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * What payslips show and how they reach staff, set on Settings → Payroll → Payslip. Holds a single row.
     */
    public function up(): void
    {
        Schema::create('payslip_settings', function (Blueprint $table) {
            $table->id();
            $table->ulid()->unique();

            $table->boolean('show_company_logo')->default(true);
            $table->boolean('show_employer_numbers')->default(true);
            $table->string('identity_display', 10)->default('masked');
            $table->boolean('mask_bank_account')->default(true);
            $table->boolean('show_employer_contributions')->default(true);
            $table->boolean('show_year_to_date')->default(true);
            $table->boolean('show_leave_balance')->default(false);
            $table->string('language', 5)->default('en');
            $table->text('footer_note')->nullable();

            $table->boolean('email_on_release')->default(true);
            $table->boolean('password_protect')->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payslip_settings');
    }
};
