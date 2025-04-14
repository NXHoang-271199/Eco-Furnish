<?php

use App\Models\BankAccount;
use App\Models\User;
use App\Models\WalletTransaction;
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
        Schema::create('withdraw_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class)->constrained()->onDelete('cascade');
            $table->foreignIdFor(WalletTransaction::class)->nullable()->constrained()->onDelete('set null');
            $table->decimal('amount', 15, 2);
            $table->foreignIdFor(BankAccount::class)->constrained()->onDelete('cascade');
            $table->enum('status', ['dang_xu_ly', 'da_duyet', 'tu_choi', 'da_huy'])->default('dang_xu_ly');
            $table->longText('qr_code')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('withdraw_requests');
    }
};
