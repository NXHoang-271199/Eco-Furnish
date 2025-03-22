const express = require("express");
const http = require("http");
const socketIo = require("socket.io");
const fetch = require("node-fetch");

const app = express();
const server = http.createServer(app);
const io = socketIo(server, {
    cors: {
        origin: ["http://localhost:5173", "http://127.0.0.1:5173"],
        methods: ["GET", "POST"]
    }
});

// Lưu trữ danh sách user & admin
const users = new Map();
const admins = new Map();

// Route test
app.get("/", (req, res) => {
    res.send("🚀 Socket.IO Server is running!");
});

// Xử lý kết nối socket
io.on("connection", (socket) => {
    console.log(`🔗 User connected: ${socket.id}`);

    /**
     * 📌 Client kết nối
     */
    socket.on("clientConnect", (userData) => {
        users.set(socket.id, { id: socket.id, ...userData });
        socket.join(socket.id); // Mỗi client có một room riêng

        // Gửi danh sách client mới cho admin
        io.to("admin").emit("newClientConnected", {
            id: socket.id,
            ...userData
        });

        console.log(`👤 Client ${socket.id} connected`);
    });

    /**
     * 📌 Admin kết nối
     */
    socket.on("adminConnect", (adminData) => {
        admins.set(socket.id, { id: socket.id, ...adminData });
        socket.join("admin"); // Admin vào room "admin"

        // Gửi danh sách user hiện tại cho admin
        socket.emit("currentUsers", Array.from(users.values()));

        console.log(`👨‍💼 Admin ${socket.id} connected`);
    });

    /**
     * 📌 Xử lý tin nhắn từ Client gửi đến Admin
     */
    socket.on("clientMessage", async (data, callback = () => {}) => {
        try {
            const user = users.get(socket.id);
            if (!user) {
                return callback({ success: false, error: "Client not found" });
            }

            const messageData = {
                text: data.text,
                userId: socket.id,
                userName: user.name || "Anonymous",
                sent_at: new Date(),
                type: "client"
            };

            await saveMessageToDatabase(messageData);

            io.to("admin").emit("newClientMessage", messageData);

            callback({ success: true });
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
            console.log("📩 Admin gửi tin nhắn:", data);
            const admin = admins.get(socket.id);
            if (!admin) return callback({ success: false, error: "Admin không tồn tại" });

            if (!data.userId) return callback({ success: false, error: "Thiếu userId" });

            const messageData = {
                text: data.text,
                adminId: socket.id,
                adminName: admin.name || "Admin",
                sent_at: new Date(),
                type: "admin"
            };

            await saveMessageToDatabase(messageData);

            console.log("📤 Gửi tin nhắn đến client:", data.userId);
            io.to(data.userId).emit("adminResponse", messageData);

            callback({ success: true });
        } catch (error) {
            console.error("❌ Lỗi khi xử lý adminMessage:", error);
            callback({ success: false, error: error.message });
        }
    });

    /**
     * 📌 Xử lý khi Client hoặc Admin ngắt kết nối
     */
    socket.on("disconnect", () => {
        if (users.has(socket.id)) {
            io.to("admin").emit("clientDisconnected", { userId: socket.id });
            users.delete(socket.id);
        }

        if (admins.has(socket.id)) {
            admins.delete(socket.id);
        }

        console.log(`❌ User disconnected: ${socket.id}`);
    });
});

/**
 * 📌 Hàm lưu tin nhắn vào database (Laravel API)
 */
async function saveMessageToDatabase(messageData) {
    try {
        console.log("📤 Đang gửi tin nhắn đến API Laravel...", messageData);

        const response = await fetch("http://127.0.0.1:8000/api/messages", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "Accept": "application/json"
            },
            body: JSON.stringify(messageData)
        });

        // Kiểm tra nếu phản hồi không phải JSON
        const contentType = response.headers.get("content-type");
        if (!contentType || !contentType.includes("application/json")) {
            console.error("❌ Lỗi: Phản hồi không phải JSON");
            console.error("❌ Status:", response.status);
            const text = await response.text();
            console.error("❌ Nội dung phản hồi:", text.substring(0, 500) + "...");
            throw new Error("Phản hồi không hợp lệ từ API");
        }

        const result = await response.json();

        if (!response.ok) {
            console.error("❌ Lỗi từ Laravel API:", result);
            throw new Error(result.error || "Lưu tin nhắn thất bại!");
        }

        console.log("✅ Laravel API Response:", result);
        return result;
    } catch (error) {
        console.error("❌ Lỗi khi lưu tin nhắn:", error);
        // Không throw lỗi để tiếp tục xử lý tin nhắn dù lưu thất bại
    }
}

// Chạy server
const PORT = 3001;
server.listen(PORT, () => {
    console.log(`🚀 Server is running on http://127.0.0.1:${PORT}`);
});
