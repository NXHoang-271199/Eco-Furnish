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
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->dateTime('start_date');
            $table->dateTime('expired_date');
            $table->text('description')->nullable();
            $table->enum('discount_type', ['fixed', 'percent']);
            $table->decimal('discount_value', 10, 2);
            $table->integer('usage_limit')->nullable();
            $table->decimal('min_order_value', 10, 2)->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vouchers');
    }
};
