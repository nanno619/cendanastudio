<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The company's bank account and the statutory numbers added with Settings → Company → Bank Account Details.
     */
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->string('bank', 50)->nullable()->after('country');
            $table->string('bank_account_holder', 150)->nullable()->after('bank');
            $table->string('bank_account_no', 30)->nullable()->after('bank_account_holder');
            $table->string('epf_foreign_no', 30)->nullable()->after('epf_no');
            $table->string('hrdf_no', 30)->nullable()->after('zakat_no');
            $table->string('ptptn_no', 30)->nullable()->after('hrdf_no');
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['bank', 'bank_account_holder', 'bank_account_no', 'epf_foreign_no', 'hrdf_no', 'ptptn_no']);
        });
    }
};
