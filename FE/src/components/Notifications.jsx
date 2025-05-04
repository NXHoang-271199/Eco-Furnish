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

// Hàm xóa sessionStorage khi cần thiết
const clearNotificationStorage = () => {
    try {
        sessionStorage.removeItem('processedNotificationIds');
        sessionStorage.removeItem('processedOrderStatuses');
        console.log('Đã xóa dữ liệu thông báo trong sessionStorage');
    } catch (error) {
        console.error('Lỗi khi xóa dữ liệu thông báo:', error);
    }
};

// Helper functions để quản lý processedNotificationIds trong sessionStorage
const getProcessedNotificationIds = () => {
    try {
        return new Set(JSON.parse(sessionStorage.getItem('processedNotificationIds') || '[]'));
    } catch (error) {
        console.error('Lỗi khi đọc processedNotificationIds từ sessionStorage:', error);
        return new Set();
    }
};

const addProcessedNotificationId = (id) => {
    try {
        // Đọc giá trị hiện tại từ sessionStorage để đảm bảo có dữ liệu mới nhất
        const ids = getProcessedNotificationIds();
        ids.add(id);
        sessionStorage.setItem('processedNotificationIds', JSON.stringify([...ids]));
    } catch (error) {
        console.error('Lỗi khi thêm ID vào processedNotificationIds:', error);
    }
};

const isProcessedNotification = (id) => {
    // Luôn đọc trực tiếp từ sessionStorage để đảm bảo dữ liệu mới nhất
    return getProcessedNotificationIds().has(id);
};

// Hàm helper mới để lưu trữ cặp order_id và order_status đã xử lý
const getProcessedOrderStatuses = () => {
    try {
        return new Map(JSON.parse(sessionStorage.getItem('processedOrderStatuses') || '[]'));
    } catch (error) {
        console.error('Lỗi khi đọc processedOrderStatuses từ sessionStorage:', error);
        return new Map();
    }
};

const addProcessedOrderStatus = (orderId, status) => {
    try {
        if (!orderId || !status) return;
        // Đọc giá trị hiện tại từ sessionStorage
        const statusMap = getProcessedOrderStatuses();
        statusMap.set(orderId.toString(), status);
        sessionStorage.setItem('processedOrderStatuses', JSON.stringify([...statusMap.entries()]));
    } catch (error) {
        console.error('Lỗi khi thêm cặp order_id/status vào processedOrderStatuses:', error);
    }
};

