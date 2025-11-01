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
        Schema::table('products', function (Blueprint $table) {
            // Track which supplier this product was originally sourced from
            $table->foreignId('primary_supplier_id')->nullable()->after('business_id')->constrained('suppliers')->onDelete('set null');
            $table->boolean('is_local_supplier_product')->default(false)->after('primary_supplier_id')->comment('True if product was added by manager from local supplier');
            $table->foreignId('added_by')->nullable()->after('is_local_supplier_product')->constrained('users')->onDelete('set null')->comment('User who added this product');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['primary_supplier_id']);
            $table->dropForeign(['added_by']);
            $table->dropColumn(['primary_supplier_id', 'is_local_supplier_product', 'added_by']);
        });
    }
};
