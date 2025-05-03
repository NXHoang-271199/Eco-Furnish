import React, { useEffect } from 'react';
import axios from 'axios';
import { showPaymentReminderToast } from './ui/toast';

/**
 * Component kiểm tra và hiển thị thông báo nhắc nhở thanh toán đơn hàng
 */
const PaymentReminder = () => {
    // Hàm kiểm tra đơn hàng chưa thanh toán
    const checkPendingPayments = async () => {
        // Kiểm tra xem người dùng đã đăng nhập chưa
        const token = localStorage.getItem('authToken');
        if (!token) return;

        try {
            const response = await axios.get('/api/user/orders/unpaid', {
                headers: { Authorization: `Bearer ${token}` }
            });

            if (response.data.status === 'success') {
                // Đảm bảo rằng data được trả về là một mảng
                const orders = Array.isArray(response.data.data) ? response.data.data : [];

                // Lấy danh sách ID đơn hàng chưa thanh toán
                const unpaidOrderIds = orders.map(order => order.id.toString());

                // Làm sạch localStorage - loại bỏ đơn hàng đã thanh toán
                cleanupReminderTimes(unpaidOrderIds);

                if (orders.length > 0) {
                    // Hiển thị thông báo nhắc nhở cho từng đơn hàng chưa thanh toán
                    // Hàm showPaymentReminderToast sẽ tự kiểm tra thời gian hiển thị
                    orders.forEach(order => {
                        showPaymentReminderToast(order);
                    });
                }
            }
        } catch (error) {
            console.error('Lỗi khi kiểm tra đơn hàng chưa thanh toán:', error);
        }
    };

    // Hàm làm sạch thời gian nhắc nhở đã lưu - xóa các đơn hàng đã thanh toán
    const cleanupReminderTimes = (unpaidOrderIds) => {
        try {
            const lastReminderTimes = JSON.parse(localStorage.getItem('paymentReminderTimes') || '{}');
            let hasChanged = false;

            // Xóa các đơn hàng không còn trong danh sách chưa thanh toán
            Object.keys(lastReminderTimes).forEach(orderId => {
                if (!unpaidOrderIds.includes(orderId)) {
                    delete lastReminderTimes[orderId];
                    hasChanged = true;
                    console.log(`Đã xóa đơn hàng đã thanh toán #${orderId} khỏi danh sách nhắc nhở`);
                }
            });

            // Lưu lại nếu có thay đổi
            if (hasChanged) {
                localStorage.setItem('paymentReminderTimes', JSON.stringify(lastReminderTimes));
            }
        } catch (error) {
            console.error('Lỗi khi làm sạch localStorage:', error);
        }
    };

    // Kiểm tra khi component được tạo
    useEffect(() => {
        // Kiểm tra ngay khi component được tạo
        checkPendingPayments();

        // Kiểm tra định kỳ mỗi 5 phút
        const interval = setInterval(() => {
            checkPendingPayments();
        }, 5 * 60 * 1000); // 5 phút

        // Cleanup khi component unmount
        return () => clearInterval(interval);
    }, []);

    // Component này không hiển thị gì
    return null;
};

export default PaymentReminder; 