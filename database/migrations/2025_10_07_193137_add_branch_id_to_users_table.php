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
            $table->foreignId('branch_id')
            ->nullable()
            ->after('role')
            ->constrained()
            ->nullOnDelete();

            // helper column to create uniquw index on branch_id
            $table->string('branch_role_key')->nullable()->after('branch_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->unique(['branch_id', 'role']);
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['branch_role_key']);
            $table->dropColumn('branch_role_key');
            $table->dropForeign(['branch_id']);
            $table->dropColumn('branch_id');
        });
    }
};
