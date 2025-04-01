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
        return $this->hasMany(ProductVariant::class)
            ->select(['id', 'product_id', 'sku', 'price', 'discount_price', 'variant_details', 'quantity', 'status'])
            ->withTrashed();
    }


    /**
     * Get the gallery images for the product.
     */
    public function gallery()
    {
        return $this->hasMany(GalleryImage::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Get the comments for the product.
     */
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }
    public function reviews() {
        return $this->hasMany(Review::class);
    }
    public function scopeWithReviewStats($query, $sort = 'desc')
    {
        return $query
            ->withCount(['reviews as total_reviews' => function ($query) {
                $query->where('is_hidden', false);
            }])
            ->withAvg(['reviews as average_rating' => function ($query) {
                $query->where('is_hidden', false);
            }], 'rating')
            ->orderBy('average_rating', $sort)
            ->orderBy('total_reviews', $sort);
    }
}
