import React, { useState, useEffect, useRef } from 'react';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuTrigger
} from './ui/dropdown-menu';
import { Bell } from 'lucide-react';
import { Button } from './ui/button';
import NotificationBadge from './ui/NotificationBadge';
import axios from 'axios';
import { format, isToday, isYesterday } from 'date-fns';
import { vi } from 'date-fns/locale';
import { useNavigate } from 'react-router-dom';
import { subscribeToNotifications } from '../utils/socketConfig';
import { showOrderStatusToast } from './ui/toast';

const Notifications = () => {
    const [notifications, setNotifications] = useState([]);
    const [unreadCount, setUnreadCount] = useState(0);
    const [isDropdownOpen, setIsDropdownOpen] = useState(false);
    const [isLoading, setIsLoading] = useState(false);
    const [isMarkAllLoading, setIsMarkAllLoading] = useState(false);
    const navigate = useNavigate();
    const audioRef = useRef(null);
    const recentNotificationIds = useRef(new Set());
    const toastShownIds = useRef(new Set());
    const notificationContainerRef = useRef(null);

    useEffect(() => {
        // Đăng ký nhận thông báo từ socket
        const unsubscribe = subscribeToNotifications(handleNewNotification);

        // Tạo thẻ audio
        audioRef.current = new Audio('/sounds/notification-sound.mp3');
        audioRef.current.volume = 0.5;

        // Đăng ký sự kiện lắng nghe event từ window
        const handleOrderNotification = (event) => {
            if (event.detail) {
                handleNewNotification(event.detail);
            }
        };
        window.addEventListener('order-notification', handleOrderNotification);

        // Cleanup khi component unmount
        return () => {
            unsubscribe();
            window.removeEventListener('order-notification', handleOrderNotification);
            recentNotificationIds.current.clear();
            toastShownIds.current.clear();
        };
    }, []);

    // Load thông báo khi component được tạo
    useEffect(() => {
        fetchNotifications();
    }, []);

    // Khi dropdown mở, cuộn lên đầu danh sách
    useEffect(() => {
        if (isDropdownOpen && notificationContainerRef.current) {
            notificationContainerRef.current.scrollTop = 0;
        }
    }, [isDropdownOpen]);

    const fetchNotifications = async () => {
        const token = localStorage.getItem('authToken');
        if (!token) return;

        setIsLoading(true);
        try {
            const response = await axios.get('http://localhost:8000/api/user/notifications', {
                headers: { Authorization: `Bearer ${token}` }
            });

            if (response.data.success) {
                // Xử lý thông báo từ API để đảm bảo có đầy đủ thông tin
                const processedNotifications = (response.data.notifications || []).map(notification => {
                    // Lấy thông tin từ order nếu có
                    const order = notification.order || {};

                    return {
                        ...notification,
                        order_code: notification.order_code || order.order_code || 'Không xác định',
                        order_status: notification.order_status || order.order_status || 'Không xác định',
                        // Tạo message mặc định nếu chưa có
                        message: notification.message ||
                            `Đơn hàng #${notification.order_code || order.order_code || 'Không xác định'} đã chuyển sang trạng thái: ${notification.order_status || order.order_status || 'Không xác định'}`
                    };
                });

                setNotifications(processedNotifications);
                setUnreadCount(response.data.unreadCount || 0);
            }
        } catch (error) {
            console.error('Lỗi khi lấy thông báo:', error);
        } finally {
            setIsLoading(false);
        }
    };

    const handleNewNotification = (notification) => {
        console.log("Nhận thông báo mới:", notification);

        // Đảm bảo dữ liệu thông báo hợp lệ
        if (!notification || !notification.id) return;

        // Kiểm tra nếu thông báo đã được xử lý gần đây
        if (recentNotificationIds.current.has(notification.id)) {
            console.log("⚠️ Đã nhận thông báo này gần đây, bỏ qua:", notification.id);
            return;
        }

        // Thêm vào danh sách đã xử lý
        recentNotificationIds.current.add(notification.id);

        // Sau 5 giây, xóa khỏi danh sách đã xử lý (để tránh danh sách quá lớn)
        setTimeout(() => {
            recentNotificationIds.current.delete(notification.id);
        }, 5000);

        // Thêm thông báo mới vào danh sách
        setNotifications(prevNotifications => {
            // Kiểm tra nếu thông báo đã tồn tại (tránh trùng lặp)
            const exists = prevNotifications.some(item => item.id === notification.id);
            if (exists) return prevNotifications;

            // Xử lý thông báo cho phù hợp với cấu trúc notification object
            const processedNotification = {
                ...notification,
                order_code: notification.order_code || (notification.order && notification.order.order_code) || 'Không xác định',
                order_status: notification.order_status || (notification.order && notification.order.order_status) || 'Không xác định',
                // Tạo message mặc định nếu chưa có
                message: notification.message ||
                    `Đơn hàng #${notification.order_code || (notification.order && notification.order.order_code) || 'Không xác định'} đã chuyển sang trạng thái: ${notification.order_status || (notification.order && notification.order.order_status) || 'Không xác định'}`
            };

            return [processedNotification, ...prevNotifications];
        });

        // Tăng số lượng thông báo chưa đọc
        setUnreadCount(prev => prev + 1);

        // Phát âm thanh thông báo
        if (audioRef.current) {
            audioRef.current.play().catch(e => console.error('Không thể phát âm thanh:', e));
        }

        // Hiển thị toast notification (chỉ hiển thị khi chưa hiển thị trước đó)
        if (!toastShownIds.current.has(notification.id)) {
            toastShownIds.current.add(notification.id);

            // Sau 10 giây, cho phép hiển thị lại toast này (nếu cần)
            setTimeout(() => {
                toastShownIds.current.delete(notification.id);
            }, 10000);

            // Kiểm tra nếu là thông báo ví tiền
            if (notification.transaction_type === 'nap_tien') {
                // Sử dụng showWalletDepositToast cho thông báo ví tiền
                import('./ui/toast').then(module => {
                    module.showWalletDepositToast(notification);
                });
            } else {
                // Sử dụng toast đơn hàng thông thường
                showOrderStatusToast(notification);
            }
        }
    };

    const markAsRead = async (notification) => {
        try {
            if (notification.is_read) return;

            // Gọi API để đánh dấu đã đọc
            const token = localStorage.getItem('authToken');
            await axios.patch(`http://localhost:8000/api/user/notifications/${notification.id}/read`, {}, {
                headers: { Authorization: `Bearer ${token}` }
            });

            // Cập nhật state
            setNotifications(prevNotifications =>
                prevNotifications.map(item =>
                    item.id === notification.id ? { ...item, is_read: true } : item
                )
            );

            setUnreadCount(prev => Math.max(0, prev - 1));
        } catch (error) {
            console.error('Lỗi khi đánh dấu đã đọc:', error);
        }
    };

    const handleNotificationClick = (notification) => {
        markAsRead(notification);

        // Kiểm tra nếu là thông báo về ví tiền
        if (notification.transaction_type === 'nap_tien' || notification.wallet_id) {
            navigate('/account/wallet');  // Chuyển đến trang ví tiền
        }
        // Xử lý đơn hàng nếu có order_id
        else if (notification.order_id) {
            navigate(`/account/order_detail/${notification.order_id}`);
        }

        setIsDropdownOpen(false);
    };

    const formatTime = (dateString) => {
        const date = new Date(dateString);

        if (isToday(date)) {
            return `Hôm nay, ${format(date, 'HH:mm', { locale: vi })}`;
        } else if (isYesterday(date)) {
            return `Hôm qua, ${format(date, 'HH:mm', { locale: vi })}`;
        } else {
            return format(date, 'dd/MM/yyyy HH:mm', { locale: vi });
        }
    };

    const markAllAsRead = async () => {
        if (isMarkAllLoading) return;

        try {
            setIsMarkAllLoading(true);
            const token = localStorage.getItem('authToken');
            await axios.patch('http://localhost:8000/api/user/notifications/read-all', {}, {
                headers: { Authorization: `Bearer ${token}` }
            });

            // Cập nhật state
            setNotifications(prevNotifications =>
                prevNotifications.map(item => ({ ...item, is_read: true }))
            );

            setUnreadCount(0);
        } catch (error) {
            console.error('Lỗi khi đánh dấu tất cả đã đọc:', error);
        } finally {
            setIsMarkAllLoading(false);
        }
    };

    // Nhóm thông báo theo ngày
    const groupNotificationsByDate = () => {
        const groups = {
            today: [],
            yesterday: [],
            older: []
        };

        notifications.forEach(notification => {
            const date = new Date(notification.created_at);
            if (isToday(date)) {
                groups.today.push(notification);
            } else if (isYesterday(date)) {
                groups.yesterday.push(notification);
            } else {
                groups.older.push(notification);
            }
        });

        return groups;
    };

    const notificationGroups = groupNotificationsByDate();

    const renderNotificationGroup = (title, items) => {
        if (items.length === 0) return null;

        return (
            <div key={title}>
                <div className="sticky top-0 bg-white px-4 py-1 text-xs font-medium text-gray-500 border-b z-10">
                    {title}
                </div>
                {items.map(notification => (
                    <div
                        key={notification.id}
                        className={`flex flex-col items-start py-3 px-4 cursor-pointer hover:bg-gray-50 ${!notification.is_read ? 'bg-green-50 hover:bg-green-100/70' : ''} border-b border-gray-100`}
                        onClick={() => handleNotificationClick(notification)}
                    >
                        <div className="flex w-full">
                            <div className="flex-1 min-w-0">
                                <p className={`text-sm ${!notification.is_read ? 'font-medium' : ''}`}>
                                    {notification.message ||
                                        `Đơn hàng #${notification.order_code || 'Không xác định'} đã chuyển sang trạng thái: ${notification.order_status || 'Không xác định'}`}
                                </p>
                                <p className="text-xs text-gray-500 mt-1">
                                    {formatTime(notification.created_at)}
                                </p>
                            </div>
                            {!notification.is_read && (
                                <div className="ml-2 h-2 w-2 bg-green-500 rounded-full flex-shrink-0 mt-1"></div>
                            )}
                        </div>
                    </div>
                ))}
            </div>
        );
    };

    return (
        <DropdownMenu open={isDropdownOpen} onOpenChange={setIsDropdownOpen}>
            <DropdownMenuTrigger asChild>
                <Button
                    variant="ghost"
                    className="relative p-2 rounded-full hover:bg-gray-100 focus-visible:ring-0 focus-visible:ring-offset-0 transition-colors duration-200"
                >
                    <Bell size={20} strokeWidth={1.8} className="text-gray-600 hover:text-green-600" />
                    {unreadCount > 0 && <NotificationBadge count={unreadCount} />}
                </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent className="w-80 md:w-96 mt-1 p-0" align="end">
                <div className="flex justify-between items-center py-2 px-4 border-b sticky top-0 bg-white z-20">
                    <span className="text-base font-semibold">Thông báo</span>
                    {unreadCount > 0 && (
                        <Button
                            variant="ghost"
                            size="sm"
                            className="h-8 px-2 text-xs text-green-600 hover:text-green-700 hover:bg-green-50 transition-colors"
                            onClick={markAllAsRead}
                            disabled={isMarkAllLoading}
                        >
                            {isMarkAllLoading ? (
                                <span className="flex items-center">
                                    <span className="animate-spin rounded-full h-3 w-3 border-b-2 border-green-500 mr-1"></span>
                                    Đang xử lý...
                                </span>
                            ) : (
                                'Đánh dấu tất cả đã đọc'
                            )}
                        </Button>
                    )}
                </div>

                {isLoading ? (
                    <div className="flex justify-center items-center py-6">
                        <div className="animate-spin rounded-full h-6 w-6 border-b-2 border-green-500"></div>
                    </div>
                ) : notifications.length === 0 ? (
                    <div className="py-8 text-center text-gray-500">
                        <Bell size={32} className="mx-auto mb-2 opacity-20" />
                        <p>Bạn chưa có thông báo nào</p>
                    </div>
                ) : (
                    <div
                        ref={notificationContainerRef}
                        className="overflow-y-auto max-h-[400px] scrollbar-thin scrollbar-thumb-gray-300 scrollbar-track-gray-100"
                    >
                        {renderNotificationGroup('Hôm nay', notificationGroups.today)}
                        {renderNotificationGroup('Hôm qua', notificationGroups.yesterday)}
                        {renderNotificationGroup('Trước đó', notificationGroups.older)}
                    </div>
                )}

                {notifications.length > 0 && (
                    <div className="py-2 text-center border-t sticky bottom-0 bg-white">
                        <Button
                            variant="ghost"
                            size="sm"
                            className="text-xs text-green-600 hover:text-green-700 transition-colors"
                            onClick={() => {
                                navigate('/account/notifications');
                                setIsDropdownOpen(false);
                            }}
                        >
                            Xem tất cả thông báo
                        </Button>
                    </div>
                )}
            </DropdownMenuContent>
        </DropdownMenu>
    );
};

export default Notifications; 