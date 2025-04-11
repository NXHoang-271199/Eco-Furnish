import { io } from "socket.io-client";
import axios from "axios";

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

    // Nếu đã có kết nối và đã kết nối, trả về kết nối đó
    if (socketInstance && socketInstance.connected) {
      console.log("✅ Sử dụng kết nối socket hiện có:", socketInstance.id);

      // Phát sự kiện để thông báo cho các component khác về trạng thái kết nối
      window.dispatchEvent(new CustomEvent('socket-connected', { detail: true }));

      return socketInstance;
    }

    // Gửi sự kiện đang kết nối nếu chưa có hoặc đang kết nối
    window.dispatchEvent(new CustomEvent('socket-connecting'));

    // Nếu có kết nối cũ nhưng đang trong quá trình kết nối, kiểm tra thời gian kết nối
    if (socketInstance) {
      // Nếu socket đã tồn tại nhưng không kết nối, thử kết nối lại nếu chưa kết nối
      if (!socketInstance.connected && !socketInstance.connecting) {
        console.log("🔄 Thử kết nối lại socket...");
        socketInstance.connect();
        return socketInstance;
      } else if (socketInstance.connecting) {
        console.log("⏳ Socket đang kết nối, chờ đợi...");
        return socketInstance;
      } else {
        console.log("⚠️ Đóng kết nối socket cũ và tạo kết nối mới");
        socketInstance.disconnect();
        socketInstance = null;
      }
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
      reconnectionAttempts: 10, // Tăng số lần thử kết nối lại
      reconnectionDelay: 1000,
      timeout: 10000, // Giảm thời gian timeout để phát hiện lỗi kết nối sớm hơn
      withCredentials: true
    });

    // Thêm sự kiện trước khi kết nối
    socketInstance.on("disconnect", (reason) => {
      console.log("🔌 Socket bị ngắt kết nối, lý do:", reason);

      // Phát sự kiện để thông báo cho các component khác
      window.dispatchEvent(new CustomEvent('socket-disconnected', {
        detail: { reason: reason }
      }));

      // Nếu ngắt kết nối do lỗi mạng, thử kết nối lại
      if (reason === "io server disconnect" || reason === "transport close" || reason === "ping timeout") {
        console.log("⚠️ Ngắt kết nối do server hoặc mạng, thử kết nối lại sau 2 giây");
        setTimeout(() => {
          if (socketInstance) {
            console.log("🔄 Thử kết nối lại sau ngắt kết nối");
            socketInstance.connect();

            // Phát sự kiện đang kết nối lại
            window.dispatchEvent(new CustomEvent('socket-connecting'));
          }
        }, 2000);
      }
    });

    // Xử lý sự kiện connect
    socketInstance.on("connect", () => {
      console.log("🔌 Socket đã kết nối thành công:", socketInstance.id);
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
        socketInstance.emit("clientConnect", {}, response => {
          console.log("✅ Phản hồi từ sự kiện clientConnect:", response);
        });
      }

      // Phát sự kiện để thông báo cho các component khác
      window.dispatchEvent(new CustomEvent('socket-connected', { detail: true }));
    });

    // Gửi ping định kỳ để kiểm tra kết nối
    let pingInterval;

    socketInstance.on("connect", () => {
      // Thiết lập ping định kỳ sau khi kết nối thành công
      pingInterval = setInterval(() => {
        if (socketInstance && socketInstance.connected) {
          socketInstance.emit("ping", { timestamp: Date.now() }, (response) => {
            if (response && response.success) {
              console.log("🏓 Ping thành công");
            }
          });
        }
      }, 30000); // 30 giây ping một lần
    });

    socketInstance.on("disconnect", () => {
      // Xóa interval ping khi ngắt kết nối
      if (pingInterval) {
        clearInterval(pingInterval);
      }
    });

    // Xử lý lỗi xác thực
    socketInstance.on("connect_error", (error) => {
      console.error("❌ Lỗi kết nối socket:", error.message);

      // Phát sự kiện để thông báo cho các component khác
      window.dispatchEvent(new CustomEvent('socket-error', {
        detail: { message: error.message }
      }));

      // Nếu lỗi xác thực, thử làm mới token trước khi đóng kết nối
      if (error.message.includes("Authentication error") || error.message.includes("jwt") || error.message.includes("Invalid token")) {
        console.error("🔒 Lỗi xác thực socket - token có thể không hợp lệ hoặc hết hạn");

        // Thử làm mới token
        tryRefreshToken()
          .then(success => {
            if (success) {
              console.log("🔄 Token đã được làm mới, thử kết nối lại socket");
              if (socketInstance) {
                socketInstance.disconnect();
                socketInstance = null;
              }
              // Phát sự kiện thông báo token đã được làm mới
              window.dispatchEvent(new Event("auth-change"));
            } else {
              console.error("🔒 Không thể làm mới token, đóng kết nối");
              closeSocket();
              // Xóa token và thông báo cho người dùng
              localStorage.removeItem("authToken");
              localStorage.removeItem("refreshToken");
              localStorage.removeItem("userData");
              // Phát sự kiện để thông báo đăng xuất
              window.dispatchEvent(new Event("auth-change"));
            }
          });
      } else {
        // Thử kết nối lại nếu là lỗi mạng
        setTimeout(() => {
          if (socketInstance) {
            console.log("🔄 Thử kết nối lại sau lỗi:", error.message);
            // Phát sự kiện đang kết nối lại
            window.dispatchEvent(new CustomEvent('socket-connecting'));
            // Thử kết nối lại
            socketInstance.connect();
          }
        }, 3000);
      }
    });

    return socketInstance;
  } catch (error) {
    console.error("❌ Lỗi khi khởi tạo socket:", error);

    // Phát sự kiện để thông báo cho các component khác
    window.dispatchEvent(new CustomEvent('socket-error', {
      detail: { message: error.message }
    }));

    return null;
  }
};

// Hàm thử làm mới token
const tryRefreshToken = async () => {
  try {
    console.log("🔄 Đang thử làm mới token...");

    const refreshToken = localStorage.getItem("refreshToken");
    if (!refreshToken) {
      console.error("❌ Không tìm thấy refresh token trong localStorage");
      return false;
    }

    // Gọi API làm mới token
    const response = await axios.post("http://localhost:8000/api/users/refresh-token", {
      refresh_token: refreshToken
    });

    if (response.data.status === "success") {
      // Lưu token mới vào localStorage
      const newToken = response.data.data.access_token;
      const newRefreshToken = response.data.data.refresh_token;

      localStorage.setItem("authToken", newToken);
      localStorage.setItem("refreshToken", newRefreshToken);

      console.log("✅ Làm mới token thành công");
      return true;
    } else {
      console.error("❌ Làm mới token không thành công:", response.data);
      return false;
    }
  } catch (error) {
    console.error("❌ Lỗi khi làm mới token:", error.message);
    return false;
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