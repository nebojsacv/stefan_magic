<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->enum('evidence_required_when', ['always', 'if_yes', 'optional'])
                ->nullable()
                ->after('need_evidence')
                ->comment('Controls when the evidence file upload is shown and required');
        });

        // Migrate existing need_evidence=true rows to 'optional' (shown but not required)
        DB::table('questions')
            ->where('need_evidence', true)
            ->update(['evidence_required_when' => 'optional']);
    }

    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->dropColumn('evidence_required_when');
        });
    }
};
