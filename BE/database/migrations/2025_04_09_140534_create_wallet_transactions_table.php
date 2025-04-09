<?php

use App\Models\PaymentMethod;
use App\Models\Wallet;
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
        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Wallet::class)->nullable()->constrained()->onDelete('cascade');
            $table->decimal('amount', 15, 2);
            $table->enum('type', ['nap_tien', 'hoan_tien', 'thanh_toan_don_hang']);
            $table->foreignIdFor(PaymentMethod::class)->nullable()->constrained()->onDelete('set null');
            $table->string('transaction_ref')->nullable();
            $table->text('description')->nullable();
            $table->enum('status', ['cho_thanh_toan', 'thanh_cong', 'that_bai', 'da_huy'])->default('cho_thanh_toan');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
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
