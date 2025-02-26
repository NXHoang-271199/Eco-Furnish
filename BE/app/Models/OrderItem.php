<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_id',
        'quantity',
        'price',
        'total_price',
        'product_variant_id',
    ];

    // Mối quan hệ với model Product
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // Mối quan hệ với model ProductVariant
    public function productVariant()
    {
        return $this->belongsTo(ProductVariant::class);
    }

    // Mối quan hệ với model Order (nếu cần)
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
