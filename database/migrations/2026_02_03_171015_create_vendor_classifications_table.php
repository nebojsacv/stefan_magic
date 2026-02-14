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
        Schema::create('vendor_classifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
            $table->enum('risk_level', ['high', 'medium', 'low']);
            $table->enum('classification_method', ['guided', 'manual']);
            $table->integer('criticality_score')->nullable();
            $table->enum('data_access_level', ['none', 'public', 'internal', 'confidential', 'restricted'])->nullable();
            $table->enum('dependency_level', ['none', 'low', 'medium', 'high', 'critical'])->nullable();
            $table->json('questionnaire_answers')->nullable();
            $table->foreignId('classified_by')->constrained('users');
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['vendor_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendor_classifications');
    }
};
