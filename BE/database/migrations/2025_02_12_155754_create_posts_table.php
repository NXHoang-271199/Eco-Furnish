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
            $table->text('content');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('category_post_id');
            $table->string('image_thumbnail', 255)->nullable();
            $table->string('slug', 255)->unique();
            $table->enum('status', ['0', '1'])->default(0);
            $table->timestamps();
            $table->softDeletes();

            // Tạo khóa ngoại
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('category_post_id')->references('id')->on('category_posts')->onDelete('cascade');

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

        Schema::table('posts', function (Blueprint $table) {
            $table->dropForeign(['category_post_id']);
        });

        Schema::dropIfExists('posts');
    }
};
