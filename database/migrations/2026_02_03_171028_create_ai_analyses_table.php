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
        Schema::create('ai_analyses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('questionnaire_id')->constrained()->cascadeOnDelete();
            $table->string('model_used');
            $table->integer('total_risk_score');
            $table->enum('risk_level', ['low', 'medium', 'high']);
            $table->decimal('confidence_score', 3, 2);
            $table->text('analysis_summary');
            $table->json('key_findings');
            $table->timestamp('processed_at');
            $table->integer('processing_time_ms');
            $table->integer('tokens_used')->nullable();
            $table->decimal('cost_usd', 8, 4)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_analyses');
    }
};
