<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Role extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'roles';

    protected $fillable = [
        'name',
        'slug',
    ];

    /**
     * Lấy tất cả người dùng thuộc vai trò này
     */
    public function users()
    {
        return $this->hasMany(User::class, 'role_id', 'id');
    }

    /**
     * Lấy tất cả quyền của vai trò này
     */
    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'role_permissions');
    }

    /**
     * Kiểm tra xem vai trò có quyền cụ thể không
     *
     * @param string|array $permission
     * @return bool
     */
    public function hasPermission($permission)
    {
        if (is_array($permission)) {
            return $this->permissions->whereIn('slug', $permission)->count() > 0;
        }
        
        return $this->permissions->where('slug', $permission)->count() > 0;
    }

    /**
     * Gán quyền cho vai trò
     *
     * @param array|Permission $permissions
     * @return $this
     */
    public function givePermissionTo($permissions)
    {
        $permissions = is_array($permissions)
            ? Permission::whereIn('id', $permissions)->get()
            : $permissions;
            
        $this->permissions()->syncWithoutDetaching($permissions);
        
        return $this;
    }

    /**
     * Thu hồi quyền từ vai trò
     *
     * @param array|Permission $permissions
     * @return $this
     */
    public function revokePermissionTo($permissions)
    {
        $permissions = is_array($permissions)
            ? Permission::whereIn('id', $permissions)->get()
            : $permissions;
            
        $this->permissions()->detach($permissions);
        
        return $this;
    }
}
