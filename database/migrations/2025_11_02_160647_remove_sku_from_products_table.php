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
        try {
            Schema::table('products', function (Blueprint $table) {
                $table->dropUnique('products_sku_unique');
            });
        } catch (\Exception $e) {
            // Index might not exist or driver mismatch, ignore
        }

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('sku');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('sku')->nullable()->unique();
        });
    }
};
