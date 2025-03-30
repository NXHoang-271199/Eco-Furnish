require('dotenv').config();
const express = require("express");
const http = require("http");
const socketIo = require("socket.io");
const fetch = require("node-fetch");
const jwt = require("jsonwebtoken");

// Các cài đặt từ biến môi trường
const API_URL = process.env.API_URL || 'http://127.0.0.1:8000';
// Sử dụng cổng 3002 cố định thay vì đọc từ biến môi trường
const SOCKET_PORT = 3002;

console.log('📌 Cài đặt cổng socket:', {
    'SOCKET_PORT env': process.env.SOCKET_PORT,
    'Cổng được sử dụng (cố định)': SOCKET_PORT
});

const app = express();
const server = http.createServer(app);

// Thêm xử lý lỗi cho server
server.on('error', (error) => {
    if (error.code === 'EADDRINUSE') {
        console.error(`❌ Cổng ${SOCKET_PORT} đã được sử dụng. Vui lòng thay đổi cổng trong biến môi trường.`);
        process.exit(1);
    } else {
        console.error('❌ Lỗi server:', error);
    }
});

const io = socketIo(server, {
    cors: {
        origin: "*",
        methods: ["GET", "POST"],
        credentials: true
    }
});

// Lưu trữ danh sách user & admin đang online
const onlineUsers = new Map();
const onlineAdmins = new Map();

// Route test
app.get("/", (req, res) => {
    res.send("🚀 Socket.IO Server is running!");
});

// Middleware xác thực token
const authenticateToken = async (socket, next) => {
    try {
        const token = socket.handshake.auth.token;
        if (!token) {
            console.error("❌ Token không được cung cấp");
            return next(new Error("Authentication error: Token not provided"));
        }

        console.log("🔑 Đang xác thực token...");

        // Xác định origin của kết nối
        let origin = socket.handshake.headers.origin || '';

        // Force role là admin nếu role được gửi trong auth
        const requestedRole = socket.handshake.auth.role || '';
        if (requestedRole === 'admin') {
            origin = 'admin'; // Force origin để API trả về role admin
            console.log("⚙️ Phát hiện yêu cầu quyền admin, force origin:", origin);
        }

        // Kiểm tra token với API Laravel
        const response = await fetch(`${API_URL}/api/auth/verify-token`, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "Accept": "application/json",
                "Authorization": `Bearer ${token}`,
                "Origin": origin
            }
        });

        // Log thông tin response để debug
        console.log("🔍 Phản hồi từ API xác thực:", {
            status: response.status,
            ok: response.ok,
            statusText: response.statusText
        });

        if (!response.ok) {
            const errorText = await response.text();
            console.error("❌ Lỗi xác thực từ API:", errorText);
            return next(new Error(`Authentication error: Invalid token (${response.status})`));
        }

        const result = await response.json();
        console.log("✅ Xác thực thành công cho:", result.user.name);

        // Lưu thông tin user vào socket
        socket.user = result.user;

        // Thêm kiểm tra cơ sở dữ liệu để xác định admin
        try {
            // Lấy thông tin user từ API
            const userResponse = await fetch(`${API_URL}/api/users/${result.user.id}`, {
                method: "GET",
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json",
                    "Authorization": `Bearer ${token}`
                }
            });

            if (userResponse.ok) {
                const userData = await userResponse.json();
                if (userData && (userData.role_id === 1 || userData.id === 1)) {
                    socket.user.role = 'admin';
                    socket.user.role_id = userData.role_id;
                    console.log(`🔒 Xác nhận quyền admin từ database cho user ${result.user.name}`);
                }
            }
        } catch (userError) {
            console.warn("⚠️ Không thể lấy thêm thông tin người dùng:", userError.message);
        }

        next();
    } catch (error) {
        console.error("❌ Lỗi xác thực:", error.message);
        next(new Error(`Authentication error: ${error.message}`));
    }
};

// Áp dụng middleware xác thực cho tất cả kết nối
io.use(authenticateToken);

