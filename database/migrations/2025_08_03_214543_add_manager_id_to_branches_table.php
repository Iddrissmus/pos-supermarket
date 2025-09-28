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
        Schema::table('branches', function (Blueprint $table) {
            if (!Schema::hasColumn('branches', 'manager_id')) {
                $table->unsignedBigInteger('manager_id')->nullable()->after('business_id');
                $table->foreign('manager_id')->references('id')->on('users')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            if (Schema::hasColumn('branches', 'manager_id')) {
                // Drop foreign key if exists (SQLite may ignore)
                $sm = Schema::getConnection()->getDoctrineSchemaManager();
                // Use conditional drop to avoid errors in sqlite during tests
                try {
                    $table->dropForeign(['manager_id']);
                } catch (\Throwable $e) {
                    // ignore
                }
                $table->dropColumn('manager_id');
            }
        });
    }
};
