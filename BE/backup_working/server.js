require('dotenv').config();
const express = require("express");
const http = require("http");
const socketIo = require("socket.io");
<<<<<<< HEAD
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
=======
const cors = require("cors");
const axios = require("axios");
const jwt = require("jsonwebtoken");

const app = express();
const server = http.createServer(app);
const io = socketIo(server, {
    cors: {
        origin: ["http://localhost:3000", "http://localhost:5173", "http://127.0.0.1:5173", "http://localhost"],
        methods: ["GET", "POST"],
        allowedHeaders: ["Authorization", "Content-Type"],
        credentials: true
    }
});

// Middleware
app.use(cors());
app.use(express.json());

// Lưu trữ danh sách user & admin
const users = new Map();
const admins = new Map();
>>>>>>> 2e14406b3e596a8478022de3f9eb13f92e5eddb7

// Biến lưu trữ danh sách người dùng online
const onlineUsers = new Map();

// Route test
app.get("/", (req, res) => {
    res.send("🚀 Socket.IO Server is running!");
});

<<<<<<< HEAD
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
=======
// Xác thực middleware - xử lý token
io.use((socket, next) => {
    try {
        // Lấy token từ header hoặc query parameters
        const token =
            socket.handshake.headers.authorization?.split(' ')[1] ||
            socket.handshake.query.token;

        console.log("🔒 Đang xác thực token:", token ? token.substring(0, 10) + "..." : "undefined");

        if (!token) {
            console.log("❌ Không tìm thấy token");
            return next(new Error("Authentication error: Token not provided"));
        }

        // Xác thực token với JWT
        jwt.verify(token, process.env.JWT_SECRET || 'your-secret-key', (err, decoded) => {
            if (err) {
                console.log("❌ Token không hợp lệ:", err.message);
                return next(new Error("Authentication error: Invalid token"));
            }

            // Lưu thông tin user vào socket để sử dụng sau này
            socket.user = decoded;
            console.log("✅ Xác thực token thành công cho user:", decoded.sub);
            next();
        });
    } catch (error) {
        console.error("❌ Lỗi xác thực socket:", error.message);
        next(new Error("Authentication error: " + error.message));
    }
});

// Hàm cập nhật danh sách người dùng online
function updateOnlineUsers() {
    const onlineUsersList = [];

    // Duyệt qua tất cả các socket đang kết nối
    for (const [id, socket] of io.of("/").sockets) {
        if (socket.userData) {
            onlineUsersList.push({
                socketId: id,
                userId: socket.userData.id,
                userName: socket.userData.name,
                userEmail: socket.userData.email,
                connectedAt: socket.userData.connectedAt || new Date(),
                isAdmin: admins.has(id)
            });
        }
    }

    // Gửi danh sách người dùng online cho admin
    io.to("admin").emit("onlineUsers", onlineUsersList);

    console.log(`👥 Danh sách người dùng online: ${onlineUsersList.length} người`);
}

