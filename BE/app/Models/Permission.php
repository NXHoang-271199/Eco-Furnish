<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Permission extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'model',
    ];

    /**
     * Lấy các vai trò có quyền này
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_permissions');
    }
} 