<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductVariant extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'product_id',
        'variant_details',
        'sku',
        'price',
        'discount_price',
        'quantity',
        'status'
    ];
    protected $casts = [
        'price' => 'float',
        'discount_price' => 'float',
        'status' => 'integer',
        'variant_details' => 'json',
    ];
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function variant()
    {
        return $this->belongsTo(Variant::class)->withTrashed();
    }

    public function variantValue()
    {
        return $this->belongsTo(VariantValue::class)->withTrashed();
    }
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
    public function getVariantDetailsAttribute()
    {
        $details = json_decode($this->attributes['variant_details'], true);
        if (!$details) return null;

        $formattedDetails = [];
        foreach ($details as $variantId => $variantValueId) {
            $variant = Variant::find($variantId);
            $variantValue = VariantValue::find($variantValueId);

            if ($variant && $variantValue) {
                $formattedDetails[] = [
                    'name' => $variant->name,
                    'value' => $variantValue->value
                ];
            }
        }

        return $formattedDetails;
    }
}
