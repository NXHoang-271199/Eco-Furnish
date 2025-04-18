<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WithdrawRequest extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'wallet_transaction_id',
        'amount',
        'bank_account_id',
        'status',
        'note',
        'updated_by',
        'qr_code'
    ];
    // Mối quan hệ: Mỗi yêu cầu rút tiền thuộc về một người dùng (User)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Mối quan hệ: Mỗi yêu cầu rút tiền có thể liên quan đến một giao dịch ví (WalletTransaction)
    public function walletTransaction()
    {
        return $this->belongsTo(WalletTransaction::class);
    }
    public function bankAccount()
{
    return $this->belongsTo(BankAccount::class);
}

}
