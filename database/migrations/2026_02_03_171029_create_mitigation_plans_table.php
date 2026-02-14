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
        Schema::create('mitigation_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ai_analysis_id')->constrained()->cascadeOnDelete();
            $table->string('risk_category');
            $table->enum('severity', ['critical', 'high', 'medium', 'low']);
            $table->text('recommendation');
            $table->json('action_items');
            $table->integer('priority')->default(1);
            $table->string('nis2_reference')->nullable();
            $table->enum('status', ['open', 'in_progress', 'completed', 'dismissed'])->default('open');
            $table->foreignId('assigned_to')->nullable()->constrained('users');
            $table->date('due_date')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            
            $table->index(['ai_analysis_id', 'status', 'severity']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mitigation_plans');
    }
};
