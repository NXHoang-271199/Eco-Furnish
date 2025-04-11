<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    use HasFactory;
    protected $fillable = [
        'cart_id',
        'product_id',
        'product_variant_id',
        'quantity',
        'variant_details',
    ];
    
    protected $casts = [
        'variant_details' => 'json',
        'quantity' => 'integer',
    ];
    
    public function setQuantityAttribute($value)
    {
        $this->attributes['quantity'] = (int) $value;
    }
    
    public function getQuantityAttribute($value)
    {
        return (int) $value;
    }
    
    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    public function productVariant()
    {
        return $this->belongsTo(ProductVariant::class);
    }
}
