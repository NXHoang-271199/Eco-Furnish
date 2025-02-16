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
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('title', 255);
            $table->string('slug');
            $table->text('img_thumbnail')->nullable();
            $table->text('image')->nullable();
            $table->text('content');
            $table->enum('status', ['0', '1'])->default('1');
            $table->timestamp('publish_date')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->timestamps();

            // Tạo khóa ngoại
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
           $table->dropForeign(['user_id']); 
        });

        Schema::dropIfExists('posts');
    }
};
