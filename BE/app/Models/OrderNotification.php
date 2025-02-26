<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderNotification extends Model
{
    use HasFactory;
    protected $fillable = [
        'order_id',
        'is_read',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
