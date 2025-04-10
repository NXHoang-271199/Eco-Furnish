import { io } from "socket.io-client";

let socketInstance = null;

// Lấy hoặc tạo kết nối socket
export const getSocket = () => {
  try {
    // Kiểm tra đăng nhập
    const token = localStorage.getItem("authToken");
    if (!token) {
      console.error("❌ Không thể kết nối socket: Không có token đăng nhập");
      return null;
    }

    // Nếu đã có kết nối, trả về kết nối đó
    if (socketInstance && socketInstance.connected) {
      console.log("✅ Sử dụng kết nối socket hiện có");
      return socketInstance;
    }

    // Nếu có kết nối cũ nhưng đang trong quá trình kết nối, đóng kết nối đó
    if (socketInstance) {
      console.log("⚠️ Đóng kết nối socket cũ đang trong quá trình kết nối");
      socketInstance.disconnect();
      socketInstance = null;
    }

    // Lấy thông tin người dùng
    const userDataString = localStorage.getItem("userData");
    const userData = userDataString ? JSON.parse(userDataString) : null;

    if (!userData) {
      console.error("❌ Không thể kết nối socket: Không có dữ liệu người dùng");
      return null;
    }

    console.log("🔌 Khởi tạo kết nối socket mới...");

    // Tạo ID phiên duy nhất để server có thể theo dõi trang/tab
    const sessionId = generateSessionId();

    // Khởi tạo socket mới với token
    socketInstance = io("http://localhost:3002", {
      transports: ["websocket", "polling"],
      auth: {
        token: token,
        userId: userData.id,
        role: userData.role,
        sessionId: sessionId // Thêm sessionId để theo dõi phiên
      },
      reconnection: true,
      reconnectionAttempts: 5,
      reconnectionDelay: 1000,
      // Tắt kết nối tự động để kiểm soát tốt hơn việc kết nối
      autoConnect: false,
      withCredentials: true // Thêm credentails để hỗ trợ CORS
    });

    // Thêm sự kiện trước khi kết nối
    socketInstance.on("disconnect", (reason) => {
      console.log("🔌 Socket bị ngắt kết nối, lý do:", reason);

      // Nếu ngắt kết nối do lỗi mạng, thử kết nối lại
      if (reason === "io server disconnect" || reason === "transport close") {
        console.log("⚠️ Ngắt kết nối do server hoặc mạng, thử kết nối lại sau 3 giây");
        setTimeout(() => {
          if (socketInstance) {
            socketInstance.connect();
          }
        }, 3000);
      }
    });

    // Kết nối socket sau khi đã thiết lập xong tất cả sự kiện
    socketInstance.connect();

    // Xử lý sự kiện connect
    socketInstance.on("connect", () => {
      console.log("🔌 Socket đã kết nối thành công!");
      console.log("👤 Thông tin người dùng:", {
        id: userData.id,
        name: userData.name,
        role: userData.role,
        sessionId: sessionId
      });

      // Tự động kết nối với vai trò tương ứng
      if (userData.role === 'admin') {
        console.log("🔑 Đăng ký kết nối với vai trò Admin");
        socketInstance.emit("adminConnect");
      } else {
        console.log("🔑 Đăng ký kết nối với vai trò Client");
        socketInstance.emit("clientConnect");
      }
    });

    // Lưu trữ ID thông báo đã nhận gần đây
    const recentNotifications = new Set();

    // Thêm sự kiện nhận thông báo đơn hàng
    socketInstance.on("order_status_notification", (data) => {
      console.log("📣 Nhận thông báo cập nhật trạng thái đơn hàng:", data);

      if (!data || !data.order_id || !data.order_code) {
        console.error("❌ Thông báo không hợp lệ:", data);
        return;
      }

      // Kiểm tra xem thông báo đã được xử lý chưa
      if (data.id && recentNotifications.has(data.id)) {
        console.log("⚠️ Thông báo đã được xử lý trước đó, bỏ qua:", data.id);
        return;
      }

      // Đảm bảo thông báo có đầy đủ thông tin trước khi sử dụng
      const enhancedData = {
        ...data,
        order_id: data.order_id,
        order_code: data.order_code || 'Không xác định',
        order_status: data.order_status || 'Không xác định',
        message: data.message || `Đơn hàng #${data.order_code || 'Không xác định'} đã chuyển sang trạng thái: ${data.order_status || 'Không xác định'}`,
        created_at: data.created_at || new Date().toISOString(),
        is_read: data.is_read || false
      };

      // Ghi log thông tin chi tiết để debug
      console.log("�� Chi tiết thông báo:", {
        id: enhancedData.id,
        order_id: enhancedData.order_id,
        order_code: enhancedData.order_code,
        order_status: enhancedData.order_status,
        message: enhancedData.message,
        created_at: enhancedData.created_at,
        is_read: enhancedData.is_read
      });

      // Thêm ID thông báo vào danh sách đã xử lý
      if (data.id) {
        recentNotifications.add(data.id);

        // Sau 10 giây, xóa khỏi danh sách để tránh danh sách quá lớn
        setTimeout(() => {
          recentNotifications.delete(data.id);
        }, 10000);
      }

      // Tạo một event để thông báo cho các component khác
      const notificationEvent = new CustomEvent('order-notification', {
        detail: enhancedData  // Sử dụng dữ liệu đã được đảm bảo
      });
      window.dispatchEvent(notificationEvent);
    });

    // Xử lý lỗi xác thực
    socketInstance.on("connect_error", (error) => {
      console.error("❌ Lỗi kết nối socket:", error.message);

      // Nếu lỗi xác thực, đóng kết nối
      if (error.message.includes("Authentication error")) {
        console.error("🔒 Lỗi xác thực socket - token không hợp lệ");
        closeSocket();

        // Xóa token và thông báo cho người dùng
        localStorage.removeItem("authToken");
        localStorage.removeItem("userData");

        // Phát sự kiện để thông báo đăng xuất
        window.dispatchEvent(new Event("auth-change"));
      }
    });

    return socketInstance;
  } catch (error) {
    console.error("❌ Lỗi khi khởi tạo socket:", error);
    return null;
  }
};

