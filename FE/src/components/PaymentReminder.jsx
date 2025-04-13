import React, { useEffect, useState } from 'react';
import axios from 'axios';
import { showPaymentReminderToast } from './ui/toast';

/**
 * Component kiểm tra và hiển thị thông báo nhắc nhở thanh toán đơn hàng
 */
const PaymentReminder = () => {
    const [lastReminderTimes, setLastReminderTimes] = useState({});

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

                if (orders.length > 0) {
                    // Lọc các đơn hàng cần hiển thị thông báo
                    const currentTime = Date.now();
                    const REMINDER_INTERVAL = 60 * 60 * 1000; // 1 giờ

                    orders.forEach(order => {
                        // Kiểm tra xem đơn hàng này đã hiển thị thông báo gần đây chưa
                        if (!lastReminderTimes[order.id] ||
                            (currentTime - lastReminderTimes[order.id]) > REMINDER_INTERVAL) {

                            // Hiển thị thông báo
                            showPaymentReminderToast(order);

                            // Lưu thời gian hiển thị thông báo
                            setLastReminderTimes(prev => ({
                                ...prev,
                                [order.id]: currentTime
                            }));
                        }
                    });
                }
            }
        } catch (error) {
            console.error('Lỗi khi kiểm tra đơn hàng chưa thanh toán:', error);
        }
    };

    // Kiểm tra khi component được tạo
    useEffect(() => {
        // Kiểm tra ngay khi component được tạo
        checkPendingPayments();

        // Kiểm tra định kỳ mỗi 30 phút
        const interval = setInterval(() => {
            checkPendingPayments();
        }, 30 * 60 * 1000);

        // Cleanup khi component unmount
        return () => clearInterval(interval);
    }, []);

    // Component này không hiển thị gì
    return null;
};

export default PaymentReminder; 