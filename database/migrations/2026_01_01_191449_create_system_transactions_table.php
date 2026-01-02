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
        Schema::create('system_transactions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            
            // Financials
            $table->decimal('amount', 12, 2);
            $table->string('currency', 3)->default('GHS');
            
            // Transaction Details
            $table->string('reference')->nullable()->index(); // Payment Gateway Ref
            $table->string('channel')->default('cash'); // cash, card, mobile_money
            
            // Source (Polymorphic: Sale, Invoice)
            $table->nullableMorphs('source');
            
            // Statuses
            $table->string('status')->default('success'); // success, failed
            $table->enum('payout_status', ['pending', 'paid', 'collected_by_business'])->default('collected_by_business');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_transactions');
    }
};
