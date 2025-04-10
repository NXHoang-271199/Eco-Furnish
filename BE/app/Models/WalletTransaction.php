<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WalletTransaction extends Model
{
    use HasFactory;
    protected $fillable = [
        'wallet_id',
        'amount',
        'type',
        'payment_method_id',
        'transaction_ref',
        'description',
        'status',
        'created_by',
    ];

    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }
    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class)->withTrashed();
    }
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
