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
        Schema::create('questionnaire_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('questionnaire_id')->constrained()->cascadeOnDelete();
            $table->foreignId('question_id')->constrained();
            $table->text('answer_text')->nullable();
            $table->json('selected_options')->nullable();
            $table->json('evidence_files')->nullable(); // [{filename, path, type, size}]
            $table->integer('manual_score')->nullable();
            $table->integer('ai_score')->nullable();
            $table->integer('final_score')->nullable();
            $table->enum('risk_level', ['low', 'medium', 'high'])->nullable();
            $table->timestamps();
            
            $table->index(['questionnaire_id', 'question_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questionnaire_answers');
    }
};
