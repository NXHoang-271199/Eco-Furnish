<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'variant_id',
        'variant_value_id',
        'sku',
        'price',
        'status',
    ];
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
    public function variant()
    {
        return $this->belongsTo(Variant::class)->withTrashed();
    }

    public function variantValue()
    {
        return $this->belongsTo(VariantValue::class)->withTrashed();
    }
}
