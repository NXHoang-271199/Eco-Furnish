import React from 'react';
import { toast } from 'react-hot-toast';
import { Bell, X, CreditCard, AlertTriangle } from 'lucide-react';
import { useNavigate } from 'react-router-dom';

/**
 * Hiển thị thông báo toast với hình dạng banner thông báo
 */
export const showOrderStatusToast = (notification) => {
    // Đảm bảo notification hợp lệ
    if (!notification) return;

    // Đảm bảo có các giá trị an toàn, tránh hiển thị undefined
    const orderId = notification.order_id;
    const orderCode = notification.order_code || notification.order?.order_code || 'không xác định';
    const status = notification.order_status || notification.order?.order_status || 'không xác định';
    const message = notification.message || `Đơn hàng #${orderCode} đã chuyển sang trạng thái: ${status}`;

    // Nếu không có orderId thì không hiển thị thông báo
    if (!orderId) {
        console.error('Thông báo thiếu order_id:', notification);
        return;
    }

    // Tạo ID duy nhất cho toast để tránh hiển thị trùng lặp
    const toastId = `order-notification-${notification.id || Date.now()}`;

    // Kiểm tra trạng thái để xác định màu sắc thông báo
    let colorClass = 'bg-green-600';

    switch (status) {
        case 'Đã Xác Nhận':
            colorClass = 'bg-green-600';
            break;
        case 'Đang Chuẩn Bị Hàng':
            colorClass = 'bg-blue-600';
            break;
        case 'Đang Giao':
            colorClass = 'bg-indigo-600';
            break;
        case 'Đã Giao':
            colorClass = 'bg-indigo-600';
            break;
        case 'Hoàn Hàng':
        case 'Hủy Đơn':
            colorClass = 'bg-red-600';
            break;
        default:
            colorClass = 'bg-blue-600';
    }

    return toast.custom(
        (t) => (
            <div
                className={`
          ${t.visible ? 'animate-enter' : 'animate-leave'}
          max-w-md w-full bg-white shadow-lg rounded-lg overflow-hidden pointer-events-auto 
          flex flex-col ring-1 ring-black ring-opacity-5 border-l-4 ${colorClass}
        `}
            >
                <div className="p-4">
                    <div className="flex items-start">
                        <div className="flex-shrink-0 pt-0.5">
                            <div className={`h-10 w-10 rounded-full ${colorClass.replace('bg-', 'bg-opacity-20 text-')} flex items-center justify-center`}>
                                <Bell className="h-6 w-6" />
                            </div>
                        </div>
                        <div className="ml-3 flex-1">
                            <p className="text-sm font-medium text-gray-900">
                                Thông báo đơn hàng
                            </p>
                            <p className="mt-1 text-sm text-gray-500">
                                {message}
                            </p>
                            <div className="mt-3 flex space-x-3">
                                <button
                                    onClick={() => {
                                        // Đóng toast trước
                                        toast.remove(toastId);
                                        // Chuyển hướng sau
                                        setTimeout(() => {
                                            window.location.href = `/account/order_detail/${orderId}`;
                                        }, 0);
                                    }}
                                    className={`px-3 py-1.5 rounded-md text-xs font-medium text-white ${colorClass} hover:${colorClass.replace('bg-', 'bg-opacity-90 ')} focus:outline-none`}
                                >
                                    Xem chi tiết
                                </button>
                                <button
                                    onClick={() => {
                                        // Đóng toast ngay lập tức
                                        toast.remove(toastId);
                                    }}
                                    className="px-3 py-1.5 rounded-md text-xs font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 focus:outline-none"
                                >
                                    Đóng
                                </button>
                            </div>
                        </div>
                        <button
                            onClick={() => {
                                // Đóng toast ngay lập tức
                                toast.remove(toastId);
                            }}
                            className="flex-shrink-0 ml-1 h-5 w-5 inline-flex items-center justify-center rounded-full text-gray-400 hover:text-gray-500 focus:outline-none"
                        >
                            <X className="h-4 w-4" />
                        </button>
                    </div>
                </div>
            </div>
        ),
        { id: toastId, duration: 3000 }
    );
};

/**
 * Hiển thị thông báo nhắc nhở thanh toán cho đơn hàng chưa thanh toán trực tuyến
 */
export const showPaymentReminderToast = (order) => {
    // Đảm bảo đơn hàng hợp lệ
    if (!order || !order.id || !order.order_code) return;

    // Tạo ID duy nhất cho toast để tránh hiển thị trùng lặp
    const toastId = `payment-reminder-${order.id || Date.now()}`;

    // Màu sắc thông báo - đỏ cho cảnh báo thanh toán
    const colorClass = 'bg-red-500';

    return toast.custom(
        (t) => (
            <div
                className={`
          ${t.visible ? 'animate-enter' : 'animate-leave'}
          max-w-md w-full bg-white shadow-lg rounded-lg overflow-hidden pointer-events-auto 
          flex flex-col ring-1 ring-black ring-opacity-5 border-l-4 ${colorClass}
        `}
            >
                <div className="p-5">
                    <div className="flex items-start">
                        <div className="flex-shrink-0 pt-0.5">
                            <div className={`h-10 w-10 rounded-full ${colorClass.replace('bg-', 'bg-opacity-20 text-')} flex items-center justify-center`}>
                                <AlertTriangle className="h-6 w-6" />
                            </div>
                        </div>
                        <div className="ml-3 flex-1">
                            <p className="text-sm font-medium text-gray-900">
                                Nhắc nhở thanh toán
                            </p>
                            <p className="mt-1 text-sm text-gray-500">
                                Đơn hàng #{order.order_code} của bạn đang chờ thanh toán. Vui lòng thanh toán để đơn hàng được xử lý.
                            </p>
                            <p className="mt-1 text-sm text-gray-500 font-medium">
                                Lưu ý: Đơn hàng sẽ tự động hủy trong vòng 24h nếu chưa thanh toán
                            </p>
                            <div className="mt-3 flex space-x-3">
                                <button
                                    onClick={() => {
                                        // Đóng toast trước
                                        toast.remove(toastId);
                                        // Chuyển hướng sau
                                        setTimeout(() => {
                                            window.location.href = `/account/order_detail/${order.id}`;
                                        }, 0);
                                    }}
                                    className={`px-3 py-1.5 rounded-md text-xs font-medium text-white ${colorClass} hover:${colorClass.replace('bg-', 'bg-opacity-90 ')} focus:outline-none`}
                                >
                                    Thanh toán ngay
                                </button>
                                <button
                                    onClick={() => {
                                        // Đóng toast ngay lập tức
                                        toast.remove(toastId);
                                    }}
                                    className="px-3 py-1.5 rounded-md text-xs font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 focus:outline-none"
                                >
                                    Để sau
                                </button>
                            </div>
                        </div>
                        <button
                            onClick={() => {
                                // Đóng toast ngay lập tức
                                toast.remove(toastId);
                            }}
                            className="flex-shrink-0 ml-1 h-5 w-5 inline-flex items-center justify-center rounded-full text-gray-400 hover:text-gray-500 focus:outline-none"
                        >
                            <X className="h-4 w-4" />
                        </button>
                    </div>
                </div>
            </div>
        ),
        { id: toastId, duration: 5000 } // Hiển thị lâu hơn (5 giây) so với thông báo thông thường
    );
}; 