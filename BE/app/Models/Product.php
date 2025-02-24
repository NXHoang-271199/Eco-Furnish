<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'product_code',
        'category_id',
        'image_thumnail',
        'short_description',
        'description',
        'price',
        'discount_price',
        'status'
    ];

    /**
     * Get the category that owns the product.
     */
    public function category()
    {
        return $this->belongsTo(Category::class)->withTrashed();
    }

    /**
     * Get the category name even if it's deleted
     */
    public function getCategoryNameAttribute()
    {
        return $this->category ? $this->category->name : 'N/A';
    }

    /**
     * Get the variants for the product.
     */
    public function variants()
    {
        return $this->hasMany(ProductVariant::class)->withTrashed();
    }

    /**
     * Get the gallery images for the product.
     */
    public function gallery()
    {
        return $this->hasMany(GalleryImage::class);
    }

}
