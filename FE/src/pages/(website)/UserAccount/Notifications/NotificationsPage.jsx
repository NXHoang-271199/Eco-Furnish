import React, { useState, useEffect, useRef } from 'react';
import { motion } from 'framer-motion';
import { Bell, Check, Filter } from 'lucide-react';
import { Button } from '../../../../components/ui/button';
import axios from 'axios';
import { format, isToday, isYesterday } from 'date-fns';
import { vi } from 'date-fns/locale';
import { useNavigate } from 'react-router-dom';
import { subscribeToNotifications } from '../../../../utils/socketConfig';
import { Card, CardContent, CardHeader, CardTitle } from '../../../../components/ui/card';

const NotificationsPage = () => {
    const [notifications, setNotifications] = useState([]);
    const [unreadCount, setUnreadCount] = useState(0);
    const [isLoading, setIsLoading] = useState(false);
    const [isMarkAllLoading, setIsMarkAllLoading] = useState(false);
    const [filter, setFilter] = useState('all'); // 'all', 'unread', 'read'
    const navigate = useNavigate();
    const audioRef = useRef(null);
    const recentNotificationIds = useRef(new Set());
    const toastShownIds = useRef(new Set());

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

    const fetchNotifications = async () => {
        const token = localStorage.getItem('authToken');
        if (!token) {
            navigate('/sign-in');
            return;
        }

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
            return [notification, ...prevNotifications];
        });

        // Tăng số lượng thông báo chưa đọc
        setUnreadCount(prev => prev + 1);

        // Phát âm thanh thông báo
        if (audioRef.current) {
            audioRef.current.play().catch(e => console.error('Không thể phát âm thanh:', e));
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

        // Chuyển hướng đến trang chi tiết đơn hàng nếu có
        if (notification.order_id) {
            navigate(`/account/order_detail/${notification.order_id}`);
        }
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
        // Lọc thông báo theo trạng thái nếu có bộ lọc
        const filteredNotifications = notifications.filter(notification => {
            if (filter === 'unread') return !notification.is_read;
            if (filter === 'read') return notification.is_read;
            return true; // 'all'
        });

        const groups = {
            today: [],
            yesterday: [],
            older: []
        };

        filteredNotifications.forEach(notification => {
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
            <div key={title} className="mb-6">
                <h3 className="px-4 py-2 bg-gray-50 text-sm font-medium text-gray-600 rounded-lg mb-2">
                    {title}
                </h3>
                <div className="space-y-2">
                    {items.map(notification => (
                        <div
                            key={notification.id}
                            className={`flex p-4 rounded-lg cursor-pointer hover:bg-gray-50 ${!notification.is_read ? 'bg-green-50 border-l-4 border-green-500' : 'bg-white border border-gray-100'
                                }`}
                            onClick={() => handleNotificationClick(notification)}
                        >
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
                                <div className="ml-4 flex-shrink-0">
                                    <div className="h-3 w-3 bg-green-500 rounded-full"></div>
                                </div>
                            )}
                        </div>
                    ))}
                </div>
            </div>
        );
    };

    // Kiểm tra nếu không có thông báo nào sau khi lọc
    const hasNoFilteredNotifications = () => {
        const groups = notificationGroups;
        return (
            groups.today.length === 0 &&
            groups.yesterday.length === 0 &&
            groups.older.length === 0
        );
    };

    return (
        <div className="container mx-auto pt-8 pb-10 px-4 md:px-6">
            <motion.div
                initial={{ opacity: 0, y: -20 }}
                animate={{ opacity: 1, y: 0 }}
                transition={{ duration: 0.5 }}
                className="mb-8"
            >
                <h1 className="text-3xl font-bold tracking-tight mb-2">Thông báo của tôi</h1>
                <p className="text-muted-foreground">
                    Quản lý và theo dõi thông báo từ hệ thống
                </p>
            </motion.div>

            <motion.div
                initial={{ opacity: 0, y: 20 }}
                animate={{ opacity: 1, y: 0 }}
                transition={{ duration: 0.5, delay: 0.1 }}
            >
                <Card>
                    <CardHeader className="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-2 sm:space-y-0">
                        <CardTitle>Tất cả thông báo</CardTitle>
                        <div className="flex items-center space-x-2">
                            <div className="flex items-center space-x-2 bg-white border rounded-lg p-1">
                                <Button
                                    variant={filter === 'all' ? 'default' : 'ghost'}
                                    size="sm"
                                    onClick={() => setFilter('all')}
                                    className="text-xs h-8"
                                >
                                    Tất cả
                                </Button>
                                <Button
                                    variant={filter === 'unread' ? 'default' : 'ghost'}
                                    size="sm"
                                    onClick={() => setFilter('unread')}
                                    className="text-xs h-8"
                                >
                                    Chưa đọc {unreadCount > 0 && `(${unreadCount})`}
                                </Button>
                                <Button
                                    variant={filter === 'read' ? 'default' : 'ghost'}
                                    size="sm"
                                    onClick={() => setFilter('read')}
                                    className="text-xs h-8"
                                >
                                    Đã đọc
                                </Button>
                            </div>

                            {unreadCount > 0 && (
                                <Button
                                    variant="outline"
                                    size="sm"
                                    className="text-xs flex items-center gap-1"
                                    onClick={markAllAsRead}
                                    disabled={isMarkAllLoading}
                                >
                                    {isMarkAllLoading ? (
                                        <span className="flex items-center">
                                            <span className="animate-spin rounded-full h-3 w-3 border-b-2 border-green-500 mr-1"></span>
                                            Đang xử lý...
                                        </span>
                                    ) : (
                                        <>
                                            <Check size={14} />
                                            Đánh dấu tất cả đã đọc
                                        </>
                                    )}
                                </Button>
                            )}
                        </div>
                    </CardHeader>
                    <CardContent>
                        {isLoading ? (
                            <div className="py-12 flex justify-center items-center">
                                <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-green-500"></div>
                            </div>
                        ) : notifications.length === 0 ? (
                            <div className="py-12 text-center">
                                <Bell size={48} className="mx-auto mb-4 text-gray-300" />
                                <p className="text-gray-500">Bạn chưa có thông báo nào</p>
                            </div>
                        ) : hasNoFilteredNotifications() ? (
                            <div className="py-12 text-center">
                                <Filter size={48} className="mx-auto mb-4 text-gray-300" />
                                <p className="text-gray-500">Không có thông báo nào phù hợp với bộ lọc</p>
                                <Button
                                    variant="ghost"
                                    size="sm"
                                    className="mt-4"
                                    onClick={() => setFilter('all')}
                                >
                                    Xem tất cả thông báo
                                </Button>
                            </div>
                        ) : (
                            <div>
                                {renderNotificationGroup('Hôm nay', notificationGroups.today)}
                                {renderNotificationGroup('Hôm qua', notificationGroups.yesterday)}
                                {renderNotificationGroup('Trước đó', notificationGroups.older)}
                            </div>
                        )}
                    </CardContent>
                </Card>
            </motion.div>
        </div>
    );
};

export default NotificationsPage; 