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
        ];

        return $modelMap[$modelName] ?? $modelName;
    }
} 