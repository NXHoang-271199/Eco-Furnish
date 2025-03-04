<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;
    protected $fillable = [
        'order_code',
        'user_id',
        'user_name',
        'user_email',
        'user_phone',
        'user_address',
        'payment_method_id',
        'payment_status',
        'order_status',
        'voucher_id',
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class)->withTrashed();
    }

    public function voucher()
    {
        return $this->belongsTo(Voucher::class);
    }
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
}
