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
        Schema::create('user_vouchers', function (Blueprint $table) {
            $table->id(); // Khóa chính
            $table->interger('user_id'); // Khóa ngoại đến bảng users
            $table->interger('voucher_id'); // Khóa ngoại đến bảng vouchers
            $table->boolean('is_used')->default(false); // Trạng thái sử dụng voucher
            $table->timestamp('used_at')->nullable(); // Thời gian sử dụng voucher
            $table->timestamps(); // Tạo 2 cột created_at & updated_at tự động
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_vouchers');
    }
};
