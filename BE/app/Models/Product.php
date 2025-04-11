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
        'quantity',
        'status'
    ];

    /**
     * Các thuộc tính mặc định ẩn khi serialize thành JSON
     */
    protected $hidden = [
        'updated_at',
        'deleted_at',
    ];

    /**
     * Các thuộc tính tính toán cần thêm vào JSON
     */
    protected $appends = [
        'has_variants',
        'price_range',
        'total_quantity'
    ];

    /**
     * Định nghĩa accessor cho thuộc tính has_variants
     */
    public function getHasVariantsAttribute()
    {
        try {
            \Log::info("Kiểm tra has_variants cho sản phẩm ID: " . $this->id);
            
            // Kiểm tra xem có biến thể không bị xóa mềm nào không
            $hasActiveVariants = $this->variants()
                ->whereNull('deleted_at')
                ->exists();
            
            \Log::info("Kết quả kiểm tra has_variants:", [
                'product_id' => $this->id,
                'has_active_variants' => $hasActiveVariants
            ]);
            
            return $hasActiveVariants;
        } catch (\Exception $e) {
            \Log::error("Lỗi khi kiểm tra has_variants: " . $e->getMessage(), [
                'product_id' => $this->id,
                'error' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * Định nghĩa accessor cho thuộc tính price_range
     */
    public function getPriceRangeAttribute()
    {
        if (!$this->getHasVariantsAttribute()) {
            return null;
        }

        $variants = $this->variants()->whereNull('deleted_at')->get();
        
        if ($variants->isEmpty()) {
            return null;
        }

        $minPrice = $variants->min('price');
        $maxPrice = $variants->max('price');
        $minDiscountPrice = $variants->where('discount_price', '>', 0)->min('discount_price');
        $maxDiscountPrice = $variants->where('discount_price', '>', 0)->max('discount_price');

        return [
            'min' => $minPrice,
            'max' => $maxPrice != $minPrice ? $maxPrice : null,
            'min_discount' => $minDiscountPrice ?: null,
            'max_discount' => $maxDiscountPrice != $minDiscountPrice ? $maxDiscountPrice : null
        ];
    }

    /**
     * Định nghĩa accessor cho thuộc tính total_quantity
     */
    public function getTotalQuantityAttribute()
    {
        if ($this->getHasVariantsAttribute()) {
            return $this->variants()->whereNull('deleted_at')->sum('quantity');
        }
        
        return $this->quantity;
    }

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
        return $this->hasMany(GalleryImage::class)->withTrashed();
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
