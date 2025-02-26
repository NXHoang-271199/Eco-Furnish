<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    use HasFactory;
    protected $fillable = [
        'code',
        'discount_percentage',
        'max_discount_amount',
        'min_order_value',
        'start_date',
        'end_date',
        'is_active',
        'usage_limit',
        'description',
    ];
    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