// Xử lý kết nối socket
io.on("connection", async (socket) => {
    console.log(`🟢 Client connected: ${socket.id}`);

    // Gửi thông báo kết nối thành công
    socket.emit("connectionSuccess", {
        message: "Socket connection established successfully",
        socketId: socket.id,
        userId: socket.user?.sub
    });

    // Thiết lập ping mỗi 30 giây để duy trì kết nối
    const pingInterval = setInterval(() => {
        socket.emit("ping", { time: new Date().toISOString() });
    }, 30000);

    // Xử lý khi client gửi thông tin kết nối
    socket.on("clientConnect", (userData) => {
        if (!userData || !userData.id) {
            console.log("❌ Thiếu thông tin người dùng trong clientConnect");
            socket.emit("authenticationError", { message: "Missing user data" });
            return;
        }

        console.log(`📝 Client info received: ${userData.name} (${userData.id})`);

        // Lưu thông tin user vào socket
        socket.userData = {
            ...userData,
            socketId: socket.id
        };

        // Join room riêng cho user này
        socket.join(`user:${userData.id}`);

        // Cập nhật danh sách người dùng online
        updateOnlineUsers();
>>>>>>> 2e14406b3e596a8478022de3f9eb13f92e5eddb7
    });

    /**
     * 📌 Admin kết nối
     */
<<<<<<< HEAD
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
=======
    socket.on("adminConnect", (adminData) => {
        // Kiểm tra admin có đủ thông tin không
        if (!adminData || !adminData.name) {
            console.log("❌ Admin connection rejected: Thiếu thông tin xác thực");
            socket.emit("authenticationError", {
                message: "Bạn cần đăng nhập với quyền admin để sử dụng tính năng này"
            });
            return;
        }

        admins.set(socket.id, { id: socket.id, ...adminData });
        socket.join("admin"); // Admin vào room "admin"
>>>>>>> 2e14406b3e596a8478022de3f9eb13f92e5eddb7

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
    socket.on("clientMessage", (messageData, callback) => {
        try {
<<<<<<< HEAD
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
=======
            console.log(`📨 Nhận tin nhắn từ client ${socket.id}:`, messageData.text);

            if (!socket.userData) {
                console.log("❌ Client chưa xác thực, từ chối tin nhắn");
                if (callback) callback({ success: false, error: "Unauthorized" });
                return;
            }

            // Lưu tin nhắn với thông tin người dùng
            const message = {
                ...messageData,
                userId: socket.userData.id,
                userName: socket.userData.name,
                timestamp: new Date().toLocaleTimeString(),
                id: generateMessageId(),
                read: false
            };

            // Lưu tin nhắn vào cơ sở dữ liệu (bạn có thể thêm code ở đây)

            // Gửi tin nhắn cho tất cả admin đang online
            io.to("admin").emit("newClientMessage", message);

            // Phản hồi cho client nếu có callback
            if (callback) callback({ success: true, messageId: message.id });

>>>>>>> 2e14406b3e596a8478022de3f9eb13f92e5eddb7
        } catch (error) {
            console.error("❌ Lỗi khi xử lý tin nhắn client:", error.message);
            if (callback) callback({ success: false, error: error.message });
        }
    });

    /**
     * 📌 Xử lý tin nhắn từ Admin gửi đến Client
     */
    socket.on("adminResponse", (responseData, callback) => {
        try {
<<<<<<< HEAD
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
                senderName: socket.user.name
            };

            io.to(`user_${data.userId}`).emit("adminResponse", messageForClient);
            console.log(`📣 Đã gửi tin nhắn đến user_${data.userId}`);

            callback({ success: true, message: savedMessage });
=======
            console.log(`📨 Nhận phản hồi từ admin ${socket.id}:`, responseData);

            const { clientId, message } = responseData;

            if (!clientId) {
                console.log("❌ Thiếu ID client để gửi phản hồi");
                if (callback) callback({ success: false, error: "Missing client ID" });
                return;
            }

            // Tạo đối tượng phản hồi
            const response = {
                text: message,
                sender: "admin",
                adminId: socket.id,
                adminName: socket.userData?.name || "Admin",
                timestamp: new Date().toLocaleTimeString(),
                id: generateMessageId()
            };

            // Gửi phản hồi cho client cụ thể
            io.to(`user:${clientId}`).emit("adminResponse", response);

            // Phản hồi cho admin
            if (callback) callback({ success: true, messageId: response.id });

>>>>>>> 2e14406b3e596a8478022de3f9eb13f92e5eddb7
        } catch (error) {
            console.error("❌ Lỗi khi xử lý phản hồi từ admin:", error.message);
            if (callback) callback({ success: false, error: error.message });
        }
    });

    // Dọn dẹp khi client ngắt kết nối
    socket.on("disconnect", () => {
<<<<<<< HEAD
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
=======
        console.log(`🔴 Client disconnected: ${socket.id}`);
        clearInterval(pingInterval);

        // Cập nhật danh sách người dùng online
        updateOnlineUsers();
    });
});

/**
 * 📌 Hàm lưu tin nhắn vào database (Laravel API)
 */
async function saveMessageToDatabase(messageData) {
    try {
        // Gọi API để lưu tin nhắn
        const apiUrl = "http://127.0.0.1:8000/api/messages";
        const response = await axios.post(apiUrl, {
            user_id: messageData.userRealId || messageData.userId,
            sender_type: messageData.type,
            text: messageData.text,
        });

        console.log("✅ Tin nhắn đã được lưu vào database");
        return response.data;
    } catch (error) {
        console.error("❌ Lỗi khi lưu tin nhắn vào database:", error.message);
        throw error;
    }
}

// Hàm tạo ID tin nhắn ngẫu nhiên
function generateMessageId() {
    return Date.now().toString(36) + Math.random().toString(36).substr(2, 5);
}

// Chạy server
const PORT = 3001;
server.listen(PORT, () => {
    console.log(`🚀 Server is running on http://127.0.0.1:${PORT}`);
});

// API routes
app.post("/send-bot-message", (req, res) => {
    try {
        const { socket_id, reply, has_products, products } = req.body;

        if (socket_id && io.sockets.adapter.rooms.has(socket_id)) {
            io.to(socket_id).emit("botResponse", {
                text: reply,
                has_products,
                products,
                sent_at: new Date(),
                sender: "bot"
            });

            res.json({ success: true, message: "Bot message sent successfully" });
        } else {
            res.status(404).json({ success: false, message: "Client not connected" });
        }
    } catch (error) {
        console.error("❌ Error sending bot message:", error);
        res.status(500).json({ success: false, message: error.message });
    }
});
>>>>>>> 2e14406b3e596a8478022de3f9eb13f92e5eddb7
