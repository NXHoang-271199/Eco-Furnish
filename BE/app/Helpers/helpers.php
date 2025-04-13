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
                return 'info';
            case 'Đang Giao':
                return 'warning';
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
            case 'Đã Giao':
            case 'Đã Nhận':
                return 'success';
            case 'Đang Chuẩn Bị Hàng':
            case 'Đang Giao':
            case 'Đã Xác Nhận':
                return 'info';
            case 'Hủy Đơn':
                return 'danger';
            case 'Chưa Xác Nhận':
                return 'warning';
            case 'Hoàn Hàng':
                return 'secondary';
            default:
                return 'secondary';
        }
    }
}
if (!function_exists('getTransactionStatusLabel')) {
    function getTransactionStatusLabel($status)
    {
        return [
            'cho_thanh_toan' => 'Chờ thanh toán',
            'thanh_cong'     => 'Thành công',
            'that_bai'       => 'Thất bại',
            'da_huy'         => 'Đã hủy',
        ][$status] ?? 'Không xác định';
    }
}

if (!function_exists('getTransactionStatusColor')) {
    function getTransactionStatusColor($status)
    {
        switch ($status) {
            case 'cho_thanh_toan':
                return 'warning';
            case 'thanh_cong':
                return 'success';
            case 'that_bai':
                return 'danger';
            case 'da_huy':
                return 'danger';
            default:
                return 'dark';
        }
    }
}
if (!function_exists('getTransactionTypeLabel')) {
    function getTransactionTypeLabel($type)
    {
        return [
            'nap_tien'     => 'Nạp Tiền',
            'hoan_tien'    => 'Hoàn Tiền',
            'thanh_toan_don_hang' => 'Thanh Toán Đơn Hàng',
            'rut_tien' => 'Rút Tiền'
        ][$type] ?? 'Không xác định';
    }
}

if (!function_exists('getTransactionTypeColor')) {
    function getTransactionTypeColor($type)
    {
        switch ($type) {
            case 'nap_tien':
                return 'primary';
            case 'hoan_tien':
                return 'danger';
            case 'thanh_toan_don_hang':
                return 'info';
            case 'rut_tien':
                return 'warning';
            default:
                return 'secondary';
        }
    }
}
