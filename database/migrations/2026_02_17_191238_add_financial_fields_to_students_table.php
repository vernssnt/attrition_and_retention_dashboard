<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->decimal('tuition_total', 10, 2)->nullable()->after('enrollment_year');
            $table->decimal('tuition_paid', 10, 2)->nullable()->after('tuition_total');
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn(['tuition_total', 'tuition_paid']);
        });
    }
};
