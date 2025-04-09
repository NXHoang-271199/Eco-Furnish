require('dotenv').config();
const express = require("express");
const http = require("http");
const socketIo = require("socket.io");
const fetch = require("node-fetch");
const jwt = require("jsonwebtoken");
const multer = require("multer");
const path = require("path");
const fs = require("fs");
const cors = require("cors");

// Các cài đặt từ biến môi trường
const API_URL = process.env.API_URL || 'http://127.0.0.1:8000';
// Sử dụng cổng 3002 cố định thay vì đọc từ biến môi trường
const SOCKET_PORT = 3002;

console.log('📌 Cài đặt cổng socket:', {
    'SOCKET_PORT env': process.env.SOCKET_PORT,
    'Cổng được sử dụng (cố định)': SOCKET_PORT
});

const app = express();
app.use(express.json());

// Cấu hình CORS cho tất cả các routes
app.use(cors({
    origin: "*", // Cho phép tất cả các origins
    methods: ["GET", "POST", "PUT", "DELETE", "OPTIONS"],
    allowedHeaders: ["Content-Type", "Authorization", "Accept"],
    credentials: true
}));

// Tạo thư mục uploads nếu chưa tồn tại
const uploadDir = path.join(__dirname, 'uploads');
if (!fs.existsSync(uploadDir)) {
    fs.mkdirSync(uploadDir, { recursive: true });
}

// Cấu hình multer để lưu file upload
const storage = multer.diskStorage({
    destination: function (req, file, cb) {
        cb(null, uploadDir)
    },
    filename: function (req, file, cb) {
        const uniqueSuffix = Date.now() + '-' + Math.round(Math.random() * 1E9);
        const fileExt = path.extname(file.originalname);
        cb(null, 'chat-image-' + uniqueSuffix + fileExt);
    }
});

// Lọc file ảnh
const fileFilter = (req, file, cb) => {
    // Chỉ chấp nhận các loại file ảnh
    if (file.mimetype.startsWith('image/')) {
        cb(null, true);
    } else {
        cb(new Error('Chỉ chấp nhận file ảnh'), false);
    }
};

const upload = multer({
    storage: storage,
    limits: {
        fileSize: 5 * 1024 * 1024 // giới hạn 5MB
    },
    fileFilter: fileFilter
});

// Phục vụ file tĩnh từ thư mục uploads
app.use('/uploads', express.static(path.join(__dirname, 'uploads')));

// Thêm API route để upload một ảnh
app.post('/upload', upload.single('image'), async (req, res) => {
    try {
        if (!req.file) {
            return res.status(400).json({ success: false, message: 'Không có file được tải lên' });
        }

        const token = req.headers.authorization?.split(' ')[1];
        if (!token) {
            return res.status(401).json({ success: false, message: 'Không có token xác thực' });
        }

        // Tạo URL cho ảnh đã upload
        const imageUrl = `${req.protocol}://${req.get('host')}/uploads/${req.file.filename}`;

        res.json({
            success: true,
            file: {
                filename: req.file.filename,
                path: req.file.path,
                url: imageUrl
            }
        });
    } catch (error) {
        console.error('Lỗi khi xử lý upload file:', error);
        res.status(500).json({ success: false, message: 'Lỗi server khi xử lý upload' });
    }
});

// Thêm API route để upload nhiều ảnh cùng lúc
app.post('/upload-multiple', upload.array('images', 10), async (req, res) => {
    try {
        if (!req.files || req.files.length === 0) {
            return res.status(400).json({ success: false, message: 'Không có file nào được tải lên' });
        }

        const token = req.headers.authorization?.split(' ')[1];
        if (!token) {
            return res.status(401).json({ success: false, message: 'Không có token xác thực' });
        }

        // Tạo URL cho mỗi ảnh đã upload
        const uploadedFiles = req.files.map(file => {
            return {
                filename: file.filename,
                path: file.path,
                url: `${req.protocol}://${req.get('host')}/uploads/${file.filename}`
            };
        });

        res.json({
            success: true,
            files: uploadedFiles
        });
    } catch (error) {
        console.error('Lỗi khi xử lý upload nhiều file:', error);
        res.status(500).json({ success: false, message: 'Lỗi server khi xử lý upload nhiều ảnh' });
    }
});

