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
        'variant_id',
        'variant_value_id',
        'sku',
        'price',
        'status'
    ];
    protected $casts = [
       'price' => 'float',
       'status' => 'integer',
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
}
