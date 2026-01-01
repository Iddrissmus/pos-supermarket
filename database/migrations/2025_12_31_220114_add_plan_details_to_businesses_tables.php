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
            $table->string('plan_type')->nullable()->after('status');
            $table->decimal('amount_paid', 10, 2)->default(0)->after('plan_type');
            $table->string('transaction_reference')->nullable()->after('amount_paid');
        });

        Schema::table('businesses', function (Blueprint $table) {
            $table->string('plan_type')->nullable()->after('status');
            $table->string('subscription_status')->default('active')->after('plan_type'); // active, inactive, expired
            $table->timestamp('subscription_expires_at')->nullable()->after('subscription_status');
            $table->integer('max_branches')->default(1)->after('subscription_expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('business_signup_requests', function (Blueprint $table) {
            $table->dropColumn(['plan_type', 'amount_paid', 'transaction_reference']);
        });

        Schema::table('businesses', function (Blueprint $table) {
            $table->dropColumn(['plan_type', 'subscription_status', 'subscription_expires_at', 'max_branches']);
        });
    }
};
