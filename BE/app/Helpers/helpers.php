<?php
use App\Models\User;
use Illuminate\Support\Facades\Http;


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
                return 'danger';
            default:
                return 'secondary';
        }
    }
}

if (!function_exists('getWithdrawStatusLabel')) {
    function getWithdrawStatusLabel($status)
    {
        return [
            'dang_xu_ly' => 'Đang xử lý',
            'da_duyet'   => 'Đã duyệt',
            'tu_choi'    => 'Đã từ chối',
            'da_huy'     => 'Đã hủy',
        ][$status] ?? 'Không xác định';
    }
}
if (!function_exists('getWithdrawStatusColor')) {
    function getWithdrawStatusColor($status)
    {
        switch ($status) {
            case 'dang_xu_ly':
                return 'warning';  // Màu vàng cho trạng thái "Đang xử lý"
            case 'da_duyet':
                return 'success';  // Màu xanh cho trạng thái "Đã duyệt"
            case 'tu_choi':
                return 'danger';   // Màu đỏ cho trạng thái "Đã từ chối"
            case 'da_huy':
                return 'danger';   // Màu đỏ cho trạng thái "Đã hủy"
            default:
                return 'dark';     // Màu xám cho trạng thái không xác định
        }
    }
}
function createVietQrCode($bankAccount, $amount, $userId)
{
    $user = User::find($userId);
    // Thông tin cần thiết cho API VietQR
    $data = [
        'accountNo' => $bankAccount->bank_account_number, // Số tài khoản
        'accountName' => $bankAccount->account_holder_name	, // Tên tài khoản
        'acqId' => $bankAccount->acq_id, // Mã ngân hàng (acqId) bạn cần cung cấp
        'addInfo' => 'Eco-Furnish gửi tiền rút cho khách hàng ' . $user->name, // Thông tin bổ sung
        'amount' => $amount, // Số tiền cần thanh toán
        'template' => 'Print', // Kiểu mã QR, ví dụ 'compact'
    ];

    // Gửi request đến API VietQR
    $response = Http::withHeaders([
        'x-client-id' => 'b638047d-ce77-4f11-9f4b-54b69692ff88', // Thay bằng CLIENT_ID của bạn
        'x-api-key' => 'e0bcdf9a-f105-47b2-9d2d-6e0539472810', // Thay bằng API_KEY của bạn
        'Content-Type' => 'application/json',
    ])->post('https://api.vietqr.io/v2/generate', $data);

    // Kiểm tra phản hồi
    if ($response->successful()) {
        $responseData = $response->json();
        $qrCodeUrl = $responseData['data']['qrDataURL']; // Lấy URL mã QR (đã base64 encoded)
        return $qrCodeUrl; // Trả về URL mã QR
    } else {
        // In ra thông tin lỗi chi tiết từ API
        return response()->json([
            'message' => 'Lỗi khi tạo mã QR từ API VietQR',
            'error' => $response->body(), // In ra thông tin lỗi chi tiết
        ], 500);
    }
}
