<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'product_code',
        'name',
        'category_id',
        'image_thumnail',
        'price',
        'discount_price',
        'short_description',
        'description',
    ];

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
    public function variants()
    {
        return $this->hasMany(ProductVariant::class)->withTrashed();
    }
    public function productVariant(){
        return $this->hasMany(ProductVariant::class);
    }
}
