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
        Schema::create('stock_transfers', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('from_branch_id');
        $table->unsignedBigInteger('to_branch_id');
        $table->unsignedBigInteger('product_id');
        $table->integer('quantity');
        $table->string('status')->default('pending'); // pending, completed
        $table->timestamps();

        $table->foreign('from_branch_id')->references('id')->on('branches')->onDelete('cascade');
        $table->foreign('to_branch_id')->references('id')->on('branches')->onDelete('cascade');
        $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_transfers');
    }
};
