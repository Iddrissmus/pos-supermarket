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
        Schema::create('stock_receipts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('supplier_id')->nullable();
            $table->string('receipt_number')->unique();
            $table->enum('type', ['central', 'purchase', 'transfer', 'adjustment']);
            $table->datetime('received_at');
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->timestamps();
            
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade');
            $table->foreign('supplier_id')->references('id')->on('suppliers')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            
            $table->index(['branch_id', 'received_at']);
            $table->index(['supplier_id', 'received_at']);
            $table->index(['type', 'received_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_receipts');
    }
};
