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
        Schema::table('businesses', function (Blueprint $table) {
            if (!Schema::hasColumn('businesses', 'business_type_id')) {
                $table->foreignId('business_type_id')->nullable()->constrained('business_types')->onDelete('set null');
            }
            if (!Schema::hasColumn('businesses', 'current_plan_id')) {
                $table->foreignId('current_plan_id')->nullable()->constrained('subscription_plans')->onDelete('set null');
            }
            if (!Schema::hasColumn('businesses', 'subscription_expires_at')) {
                $table->timestamp('subscription_expires_at')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            // Only drop if they exist (though down usually assumes up ran)
            $table->dropForeign(['business_type_id']);
            $table->dropForeign(['current_plan_id']);
            $table->dropColumn(['business_type_id', 'current_plan_id', 'subscription_expires_at']);
        });
    }
};
