<?php

use App\Models\User;
use App\Models\Voucher;
use App\Models\PaymentMethod;
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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_code',50)->unique();
            $table->foreignIdFor(User::class)->constrained();
            $table->string('user_name', 50);
            $table->string('user_email', 255);
            $table->string('user_phone', 15);
            $table->string('user_address', 255);
            $table->foreignIdFor(PaymentMethod::class)->constrained();
            $table->boolean('payment_status')->default('1');
            $table->enum('order_status', ['Chưa Xác Nhận', 'Đã Xác Nhận', 'Đang Chuẩn Bị Hàng', 'Đang Giao', 'Đã Giao', 'Đã Nhận', 'Hoàn Hàng', 'Hủy Đơn', 'Từ Chối Hoàn Hàng'])->default('Chưa Xác Nhận');
            $table->decimal('total_price', 10, 2);
            $table->foreignIdFor(Voucher::class)->nullable()->constrained();
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
