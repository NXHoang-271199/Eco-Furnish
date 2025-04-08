<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class Category extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug'
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($category) {
            if (!$category->slug) {
                $category->slug = Str::slug($category->name);
            }
        });

        static::deleting(function ($category) {
            $category->spaces()->detach();
        });
    }

    /**
     * Get the products for the category.
     */
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    /**
     * The spaces that belong to the category.
     */
    public function spaces()
    {
        return $this->belongsToMany(Category::class, 'category_space', 'category_id', 'space_key');
    }

    public function getSpaceKeysAttribute()
    {
        return DB::table('category_space')
                        ->where('category_id', $this->id)
                        ->pluck('space_key')
                        ->toArray();
    }

    public function syncSpaces(array $spaceKeys)
    {
        DB::table('category_space')->where('category_id', $this->id)->delete();
        if (!empty($spaceKeys)) {
            $dataToInsert = array_map(function ($key) {
                return ['category_id' => $this->id, 'space_key' => $key];
            }, $spaceKeys);
            DB::table('category_space')->insert($dataToInsert);
        }
    }
} 