// Tạo ID phiên duy nhất
const generateSessionId = () => {
  // Tạo ID ngẫu nhiên kết hợp với timestamp
  return 'session_' + Math.random().toString(36).substring(2, 15) + '_' + Date.now();
};

// Đóng kết nối socket
export const closeSocket = () => {
  if (socketInstance) {
    console.log("🔌 Đóng kết nối socket");
    socketInstance.disconnect();
    socketInstance = null;
  }
};

// Khởi tạo socket mới
export const resetSocket = () => {
  closeSocket();
  return getSocket();
};

// Hàm xử lý sự kiện nhận thông báo từ server
export const subscribeToNotifications = (callback) => {
  const socketInstance = getSocket();
  if (socketInstance) {
    socketInstance.on("order_status_notification", callback);
  }
  return () => {
    if (socketInstance) {
      socketInstance.off("order_status_notification", callback);
    }
  };
};

// Hàm đánh dấu thông báo đã đọc
export const markNotificationAsRead = (notificationId) => {
  const socketInstance = getSocket();
  if (socketInstance) {
    return new Promise((resolve, reject) => {
      socketInstance.emit("markNotificationAsRead", { id: notificationId }, (response) => {
        if (response.success) {
          resolve(response);
        } else {
          reject(new Error(response.error || "Không thể đánh dấu thông báo là đã đọc"));
        }
      });
    });
  }
  return Promise.reject(new Error("Không có kết nối socket"));
};

// Kiểm tra xem có đang kết nối không
export const isSocketConnected = () => {
  return socketInstance && socketInstance.connected;
};