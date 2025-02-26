<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasFactory;

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
        'avatar',
        'email_verified_at',
        'access_token',
        'refresh_token',
    ];
    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
