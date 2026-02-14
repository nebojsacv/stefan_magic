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
        Schema::create('vendor_reassessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
            $table->date('scheduled_date');
            $table->enum('reason', ['scheduled', 'incident', 'contract_change', 'risk_change', 'manual'])->default('scheduled');
            $table->enum('status', ['pending', 'in_progress', 'completed', 'overdue'])->default('pending');
            $table->timestamp('completed_at')->nullable();
            $table->enum('previous_risk_level', ['high', 'medium', 'low'])->nullable();
            $table->enum('new_risk_level', ['high', 'medium', 'low'])->nullable();
            $table->timestamps();
            
            $table->index(['vendor_id', 'scheduled_date', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendor_reassessments');
    }
};
