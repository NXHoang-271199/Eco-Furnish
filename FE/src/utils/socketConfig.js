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

// Kiểm tra xem có đang kết nối không
export const isSocketConnected = () => {
  return socketInstance && socketInstance.connected;
};