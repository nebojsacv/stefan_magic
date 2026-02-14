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
        Schema::create('vendor_dependencies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('depends_on_vendor_id')->constrained('vendors')->cascadeOnDelete();
            $table->enum('dependency_type', ['service', 'data', 'infrastructure', 'compliance']);
            $table->enum('criticality', ['critical', 'high', 'medium', 'low']);
            $table->text('description')->nullable();
            $table->timestamps();
            
            $table->index(['vendor_id', 'criticality']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendor_dependencies');
    }
};
