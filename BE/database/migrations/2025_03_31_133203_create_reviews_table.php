<?php

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
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
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class)->constrained()->onDelete('cascade'); // Khách hàng
            $table->foreignIdFor(Order::class)->nullable()->constrained()->onDelete('cascade'); // Đơn hàng
            $table->foreignIdFor(Product::class)->constrained()->onDelete('cascade'); // Sản phẩm
            $table->integer('rating')->default(5); // Số sao (1-5)
            $table->json('images')->nullable();
            $table->boolean('is_hidden')->default(false);
            $table->text('review_text')->nullable(); // Nội dung đánh giá
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
