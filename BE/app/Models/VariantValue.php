<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
<<<<<<< HEAD

class VariantValue extends Model
{
    use HasFactory, SoftDeletes;
=======
class VariantValue extends Model
{
    use HasFactory , SoftDeletes;

>>>>>>> 111b2cf7b331a3bd56268381dce795463d612451
    protected $fillable = ['variant_id', 'value'];

    public function variant()
    {
<<<<<<< HEAD
        return $this->belongsTo(Variant::class);
=======
        return $this->belongsTo(Variant::class)->withTrashed();
>>>>>>> 111b2cf7b331a3bd56268381dce795463d612451
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_variants')
        ->withPivot(['variant_id', 'sku', 'price', 'status'])
        ->withTimestamps();
    }
}