// Xử lý kết nối socket
io.on("connection", (socket) => {
    console.log(`🔗 User connected: ${socket.id}`);
    console.log(`🧑‍💼 User data:`, {
        id: socket.user.id,
        name: socket.user.name,
        role: socket.user.role
    });

    // Chuẩn hóa user role
    let normalizedRole = '';
    // Xử lý nếu role của user là object thay vì string
    if (typeof socket.user.role === 'object') {
        console.log("⚠️ Phát hiện role là object, chuyển đổi thành string");
        // Chuyển đổi role từ object thành string
        if (socket.user.role && socket.user.role.name === 'admin') {
            normalizedRole = 'admin';
            console.log("✅ Đã chuyển đổi role thành: 'admin'");
        } else {
            normalizedRole = 'user';
            console.log("✅ Đã chuyển đổi role thành: 'user'");
        }
    } else {
        normalizedRole = socket.user.role;
    }

    // Gán lại role đã chuẩn hóa
    socket.user.role = normalizedRole;

    // QUAN TRỌNG: Nếu người dùng có ID = 1, coi như họ là admin
    if (socket.user.id === 1 || socket.user.role_id === 1) {
        socket.user.role = 'admin';
        console.log(`🔑 Đặc biệt: User ID ${socket.user.id} được gán quyền admin tự động`);
    }

    console.log(`🔗 Role đã chuẩn hóa: ${socket.user.role}`);

    /**
     * 📌 Client kết nối
     */
    socket.on("clientConnect", () => {
        console.log(`📣 Nhận sự kiện clientConnect từ socket ${socket.id}`);
        console.log(`📊 Thông tin user:`, socket.user);

        // Chỉ lưu người dùng không phải admin
        if (socket.user.role !== 'admin') {
            const userData = {
                socketId: socket.id,
                userId: socket.user.id,
                name: socket.user.name,
                avatar: socket.user.avatar || null
            };

            onlineUsers.set(socket.id, userData);
            socket.join(`user_${socket.user.id}`); // Room riêng cho user

            // Gửi danh sách client mới cho admin
            console.log(`📣 Gửi thông báo newClientConnected đến admin_room:`, userData);
            io.to("admin_room").emit("newClientConnected", userData);

            console.log(`👤 Client ${socket.id} (${socket.user.name}) đã kết nối và được đăng ký với ID ${socket.user.id}`);
            console.log(`📊 Tổng số client online hiện tại: ${onlineUsers.size}`);

            // Log danh sách người dùng online để debug
            console.log(`📊 Danh sách người dùng online:`, Array.from(onlineUsers.values()));
        } else {
            console.log(`⚠️ User ${socket.user.name} (${socket.id}) là admin, không được đăng ký làm client`);
        }
    });

    /**
     * 📌 Admin kết nối
     */
    socket.on("adminConnect", (data, callback) => {
        console.log(`📣 Nhận sự kiện adminConnect từ socket ${socket.id}`);

        // Kiểm tra xem người dùng có phải là admin không
        if (socket.user.role !== 'admin') {
            console.error(`⚠️ User ${socket.user.name} (${socket.id}) cố đăng ký làm admin nhưng không có quyền`);
            socket.emit("error", { message: "Unauthorized: Admin role required" });
            if (callback) callback({ success: false, error: "Unauthorized: Admin role required" });
            return;
        }

        // Log chi tiết vai trò admin
        console.log(`👨‍💼 Xác nhận admin ${socket.id} (${socket.user.name}), role:`, socket.user.role);

        const adminData = {
            socketId: socket.id,
            userId: socket.user.id,
            name: socket.user.name
        };

        onlineAdmins.set(socket.id, adminData);
        socket.join("admin_room"); // Admin vào room "admin_room"

        // Gửi danh sách user hiện tại cho admin
        const currentUsers = Array.from(onlineUsers.values());
        socket.emit("currentUsers", currentUsers);
        console.log(`📝 Gửi danh sách ${currentUsers.length} người dùng online cho admin:`, currentUsers);
        console.log(`📊 Tổng số admin online hiện tại: ${onlineAdmins.size}`);

        console.log(`👨‍💼 Admin ${socket.id} (${socket.user.name}) đã kết nối và tham gia phòng admin_room`);

        if (callback) callback({ success: true });
    });

    /**
     * 📌 Xử lý tin nhắn từ Client gửi đến Admin
     */
    socket.on("clientMessage", async (data, callback = () => {}) => {
        try {
            // Kiểm tra xem người dùng có tồn tại không
            if (socket.user.role === 'admin') {
                return callback({ success: false, error: "Admin không thể gửi tin nhắn như client" });
            }

            console.log(`📨 Nhận tin nhắn từ client: ${socket.user.name} (${socket.id}):`, data.text);

            // Tạo dữ liệu tin nhắn để lưu vào DB
            const messageData = {
                text: data.text,
                sender_id: socket.user.id,
                receiver_id: null, // Mặc định là null, admin sẽ nhận
                sent_at: new Date()
            };

            // Gọi API để lưu tin nhắn
            const response = await fetch(`${API_URL}/api/messages`, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json",
                    "Authorization": `Bearer ${socket.handshake.auth.token}`
                },
                body: JSON.stringify(messageData)
            });

            if (!response.ok) {
                const error = await response.json();
                throw new Error(error.message || "Không thể lưu tin nhắn");
            }

            const savedMessage = await response.json();
            console.log("✅ Tin nhắn đã được lưu vào DB:", savedMessage.id);

            // Chuẩn bị dữ liệu tin nhắn để gửi cho admin
            const messageForAdmin = {
                ...savedMessage,
                senderName: socket.user.name
            };

            // Gửi tin nhắn đến tất cả admin
            console.log(`📣 Phát tin nhắn đến phòng admin_room. Admins online: ${onlineAdmins.size}`);
            io.to("admin_room").emit("newClientMessage", messageForAdmin);

            callback({ success: true, message: savedMessage });
        } catch (error) {
            console.error("❌ Lỗi khi xử lý clientMessage:", error);
            callback({ success: false, error: error.message });
        }
    });

    /**
     * 📌 Xử lý tin nhắn từ Admin gửi đến Client
     */
    socket.on("adminMessage", async (data, callback = () => {}) => {
        try {
            // Kiểm tra xem người dùng có phải là admin không
            if (socket.user.role !== 'admin') {
                console.error(`⚠️ User ${socket.user.name} (${socket.id}) cố gửi tin nhắn admin nhưng không có quyền`);
                return callback({ success: false, error: "Unauthorized: Admin role required" });
            }

            if (!data.userId) {
                console.error("❌ Thiếu userId của người nhận trong adminMessage");
                return callback({ success: false, error: "Thiếu userId của người nhận" });
            }

            console.log(`📨 Admin ${socket.user.name} gửi tin nhắn đến user ${data.userId}:`, data.text);

            // Tạo dữ liệu tin nhắn để lưu vào DB
            const messageData = {
                text: data.text,
                sender_id: socket.user.id,
                receiver_id: data.userId,
                sent_at: new Date()
            };

            // Gọi API để lưu tin nhắn
            const response = await fetch(`${API_URL}/api/messages`, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json",
                    "Authorization": `Bearer ${socket.handshake.auth.token}`
                },
                body: JSON.stringify(messageData)
            });

            if (!response.ok) {
                const error = await response.json();
                throw new Error(error.message || "Không thể lưu tin nhắn");
            }

            const savedMessage = await response.json();
            console.log("✅ Tin nhắn admin đã được lưu vào DB:", savedMessage.id);

            // Gửi tin nhắn đến client cụ thể
            const messageForClient = {
                ...savedMessage,
                senderName: socket.user.name,
                is_read: false // Thêm trạng thái chưa đọc
            };

            io.to(`user_${data.userId}`).emit("adminResponse", messageForClient);
            console.log(`📣 Đã gửi tin nhắn đến user_${data.userId}`);

            callback({ success: true, message: savedMessage });
        } catch (error) {
            console.error("❌ Lỗi khi xử lý adminMessage:", error);
            callback({ success: false, error: error.message });
        }
    });

    /**
     * 📌 Xử lý khi Client hoặc Admin ngắt kết nối
     */
    socket.on("disconnect", () => {
        if (onlineUsers.has(socket.id)) {
            const userData = onlineUsers.get(socket.id);
            io.to("admin_room").emit("clientDisconnected", { userId: userData.userId });
            onlineUsers.delete(socket.id);
            console.log(`👤 Client ${socket.id} (${userData.name}) disconnected`);
            console.log(`📊 Tổng số client online còn lại: ${onlineUsers.size}`);
        }

        if (onlineAdmins.has(socket.id)) {
            onlineAdmins.delete(socket.id);
            console.log(`👨‍💼 Admin ${socket.id} (${socket.user.name}) disconnected`);
            console.log(`📊 Tổng số admin online còn lại: ${onlineAdmins.size}`);
        }
    });

    // Thêm event listener mới cho sự kiện đánh dấu đã đọc
    socket.on("markMessagesAsRead", async (data, callback = () => {}) => {
        try {
            // Kiểm tra người dùng
            if (!data.userId) {
                return callback({ success: false, error: "Thiếu userId" });
            }

            console.log(`📬 Đánh dấu tin nhắn đã đọc cho user ${data.userId}`);

            // Gọi API để đánh dấu tin nhắn đã đọc
            const response = await fetch(`${API_URL}/api/messages/read-all/${data.userId}`, {
                method: "PATCH",
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json",
                    "Authorization": `Bearer ${socket.handshake.auth.token}`
                }
            });

            if (!response.ok) {
                throw new Error(`API error: ${response.status}`);
            }

            // Thông báo cho admin biết rằng tin nhắn đã được đọc
            io.to("admin_room").emit("messagesRead", { userId: data.userId });
            console.log(`📣 Đã thông báo admin rằng tin nhắn của user ${data.userId} đã được đọc`);

            callback({ success: true });
        } catch (error) {
            console.error("❌ Lỗi khi đánh dấu tin nhắn đã đọc:", error);
            callback({ success: false, error: error.message });
        }
    });
});

// Khởi động server
try {
    server.listen(SOCKET_PORT, () => {
        console.log(`🚀 Socket.IO Server đang chạy trên cổng ${SOCKET_PORT}`);
    });
} catch (error) {
    console.error('❌ Không thể khởi động server:', error);
    process.exit(1);
}
