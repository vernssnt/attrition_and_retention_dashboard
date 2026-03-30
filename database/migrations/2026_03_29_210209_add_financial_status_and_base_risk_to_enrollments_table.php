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
    Schema::table('enrollments', function (Blueprint $table) {
        if (!Schema::hasColumn('enrollments', 'financial_status')) {
            $table->string('financial_status')->nullable()->after('attendance');
        }
        if (!Schema::hasColumn('enrollments', 'base_risk')) {
            $table->integer('base_risk')->nullable()->after('financial_status');
        }
    });
}

public function down(): void
{
    Schema::table('enrollments', function (Blueprint $table) {
        $table->dropColumn(['financial_status', 'base_risk']);
    });
}
};
