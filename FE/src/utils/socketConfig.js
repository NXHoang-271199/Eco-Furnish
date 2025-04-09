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
    socketInstance = io("http://localhost:3001", {
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
      timeout: 1000, // Tăng timeout lên 10 giây
      // Tắt kết nối tự động để kiểm soát tốt hơn việc kết nối
      autoConnect: false,
      withCredentials: true, // Thêm credentails để hỗ trợ CORS
      forceNew: true, // Buộc tạo kết nối mới, tránh xung đột với kết nối có sẵn
      // Ấn định namespace và path để tránh xung đột với Vite HMR
      path: "/socket.io/"
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
  try {
    // Đóng kết nối cũ một cách đúng đắn
    closeSocket();
    
    // Chờ 300ms để đảm bảo kết nối cũ được đóng hoàn toàn
    console.log("🔄 Reset Socket: Khởi tạo lại socket...");
    
    // Tạo socket mới
    const newSocket = getSocket();
    
    // Đảm bảo socket kết nối ngay lập tức
    if (newSocket) {
      console.log("🔄 Reset Socket: Đảm bảo kết nối được thiết lập ngay lập tức");
      
      // Nếu socket chưa kết nối, gọi connect một cách tường minh
      if (!newSocket.connected) {
        console.log("🔄 Reset Socket: Gọi connect() vì socket chưa kết nối");
        newSocket.connect();
        
        // Log trạng thái kết nối sau một khoảng thời gian
        setTimeout(() => {
          if (newSocket.connected) {
            console.log("✅ Trạng thái kết nối sau 1s: Đã kết nối thành công");
          } else {
            console.log("⚠️ Trạng thái kết nối sau 1s: Vẫn đang kết nối...");
            
            // Kiểm tra lại sau 3 giây
            setTimeout(() => {
              console.log("🔍 Kiểm tra lại trạng thái kết nối sau 3s:", 
                newSocket.connected ? "đã kết nối" : "vẫn chưa kết nối");
              
              // Thử kết nối lại nếu vẫn chưa thành công
              if (!newSocket.connected) {
                console.log("🔄 Thử kết nối lại sau 3s không thành công");
                newSocket.connect();
              }
            }, 3000);
          }
        }, 1000);
      } else {
        console.log("✅ Socket đã kết nối sẵn, không cần gọi connect()");
      }
    } else {
      console.error("❌ Không thể khởi tạo socket mới trong resetSocket()");
    }
    
    return newSocket;
  } catch (error) {
    console.error("❌ Lỗi trong resetSocket():", error);
    return null;
  }
};

// Kiểm tra xem có đang kết nối không
export const isSocketConnected = () => {
  return socketInstance && socketInstance.connected;
};