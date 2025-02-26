<?php

use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
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
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
 	        $table->foreignIdFor(Order::class)->constrained();
            $table->foreignIdFor(Product::class)->constrained();
            $table->string('product_name', 255);
            $table->string('image_url',255);
            $table->integer('quantity');
            $table->decimal('price', 15, 2);
            $table->decimal('total_price', 15, 2);
            $table->foreignIdFor(ProductVariant::class)->constrained()->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
