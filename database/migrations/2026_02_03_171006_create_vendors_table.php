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
        Schema::create('vendors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('poc_name');
            $table->string('poc_email');
            $table->text('company_info')->nullable();
            $table->string('industry')->nullable();
            $table->enum('current_risk_level', ['high', 'medium', 'low'])->nullable();
            $table->enum('classification_method', ['guided', 'manual'])->nullable();
            $table->enum('classification_status', ['pending', 'pending_approval', 'approved', 'rejected'])->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->date('next_reassessment_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['user_id', 'current_risk_level', 'is_active']);
            $table->fullText(['name', 'poc_name', 'poc_email']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendors');
    }
};
