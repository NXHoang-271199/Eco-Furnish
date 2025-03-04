<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class OrderPolicy
{
    use HandlesAuthorization;

    /**
     * Xác định xem người dùng có thể xem danh sách đơn hàng không.
     *
     * @param  \App\Models\User  $user
     * @return bool
     */
    public function viewAny(User $user)
    {
        return $user->hasPermission('view-orders');
    }

    /**
     * Xác định xem người dùng có thể xem đơn hàng cụ thể không.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Order  $order
     * @return bool
     */
    public function view(User $user, Order $order)
    {
        // Người dùng có thể xem đơn hàng nếu họ là admin, có quyền xem đơn hàng, hoặc là chủ sở hữu đơn hàng
        return $user->isAdmin() || 
               $user->hasPermission('view-orders') || 
               $user->owns($order);
    }

    /**
     * Xác định xem người dùng có thể tạo đơn hàng không.
     *
     * @param  \App\Models\User  $user
     * @return bool
     */
    public function create(User $user)
    {
        return $user->hasPermission('create-orders');
    }

    /**
     * Xác định xem người dùng có thể cập nhật đơn hàng không.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Order  $order
     * @return bool
     */
    public function update(User $user, Order $order)
    {
        // Chỉ admin hoặc nhân viên có quyền cập nhật đơn hàng
        return $user->isAdmin() || $user->hasPermission('update-orders');
    }

    /**
     * Xác định xem người dùng có thể xóa đơn hàng không.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Order  $order
     * @return bool
     */
    public function delete(User $user, Order $order)
    {
        // Chỉ admin mới có thể xóa đơn hàng
        return $user->isAdmin();
    }

    /**
     * Xác định xem người dùng có thể khôi phục đơn hàng không.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Order  $order
     * @return bool
     */
    public function restore(User $user, Order $order)
    {
        return $user->isAdmin();
    }

    /**
     * Xác định xem người dùng có thể xóa vĩnh viễn đơn hàng không.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Order  $order
     * @return bool
     */
    public function forceDelete(User $user, Order $order)
    {
        return $user->isAdmin();
    }
} 