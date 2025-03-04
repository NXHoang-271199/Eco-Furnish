<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class DashboardPolicy
{
    use HandlesAuthorization;

    /**
     * Xác định xem người dùng có thể xem dashboard không.
     *
     * @param  \App\Models\User  $user
     * @return bool
     */
    public function view(User $user)
    {
        return $user->hasPermission('view-dashboard');
    }

    /**
     * Xác định xem người dùng có thể xem thống kê doanh thu không.
     *
     * @param  \App\Models\User  $user
     * @return bool
     */
    public function viewRevenue(User $user)
    {
        return $user->isAdmin() || $user->hasPermission('view-revenue');
    }

    /**
     * Xác định xem người dùng có thể xem thống kê người dùng không.
     *
     * @param  \App\Models\User  $user
     * @return bool
     */
    public function viewUserStats(User $user)
    {
        return $user->isAdmin() || $user->hasPermission('view-user-stats');
    }

    /**
     * Xác định xem người dùng có thể xem thống kê sản phẩm không.
     *
     * @param  \App\Models\User  $user
     * @return bool
     */
    public function viewProductStats(User $user)
    {
        return $user->isAdmin() || $user->hasPermission('view-product-stats');
    }

    /**
     * Xác định xem người dùng có thể xem thống kê đơn hàng không.
     *
     * @param  \App\Models\User  $user
     * @return bool
     */
    public function viewOrderStats(User $user)
    {
        return $user->isAdmin() || $user->hasPermission('view-order-stats');
    }
} 