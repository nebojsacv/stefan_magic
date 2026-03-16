<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vendor_classifications', function (Blueprint $table) {
            $table->enum('tier_system', ['high', 'medium', 'low'])->nullable()->after('risk_level');
            $table->enum('tier_manual_override', ['high', 'medium', 'low'])->nullable()->after('tier_system');
            $table->enum('tier_final', ['high', 'medium', 'low'])->nullable()->after('tier_manual_override');
            $table->json('classification_answers')->nullable()->after('questionnaire_answers');
        });

        Schema::create('classification_questions', function (Blueprint $table) {
            $table->id();
            $table->string('key', 10)->unique();
            $table->string('label');
            $table->text('description')->nullable();
            $table->enum('triggers_tier', ['high', 'medium'])->comment('Which tier is triggered when this answer is Yes');
            $table->unsignedTinyInteger('order_index');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('classification_questions');

        Schema::table('vendor_classifications', function (Blueprint $table) {
            $table->dropColumn(['tier_system', 'tier_manual_override', 'tier_final', 'classification_answers']);
        });
    }
};
