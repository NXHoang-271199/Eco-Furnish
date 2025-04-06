<?php

use Illuminate\Support\Facades\Log;

if (!function_exists('getStatusBadgeColor')) {
    function getStatusBadgeColor($status)
    {
        switch ($status) {
            case 'Chưa Xác Nhận':
                return 'warning';
            case 'Đã Xác Nhận':
                return 'info';
            case 'Đang Chuẩn Bị Hàng':
                return 'primary';
            case 'Đang Giao':
                return 'indigo'; // Assuming you have an 'indigo' color defined in your CSS/framework
            case 'Đã Giao':
            case 'Đã Nhận':
                return 'success';
            case 'Hoàn Hàng':
            case 'Hủy Đơn':
                return 'danger';
            default:
                return 'secondary';
        }
    }
}

if (!function_exists('getOrderStatusBadge')) {
    function getOrderStatusBadge($status)
    {
        $icons = [
            'Chưa Xác Nhận' => '<i class="far fa-clock"></i>',
            'Đã Xác Nhận' => '<i class="fas fa-check-circle"></i>',
            'Đang Chuẩn Bị Hàng' => '<i class="fas fa-box"></i>',
            'Đang Giao' => '<i class="fas fa-truck"></i>',
            'Đã Giao' => '<i class="fas fa-check-double"></i>',
            'Đã Nhận' => '<i class="fas fa-handshake"></i>',
            'Hoàn Hàng' => '<i class="fas fa-undo"></i>',
            'Hủy Đơn' => '<i class="fas fa-ban"></i>',
        ];

        $color = getStatusBadgeColor($status);
        $icon = $icons[$status] ?? '<i class="fas fa-question-circle"></i>';

        return '<span class="badge bg-' . $color . ' py-2 px-3">' . $icon . ' ' . $status . '</span>';
    }
}

if (!function_exists('getOrderStatusColor')) {
    /**
     * Trả về màu sắc dựa trên trạng thái đơn hàng
     *
     * @param string $status
     * @return string
     */
    function getOrderStatusColor($status)
    {
        switch ($status) {
            case 'Đã giao hàng':
            case 'Đã nhận hàng':
            case 'Hoàn thành':
                return 'success';
            case 'Đang xử lý':
            case 'Đang giao hàng':
            case 'Chờ xác nhận':
                return 'info';
            case 'Đã hủy':
                return 'danger';
            case 'Chờ thanh toán':
            case 'Đang xác nhận':
                return 'warning';
            default:
                return 'secondary';
        }
    }
}
