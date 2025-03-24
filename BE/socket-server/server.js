const express = require("express");
const http = require("http");
const socketIo = require("socket.io");
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

// Biến lưu trữ danh sách người dùng online
const onlineUsers = new Map();

// Route test
app.get("/", (req, res) => {
    res.send("🚀 Socket.IO Server is running!");
});

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
    });

    /**
     * 📌 Admin kết nối
     */
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

        // Gửi danh sách user hiện tại cho admin
        socket.emit("currentUsers", Array.from(users.values()));

        console.log(`👨‍💼 Admin ${socket.id} connected`);
    });

    /**
     * 📌 Xử lý tin nhắn từ Client gửi đến Admin
     */
    socket.on("clientMessage", (messageData, callback) => {
        try {
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

        } catch (error) {
            console.error("❌ Lỗi khi xử lý phản hồi từ admin:", error.message);
            if (callback) callback({ success: false, error: error.message });
        }
    });

    // Dọn dẹp khi client ngắt kết nối
    socket.on("disconnect", () => {
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
