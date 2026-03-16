<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {

            if (Schema::hasColumn('students', 'risk_score')) {
                $table->dropColumn('risk_score');
            }

            if (Schema::hasColumn('students', 'risk_level')) {
                $table->dropColumn('risk_level');
            }

            if (Schema::hasColumn('students', 'financial_status')) {
                $table->dropColumn('financial_status');
            }

            if (Schema::hasColumn('students', 'intervention_recommendation')) {
                $table->dropColumn('intervention_recommendation');
            }

        });
    }

    public function down(): void
    {
        // Leave empty (we don't want to restore them)
    }
};
