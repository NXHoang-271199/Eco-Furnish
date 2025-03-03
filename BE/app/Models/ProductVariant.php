<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
<<<<<<< HEAD

class ProductVariant extends Model
{
    use HasFactory;
=======
use Illuminate\Database\Eloquent\SoftDeletes;
class ProductVariant extends Model
{
    use SoftDeletes;
>>>>>>> 111b2cf7b331a3bd56268381dce795463d612451

    protected $fillable = [
        'product_id',
        'variant_id',
        'variant_value_id',
        'sku',
        'price',
<<<<<<< HEAD
        'status',
    ];
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
=======
        'quantity',
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

>>>>>>> 111b2cf7b331a3bd56268381dce795463d612451
    public function variant()
    {
        return $this->belongsTo(Variant::class)->withTrashed();
    }

    public function variantValue()
    {
        return $this->belongsTo(VariantValue::class)->withTrashed();
    }
}
