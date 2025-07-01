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
        Schema::create('branches', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('business_id');
        $table->string('name');
        $table->string('location')->nullable();
        $table->string('contact')->nullable();
        $table->timestamps();

        $table->foreign('business_id')->references('id')->on('businesses')->onDelete('cascade');
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('branches');
    }
};
