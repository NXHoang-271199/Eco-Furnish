<?php

namespace App\Models;

use App\Models\User;
use App\Models\CategoryPost;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Testing\Fluent\Concerns\Has;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Post extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'content',
        'user_id',
        'category_post_id',
        'image_thumbnail',
        'slug',
        'status'
    ];

    public function scopeSearch($query, $filters)
    {
        if (!empty($filters['title'])) {
            $query->where('title', 'like', '%' . $filters['title'] . '%');
        }
        if (isset($filters['status']) && in_array($filters['status'], ['0', '1'])) {
            $query->where('status', $filters['status']);
        }
        if (!empty($filters['category_post_id'])) {
            $query->where('category_post_id', $filters['category_post_id']);
        }
        if (!empty($filters['year'])) {
            $query->whereYear('created_at', $filters['year']);
        }
        return $query;
    }



    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];
    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function categoryPost()
    {
        return $this->belongsTo(CategoryPost::class);
    }
}