const isProcessedOrderStatus = (orderId, status) => {
    if (!orderId || !status) return false;
    const statusMap = getProcessedOrderStatuses();
    return statusMap.get(orderId.toString()) === status;
};

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
    const reloadCountRef = useRef(0);

    // Load khi component được tạo
    useEffect(() => {
        // Reset thông báo đã xử lý khi tải lại trang
        const currentReloadCount = parseInt(localStorage.getItem('notificationReloadCount') || '0');
        if (currentReloadCount !== reloadCountRef.current) {
            // Cập nhật giá trị mới
            reloadCountRef.current = currentReloadCount;
            // Xóa dữ liệu thông báo cũ khi tải lại trang
            clearNotificationStorage();
        }
        // Tăng counter và lưu lại
        const newReloadCount = currentReloadCount + 1;
        localStorage.setItem('notificationReloadCount', newReloadCount.toString());
        reloadCountRef.current = newReloadCount;

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

                // Chỉ lọc bỏ các thông báo hoàn toàn trùng lặp (trùng ID)
                const uniqueNotifications = filterDuplicateNotifications(processedNotifications);

                // Đánh dấu tất cả thông báo là đã xử lý để tránh trùng lặp trong tương lai
                uniqueNotifications.forEach(notification => {
                    if (notification && notification.id) {
                        addProcessedNotificationId(notification.id);
                    }
                    if (notification.order_id && notification.order_status) {
                        addProcessedOrderStatus(notification.order_id, notification.order_status);
                    }
                });

                // Sắp xếp thông báo theo thời gian tạo giảm dần
                const sortedNotifications = uniqueNotifications.sort((a, b) =>
                    new Date(b.created_at) - new Date(a.created_at)
                );

                setNotifications(sortedNotifications);
                setUnreadCount(response.data.unreadCount || 0);

                // Debug
                console.log(`Hiển thị ${sortedNotifications.length} thông báo từ tổng số ${processedNotifications.length} thông báo`);
                console.log(`Số lượng thông báo chưa đọc: ${response.data.unreadCount || 0}`);
            }
        } catch (error) {
            console.error('Lỗi khi lấy thông báo:', error);
        } finally {
            setIsLoading(false);
        }
    };

    // Hàm mới chỉ lọc bỏ thông báo trùng ID
    const filterDuplicateNotifications = (notifications) => {
        const uniqueNotifications = [];
        const processedIds = new Set();
        const processedOrderStatusPairs = new Map();

        console.log(`Bắt đầu lọc ${notifications.length} thông báo...`);

        // Chỉ lọc bỏ thông báo có ID trùng lặp hoặc trùng cặp order_id + order_status gần nhau
        notifications.forEach(notification => {
            // Bỏ qua thông báo đã có ID trùng lặp
            if (notification.id && processedIds.has(notification.id)) {
                console.log(`→ Bỏ qua thông báo trùng ID: ${notification.id}`);
                return;
            }

            // Đánh dấu ID là đã xử lý
            if (notification.id) {
                processedIds.add(notification.id);
            }

            // Tạo khóa duy nhất cho thông báo dựa trên order_id và order_status (nếu có)
            if (notification.order_id && notification.order_status) {
                const key = `${notification.order_id}_${notification.order_status}`;

                // Nếu đã có thông báo với cùng order_id và order_status, kiểm tra thời gian
                if (processedOrderStatusPairs.has(key)) {
                    const existingIndex = processedOrderStatusPairs.get(key);
                    const existingNotification = uniqueNotifications[existingIndex];

                    // Nếu thông báo hiện tại mới hơn thông báo đã có, thay thế thông báo cũ
                    if (new Date(notification.created_at) > new Date(existingNotification.created_at)) {
                        console.log(`→ Thay thế thông báo cũ với key ${key}: ID cũ ${existingNotification.id} -> ID mới ${notification.id}`);
                        uniqueNotifications[existingIndex] = notification;
                    } else {
                        console.log(`→ Giữ lại thông báo cũ với key ${key}: ID ${existingNotification.id} (mới hơn ID ${notification.id})`);
                    }
                    return;
                }

                // Lưu vị trí của thông báo trong mảng kết quả
                processedOrderStatusPairs.set(key, uniqueNotifications.length);
            }

            // Thêm thông báo vào danh sách kết quả
            uniqueNotifications.push(notification);
        });

        console.log(`Kết quả lọc: Từ ${notifications.length} thông báo -> ${uniqueNotifications.length} thông báo duy nhất`);
        if (uniqueNotifications.length < notifications.length) {
            console.log(`Đã loại bỏ ${notifications.length - uniqueNotifications.length} thông báo trùng lặp`);
        }

        return uniqueNotifications;
    };

    const handleNewNotification = (notification) => {
        console.log("Nhận thông báo mới:", notification);

        // Đảm bảo dữ liệu thông báo hợp lệ
        if (!notification || !notification.id) return;

        // Kiểm tra trực tiếp từ sessionStorage nếu thông báo đã được xử lý
        if (isProcessedNotification(notification.id)) {
            console.log("⚠️ Thông báo này đã được xử lý trước đó, bỏ qua:", notification.id);
            return;
        }

        // Kiểm tra nếu thông báo đã được xử lý gần đây (để đề phòng nhận trùng lặp ngay lập tức)
        if (recentNotificationIds.current.has(notification.id)) {
            console.log("⚠️ Đã nhận thông báo này gần đây, bỏ qua:", notification.id);
            return;
        }

        // Kiểm tra cặp order_id và order_status
        if (notification.order_id && notification.order_status &&
            isProcessedOrderStatus(notification.order_id, notification.order_status)) {
            console.log(`⚠️ Cặp order_id (${notification.order_id}) và order_status (${notification.order_status}) đã được xử lý trước đó, bỏ qua`);
            return;
        }

        // Đánh dấu thông báo đã được xử lý
        addProcessedNotificationId(notification.id);

        // Nếu là thông báo đơn hàng, đánh dấu cặp order_id và order_status
        if (notification.order_id && notification.order_status) {
            addProcessedOrderStatus(notification.order_id, notification.order_status);
        }

        // Thêm vào danh sách đã xử lý gần đây
        recentNotificationIds.current.add(notification.id);

        // Tăng thời gian để xóa ID khỏi danh sách, từ 30 giây lên 60 giây
        setTimeout(() => {
            recentNotificationIds.current.delete(notification.id);
        }, 60000);

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

            // Tăng thời gian, từ 10 giây lên 60 giây
            setTimeout(() => {
                toastShownIds.current.delete(notification.id);
            }, 60000);

            // Kiểm tra loại thông báo và hiển thị toast tương ứng
            if (notification.transaction_type === 'nap_tien') {
                // Sử dụng showWalletDepositToast cho thông báo ví tiền
                import('./ui/toast').then(module => {
                    module.showWalletDepositToast(notification);
                });
            } else if (notification.review_id) {
                // Sử dụng showReviewHiddenToast cho thông báo đánh giá bị ẩn
                import('./ui/toast').then(module => {
                    module.showReviewHiddenToast(notification);
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