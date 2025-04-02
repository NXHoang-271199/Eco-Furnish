<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;
    protected $fillable = ['user_id', 'order_id', 'product_id', 'rating', 'review_text', 'is_hidden', 'images'];
    protected $casts = [
        'images' => 'array',
    ];
    public function user() {
        return $this->belongsTo(User::class);
    }

    public function product() {
        return $this->belongsTo(Product::class);
    }
    public function order() {
        return $this->belongsTo(Order::class);
    }
}
