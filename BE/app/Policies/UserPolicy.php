<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Xác định xem người dùng có thể xem danh sách người dùng không.
     *
     * @param  \App\Models\User  $user
     * @return bool
     */
    public function viewAny(User $user)
    {
        return $user->hasPermission('view-users');
    }

    /**
     * Xác định xem người dùng có thể xem thông tin người dùng cụ thể không.
     *
     * @param  \App\Models\User  $currentUser
     * @param  \App\Models\User  $user
     * @return bool
     */
    public function view(User $currentUser, User $user)
    {
        // Người dùng có thể xem thông tin của chính mình hoặc có quyền xem người dùng
        return $currentUser->id === $user->id || 
               $currentUser->hasPermission('view-users');
    }

    /**
     * Xác định xem người dùng có thể tạo người dùng mới không.
     *
     * @param  \App\Models\User  $user
     * @return bool
     */
    public function create(User $user)
    {
        return $user->isAdmin() || $user->hasPermission('create-users');
    }

    /**
     * Xác định xem người dùng có thể cập nhật thông tin người dùng không.
     *
     * @param  \App\Models\User  $currentUser
     * @param  \App\Models\User  $user
     * @return bool
     */
    public function update(User $currentUser, User $user)
    {
        // Người dùng có thể cập nhật thông tin của chính mình hoặc có quyền cập nhật người dùng
        return $currentUser->id === $user->id || 
               $currentUser->isAdmin() || 
               $currentUser->hasPermission('update-users');
    }

    /**
     * Xác định xem người dùng có thể xóa người dùng không.
     *
     * @param  \App\Models\User  $currentUser
     * @param  \App\Models\User  $user
     * @return bool
     */
    public function delete(User $currentUser, User $user)
    {
        // Người dùng không thể tự xóa tài khoản của mình
        // Chỉ admin hoặc người có quyền xóa người dùng mới có thể xóa người dùng khác
        return $currentUser->id !== $user->id && 
               ($currentUser->isAdmin() || $currentUser->hasPermission('delete-users'));
    }

    /**
     * Xác định xem người dùng có thể khôi phục người dùng không.
     *
     * @param  \App\Models\User  $currentUser
     * @param  \App\Models\User  $user
     * @return bool
     */
    public function restore(User $currentUser, User $user)
    {
        return $currentUser->isAdmin() || $currentUser->hasPermission('restore-users');
    }

    /**
     * Xác định xem người dùng có thể xóa vĩnh viễn người dùng không.
     *
     * @param  \App\Models\User  $currentUser
     * @param  \App\Models\User  $user
     * @return bool
     */
    public function forceDelete(User $currentUser, User $user)
    {
        return $currentUser->isAdmin();
    }
} 