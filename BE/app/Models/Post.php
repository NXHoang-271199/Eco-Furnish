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

    public function scopeSearch($query, $fillers)
    {
        if (!empty($fillers['title'])) {
            $query->where('title', 'like', '%' . $fillers['title'] . '%');
        }
        return $query;
    }

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function categoryPost()
    {
        return $this->belongsTo(CategoryPost::class);
    }

}
