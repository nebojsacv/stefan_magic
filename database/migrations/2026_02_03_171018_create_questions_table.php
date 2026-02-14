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
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')->constrained('questionnaire_templates')->cascadeOnDelete();
            $table->text('question_text');
            $table->string('question_category')->nullable();
            $table->enum('type', ['select_bool', 'select_other', 'checkbox', 'radio', 'textarea', 'text']);
            $table->boolean('need_evidence')->default(false);
            $table->string('nis2_requirement_id')->nullable();
            $table->integer('order_index')->default(0);
            $table->decimal('scoring_weight', 3, 2)->default(1.00);
            $table->timestamps();
            
            $table->index(['template_id', 'order_index']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
