<?php

use App\Models\User;
use App\Models\Order;
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
        Schema::create('refund_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Order::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(User::class)->constrained()->cascadeOnDelete();
            $table->text('reason')->nullable(); // Lý do hoàn hàng
            $table->enum('status', ['Chờ Duyệt', 'Đã Duyệt', 'Từ Chối'])->default('Chờ Duyệt');// Trạng thái: Chờ Duyệt, Đã Duyệt, Từ Chối
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('refund_requests');
    }
};
