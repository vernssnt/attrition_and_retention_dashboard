<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->integer('risk_score')->default(0)->after('tuition_paid');
            $table->string('risk_level')->nullable()->after('risk_score');
            $table->string('financial_status')->nullable()->after('unpaid_percentage');
            $table->string('intervention_recommendation')->nullable()->after('financial_status');
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn([
                'risk_score',
                'risk_level',
                'financial_status',
                'intervention_recommendation'
            ]);
        });
    }
};
