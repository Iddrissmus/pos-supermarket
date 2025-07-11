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
        Schema::create('products', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('business_id');
        $table->unsignedBigInteger('category_id')->nullable();
        $table->string('name');
        $table->text('description')->nullable();
        $table->string('sku')->unique();
        $table->string('image')->nullable();
        $table->timestamps();

        $table->foreign('business_id')->references('id')->on('businesses')->onDelete('cascade');
        $table->foreign('category_id')->references('id')->on('categories')->onDelete('set null');
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
