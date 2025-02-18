<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CategoryPost extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'category_posts';

    protected $fillable = [
        'title',
    ];

    protected $dates = ['deleted_at'];
    
    public function posts()
    {
        return $this->hasMany(Post::class, 'category_post_id');
    }
}
