<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Company-wide claim rules, shown on Settings → Claim → General. Holds a single row.
     */
    public function up(): void
    {
        Schema::create('claim_settings', function (Blueprint $table) {
            $table->id();
            $table->ulid()->unique();

            $table->unsignedSmallInteger('submission_deadline_days')->default(30);
            $table->decimal('receipt_required_above', 8, 2)->default(0);
            $table->boolean('allow_resubmission')->default(true);

            $table->boolean('mileage_enabled')->default(false);
            $table->decimal('mileage_rate', 4, 2)->nullable();

            $table->string('payment_method', 20)->default('with-payroll');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('claim_settings');
    }
};
