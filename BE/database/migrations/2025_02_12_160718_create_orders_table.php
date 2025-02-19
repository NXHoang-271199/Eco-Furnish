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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->string('user_name', 50);
            $table->string('user_email', 255);
            $table->string('user_phone', 15);
            $table->string('user_address', 255);
            $table->integer('order_status_id');
            $table->integer('payment_method_id');
            $table->enum('payment_status', ['Đã thanh toán', 'Chưa thanh toán'])->default('Chưa thanh toán');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
