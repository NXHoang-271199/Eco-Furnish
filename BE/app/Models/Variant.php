<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Variant extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = ['name'];

    public function values()
    {
        return $this->hasMany(VariantValue::class);
    }

    public function valuesWithTrashed()
    {
        return $this->hasMany(VariantValue::class)->withTrashed();
    }

    public function productVariants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_variants')
        ->withPivot(['variant_value_id', 'sku', 'price', 'status'])
        ->withTimestamps();
    }
}
