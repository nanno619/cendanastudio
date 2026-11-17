<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Whether cancelling approved leave goes back to the approvers (Settings → Leave → Leave Approval).
     */
    public function up(): void
    {
        Schema::table('leave_settings', function (Blueprint $table) {
            $table->boolean('cancellation_requires_approval')->default(true)->after('share_leave_calendar');
        });
    }

    public function down(): void
    {
        Schema::table('leave_settings', function (Blueprint $table) {
            $table->dropColumn('cancellation_requires_approval');
        });
    }
};
