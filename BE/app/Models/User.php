<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Post;
use App\Models\Role;
use App\Models\Comment;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'age',
        'email',
        'password',
        'address',
        'role_id',
        'is_active',
        'avatar',
        'email_verified_at',
        'access_token',
        'refresh_token'
    ];

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function scopeSearch($query, $fillers)
    {
        if (!empty($fillers['name'])) {
            $query->where('name', 'like', '%' . $fillers['name'] . '%');
        }

        if (!empty($fillers['email'])) {
            $query->where('email', 'like', '%' . $fillers['email'] . '%');
        }

        return $query;
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'access_token',
        'refresh_token'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function getDisplayNameAttribute()
    {
        if(is_string($this->name) && is_array(json_decode($this->name, true))) {
            $userData = json_decode($this->name, true);
            return $userData['name'] ?? 'Admin';
        }
        return $this->name ?? 'Admin';
    }

    private function isJson($string) {
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }
    
    public function orders()
    {
        return $this->hasMany(Order::class);
    }
    
    /**
     * Get the comments for the user.
     */
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
}
