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
        Schema::table('business_signup_requests', function (Blueprint $table) {
            // Change status from ENUM to STRING to support 'pending_payment'
            // We use raw statement or change() if doctrine/dbal is available.
            // Using change() is cleaner but if it fails we might need DB::statement
            $table->string('status')->default('pending')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('business_signup_requests', function (Blueprint $table) {
            // Revert back to enum if rolling back (might be tricky if data exists, just best effort)
            // Note: DBAL might not support converting string back to enum easily without raw SQL
            // For safety we can keep it as string or try to revert
        });
    }
};
