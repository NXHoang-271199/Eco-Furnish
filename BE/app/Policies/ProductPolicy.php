<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProductPolicy
{
    use HandlesAuthorization;

    /**
     * Xác định xem người dùng có thể xem danh sách sản phẩm không.
     *
     * @param  \App\Models\User  $user
     * @return bool
     */
    public function viewAny(User $user)
    {
        return $user->hasPermission('view-products');
    }

    /**
     * Xác định xem người dùng có thể xem sản phẩm cụ thể không.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Product  $product
     * @return bool
     */
    public function view(User $user, Product $product)
    {
        return $user->hasPermission('view-products');
    }

    /**
     * Xác định xem người dùng có thể tạo sản phẩm không.
     *
     * @param  \App\Models\User  $user
     * @return bool
     */
    public function create(User $user)
    {
        return $user->hasPermission('create-products');
    }

    /**
     * Xác định xem người dùng có thể cập nhật sản phẩm không.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Product  $product
     * @return bool
     */
    public function update(User $user, Product $product)
    {
        return $user->isAdmin() || $user->hasPermission('update-products');
    }

    /**
     * Xác định xem người dùng có thể xóa sản phẩm không.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Product  $product
     * @return bool
     */
    public function delete(User $user, Product $product)
    {
        return $user->isAdmin() || $user->hasPermission('delete-products');
    }

    /**
     * Xác định xem người dùng có thể khôi phục sản phẩm không.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Product  $product
     * @return bool
     */
    public function restore(User $user, Product $product)
    {
        return $user->isAdmin() || $user->hasPermission('restore-products');
    }

    /**
     * Xác định xem người dùng có thể xóa vĩnh viễn sản phẩm không.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Product  $product
     * @return bool
     */
    public function forceDelete(User $user, Product $product)
    {
        return $user->isAdmin();
    }
} 