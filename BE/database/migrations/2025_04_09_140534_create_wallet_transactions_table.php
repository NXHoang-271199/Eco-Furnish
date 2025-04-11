<?php

use App\Models\Order;
use App\Models\Wallet;
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
        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Wallet::class)->nullable()->constrained()->onDelete('cascade');
            $table->foreignIdFor(Order::class)->nullable()->constrained()->onDelete('set null');
            $table->string('wallet_code')->unique()->nullable();
            $table->decimal('amount', 15, 2);
            $table->enum('type', ['nap_tien', 'hoan_tien', 'thanh_toan_don_hang']);
            $table->foreignIdFor(PaymentMethod::class)->nullable()->constrained()->onDelete('set null');
            $table->text('description')->nullable();
            $table->enum('status', ['cho_thanh_toan', 'thanh_cong', 'that_bai', 'da_huy'])->default('cho_thanh_toan');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->decimal('balance_before', 15, 2)->nullable(); // Số dư trước giao dịch
            $table->decimal('balance_after', 15, 2)->nullable();  // Số dư sau giao dịch
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallet_transactions');
    }
};
