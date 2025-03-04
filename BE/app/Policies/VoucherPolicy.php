<?php

namespace App\Policies;

use App\Models\Voucher;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class VoucherPolicy
{
    use HandlesAuthorization;

    /**
     * Xác định xem người dùng có thể xem danh sách mã giảm giá không.
     *
     * @param  \App\Models\User  $user
     * @return bool
     */
    public function viewAny(User $user)
    {
        return $user->hasPermission('view-vouchers');
    }

    /**
     * Xác định xem người dùng có thể xem mã giảm giá cụ thể không.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Voucher  $voucher
     * @return bool
     */
    public function view(User $user, Voucher $voucher)
    {
        return $user->hasPermission('view-vouchers');
    }

    /**
     * Xác định xem người dùng có thể tạo mã giảm giá không.
     *
     * @param  \App\Models\User  $user
     * @return bool
     */
    public function create(User $user)
    {
        return $user->isAdmin() || $user->hasPermission('create-vouchers');
    }

    /**
     * Xác định xem người dùng có thể cập nhật mã giảm giá không.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Voucher  $voucher
     * @return bool
     */
    public function update(User $user, Voucher $voucher)
    {
        return $user->isAdmin() || $user->hasPermission('update-vouchers');
    }

    /**
     * Xác định xem người dùng có thể xóa mã giảm giá không.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Voucher  $voucher
     * @return bool
     */
    public function delete(User $user, Voucher $voucher)
    {
        return $user->isAdmin() || $user->hasPermission('delete-vouchers');
    }

    /**
     * Xác định xem người dùng có thể khôi phục mã giảm giá không.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Voucher  $voucher
     * @return bool
     */
    public function restore(User $user, Voucher $voucher)
    {
        return $user->isAdmin();
    }

    /**
     * Xác định xem người dùng có thể xóa vĩnh viễn mã giảm giá không.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Voucher  $voucher
     * @return bool
     */
    public function forceDelete(User $user, Voucher $voucher)
    {
        return $user->isAdmin();
    }
} 