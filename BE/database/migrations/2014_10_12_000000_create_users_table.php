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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
<<<<<<< Updated upstream
            $table->integer('age');
            $table->string('email', 255)->unique(); // Email người dùng, đảm bảo không trùng
            $table->string('password', 255); // Mật khẩu người dùng
            $table->string('address', 255)->nullable(); // Địa chỉ người dùng, có thể null
            $table->unsignedBigInteger('role_id'); // ID phân quyền người dùng
            $table->text('avatar')->nullable(); // Ảnh người dùng, có thể null
            $table->timestamps(); // Tạo 2 cột created_at & updated_at tự động
=======
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
>>>>>>> Stashed changes
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
