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
        Schema::create('branch_products', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('branch_id');
        $table->unsignedBigInteger('product_id');
        $table->integer('stock_quantity')->default(0);
        $table->integer('reorder_level')->default(0);
        $table->decimal('price', 10, 2);
        $table->decimal('cost_price', 10, 2)->nullable();
        $table->timestamps();

        $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade');
        $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
    });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('branch_products');
    }
};
