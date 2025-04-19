<?php

namespace App\Helpers;

class ModelHelper
{
    /**
     * Chuyển đổi tên model thành tên thân thiện hơn
     *
     * @param string|null $modelName
     * @return string
     */
    public static function getFriendlyModelName(?string $modelName): string
    {
        if (empty($modelName)) {
            return 'Chung';
        }

        $modelMap = [
            'App\Models\User' => 'Quản lý người dùng',
            'App\Models\Post' => 'Quản lý bài viết',
            'App\Models\Product' => 'Quản lý sản phẩm',
            'App\Models\Voucher' => 'Quản lý mã giảm giá',
            'App\Models\Order' => 'Quản lý đơn hàng',
            'App\Models\Category' => 'Quản lý danh mục',
            'App\Models\Comment' => 'Quản lý bình luận',
            'App\Models\Role' => 'Quản lý vai trò',
            'App\Models\Permission' => 'Quản lý quyền',
            'App\Models\Variant' => 'Quản lý biến thể',
            'App\Models\VariantValue' => 'Quản lý giá trị biến thể',
            'App\Models\PaymentMethod' => 'Quản lý phương thức thanh toán',
            'App\Models\OrderStatus' => 'Quản lý trạng thái đơn hàng',
            'App\Models\OrderDetail' => 'Quản lý chi tiết đơn hàng',
            'App\Models\OrderNotification' => 'Quản lý thông báo đơn hàng',
            'App\Models\CategoryPost' => 'Quản lý danh mục bài viết',
            'App\Models\Review' => 'Quản lý đánh giá sản phẩm',
            'App\Models\Wallet' => 'Quản lý ví',
            'App\Models\WalletTransaction' => 'Quản lý giao dịch ví',
            'App\Models\WithdrawRequest' => 'Quản lý yêu cầu rút tiền',
            'App\Models\Message' => 'Quản lý tin nhắn',
            'App\Models\Banner' => 'Quản lý banner',
        ];

        return $modelMap[$modelName] ?? $modelName;
    }
} 