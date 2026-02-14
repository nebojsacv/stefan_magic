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
        Schema::create('ai_answer_analyses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('answer_id')->constrained('questionnaire_answers')->cascadeOnDelete();
            $table->foreignId('ai_analysis_id')->constrained('ai_analyses')->cascadeOnDelete();
            $table->text('evidence_summary')->nullable();
            $table->enum('compliance_verdict', ['pass', 'fail', 'partial', 'insufficient_evidence'])->nullable();
            $table->json('risk_indicators')->nullable();
            $table->integer('suggested_score')->nullable();
            $table->decimal('confidence', 3, 2)->nullable();
            $table->text('reasoning')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_answer_analyses');
    }
};
