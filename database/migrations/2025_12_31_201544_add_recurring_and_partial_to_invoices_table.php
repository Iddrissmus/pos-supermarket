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
        Schema::table('invoices', function (Blueprint $table) {
            $table->boolean('allow_partial_payment')->default(false)->after('payment_link_token');
            $table->boolean('is_recurring')->default(false)->after('allow_partial_payment');
            $table->string('recurring_frequency')->nullable()->after('is_recurring')->comment('weekly, monthly, quarterly, yearly');
            $table->date('recurring_end_date')->nullable()->after('recurring_frequency');
            $table->date('recurring_next_date')->nullable()->after('recurring_end_date');
            $table->unsignedBigInteger('parent_invoice_id')->nullable()->after('recurring_next_date');
            
            $table->foreign('parent_invoice_id')->references('id')->on('invoices')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['parent_invoice_id']);
            $table->dropColumn([
                'allow_partial_payment', 
                'is_recurring', 
                'recurring_frequency', 
                'recurring_end_date', 
                'recurring_next_date',
                'parent_invoice_id'
            ]);
        });
    }
};
