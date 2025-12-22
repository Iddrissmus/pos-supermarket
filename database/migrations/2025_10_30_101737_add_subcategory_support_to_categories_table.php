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
        Schema::table('categories', function (Blueprint $table) {
            $table->unsignedBigInteger('parent_id')->nullable()->after('business_id');
            $table->string('icon')->nullable()->after('name'); //font awesome icon class
            $table->string('color')->nullable()->after('icon'); //tailwind color code
            $table->text('description')->nullable()->after('color');
            $table->boolean('is_active')->default(true)->after('description');
            $table->integer('display_order')->default(0)->after('is_active');

            $table->foreign('parent_id')->references('id')->on('categories')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropColumn(['parent_id', 'icon', 'color', 'description', 'is_active', 'display_order']);
        });
    }
};
