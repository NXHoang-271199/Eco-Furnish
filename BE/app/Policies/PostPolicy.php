<?php

namespace App\Policies;

use App\Models\Post;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PostPolicy
{
    use HandlesAuthorization;

    /**
     * Xác định xem người dùng có thể xem danh sách bài viết không.
     *
     * @param  \App\Models\User  $user
     * @return bool
     */
    public function viewAny(User $user)
    {
        return $user->hasPermission('view-posts');
    }

    /**
     * Xác định xem người dùng có thể xem bài viết cụ thể không.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Post  $post
     * @return bool
     */
    public function view(User $user, Post $post)
    {
        return $user->hasPermission('view-posts');
    }

    /**
     * Xác định xem người dùng có thể tạo bài viết không.
     *
     * @param  \App\Models\User  $user
     * @return bool
     */
    public function create(User $user)
    {
        return $user->hasPermission('create-posts');
    }

    /**
     * Xác định xem người dùng có thể cập nhật bài viết không.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Post  $post
     * @return bool
     */
    public function update(User $user, Post $post)
    {
        // Người dùng có thể cập nhật bài viết nếu họ là admin hoặc là chủ sở hữu bài viết
        return $user->isAdmin() || 
               $user->hasPermission('update-posts') && $user->owns($post);
    }

    /**
     * Xác định xem người dùng có thể xóa bài viết không.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Post  $post
     * @return bool
     */
    public function delete(User $user, Post $post)
    {
        // Người dùng có thể xóa bài viết nếu họ là admin hoặc là chủ sở hữu bài viết
        return $user->isAdmin() || 
               $user->hasPermission('delete-posts') && $user->owns($post);
    }

    /**
     * Xác định xem người dùng có thể khôi phục bài viết không.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Post  $post
     * @return bool
     */
    public function restore(User $user, Post $post)
    {
        return $user->isAdmin() || $user->hasPermission('restore-posts');
    }

    /**
     * Xác định xem người dùng có thể xóa vĩnh viễn bài viết không.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Post  $post
     * @return bool
     */
    public function forceDelete(User $user, Post $post)
    {
        return $user->isAdmin();
    }
} 