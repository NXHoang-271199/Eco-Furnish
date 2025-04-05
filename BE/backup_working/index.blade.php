@extends('layouts.admin')

@section('content')
<div class="card">
    <div class="card-header">Quản lý tin nhắn</div>
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        Danh sách người dùng
                        <span id="connection-status" class="badge bg-success float-end">Đang kết nối...</span>
                    </div>
                    <div class="card-body p-0">
                        <ul id="user-list" class="list-group list-group-flush">
                            <li class="list-group-item text-center text-muted">Chưa có người dùng kết nối</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        Cuộc trò chuyện
                        <span id="chatting-with" class="badge bg-light text-dark float-end">Chưa chọn người dùng</span>
                    </div>
                    <div class="card-body">
                        <div id="chat-box" class="border p-3 mb-3" style="height: 350px; overflow-y: auto;">
                            <div class="text-center text-muted">
                                <p>Chọn một người dùng từ danh sách để bắt đầu trò chuyện</p>
                            </div>
                        </div>
                        <div class="input-group">
                            <input type="text" id="messageInput" class="form-control" placeholder="Nhập tin nhắn..." disabled>
                            <button id="sendMessageBtn" class="btn btn-primary" disabled>Gửi</button>
                        </div>
                    </div>
                </div>
        </div>
        </div>
    </div>
</div>

