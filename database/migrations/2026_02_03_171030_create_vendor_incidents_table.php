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
        Schema::create('vendor_incidents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
            $table->enum('incident_type', ['security_breach', 'service_outage', 'data_loss', 'compliance_violation', 'other']);
            $table->enum('severity', ['critical', 'high', 'medium', 'low']);
            $table->text('description');
            $table->foreignId('reported_by')->constrained('users');
            $table->timestamp('reported_at');
            $table->timestamp('resolved_at')->nullable();
            $table->text('impact_assessment')->nullable();
            $table->boolean('nis2_reportable')->default(false);
            $table->timestamps();
            
            $table->index(['vendor_id', 'severity', 'reported_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendor_incidents');
    }
};
