<?php

use App\Models\User;
use App\Models\CategoryPost;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

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
            $table->string('image_thumbnail', 255)->nullable();
            $table->string('slug', 255)->unique();
            $table->enum('status', ['0', '1'])->default(0);
            $table->timestamps();
            $table->softDeletes();

            // Tạo khóa ngoại
            $table->foreignIdFor(User::class)->constrained()->onDelete('cascade');
            $table->foreignIdFor(CategoryPost::class)->constrained()->onDelete('cascade');

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