<script src="https://cdn.socket.io/4.6.0/socket.io.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const connectionStatus = document.getElementById('connection-status');
    const userList = document.getElementById('user-list');
    const chatBox = document.getElementById('chat-box');
    const messageInput = document.getElementById('messageInput');
    const sendMessageBtn = document.getElementById('sendMessageBtn');
    const chattingWith = document.getElementById('chatting-with');

    let currentUserId = null;
    let socket = null;
    let connectionAttempts = 0;
    const maxConnectionAttempts = 3;
    let adminToken = null;

    // Lấy admin token ngay khi trang tải xong
    adminToken = "{{ Auth::user()->createToken('admin-token')->plainTextToken }}";
    console.log('Token admin được tạo:', adminToken.substring(0, 15) + '...');

    // Debug info function
    function addDebugInfo(message, type = 'info') {
        console.log(`[DEBUG] ${message}`);
        const debugDiv = document.createElement('div');
        debugDiv.className = `alert alert-${type === 'error' ? 'danger' : 'info'} mt-2 mb-2 p-2 text-small`;
        debugDiv.innerText = message;
        debugDiv.style.fontSize = '12px';
        chatBox.appendChild(debugDiv);
        chatBox.scrollTop = chatBox.scrollHeight;
    }

    // Kết nối đến Socket.IO server
    function connectToSocket() {
<<<<<<< HEAD
        connectionStatus.textContent = "Đang kết nối...";
        connectionStatus.classList.remove('bg-success', 'bg-danger');
        connectionStatus.classList.add('bg-warning');
=======
        socket = io("http://127.0.0.1:3001", {
            transports: ["websocket", "polling"],
            reconnectionAttempts: 5,
            reconnectionDelay: 1000,
            timeout: 10000
        });
>>>>>>> 2e14406b3e596a8478022de3f9eb13f92e5eddb7

        addDebugInfo("Đang kết nối đến socket server 3002...");

        try {
            // Thử tất cả các cách kết nối có thể
            const possibleUrls = [
                'http://localhost:3002',
                'http://127.0.0.1:3002'
            ];

            // Chọn URL đầu tiên trong danh sách
            const serverUrl = possibleUrls[0];
            addDebugInfo(`Đang thử kết nối đến: ${serverUrl}`);

            socket = io(serverUrl, {
                transports: ['polling', 'websocket'], // Dùng polling trước, sau đó mới dùng websocket
                auth: {
                    token: adminToken,
                    userId: "{{ Auth::id() }}",
                    role: "admin"
                },
                reconnection: true,
                reconnectionAttempts: 5,
                reconnectionDelay: 1000,
                timeout: 20000,
                forceNew: true,
                withCredentials: false
            });

<<<<<<< HEAD
            // Sự kiện kết nối thành công
            socket.on("connect", () => {
                console.log("Đã kết nối đến Socket.IO server");
                addDebugInfo(`Socket đã kết nối: ${socket.id}`);
                connectionStatus.textContent = "Đã kết nối";
                connectionStatus.classList.remove('bg-warning', 'bg-danger');
                connectionStatus.classList.add('bg-success');
                connectionAttempts = 0;

                // Đăng ký là admin
                addDebugInfo("Đang đăng ký với vai trò admin...");
                socket.emit("adminConnect", { token: adminToken }, (response) => {
                    if (response && response.success) {
                        addDebugInfo("✅ Đăng ký admin thành công");
                    } else {
                        addDebugInfo("❌ Đăng ký admin thất bại", "error");
                        reconnectWithDelay();
=======
        // Sự kiện lỗi kết nối
        socket.on("connect_error", (error) => {
            console.error("Lỗi kết nối:", error);
            connectionStatus.textContent = "Lỗi kết nối";
            connectionStatus.classList.remove('bg-success', 'bg-danger');
            connectionStatus.classList.add('bg-warning');

            // Thử kết nối lại
            setTimeout(() => {
                if (!socket.connected) {
                    socket.connect();
                }
            }, 5000);
        });

        // Sự kiện lỗi xác thực
        socket.on("authenticationError", (data) => {
            console.error("Lỗi xác thực:", data.message);
            connectionStatus.textContent = "Lỗi xác thực";
            connectionStatus.classList.remove('bg-success', 'bg-warning');
            connectionStatus.classList.add('bg-danger');

            alert("Lỗi xác thực: " + data.message);
        });

        // Sự kiện xác thực thành công
        socket.on("connectionSuccess", (data) => {
            console.log("Kết nối thành công:", data.message);
            connectionStatus.textContent = "Đã kết nối";
            connectionStatus.classList.remove('bg-warning', 'bg-danger');
            connectionStatus.classList.add('bg-success');
        });

        // Sự kiện ngắt kết nối
        socket.on("disconnect", () => {
            console.log("Mất kết nối đến Socket.IO server");
            connectionStatus.textContent = "Mất kết nối";
            connectionStatus.classList.remove('bg-success', 'bg-warning');
            connectionStatus.classList.add('bg-danger');

            userList.innerHTML = '<li class="list-group-item text-center text-muted">Đang kết nối lại...</li>';
            disableChat();

            // Thử kết nối lại sau 3 giây
            setTimeout(() => {
                if (!socket.connected) {
                    socket.connect();
                }
            }, 3000);
        });

        // Nhận danh sách người dùng hiện tại
        socket.on("currentUsers", (users) => {
            updateUserList(users);
        });

        // Người dùng mới kết nối
        socket.on("newClientConnected", (user) => {
            console.log("Người dùng mới kết nối:", user);

            // Thêm người dùng vào danh sách nếu chưa có
            if (!document.getElementById(`user-${user.id}`)) {
                addUserToList(user);
            }
        });

        // Người dùng ngắt kết nối
        socket.on("clientDisconnected", (data) => {
            console.log("Người dùng ngắt kết nối:", data);

            const userElement = document.getElementById(`user-${data.userId}`);
            if (userElement) {
                userElement.remove();
            }

            // Nếu không còn người dùng nào
            if (userList.children.length === 0) {
                userList.innerHTML = '<li class="list-group-item text-center text-muted">Chưa có người dùng kết nối</li>';
            }

            // Nếu người dùng đang chat bị ngắt kết nối
            if (currentUserId === data.userId) {
                chatBox.innerHTML += `
                    <div class="alert alert-warning text-center">
                        Người dùng đã ngắt kết nối
                    </div>
                `;
                disableChat();
            }
        });

        // Nhận tin nhắn từ client
        socket.on("newClientMessage", (message) => {
            console.log("Tin nhắn mới từ client:", message);

            // Nếu đang chat với người dùng này
            if (currentUserId === message.userId) {
                addMessageToChat(message, 'client');
            } else {
                // Hiển thị thông báo có tin nhắn mới
                const userElement = document.getElementById(`user-${message.userId}`);
                if (userElement) {
                    userElement.classList.add('list-group-item-warning');

                    // Thêm badge thông báo nếu chưa có
                    if (!userElement.querySelector('.new-message-badge')) {
                        const badge = document.createElement('span');
                        badge.className = 'badge bg-danger float-end new-message-badge';
                        badge.textContent = 'Mới';
                        userElement.appendChild(badge);
>>>>>>> 2e14406b3e596a8478022de3f9eb13f92e5eddb7
                    }
                });
            });

            // Thêm hàm retry kết nối
            function reconnectWithDelay() {
                if (connectionAttempts < maxConnectionAttempts) {
                    connectionAttempts++;
                    const delay = connectionAttempts * 2000; // Tăng thời gian delay mỗi lần thử
                    addDebugInfo(`Đang thử kết nối lại sau ${delay/1000}s (lần ${connectionAttempts})...`);
                    setTimeout(connectToSocket, delay);
                } else {
                    addDebugInfo("❌ Đã vượt quá số lần thử kết nối. Vui lòng tải lại trang.", "error");
                }
            }

            // Sự kiện ngắt kết nối
            socket.on("disconnect", (reason) => {
                console.log("Mất kết nối đến Socket.IO server:", reason);
                addDebugInfo(`⚠️ Mất kết nối đến socket server (${reason})`, "error");
                connectionStatus.textContent = "Mất kết nối";
                connectionStatus.classList.remove('bg-success', 'bg-warning');
                connectionStatus.classList.add('bg-danger');

                userList.innerHTML = '<li class="list-group-item text-center text-muted">Đang kết nối lại...</li>';
                disableChat();

                if (reason === 'io server disconnect' || reason === 'transport close') {
                    reconnectWithDelay();
                }
            });

            // Sự kiện lỗi kết nối
            socket.on("connect_error", (error) => {
                console.error("Lỗi kết nối socket:", error.message);
                addDebugInfo(`❌ Lỗi kết nối: ${error.message}`, "error");
                connectionStatus.textContent = "Lỗi kết nối";
                connectionStatus.classList.remove('bg-success', 'bg-warning');
                connectionStatus.classList.add('bg-danger');

                // Thử kết nối lại với polling nếu websocket thất bại
                if (error.message.includes('websocket')) {
                    addDebugInfo("⚠️ Websocket thất bại, đang thử lại với polling...");
                    socket.io.opts.transports = ['polling', 'websocket'];
                }

                reconnectWithDelay();
            });

            // Nhận danh sách người dùng hiện tại
            socket.on("currentUsers", (users) => {
                console.log("Nhận danh sách users:", users);
                addDebugInfo(`Đã nhận danh sách ${users.length} người dùng online`);
                updateUserList(users);
            });

            // Người dùng mới kết nối
            socket.on("newClientConnected", (user) => {
                console.log("Người dùng mới kết nối:", user);
                addDebugInfo(`Người dùng mới kết nối: ${user.name || 'Không tên'} (${user.userId})`);

                // Thêm người dùng vào danh sách nếu chưa có
                if (!document.getElementById(`user-${user.userId}`)) {
                    addUserToList(user);
                }
            });

            // Người dùng ngắt kết nối
            socket.on("clientDisconnected", (data) => {
                console.log("Người dùng ngắt kết nối:", data);
                addDebugInfo(`Người dùng ngắt kết nối: ${data.userId}`);

                const userElement = document.getElementById(`user-${data.userId}`);
                if (userElement) {
                    userElement.remove();
                }

                // Nếu không còn người dùng nào
                if (userList.children.length === 0) {
                    userList.innerHTML = '<li class="list-group-item text-center text-muted">Chưa có người dùng kết nối</li>';
                }

                // Nếu người dùng đang chat bị ngắt kết nối
                if (currentUserId === data.userId) {
                    chatBox.innerHTML += `
                        <div class="alert alert-warning text-center">
                            Người dùng đã ngắt kết nối
                        </div>
                    `;
                    disableChat();
                }
            });

            // Nhận tin nhắn từ client
            socket.on("newClientMessage", (message) => {
                console.log("Tin nhắn mới từ client:", message);
                addDebugInfo(`Tin nhắn mới từ client: ${message.sender_id}, nội dung: ${message.text}`);

                // Nếu đang chat với người dùng này
                if (currentUserId === message.sender_id) {
                    addMessageToChat(message, 'client');
                } else {
                    // Hiển thị thông báo có tin nhắn mới
                    const userElement = document.getElementById(`user-${message.sender_id}`);
                    if (userElement) {
                        userElement.classList.add('list-group-item-warning');

                        // Thêm badge thông báo nếu chưa có
                        if (!userElement.querySelector('.new-message-badge')) {
                            const badge = document.createElement('span');
                            badge.className = 'badge bg-danger float-end new-message-badge';
                            badge.textContent = 'Mới';
                            userElement.appendChild(badge);
                        }
                    } else {
                        // Nếu người dùng không có trong danh sách, cần tải lại danh sách
                        addDebugInfo(`Không tìm thấy user ${message.sender_id} trong danh sách, yêu cầu danh sách mới`);
                        socket.emit("adminConnect"); // Yêu cầu danh sách users mới
                    }
                }
            });

            // Tin nhắn lỗi từ server
            socket.on("error", (data) => {
                console.error("Lỗi từ server:", data.message);
                addDebugInfo(`❌ Lỗi từ server: ${data.message}`, "error");
            });
        } catch (error) {
            console.error("Lỗi khởi tạo socket:", error);
            addDebugInfo(`❌ Lỗi khởi tạo socket: ${error.message}`, "error");
            reconnectWithDelay();
        }
    }

    // Cập nhật danh sách người dùng
    function updateUserList(users) {
        if (users.length === 0) {
            userList.innerHTML = '<li class="list-group-item text-center text-muted">Chưa có người dùng kết nối</li>';
            return;
        }

        userList.innerHTML = '';
        users.forEach(user => {
            addUserToList(user);
        });
    }

    // Thêm người dùng vào danh sách
    function addUserToList(user) {
        // Xóa thông báo "Chưa có người dùng" nếu có
        const emptyNotice = userList.querySelector('.text-center.text-muted');
        if (emptyNotice) {
            emptyNotice.remove();
        }

        // Debug để xem cấu trúc user object
        console.log("Thêm user vào danh sách:", user);

        const userId = user.userId || user.id; // Hỗ trợ cả 2 trường hợp userId hoặc id
        const name = user.name || 'Khách hàng không rõ';

        const li = document.createElement('li');
        li.id = `user-${userId}`;
        li.className = 'list-group-item d-flex justify-content-between align-items-center user-item';
        li.innerHTML = `
            <span>${name}</span>
            <span class="badge bg-primary">${new Date().toLocaleTimeString()}</span>
        `;

        // Thêm sự kiện click để chọn người dùng
        li.addEventListener('click', () => {
            // Xóa highlight khỏi tất cả các user
            userList.querySelectorAll('.user-item').forEach(item => {
                item.classList.remove('active', 'list-group-item-warning');

                // Xóa badge thông báo tin nhắn mới
                const badge = item.querySelector('.new-message-badge');
                if (badge) badge.remove();
            });

            // Highlight user được chọn
            li.classList.add('active');

            // Lưu user_id hiện tại
            currentUserId = userId;

            // Hiển thị tên người dùng đang chat
            chattingWith.textContent = name;

            // Kích hoạt ô nhập tin nhắn
            enableChat();

            // Tải tin nhắn cũ
            loadMessages(userId);
        });

        userList.appendChild(li);
    }

    // Chọn người dùng để chat
    function selectUser(userId, userName) {
        currentUserId = userId;
        chattingWith.textContent = userName;
        chatBox.innerHTML = '<div class="text-center"><div class="spinner-border text-primary" role="status"></div><p>Đang tải tin nhắn...</p></div>';

<<<<<<< HEAD
        // Xóa thông báo tin nhắn mới
        const userElement = document.getElementById(`user-${userId}`);
        if (userElement) {
            userElement.classList.remove('list-group-item-warning');
            const badgeElement = userElement.querySelector('.new-message-badge');
            if (badgeElement) {
                badgeElement.remove();
            }
        }

        // Kích hoạt chat
        enableChat();

        // Tải tin nhắn cũ
        loadMessages(userId);
    }

    // Tải tin nhắn cũ
    async function loadMessages(userId) {
        if (!userId) {
            addDebugInfo("❌ Không có userId để tải tin nhắn", "error");
            return;
        }

        chatBox.innerHTML = '<div class="text-center"><div class="spinner-border text-primary" role="status"></div><p>Đang tải tin nhắn...</p></div>';

        let retryCount = 0;
        const maxRetries = 3;

        addDebugInfo(`User ID yêu cầu: ${userId}`);

        async function tryLoadMessages() {
            try {
                // Gọi API để lấy tin nhắn
                addDebugInfo(`Đang gọi API tin nhắn cho user ${userId}...`);

                const apiUrl = `/api/messages/user/${userId}`;
                addDebugInfo(`API URL: ${apiUrl}`);

                const response = await fetch(apiUrl, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'Authorization': `Bearer ${adminToken}`
                    },
                    credentials: 'include' // Thêm credentials
                });

                // Lấy response dưới dạng text để debug
                const responseText = await response.text();

                if (!response.ok) {
                    addDebugInfo(`❌ Lỗi HTTP: ${response.status}`, "error");
                    addDebugInfo(`❌ Phản hồi: ${responseText}`, "error");

                    if (response.status === 401) {
                        addDebugInfo("❌ Token hết hạn hoặc không hợp lệ. Vui lòng tải lại trang.", "error");
                        chatBox.innerHTML = '<div class="alert alert-danger">Token hết hạn hoặc không hợp lệ. Vui lòng tải lại trang.</div>';
                        return;
                    } else if (response.status === 403) {
                        addDebugInfo("❌ Không có quyền xem tin nhắn của người dùng này. Mã lỗi: 403", "error");
                        chatBox.innerHTML = '<div class="alert alert-danger">Không có quyền xem tin nhắn của người dùng này.</div>';
                        return;
                    }

                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                // Parse JSON từ responseText
                let messages;
                try {
                    messages = JSON.parse(responseText);
                } catch (parseError) {
                    addDebugInfo(`❌ Lỗi parse JSON: ${parseError.message}`, "error");
                    addDebugInfo(`❌ Dữ liệu: ${responseText.substring(0, 100)}...`, "error");
                    throw new Error(`Lỗi parse JSON: ${parseError.message}`);
                }

                if (!Array.isArray(messages)) {
                    console.error('Dữ liệu không đúng định dạng:', messages);
                    addDebugInfo(`Lỗi: Dữ liệu không đúng định dạng`, "error");
                    chatBox.innerHTML = '<div class="alert alert-danger">Lỗi: Dữ liệu không đúng định dạng</div>';
                    return;
                }

                // Clear chat box
=======
        // Lấy token từ meta tag
        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        // Gọi API để lấy tin nhắn với token xác thực
        fetch(`/api/messages/user/${userId}`, {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': token,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            credentials: 'same-origin'
        })
            .then(response => {
                console.log('API response status:', response.status);
                if (!response.ok) {
                    if (response.status === 401) {
                        throw new Error('Phiên đăng nhập đã hết hạn. Vui lòng đăng nhập lại.');
                    }
                    return response.text().then(text => {
                        throw new Error(`Lỗi ${response.status}: ${text}`);
                    });
                }
                return response.json();
            })
            .then(data => {
>>>>>>> 2e14406b3e596a8478022de3f9eb13f92e5eddb7
                chatBox.innerHTML = '';
                console.log('API response:', data);

                // Kiểm tra cấu trúc response
                if (!data.success) {
                    chatBox.innerHTML = `<div class="alert alert-danger">Lỗi: ${data.message || 'Không thể tải tin nhắn'}</div>`;
                    return;
                }

                const messages = data.data?.messages || [];

                if (messages.length === 0) {
                    chatBox.innerHTML = '<div class="text-center text-muted"><p>Chưa có tin nhắn nào</p></div>';
                    return;
                }

                // Hiển thị tin nhắn
                messages.forEach(msg => {
                    // Phân biệt tin nhắn của admin và client
                    const isSentByAdmin = msg.sender_id.toString() === "{{ Auth::id() }}";
                    const messageType = isSentByAdmin ? 'admin' : 'client';

                    addMessageToChat({
                        text: msg.text,
<<<<<<< HEAD
                        sent_at: new Date(msg.sent_at).toLocaleString(),
                        sender: msg.sender
                    }, messageType);
=======
                        sent_at: new Date(msg.sent_at || msg.created_at).toLocaleString()
                    }, type);
>>>>>>> 2e14406b3e596a8478022de3f9eb13f92e5eddb7
                });

                // Cuộn xuống dưới cùng
                chatBox.scrollTop = chatBox.scrollHeight;

                addDebugInfo(`✅ Đã tải ${messages.length} tin nhắn thành công`);

            } catch (error) {
                console.error('Lỗi khi tải tin nhắn:', error);
<<<<<<< HEAD
                addDebugInfo(`Lỗi khi tải tin nhắn: ${error.message}`, "error");

                if (retryCount < maxRetries) {
                    retryCount++;
                    const delay = retryCount * 2000;
                    addDebugInfo(`Đang thử tải lại tin nhắn sau ${delay/1000}s (lần ${retryCount})...`);
                    setTimeout(tryLoadMessages, delay);
                } else {
                    if (error.name === 'TypeError') {
                        chatBox.innerHTML = '<div class="alert alert-danger">Lỗi khi xử lý dữ liệu tin nhắn</div>';
                    } else {
                        chatBox.innerHTML = '<div class="alert alert-danger">Lỗi khi tải tin nhắn. Vui lòng thử lại sau.</div>';
                    }
                }
            }
        }

        await tryLoadMessages();
=======
                chatBox.innerHTML = `<div class="alert alert-danger">Lỗi khi tải tin nhắn: ${error.message}</div>`;

                // Nếu lỗi do hết hạn token, hiển thị nút refresh
                if (error.message.includes('401') || error.message.includes('hết hạn')) {
                    chatBox.innerHTML += `
                        <div class="text-center mt-3">
                            <button class="btn btn-primary" onclick="window.location.reload()">Làm mới trang</button>
                        </div>
                    `;
                }
            });
>>>>>>> 2e14406b3e596a8478022de3f9eb13f92e5eddb7
    }

    // Thêm tin nhắn vào khung chat
    function addMessageToChat(message, sender) {
        const messageDiv = document.createElement('div');
        messageDiv.className = `message ${sender}-message mb-3`;

        if (sender === 'client') {
            messageDiv.innerHTML = `
                <div class="d-flex">
                    <div class="message-bubble bg-light p-2 rounded">
                        <div class="message-sender text-muted small">${message.sender?.name || 'Khách hàng'}</div>
                        <div class="message-text">${message.text}</div>
                        <div class="message-time text-muted small">${message.sent_at || new Date().toLocaleString()}</div>
                    </div>
                </div>
            `;
        } else {
            messageDiv.innerHTML = `
                <div class="d-flex justify-content-end">
                    <div class="message-bubble bg-primary text-white p-2 rounded">
                        <div class="message-sender text-white-50 small">Admin</div>
                        <div class="message-text">${message.text}</div>
                        <div class="message-time text-white-50 small">${message.sent_at || new Date().toLocaleString()}</div>
                    </div>
                </div>
            `;
        }

        chatBox.appendChild(messageDiv);
        chatBox.scrollTop = chatBox.scrollHeight;
    }

    // Bật chức năng chat
    function enableChat() {
        messageInput.disabled = false;
        sendMessageBtn.disabled = false;
        messageInput.focus();
    }

    // Tắt chức năng chat
    function disableChat() {
        messageInput.disabled = true;
        sendMessageBtn.disabled = true;
        currentUserId = null;
        chattingWith.textContent = "Chưa chọn người dùng";
    }

    // Gửi tin nhắn
    function sendMessage() {
        const text = messageInput.value.trim();
        if (!text || !currentUserId) return;

        const messageData = {
            text: text,
            userId: currentUserId
        };

        // Gửi tin nhắn qua socket
        socket.emit('adminMessage', messageData, (response) => {
            if (response.success) {
                console.log('Tin nhắn đã được gửi thành công');
                addDebugInfo(`Tin nhắn đã được gửi thành công tới ${currentUserId}`);

                // Thêm tin nhắn vào khung chat
                addMessageToChat({
                    text: text,
                    sent_at: new Date().toLocaleString()
                }, 'admin');

                // Xóa nội dung input
                messageInput.value = '';
                messageInput.focus();
            } else {
                console.error('Lỗi khi gửi tin nhắn:', response.error);
                addDebugInfo(`Lỗi khi gửi tin nhắn: ${response.error}`, "error");
                alert('Không thể gửi tin nhắn: ' + response.error);
            }
        });
    }

    // Xử lý sự kiện nút gửi
    sendMessageBtn.addEventListener('click', sendMessage);

    // Xử lý sự kiện nhấn Enter
    messageInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    });

    // Thêm nút kiểm tra kết nối
    const debugButton = document.createElement('button');
    debugButton.innerText = "Kiểm tra kết nối";
    debugButton.className = "btn btn-sm btn-secondary mt-2 mb-2";
    debugButton.onclick = function() {
        addDebugInfo("Kiểm tra kết nối...");

        if (socket && socket.connected) {
            addDebugInfo(`Socket đang kết nối: ${socket.id}`);
        } else {
            addDebugInfo("Socket không kết nối! Đang kết nối lại...", "error");
            connectToSocket();
        }
    };
    document.querySelector('.card-body').prepend(debugButton);

    // Thêm nút kiểm tra token
    const tokenButton = document.createElement('button');
    tokenButton.innerText = "Kiểm tra token";
    tokenButton.className = "btn btn-sm btn-info mt-2 mb-2 ml-2";
    tokenButton.style.marginLeft = '10px';
    tokenButton.onclick = async function() {
        addDebugInfo("Đang kiểm tra token...");

        try {
            const response = await fetch('/api/test-auth', {
                headers: {
                    'Accept': 'application/json',
                    'Authorization': `Bearer ${adminToken}`
                }
            });

            const data = await response.json();
            if (response.ok) {
                addDebugInfo(`✅ Token hợp lệ. User: ${data.user.name}, Role: ${data.user.role}`);
            } else {
                addDebugInfo(`❌ Token không hợp lệ: ${data.message}`, "error");
            }
        } catch (error) {
            addDebugInfo(`❌ Lỗi kiểm tra token: ${error.message}`, "error");
        }
    };
    document.querySelector('.card-body').prepend(tokenButton);

    // Kết nối đến socket khi trang load xong
    connectToSocket();
    });
</script>
@endsection
