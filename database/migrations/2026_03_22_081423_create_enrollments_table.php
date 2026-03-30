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
    Schema::create('enrollments', function (Blueprint $table) {
        $table->id();

        // RELATIONSHIPS
        $table->foreignId('student_id')->constrained()->onDelete('cascade');
        $table->foreignId('academic_period_id')->constrained()->onDelete('cascade');

        // YEAR LEVEL (changes every term)
        $table->integer('year_level');

        // INPUT VARIABLES
        $table->decimal('grades', 5, 2)->nullable();
        $table->decimal('attendance', 5, 2)->nullable();
        $table->string('financial_status')->nullable();

        // RISK SYSTEM
        $table->decimal('risk_score', 5, 2)->nullable(); // current
        $table->decimal('cumulative_risk_score', 5, 2)->nullable(); // adjusted
        $table->string('risk_level')->nullable(); // Low / Medium / High

        // OUTCOME
        $table->string('status')->default('Enrolled'); // Enrolled / Dropped

        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enrollments');
    }
};
