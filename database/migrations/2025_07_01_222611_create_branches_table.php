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

        $table->foreign('manager_id')->nullable()->constrained('users')->onDelete('set null');
        $table->foreign('business_id')->references('id')->on('businesses')->onDelete('cascade');
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('branches');

        Schema::table('branches', function (Blueprint $table) {
            $table->dropForeign(['manager_id']);
            $table->dropColumn('manager_id');
        });
        
    }
};
