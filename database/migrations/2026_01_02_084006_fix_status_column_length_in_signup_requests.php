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
        // Use raw SQL to broaden the column to VARCHAR(255) to ensure it can store 'pending_payment'
        // This is more robust than $table->string()->change() which requires doctrine/dbal
        $driver = Schema::getConnection()->getDriverName();
        
        if ($driver === 'sqlite') {
            // SQLite doesn't support MODIFY COLUMN directly, but usually handles longer strings automatically
            // if we just change the definition. However, if it's a CHECK constraint issue, we might need a workaround.
            // For now, let's try the standard Laravel way for SQLite if possible, otherwise we keep as is
            // as string(255) is usually the default.
            Schema::table('business_signup_requests', function (Blueprint $table) {
                $table->string('status', 255)->default('pending')->change();
            });
        } else {
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE business_signup_requests MODIFY COLUMN status VARCHAR(255) DEFAULT 'pending'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('business_signup_requests', function (Blueprint $table) {
            //
        });
    }
};
