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
        Schema::create('questionnaires', function (Blueprint $table) {
            $table->id();
            $table->string('unique_id')->unique();
            $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('template_id')->nullable()->constrained('questionnaire_templates');
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['draft', 'sent', 'opened', 'in_progress', 'submitted', 'processing', 'completed', 'failed'])->default('draft');
            $table->boolean('is_opened')->default(false);
            $table->boolean('is_submitted')->default(false);
            $table->integer('questions_completed')->default(0);
            $table->timestamp('submitted_at')->nullable();
            $table->enum('processing_status', ['pending', 'processing', 'completed', 'failed'])->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['vendor_id', 'status', 'created_at']);
            $table->index(['user_id', 'is_submitted']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questionnaires');
    }
};
