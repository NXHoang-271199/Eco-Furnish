<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class VariantValue extends Model
{
    use HasFactory , SoftDeletes;

    protected $fillable = ['variant_id', 'value'];

    public function variant()
    {
        return $this->belongsTo(Variant::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_variants')
        ->withPivot(['variant_id', 'sku', 'price', 'status'])
        ->withTimestamps();
    }
}
