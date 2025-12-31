<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use App\Models\Invoice;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->uuid('uuid')->after('id')->nullable()->unique();
            $table->string('customer_email')->after('customer_id')->nullable();
            $table->string('customer_phone')->after('customer_email')->nullable();
            $table->string('payment_link_token')->after('balance_due')->nullable();
        });
        
        // Generate UUIDs for existing invoices
        $invoices = Invoice::whereNull('uuid')->get();
        foreach ($invoices as $invoice) {
            $invoice->update(['uuid' => (string) Str::uuid()]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['uuid', 'customer_email', 'customer_phone', 'payment_link_token']);
        });
    }
};
