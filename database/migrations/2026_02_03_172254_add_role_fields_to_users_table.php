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
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['super', 'tester', 'approver'])->default('tester')->after('email');
            $table->string('company_name')->nullable()->after('name');
            $table->text('address')->nullable()->after('company_name');
            $table->foreignId('package_id')->nullable()->constrained()->after('address');
            $table->integer('assessments_allowed')->default(0)->after('package_id');
            $table->enum('status', ['active', 'inactive', 'trial'])->default('trial')->after('assessments_allowed');
            $table->json('options')->nullable()->after('status');
            $table->string('timezone')->default('UTC')->after('options');
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'role',
                'company_name',
                'address',
                'package_id',
                'assessments_allowed',
                'status',
                'options',
                'timezone',
            ]);
            $table->dropSoftDeletes();
        });
    }
};
