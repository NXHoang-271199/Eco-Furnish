<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Voucher extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'vouchers';
    public $timestamps = true;
    protected $dates = 'delete_at';
    protected $fillable = [
        'code',
        'discount_percentage',
        'max_discount_amount',
        'min_order_value',
        'start_date',
        'end_date',
        'usage_limit',
        'image'
    ];
}
