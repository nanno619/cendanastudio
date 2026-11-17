<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Company-wide payroll rules, shown on Settings → Payroll → General. Holds a single row.
     */
    public function up(): void
    {
        Schema::create('payroll_settings', function (Blueprint $table) {
            $table->id();
            $table->ulid()->unique();

            $table->string('pay_day_rule', 20)->default('fixed-day');
            $table->unsignedTinyInteger('pay_day')->nullable();
            $table->string('pay_day_adjustment', 25)->default('previous-working-day');
            $table->unsignedTinyInteger('cutoff_day')->default(20);

            $table->boolean('socso_enabled')->default(true);
            $table->boolean('eis_enabled')->default(true);
            $table->boolean('pcb_enabled')->default(true);
            $table->string('pcb_method', 20)->default('computerised');
            $table->string('hrdf_levy', 20)->default('none');
            $table->boolean('zakat_via_payroll')->default(true);

            $table->string('payment_method', 20)->default('bank-transfer');
            $table->string('net_pay_rounding', 20)->default('sen');

            $table->boolean('requires_approval')->default(true);
            $table->string('approver_role', 50)->nullable();
            $table->boolean('lock_after_release')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_settings');
    }
};
