<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankAccount extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'bank_code',
        'bank_name',
        'bank_logo_url',
        'account_holder_name',
        'bank_account_number',
        'is_default',
        'acq_id',
    ];
    protected $casts = [
        'is_default' => 'boolean',
    ];

    // Một tài khoản ngân hàng thuộc về 1 user
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function withdrawRequests()
    {
        return $this->hasMany(WithdrawRequest::class);
    }
}