// Thêm route để server kiểm tra tình trạng kết nối
app.get("/", (req, res) => {
    res.send("🚀 Socket.IO Server is running!");
});

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
// Thêm map để theo dõi userId đang online và socket.id tương ứng
const userIdToSocketMap = new Map();
// Map lưu trữ cả mảng socketIds cho mỗi userId (cho phép quản lý nhiều tab)
const userIdToSocketsMap = new Map();

// Middleware xác thực token
const authenticateToken = async (socket, next) => {
    try {
        const token = socket.handshake.auth.token;
        if (!token) {
            console.error("❌ Token không được cung cấp");
            return next(new Error("Authentication error: Token not provided"));
        }

        console.log("🔑 Đang xác thực token...");

        // Lấy sessionId từ handshake nếu có
        const sessionId = socket.handshake.auth.sessionId || null;
        if (sessionId) {
            socket.sessionId = sessionId;
            console.log(`🔑 Phiên kết nối: ${sessionId}`);
        }

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

        // Lưu userId vào socket để dễ theo dõi
        socket.userId = result.user.id;

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
                avatar: socket.user.avatar || null,
                sessionId: socket.sessionId || null
            };

            // Kiểm tra xem userId này đã online chưa
            const existingSocketId = userIdToSocketMap.get(socket.user.id);

            if (existingSocketId) {
                // Nếu đã online, kiểm tra xem socket cũ có còn hoạt động không
                const existingSocket = Array.from(io.sockets.sockets).find(
                    ([id]) => id === existingSocketId
                );

                if (existingSocket) {
                    // Nếu sessionId giống nhau, thay thế socket cũ mà không thông báo
                    const [, existingSocketObj] = existingSocket;
                    if (socket.sessionId && existingSocketObj.sessionId === socket.sessionId) {
                        console.log(`⚠️ Phát hiện kết nối trùng lặp từ cùng một phiên cho user ${socket.user.name}`);
                        console.log(`⚠️ Thay thế kết nối cũ ${existingSocketId} bằng kết nối mới ${socket.id}`);

                        // Xóa kết nối cũ khỏi danh sách online nhưng không thông báo
                        onlineUsers.delete(existingSocketId);
                    } else {
                        console.log(`⚠️ User ${socket.user.name} đã có kết nối trước đó với socketId: ${existingSocketId}`);
                        console.log(`⚠️ Phiên khác nhau: hiện tại=${socket.sessionId}, cũ=${existingSocketObj.sessionId || 'không có'}`);

                        // Xóa kết nối cũ khỏi danh sách online
                        onlineUsers.delete(existingSocketId);

                        // Thông báo cho admin biết có sự thay đổi kết nối
                        io.to("admin_room").emit("clientDisconnected", { userId: socket.user.id });
                    }
                }
            }

            // Lưu thông tin mới
            onlineUsers.set(socket.id, userData);
            userIdToSocketMap.set(socket.user.id, socket.id);

            // Cập nhật mảng socketIds cho userId này
            let userSockets = userIdToSocketsMap.get(socket.user.id) || [];
            // Lọc bỏ các socketId không còn tồn tại
            userSockets = userSockets.filter(id =>
                Array.from(io.sockets.sockets).some(([socketId]) => socketId === id)
            );
            // Thêm socketId mới
            userSockets.push(socket.id);
            userIdToSocketsMap.set(socket.user.id, userSockets);

            socket.join(`user_${socket.user.id}`); // Room riêng cho user

            // Gửi danh sách client mới cho admin
            console.log(`📣 Gửi thông báo newClientConnected đến admin_room:`, userData);
            io.to("admin_room").emit("newClientConnected", userData);

            console.log(`👤 Client ${socket.id} (${socket.user.name}) đã kết nối và được đăng ký với ID ${socket.user.id}`);
            if (socket.sessionId) {
                console.log(`👤 SessionId: ${socket.sessionId}`);
            }
            console.log(`📊 Tổng số client online hiện tại: ${onlineUsers.size}`);
            console.log(`📊 Số lượng kết nối cho user ${socket.user.name}: ${userSockets.length}`);

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
    socket.on("clientMessage", async (data, callback = () => { }) => {
        try {
            // Kiểm tra xem người dùng có tồn tại không
            if (socket.user.role === 'admin') {
                return callback({ success: false, error: "Admin không thể gửi tin nhắn như client" });
            }

            console.log(`📨 Nhận tin nhắn từ client: ${socket.user.name} (${socket.id}):`, data.text);

            // Tạo dữ liệu tin nhắn để lưu vào DB
            const messageData = {
                text: data.text || null,
                image: data.image || null,
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

            // Đảm bảo trạng thái is_read luôn là false cho tin nhắn mới từ client
            savedMessage.is_read = false;

            // Chuẩn bị dữ liệu tin nhắn để gửi cho admin
            const messageForAdmin = {
                ...savedMessage,
                senderName: socket.user.name,
                is_read: false
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
    socket.on("adminMessage", async (data, callback = () => { }) => {
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

            console.log(`📨 Admin ${socket.user.name} gửi tin nhắn đến user ${data.userId}:`, data.text || data.image);

            // Tạo dữ liệu tin nhắn để lưu vào DB
            const messageData = {
                text: data.text || null,
                image: data.image || null,
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
                is_read: false // Tin nhắn admin mới gửi thì client chưa đọc
            };
            io.to(`user_${data.userId}`).emit("adminResponse", messageForClient);
            console.log(`📣 Đã gửi tin nhắn đến user_${data.userId}`);

            // *** Thêm log kiểm tra ***
            console.log('🚦 [Admin Sent Msg] Chuẩn bị gọi API đánh dấu đã đọc...');
            // *** Kết thúc log kiểm tra ***

            // *** Thêm: Sau khi admin gửi tin nhắn, tự động đánh dấu tin nhắn của client là đã đọc ***
            try {
                const clientId = data.userId;
                const adminToken = socket.handshake.auth.token;
                const markAsReadApiUrl = `${API_URL}/api/messages/mark-client-messages-as-read/${clientId}`;

                console.log(`🔄 [Admin Sent Msg] Triggering mark client messages as read. Calling API: PATCH ${markAsReadApiUrl}`);

                const markAsReadResponse = await fetch(markAsReadApiUrl, {
                    method: "PATCH",
                    headers: {
                        "Content-Type": "application/json",
                        "Accept": "application/json",
                        "Authorization": `Bearer ${adminToken}`
                    }
                });

                const markAsReadResult = await markAsReadResponse.json();

                if (!markAsReadResponse.ok) {
                    console.error(`❌ [Admin Sent Msg] Lỗi khi gọi API đánh dấu đã đọc sau khi gửi tin nhắn: ${markAsReadResponse.status}`, markAsReadResult);
                } else {
                    console.log(`✅ [Admin Sent Msg] API đánh dấu đã đọc thành công:`, markAsReadResult);
                    // Chỉ gửi tín hiệu cập nhật UI về client nếu có tin nhắn được cập nhật
                    if (markAsReadResult.success && markAsReadResult.updated_count > 0) {
                        io.to(`user_${clientId}`).emit('clientMessagesReadByAdmin');
                        console.log(`📣 [Admin Sent Msg] Đã gửi clientMessagesReadByAdmin đến user_${clientId} sau khi admin gửi tin nhắn.`);
                    } else {
                         console.log(`ℹ️ [Admin Sent Msg] Không có tin nhắn nào của client cần đánh dấu đã đọc.`);
                    }
                }
            } catch (error) {
                 console.error("❌ [Admin Sent Msg] Lỗi nghiêm trọng khi trigger đánh dấu đã đọc sau khi gửi tin nhắn:", error);
            }
            // *** Kết thúc thêm ***

            callback({ success: true, message: savedMessage });
        } catch (error) {
            console.error("❌ Lỗi khi xử lý adminMessage:", error);
            callback({ success: false, error: error.message });
        }
    });

    // Thêm xử lý upload ảnh từ client
    socket.on("clientImageUpload", async (data, callback = () => { }) => {
        try {
            // Kiểm tra xem người dùng có tồn tại không
            if (socket.user.role === 'admin') {
                return callback({ success: false, error: "Admin không thể gửi tin nhắn như client" });
            }

            if (!data.image) {
                return callback({ success: false, error: "Không có dữ liệu ảnh" });
            }

            console.log(`📸 Nhận ảnh từ client: ${socket.user.name} (${socket.id})`);

            // Tạo dữ liệu tin nhắn để lưu vào DB
            const messageData = {
                text: null,
                image: data.image,
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
                throw new Error(error.message || "Không thể lưu tin nhắn ảnh");
            }

            const savedMessage = await response.json();
            console.log("✅ Tin nhắn ảnh đã được lưu vào DB:", savedMessage.id);

            // Đảm bảo trạng thái is_read luôn là false cho tin nhắn mới từ client
            savedMessage.is_read = false;

            // Chuẩn bị dữ liệu tin nhắn để gửi cho admin
            const messageForAdmin = {
                ...savedMessage,
                senderName: socket.user.name,
                is_read: false
            };

            // Gửi tin nhắn đến tất cả admin
            console.log(`📣 Phát tin nhắn ảnh đến phòng admin_room. Admins online: ${onlineAdmins.size}`);
            io.to("admin_room").emit("newClientMessage", messageForAdmin);

            callback({ success: true, message: savedMessage });
        } catch (error) {
            console.error("❌ Lỗi khi xử lý clientImageUpload:", error);
            callback({ success: false, error: error.message });
        }
    });

    // Thêm xử lý upload ảnh từ admin
    socket.on("adminImageUpload", async (data, callback = () => { }) => {
        try {
            // Kiểm tra xem người dùng có phải là admin không
            if (socket.user.role !== 'admin') {
                console.error(`⚠️ User ${socket.user.name} (${socket.id}) cố gửi ảnh admin nhưng không có quyền`);
                return callback({ success: false, error: "Unauthorized: Admin role required" });
            }

            if (!data.userId) {
                console.error("❌ Thiếu userId của người nhận trong adminImageUpload");
                return callback({ success: false, error: "Thiếu userId của người nhận" });
            }

            if (!data.image) {
                return callback({ success: false, error: "Không có dữ liệu ảnh" });
            }

            console.log(`📸 Admin ${socket.user.name} gửi ảnh đến user ${data.userId}`);

            // Tạo dữ liệu tin nhắn để lưu vào DB
            const messageData = {
                text: null,
                image: data.image,
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
                throw new Error(error.message || "Không thể lưu tin nhắn ảnh");
            }

            const savedMessage = await response.json();
            console.log("✅ Tin nhắn ảnh admin đã được lưu vào DB:", savedMessage.id);

            // Gửi tin nhắn đến client cụ thể
            const messageForClient = {
                ...savedMessage,
                senderName: socket.user.name,
                is_read: false // Thêm trạng thái chưa đọc
            };

            io.to(`user_${data.userId}`).emit("adminResponse", messageForClient);
            console.log(`📣 Đã gửi tin nhắn ảnh đến user_${data.userId}`);

            callback({ success: true, message: savedMessage });
        } catch (error) {
            console.error("❌ Lỗi khi xử lý adminImageUpload:", error);
            callback({ success: false, error: error.message });
        }
    });

    // Thêm xử lý upload nhiều ảnh từ client
    socket.on("clientMultipleImagesUpload", async (data, callback = () => { }) => {
        try {
            // Kiểm tra xem người dùng có tồn tại không
            if (socket.user.role === 'admin') {
                return callback({ success: false, error: "Admin không thể gửi tin nhắn như client" });
            }

            if (!Array.isArray(data.images) || data.images.length === 0) {
                return callback({ success: false, error: "Không có dữ liệu ảnh" });
            }

            console.log(`📸 Nhận ${data.images.length} ảnh từ client: ${socket.user.name} (${socket.id})`);

            // Tạo dữ liệu tin nhắn riêng cho mỗi ảnh
            const savedMessages = [];

            // Lưu từng ảnh vào database
            for (const imageUrl of data.images) {
                // Tạo dữ liệu tin nhắn để lưu vào DB
                const messageData = {
                    text: null,
                    image: imageUrl,
                    sender_id: socket.user.id,
                    receiver_id: null, // Mặc định là null, admin sẽ nhận
                    sent_at: new Date()
                };

                try {
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
                        throw new Error(`API error: ${response.status}`);
                    }

                    const savedMessage = await response.json();
                    console.log("✅ Tin nhắn ảnh đã được lưu vào DB:", savedMessage.id);

                    // Đảm bảo trạng thái is_read luôn là false cho tin nhắn mới từ client
                    savedMessage.is_read = false;
                    savedMessages.push(savedMessage);

                    // Chuẩn bị dữ liệu tin nhắn để gửi cho admin
                    const messageForAdmin = {
                        ...savedMessage,
                        senderName: socket.user.name,
                        is_read: false
                    };

                    // Gửi tin nhắn đến tất cả admin
                    io.to("admin_room").emit("newClientMessage", messageForAdmin);
                } catch (error) {
                    console.error("❌ Lỗi khi lưu tin nhắn ảnh:", error);
                }
            }

            callback({ success: true, messages: savedMessages });
        } catch (error) {
            console.error("❌ Lỗi khi xử lý clientMultipleImagesUpload:", error);
            callback({ success: false, error: error.message });
        }
    });

    // Thêm xử lý upload nhiều ảnh từ admin
    socket.on("adminMultipleImagesUpload", async (data, callback = () => { }) => {
        try {
            // Kiểm tra xem người dùng có phải là admin không
            if (socket.user.role !== 'admin') {
                console.error(`⚠️ User ${socket.user.name} (${socket.id}) cố gửi ảnh admin nhưng không có quyền`);
                return callback({ success: false, error: "Unauthorized: Admin role required" });
            }

            if (!data.userId) {
                console.error("❌ Thiếu userId của người nhận trong adminMultipleImagesUpload");
                return callback({ success: false, error: "Thiếu userId của người nhận" });
            }

            if (!Array.isArray(data.images) || data.images.length === 0) {
                return callback({ success: false, error: "Không có dữ liệu ảnh" });
            }

            console.log(`📸 Admin ${socket.user.name} gửi ${data.images.length} ảnh đến user ${data.userId}`);

            // Lưu từng ảnh vào database và tạo tin nhắn riêng
            const savedMessages = [];

            for (const imageUrl of data.images) {
                // Tạo dữ liệu tin nhắn để lưu vào DB
                const messageData = {
                    text: null,
                    image: imageUrl,
                    sender_id: socket.user.id,
                    receiver_id: data.userId,
                    sent_at: new Date()
                };

                try {
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
                        throw new Error(`API error: ${response.status}`);
                    }

                    const savedMessage = await response.json();
                    console.log("✅ Tin nhắn ảnh admin đã được lưu vào DB:", savedMessage.id);
                    savedMessages.push(savedMessage);

                    // Gửi tin nhắn đến client cụ thể
                    const messageForClient = {
                        ...savedMessage,
                        senderName: socket.user.name,
                        is_read: false // Thêm trạng thái chưa đọc
                    };

                    io.to(`user_${data.userId}`).emit("adminResponse", messageForClient);
                } catch (error) {
                    console.error("❌ Lỗi khi lưu tin nhắn ảnh admin:", error);
                }
            }

            console.log(`📣 Đã gửi ${savedMessages.length} tin nhắn ảnh đến user_${data.userId}`);
            callback({ success: true, messages: savedMessages });
        } catch (error) {
            console.error("❌ Lỗi khi xử lý adminMultipleImagesUpload:", error);
            callback({ success: false, error: error.message });
        }
    });

    // *** Thêm: Xử lý sự kiện Admin xem chat của Client ***
    socket.on('adminViewedClientChat', async (data) => {
        // *** Thêm log để kiểm tra sự kiện có được nhận không ***
        console.log(`✅ [Server] Nhận được sự kiện adminViewedClientChat từ socket ${socket.id} với data:`, data);
        // *** Kết thúc log ***

        if (socket.user.role !== 'admin') {
            console.error('⚠️ Lỗi: Chỉ admin mới có thể gửi sự kiện adminViewedClientChat');
            return; // Bỏ qua nếu không phải admin
        }
        if (!data || !data.clientId) {
            console.error('⚠️ Lỗi: Sự kiện adminViewedClientChat thiếu clientId');
            return;
        }

        const clientId = data.clientId;
        const adminToken = socket.handshake.auth.token; // Lấy token của admin đang thực hiện

        console.log(`👀 Admin ${socket.user.name} đang xem tin nhắn của client ${clientId}`);

        try {
            // *** Gọi API mới để đánh dấu tin nhắn trong DB là đã đọc ***
            const apiUrl = `${API_URL}/api/messages/mark-client-messages-as-read/${clientId}`;
            console.log(`📞 Gọi API: PATCH ${apiUrl}`);

            const response = await fetch(apiUrl, {
                method: "PATCH",
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json",
                    "Authorization": `Bearer ${adminToken}`
                },
                // Không cần body cho request PATCH này
            });

            const result = await response.json();

            if (!response.ok) {
                console.error(`❌ Lỗi khi gọi API đánh dấu đã đọc: ${response.status}`, result);
                // Có thể không gửi sự kiện về client nếu API lỗi
                return;
            }

            console.log(`✅ API đánh dấu đã đọc thành công:`, result);

            // *** Chỉ gửi sự kiện về client SAU KHI API thành công ***
            io.to(`user_${clientId}`).emit('clientMessagesReadByAdmin');
            console.log(`📣 Đã gửi clientMessagesReadByAdmin đến user_${clientId} sau khi DB cập nhật`);

        } catch (error) {
            console.error("❌ Lỗi nghiêm trọng khi xử lý adminViewedClientChat hoặc gọi API:", error);
        }
    });
    // *** Kết thúc thêm ***

    /**
     * 📌 Xử lý khi Client hoặc Admin ngắt kết nối
     */
    socket.on("disconnect", () => {
        if (onlineUsers.has(socket.id)) {
            const userData = onlineUsers.get(socket.id);

            // Cập nhật mảng socketIds cho userId này
            let userSockets = userIdToSocketsMap.get(userData.userId) || [];
            userSockets = userSockets.filter(id => id !== socket.id);

            if (userSockets.length === 0) {
                // Nếu không còn kết nối nào, xóa khỏi map
                userIdToSocketsMap.delete(userData.userId);
                // Chỉ xóa khỏi userIdToSocketMap khi không còn kết nối nào
                userIdToSocketMap.delete(userData.userId);

                // Thông báo cho admin biết người dùng đã offline
                io.to("admin_room").emit("clientDisconnected", { userId: userData.userId });
                console.log(`👤 Client ${socket.user.name} đã ngắt kết nối hoàn toàn`);
            } else {
                // Cập nhật lại mảng socketIds
                userIdToSocketsMap.set(userData.userId, userSockets);
                // Cập nhật socketId chính cho userId (lấy cái đầu tiên trong mảng)
                userIdToSocketMap.set(userData.userId, userSockets[0]);
                console.log(`👤 Client ${socket.user.name} còn ${userSockets.length} kết nối khác`);
            }

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
    socket.on("markMessagesAsRead", async (data, callback = () => { }) => {
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

            const result = await response.json();
            console.log(`📬 Kết quả đánh dấu đã đọc:`, result);

            // Thông báo cho admin biết rằng tin nhắn đã được đọc
            io.to("admin_room").emit("messagesRead", { userId: data.userId });
            console.log(`📣 Đã thông báo admin rằng tin nhắn của user ${data.userId} đã được đọc`);

            // Thông báo cho client rằng tin nhắn đã được cập nhật
            io.to(`user_${data.userId}`).emit("messagesMarkedAsRead", { success: true });
            console.log(`📣 Đã thông báo client ${data.userId} rằng tin nhắn đã được đánh dấu đã đọc`);

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