<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GalleryImage extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'product_id',
        'image_url'
    ];

    /**
     * Get the product that owns the gallery image.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
} 