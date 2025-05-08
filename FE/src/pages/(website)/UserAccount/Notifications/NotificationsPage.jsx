import React, { useState, useEffect } from "react";
import axios from "axios";
import { format, isToday, isYesterday } from "date-fns";
import { vi } from "date-fns/locale";
import { useNavigate } from "react-router-dom";
import { Bell, Check, Package, Clock, Filter } from "lucide-react";
import { motion, AnimatePresence } from "framer-motion";

const NotificationsPage = () => {
  const [notifications, setNotifications] = useState([]);
  const [isLoading, setIsLoading] = useState(false);
  const [isMarkingAll, setIsMarkingAll] = useState(false);
  const [activeFilter, setActiveFilter] = useState("all");
  const [selectedNotification, setSelectedNotification] = useState(null);
  const navigate = useNavigate();

  useEffect(() => {
    fetchNotifications();
    // Thêm hiệu ứng chuyển động khi trang tải lên
    document.title = "Thông báo | Eco-Furnish";
  }, []);

  const fetchNotifications = async () => {
    const token = localStorage.getItem("authToken");
    if (!token) return;

    setIsLoading(true);
    try {
      const response = await axios.get(
        "http://localhost:8000/api/user/notifications",
        {
          headers: { Authorization: `Bearer ${token}` },
        }
      );

      if (response.data.success) {
        // Xử lý thông báo từ API để đảm bảo có đầy đủ thông tin
        const processedNotifications = (response.data.notifications || []).map(
          (notification) => {
            // Lấy thông tin từ order nếu có
            const order = notification.order || {};

            return {
              ...notification,
              order_code:
                notification.order_code || order.order_code || "Không xác định",
              order_status:
                notification.order_status ||
                order.order_status ||
                "Không xác định",
              // Tạo message mặc định nếu chưa có
              message:
                notification.message ||
                `Đơn hàng #${notification.order_code || order.order_code || "Không xác định"
                } đã chuyển sang trạng thái: ${notification.order_status ||
                order.order_status ||
                "Không xác định"
                }`
            };
          }
        );

        // Lọc bỏ các thông báo trùng lặp dựa trên order_id và order_status
        const uniqueNotifications = [];
        const processedIds = new Set();

        processedNotifications.forEach(notification => {
          // Tạo một khóa duy nhất dựa trên order_id và order_status
          const uniqueKey = `${notification.order_id}_${notification.order_status}`;

          // Chỉ thêm vào danh sách nếu chưa có thông báo với cùng order_id và order_status
          if (!processedIds.has(uniqueKey)) {
            processedIds.add(uniqueKey);
            uniqueNotifications.push(notification);
          }
        });

        setNotifications(uniqueNotifications);
      }
    } catch (error) {
      console.error("Lỗi khi lấy thông báo:", error);
    } finally {
      setIsLoading(false);
    }
  };

  const markAsRead = async (notification) => {
    try {
      if (notification.is_read) return;

      // Cập nhật state ngay lập tức để UI phản hồi nhanh
      setNotifications((prevNotifications) =>
        prevNotifications.map((item) =>
          item.id === notification.id ? { ...item, is_read: true } : item
        )
      );

      // Gọi API để đánh dấu đã đọc
      const token = localStorage.getItem("authToken");
      await axios.patch(
        `http://localhost:8000/api/user/notifications/${notification.id}/read`,
        {},
        {
          headers: { Authorization: `Bearer ${token}` },
        }
      );
    } catch (error) {
      console.error("Lỗi khi đánh dấu đã đọc:", error);
      // Khôi phục trạng thái cũ nếu API gặp lỗi
      setNotifications((prevNotifications) =>
        prevNotifications.map((item) =>
          item.id === notification.id ? { ...item, is_read: false } : item
        )
      );
    }
  };

  const handleNotificationClick = (notification) => {
    setSelectedNotification(notification);
    markAsRead(notification);

    // Thêm hiệu ứng chậm để người dùng thấy được hiệu ứng
    setTimeout(() => {
      // Xử lý đơn hàng nếu có order_id
      if (notification.order_id) {
        navigate(`/account/order_detail/${notification.order_id}`);
      }
      setSelectedNotification(null);
    }, 300);
  };

  const formatTime = (dateString) => {
    const date = new Date(dateString);

    if (isToday(date)) {
      return `Hôm nay, ${format(date, "HH:mm", { locale: vi })}`;
    } else if (isYesterday(date)) {
      return `Hôm qua, ${format(date, "HH:mm", { locale: vi })}`;
    } else {
      return format(date, "dd/MM/yyyy HH:mm", { locale: vi });
    }
  };

  const markAllAsRead = async () => {
    try {
      setIsMarkingAll(true);
      const token = localStorage.getItem("authToken");

      // Cập nhật state ngay lập tức để UI phản hồi nhanh
      setNotifications((prevNotifications) =>
        prevNotifications.map((item) => ({ ...item, is_read: true }))
      );

      await axios.patch(
        "http://localhost:8000/api/user/notifications/read-all",
        {},
        {
          headers: { Authorization: `Bearer ${token}` },
        }
      );
    } catch (error) {
      console.error("Lỗi khi đánh dấu tất cả đã đọc:", error);
    } finally {
      setIsMarkingAll(false);
    }
  };

  // Nhóm thông báo theo ngày
  const groupNotificationsByDate = () => {
    const groups = {
      today: [],
      yesterday: [],
      older: [],
    };

    // Lọc thông báo theo bộ lọc được chọn
    const filteredNotifications = notifications.filter(notification => {
      if (activeFilter === "all") return true;
      if (activeFilter === "unread") return !notification.is_read;
      if (activeFilter === "order") return notification.type === "order";
      return true;
    });

    filteredNotifications.forEach((notification) => {
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

  // Lấy tổng số thông báo chưa đọc
  const unreadCount = notifications.filter(n => !n.is_read).length;

  // Biểu tượng cho từng loại thông báo
  const getNotificationIcon = (type) => {
    switch (type) {
      case "order":
        return <Package size={18} className="text-green-500" />;
      default:
        return <Bell size={18} className="text-orange-500" />;
    }
  };

  const renderNotificationGroup = (title, items) => {
    if (items.length === 0) return null;

    return (
      <motion.div
        key={title}
        initial={{ opacity: 0, y: 20 }}
        animate={{ opacity: 1, y: 0 }}
        transition={{ duration: 0.4 }}
        className="mb-6"
      >
        <div className="sticky top-0 bg-gradient-to-r from-green-50 to-green-100 px-4 py-3 text-sm font-medium text-gray-700 border-b rounded-t-lg shadow-sm z-10">
          {title}
        </div>
        <AnimatePresence>
          {items.map((notification, index) => (
            <motion.div
              key={notification.id}
              initial={{ opacity: 0, x: -20 }}
              animate={{ opacity: 1, x: 0 }}
              exit={{ opacity: 0, x: 20 }}
              transition={{
                duration: 0.3,
                delay: index * 0.05,
                type: "spring",
                stiffness: 100
              }}
              whileHover={{
                scale: 1.01,
                boxShadow: "0 4px 6px rgba(0, 0, 0, 0.05)"
              }}
              className={`flex items-start py-4 px-4 cursor-pointer transition-all duration-200 ${selectedNotification?.id === notification.id
                ? "bg-green-100"
                : !notification.is_read
                  ? "bg-green-50 hover:bg-green-100/70"
                  : "hover:bg-gray-50"
                } border-b border-gray-100 relative overflow-hidden`}
              onClick={() => handleNotificationClick(notification)}
            >
              {/* Indicator cho thông báo đang chọn */}
              {selectedNotification?.id === notification.id && (
                <motion.div
                  className="absolute left-0 top-0 bottom-0 w-1 bg-green-500"
                  layoutId="notification-indicator"
                  initial={{ height: 0 }}
                  animate={{ height: "100%" }}
                  transition={{ duration: 0.2 }}
                />
              )}

              <div className="flex-shrink-0 mr-3 mt-1">
                <div className="p-2 rounded-full bg-gray-100">
                  {getNotificationIcon(notification.type)}
                </div>
              </div>

              <div className="flex-1">
                <p className={`text-sm ${!notification.is_read ? "font-medium" : ""}`}>
                  {notification.message ||
                    `Đơn hàng #${notification.order_code || "Không xác định"
                    } đã chuyển sang trạng thái: ${notification.order_status || "Không xác định"
                    }`}
                </p>
                <div className="flex items-center mt-1">
                  <Clock size={12} className="text-gray-400 mr-1" />
                  <p className="text-xs text-gray-500">
                    {formatTime(notification.created_at)}
                  </p>
                </div>
              </div>

              {!notification.is_read && (
                <div className="ml-2 h-2.5 w-2.5 bg-green-500 rounded-full flex-shrink-0 mt-1 pulse-animation"></div>
              )}
            </motion.div>
          ))}
        </AnimatePresence>
      </motion.div>
    );
  };

  // Filter options
  const filterOptions = [
    { id: "all", label: "Tất cả", icon: <Bell size={15} /> },
    { id: "unread", label: "Chưa đọc", icon: <Check size={15} /> },
  ];

  return (
    <motion.div
      initial={{ opacity: 0, y: 20 }}
      animate={{ opacity: 1, y: 0 }}
      transition={{ duration: 0.5 }}
      className="bg-white p-0 rounded-xl shadow-md max-w-5xl mx-auto overflow-hidden"
    >
      {/* Header with decorative gradient */}
      <div className="relative">
        <div className="absolute inset-0 bg-gradient-to-r from-green-400 to-emerald-500 opacity-90"></div>
        <div className="relative py-6 px-8">
          <div className="flex justify-between items-center">
            <div className="flex items-center">
              <motion.div
                initial={{ scale: 0.5, opacity: 0 }}
                animate={{ scale: 1, opacity: 1 }}
                transition={{
                  type: "spring",
                  stiffness: 260,
                  damping: 20,
                  delay: 0.2
                }}
                className="p-3 rounded-full bg-white/20 backdrop-blur-sm shadow-md mr-4"
              >
                <Bell className="text-white" size={24} />
              </motion.div>
              <div>
                <motion.h1
                  initial={{ x: -20, opacity: 0 }}
                  animate={{ x: 0, opacity: 1 }}
                  transition={{ delay: 0.3, duration: 0.5 }}
                  className="text-2xl font-bold text-white"
                >
                  Thông báo của bạn
                </motion.h1>
                <motion.p
                  initial={{ x: -20, opacity: 0 }}
                  animate={{ x: 0, opacity: 1 }}
                  transition={{ delay: 0.4, duration: 0.5 }}
                  className="text-white/80 text-sm"
                >
                  {unreadCount > 0
                    ? `Bạn có ${unreadCount} thông báo chưa đọc`
                    : "Tất cả thông báo đã được đọc"}
                </motion.p>
              </div>
            </div>

            <motion.button
              initial={{ opacity: 0, scale: 0.8 }}
              animate={{ opacity: 1, scale: 1 }}
              transition={{ delay: 0.5 }}
              onClick={markAllAsRead}
              disabled={isMarkingAll || unreadCount === 0}
              className={`px-4 py-2 rounded-full text-sm font-medium transition-all duration-300 
                ${unreadCount === 0
                  ? "bg-white/30 text-white/60 cursor-not-allowed"
                  : "bg-white text-green-600 hover:bg-white/90 hover:shadow-md"}`}
            >
              {isMarkingAll ? (
                <span className="flex items-center">
                  <span className="animate-spin h-4 w-4 border-2 border-green-500 border-t-transparent rounded-full mr-2"></span>
                  Đang xử lý...
                </span>
              ) : (
                "Đánh dấu tất cả đã đọc"
              )}
            </motion.button>
          </div>
        </div>
      </div>

      {/* Filter tabs */}
      <motion.div
        initial={{ opacity: 0, y: -10 }}
        animate={{ opacity: 1, y: 0 }}
        transition={{ delay: 0.6, duration: 0.4 }}
        className="px-4 py-3 bg-gray-50 border-b flex overflow-x-auto gap-2 scrollbar-thin scrollbar-thumb-gray-300"
      >
        <div className="flex items-center mr-2">
          <Filter size={15} className="text-gray-500 mr-1" />
          <span className="text-sm text-gray-500">Lọc:</span>
        </div>
        {filterOptions.map((option) => (
          <motion.button
            key={option.id}
            whileHover={{ scale: 1.05 }}
            whileTap={{ scale: 0.95 }}
            onClick={() => setActiveFilter(option.id)}
            className={`flex items-center px-3 py-1.5 rounded-full text-xs font-medium whitespace-nowrap ${activeFilter === option.id
              ? "bg-green-100 text-green-700 border border-green-300"
              : "bg-white text-gray-600 border border-gray-300 hover:bg-gray-50"
              }`}
          >
            <span className="mr-1.5">{option.icon}</span>
            {option.label}
          </motion.button>
        ))}
      </motion.div>

      {/* Content */}
      <div className="p-4">
        {isLoading ? (
          <div className="flex flex-col justify-center items-center py-20">
            <motion.div
              animate={{ rotate: 360 }}
              transition={{ repeat: Infinity, duration: 1, ease: "linear" }}
              className="h-12 w-12 rounded-full border-4 border-green-100 border-t-green-500"
            ></motion.div>
            <motion.p
              initial={{ opacity: 0 }}
              animate={{ opacity: 1 }}
              transition={{ delay: 0.5 }}
              className="mt-4 text-gray-500"
            >
              Đang tải thông báo...
            </motion.p>
          </div>
        ) : notifications.length === 0 ? (
          <motion.div
            initial={{ opacity: 0, scale: 0.9 }}
            animate={{ opacity: 1, scale: 1 }}
            transition={{ duration: 0.5 }}
            className="py-20 text-center"
          >
            <motion.div
              initial={{ y: -20 }}
              animate={{ y: [0, -10, 0] }}
              transition={{
                repeat: Infinity,
                duration: 2,
                repeatType: "reverse"
              }}
              className="mx-auto mb-4 bg-gray-100 rounded-full p-5 w-20 h-20 flex items-center justify-center"
            >
              <Bell size={40} className="text-gray-400" />
            </motion.div>
            <h3 className="text-xl font-semibold text-gray-700">Chưa có thông báo nào</h3>
            <p className="text-gray-500 mt-2">Bạn sẽ nhận được thông báo khi có cập nhật mới</p>
          </motion.div>
        ) : (
          <div className="overflow-y-auto max-h-[calc(100vh-300px)] pr-1 scrollbar-thin scrollbar-thumb-gray-300 scrollbar-track-gray-100">
            {renderNotificationGroup("Hôm nay", notificationGroups.today)}
            {renderNotificationGroup("Hôm qua", notificationGroups.yesterday)}
            {renderNotificationGroup("Trước đó", notificationGroups.older)}

            {/* Không tìm thấy kết quả khi lọc */}
            {activeFilter !== "all" &&
              !notificationGroups.today.length &&
              !notificationGroups.yesterday.length &&
              !notificationGroups.older.length && (
                <motion.div
                  initial={{ opacity: 0 }}
                  animate={{ opacity: 1 }}
                  className="py-12 text-center"
                >
                  <div className="mx-auto mb-4 bg-gray-100 rounded-full p-4 w-16 h-16 flex items-center justify-center">
                    <Filter size={24} className="text-gray-400" />
                  </div>
                  <p className="text-gray-500">Không tìm thấy thông báo nào phù hợp với bộ lọc</p>
                </motion.div>
              )}
          </div>
        )}
      </div>

      {/* Thêm CSS để tạo hiệu ứng pulse cho điểm tròn thông báo chưa đọc */}
      <style jsx>{`
        @keyframes pulse {
          0% {
            transform: scale(0.95);
            box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
          }
          
          70% {
            transform: scale(1);
            box-shadow: 0 0 0 6px rgba(16, 185, 129, 0);
          }
          
          100% {
            transform: scale(0.95);
            box-shadow: 0 0 0 0 rgba(16, 185, 129, 0);
          }
        }
        
        .pulse-animation {
          animation: pulse 2s infinite;
        }
      `}</style>
    </motion.div>
  );
};

export default NotificationsPage;
