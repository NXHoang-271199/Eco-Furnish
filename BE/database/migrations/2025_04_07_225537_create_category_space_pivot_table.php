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
        Schema::create('category_space', function (Blueprint $table) {
            $table->unsignedBigInteger('category_id');
            $table->string('space_key'); // Lưu key của không gian như 'living_room', 'bedroom'

            // Foreign key constraint
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');

            // Primary key (composite key)
            $table->primary(['category_id', 'space_key']);

            // Index để tối ưu truy vấn theo không gian
            $table->index('space_key');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('category_space');
    }
};
