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
                            <button id="imageUploadBtn" class="btn btn-outline-secondary" type="button" disabled>
                                <i class="fas fa-image"></i>
                            </button>
                            <button id="sendMessageBtn" class="btn btn-primary" disabled>Gửi</button>

                            <!-- Thêm input ẩn để upload ảnh -->
                            <input type="file" id="imageInput" accept="image/*" multiple style="display: none;">
                        </div>

                        <!-- Thêm phần hiển thị trạng thái upload -->
                        <div id="upload-progress-container" class="mt-2" style="display: none;">
                            <div class="progress">
                                <div id="upload-progress-bar" class="progress-bar progress-bar-striped progress-bar-animated"
                                    role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%">
                                    0%
                                </div>
                            </div>
                            <div id="upload-status" class="small text-muted mt-1"></div>
                        </div>

                        <!-- Thêm phần preview ảnh -->
                        <div id="image-preview-container" class="mt-2" style="display: none;">
                            <div class="card">
                                <div class="card-body p-2">
                                    <div id="preview-list" class="d-flex flex-wrap gap-2 mb-2">
                                        <div class="preview-item d-flex align-items-center">
                                            <img id="image-preview" src="" alt="Preview" style="max-height: 60px; max-width: 60px;">
                                            <span id="image-name" class="ms-2 small text-truncate"></span>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span id="selected-count" class="text-muted small">1 ảnh được chọn</span>
                                        <button id="cancel-upload" class="btn btn-sm btn-danger">
                                            <i class="fas fa-times"></i> Hủy
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.socket.io/4.6.0/socket.io.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/lightbox2@2.11.3/dist/js/lightbox.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/lightbox2@2.11.3/dist/css/lightbox.min.css" rel="stylesheet" />

<script>
document.addEventListener('DOMContentLoaded', function() {
    const connectionStatus = document.getElementById('connection-status');
    const userList = document.getElementById('user-list');
    const chatBox = document.getElementById('chat-box');
    const messageInput = document.getElementById('messageInput');
    const sendMessageBtn = document.getElementById('sendMessageBtn');
    const chattingWith = document.getElementById('chatting-with');

    // Elements for image upload
    const imageUploadBtn = document.getElementById('imageUploadBtn');
    const imageInput = document.getElementById('imageInput');
    const uploadProgressContainer = document.getElementById('upload-progress-container');
    const uploadProgressBar = document.getElementById('upload-progress-bar');
    const uploadStatus = document.getElementById('upload-status');
    const imagePreviewContainer = document.getElementById('image-preview-container');
    const imagePreview = document.getElementById('image-preview');
    const imageName = document.getElementById('image-name');
    const cancelUpload = document.getElementById('cancel-upload');

    let currentUserId = null;
    let socket = null;
    let connectionAttempts = 0;
    const maxConnectionAttempts = 3;
<<<<<<< Updated upstream
    let adminToken = null;
    let selectedImageFiles = [];

    // Lấy admin token ngay khi trang tải xong
    adminToken = "{{ Auth::user()->createToken('admin-token')->plainTextToken }}";
    console.log('Token admin được tạo:', adminToken.substring(0, 15) + '...');
=======
>>>>>>> Stashed changes

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
        connectionStatus.textContent = "Đang kết nối...";
        connectionStatus.classList.remove('bg-success', 'bg-danger');
        connectionStatus.classList.add('bg-warning');

        addDebugInfo("Đang kết nối đến socket server 3002...");

        try {
            // Tạo token admin mới
            const adminToken = "{{ Auth::user()->createToken('admin-token')->plainTextToken }}";

            // Debug token info
            addDebugInfo(`Token: ${adminToken.substring(0, 15)}...`);

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

    // Tải tin nhắn cũ
    async function loadMessages(userId) {
        chatBox.innerHTML = '<div class="text-center"><div class="spinner-border text-primary" role="status"></div><p>Đang tải tin nhắn...</p></div>';

        let retryCount = 0;
        const maxRetries = 3;

        // Tạo token admin mới
        const adminToken = "{{ Auth::user()->createToken('admin-token')->plainTextToken }}";
        addDebugInfo(`Token API: ${adminToken.substring(0, 15)}...`);

        async function tryLoadMessages() {
            try {
                // Gọi API để lấy tin nhắn
                const response = await fetch(`http://localhost:8000/api/messages/user/${userId}`, {
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'Authorization': `Bearer ${adminToken}`
                    }
                });

                if (!response.ok) {
                    if (response.status === 401) {
                        addDebugInfo("❌ Token hết hạn hoặc không hợp lệ. Vui lòng tải lại trang.", "error");
                        chatBox.innerHTML = '<div class="alert alert-danger">Token hết hạn hoặc không hợp lệ. Vui lòng tải lại trang.</div>';
                        return;
                    }
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const messages = await response.json();

                if (!Array.isArray(messages)) {
                    console.error('Dữ liệu không đúng định dạng:', messages);
                    addDebugInfo(`Lỗi: Dữ liệu không đúng định dạng`, "error");
                    chatBox.innerHTML = '<div class="alert alert-danger">Lỗi: Dữ liệu không đúng định dạng</div>';
                    return;
                }

                chatBox.innerHTML = '';

                if (messages.length === 0) {
                    chatBox.innerHTML = '<div class="text-center text-muted"><p>Chưa có tin nhắn nào</p></div>';
                    return;
                }

                // Nhóm các tin nhắn của cùng người gửi theo thời gian
                const groupedMessages = groupMessagesForDisplay(messages);

                // Hiển thị các nhóm tin nhắn
                groupedMessages.forEach(group => {
                    displayMessageGroup(group);
                });

                // Cuộn xuống dưới cùng
                chatBox.scrollTop = chatBox.scrollHeight;

<<<<<<< Updated upstream
                addDebugInfo(`✅ Đã tải ${messages.length} tin nhắn thành công`);

                // Log dữ liệu tin nhắn để debug
                console.log("Dữ liệu tin nhắn:", messages);

=======
>>>>>>> Stashed changes
            } catch (error) {
                console.error('Lỗi khi tải tin nhắn:', error);
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
    }

    // Nhóm tin nhắn để hiển thị
    function groupMessagesForDisplay(messages) {
        // Sắp xếp tin nhắn theo thời gian
        const sortedMessages = [...messages].sort((a, b) =>
            new Date(a.sent_at) - new Date(b.sent_at)
        );

        const groups = [];
        let currentGroup = null;

        sortedMessages.forEach(msg => {
            const isSentByAdmin = msg.sender_id.toString() === "{{ Auth::id() }}";
            const messageType = isSentByAdmin ? 'admin' : 'client';

            // Nếu chưa có nhóm hoặc nhóm mới khác loại với nhóm hiện tại
            if (!currentGroup || currentGroup.type !== messageType) {
                // Tạo nhóm mới
                currentGroup = {
                    type: messageType,
                    sender: msg.sender,
                    messages: [msg],
                    lastTime: new Date(msg.sent_at)
                };
                groups.push(currentGroup);
            } else {
                // Thêm tin nhắn vào nhóm hiện tại
                currentGroup.messages.push(msg);
                currentGroup.lastTime = new Date(msg.sent_at);
            }
        });

        return groups;
    }

    // Hiển thị nhóm tin nhắn
    function displayMessageGroup(group) {
        const messageDiv = document.createElement('div');
        messageDiv.className = `message ${group.type}-message mb-3`;

        let allContent = '';

        // Phần text
        const textMessages = group.messages.filter(msg => msg.text);
        if (textMessages.length > 0) {
            textMessages.forEach(msg => {
                allContent += `<div class="message-text mb-1">${msg.text}</div>`;
            });
        }

        // Phần ảnh - hiển thị thành một khối
        const imageMessages = group.messages.filter(msg => msg.image);
        if (imageMessages.length > 0) {
            if (imageMessages.length === 1) {
                // Nếu chỉ có 1 ảnh thì hiển thị bình thường
                allContent += `<div class="message-image mb-2">
                    <a href="${imageMessages[0].image}" target="_blank">
                        <img src="${imageMessages[0].image}" alt="Hình ảnh" class="img-fluid rounded" style="max-height: 200px;">
                    </a>
                </div>`;
            } else {
                // Nếu có nhiều ảnh thì hiển thị dạng lưới
                allContent += `<div class="message-images-grid mb-2">
                    <div class="d-flex flex-wrap">`;

                imageMessages.forEach(msg => {
                    allContent += `
                        <div class="image-item m-1">
                            <a href="${msg.image}" target="_blank">
                                <img src="${msg.image}" alt="Hình ảnh" class="rounded" style="height: 100px; object-fit: cover;">
                            </a>
                        </div>`;
                });

                allContent += `</div>
                </div>`;
            }
        }

        if (group.type === 'client') {
            messageDiv.innerHTML = `
                <div class="d-flex">
                    <div class="message-bubble bg-light p-2 rounded">
                        <div class="message-sender text-muted small">${group.sender?.name || 'Khách hàng'}</div>
                        ${allContent}
                        <div class="message-time text-muted small">${group.lastTime.toLocaleString()}</div>
                    </div>
                </div>
            `;
        } else {
            messageDiv.innerHTML = `
                <div class="d-flex justify-content-end">
                    <div class="message-bubble bg-primary text-white p-2 rounded">
                        <div class="message-sender text-white-50 small">Admin</div>
                        ${allContent}
                        <div class="message-time text-white-50 small">${group.lastTime.toLocaleString()}</div>
                    </div>
                </div>
            `;
        }

        chatBox.appendChild(messageDiv);
    }

    // Thêm tin nhắn vào khung chat
    function addMessageToChat(message, sender) {
        const messageDiv = document.createElement('div');
        messageDiv.className = `message ${sender}-message mb-3`;

        let messageContent = '';
        // Kiểm tra nếu có ảnh thì hiển thị ảnh
        if (message.image) {
            messageContent += `<div class="message-image mb-2">
                <a href="${message.image}" target="_blank">
                    <img src="${message.image}" alt="Hình ảnh" class="img-fluid rounded" style="max-height: 200px;">
                </a>
            </div>`;
        }

        // Nếu có văn bản thì hiển thị văn bản
        if (message.text) {
            messageContent += `<div class="message-text">${message.text}</div>`;
        }

        if (sender === 'client') {
            messageDiv.innerHTML = `
                <div class="d-flex">
                    <div class="message-bubble bg-light p-2 rounded">
                        <div class="message-sender text-muted small">${message.sender?.name || 'Khách hàng'}</div>
                        ${messageContent}
                        <div class="message-time text-muted small">${message.sent_at || new Date().toLocaleString()}</div>
                    </div>
                </div>
            `;
        } else {
            messageDiv.innerHTML = `
                <div class="d-flex justify-content-end">
                    <div class="message-bubble bg-primary text-white p-2 rounded">
                        <div class="message-sender text-white-50 small">Admin</div>
                        ${messageContent}
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
        imageUploadBtn.disabled = false;
        messageInput.focus();
    }

    // Tắt chức năng chat
    function disableChat() {
        messageInput.disabled = true;
        sendMessageBtn.disabled = true;
        imageUploadBtn.disabled = true;
        currentUserId = null;
        chattingWith.textContent = "Chưa chọn người dùng";
    }

    // Gửi tin nhắn
    function sendMessage() {
        const text = messageInput.value.trim();

        // Nếu không có text và không có ảnh được chọn, không làm gì cả
        if (!text && !selectedImageFiles.length) return;

        // Nếu không có người dùng được chọn, không làm gì cả
        if (!currentUserId) return;

        // Nếu có ảnh được chọn, xử lý cả ảnh và text
        if (selectedImageFiles.length > 0) {
            uploadAndSendImagesWithText(text);
            return;
        }

        // Nếu chỉ có text, xử lý gửi tin nhắn text
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

    // Xử lý upload và gửi nhiều ảnh kèm text
    function uploadAndSendImagesWithText(text) {
        if (selectedImageFiles.length === 0 || !currentUserId) return;

        // Hiển thị progress bar
        uploadProgressContainer.style.display = 'block';
        uploadStatus.textContent = 'Đang tải lên...';

        // Sử dụng endpoint upload nhiều ảnh
        const formData = new FormData();
        selectedImageFiles.forEach(file => {
            formData.append('images', file);
        });

        // Create XMLHttpRequest
        const xhr = new XMLHttpRequest();

        // Set up upload progress event
        xhr.upload.addEventListener('progress', (event) => {
            if (event.lengthComputable) {
                const percentComplete = Math.round((event.loaded / event.total) * 100);
                uploadProgressBar.style.width = percentComplete + '%';
                uploadProgressBar.textContent = percentComplete + '%';
                uploadProgressBar.setAttribute('aria-valuenow', percentComplete);
            }
        });

        // Set up load complete event
        xhr.addEventListener('load', () => {
            if (xhr.status >= 200 && xhr.status < 300) {
                const response = JSON.parse(xhr.responseText);

                if (response.success) {
                    uploadStatus.textContent = 'Tải lên thành công!';

                    // Gửi text trước nếu có
                    if (text) {
                        const textMessageData = {
                            text: text,
                            userId: currentUserId
                        };

                        socket.emit('adminMessage', textMessageData, (textResponse) => {
                            if (textResponse.success) {
                                addMessageToChat({
                                    text: text,
                                    sent_at: new Date().toLocaleString()
                                }, 'admin');

                                // Sau khi gửi text thì gửi hình ảnh
                                sendUploadedImages(response.files);
                            }
                        });
                    } else {
                        // Nếu không có text thì gửi ảnh luôn
                        sendUploadedImages(response.files);
                    }

                    // Xóa nội dung input
                    messageInput.value = '';
                } else {
                    uploadStatus.textContent = 'Lỗi: ' + response.message;
                    uploadProgressBar.classList.remove('bg-success');
                    uploadProgressBar.classList.add('bg-danger');
                }
            } else {
                uploadStatus.textContent = 'Lỗi khi tải lên: ' + xhr.statusText;
                uploadProgressBar.classList.remove('bg-success');
                uploadProgressBar.classList.add('bg-danger');
            }
        });

        // Set up error event
        xhr.addEventListener('error', () => {
            uploadStatus.textContent = 'Lỗi kết nối khi tải lên ảnh';
            uploadProgressBar.classList.remove('bg-success');
            uploadProgressBar.classList.add('bg-danger');
        });

        // Set up abort event
        xhr.addEventListener('abort', () => {
            uploadStatus.textContent = 'Đã hủy tải lên';
        });

        // Open connection and send the request
        xhr.open('POST', 'http://localhost:3002/upload-multiple', true);
        xhr.setRequestHeader('Authorization', 'Bearer ' + adminToken);
        xhr.send(formData);
    }

    // Hàm gửi ảnh đã upload
    function sendUploadedImages(files) {
        const messageData = {
            images: files.map(file => file.url),
            userId: currentUserId
        };

        socket.emit('adminMultipleImagesUpload', messageData, (socketResponse) => {
            if (socketResponse.success) {
                console.log('Tất cả ảnh đã được gửi thành công');
                addDebugInfo(`${files.length} ảnh đã được gửi thành công tới ${currentUserId}`);

                // Hiển thị tất cả ảnh trong một nhóm thay vì từng ảnh một
                addImageGroupToChat(files.map(file => file.url), new Date().toLocaleString(), 'admin');

                // Reset sau khi gửi thành công
                resetImageUpload();
            } else {
                console.error('Lỗi khi gửi ảnh:', socketResponse.error);
                addDebugInfo(`Lỗi khi gửi ảnh: ${socketResponse.error}`, "error");
                alert('Không thể gửi ảnh: ' + socketResponse.error);

                uploadStatus.textContent = 'Lỗi khi gửi ảnh!';
                uploadProgressBar.classList.remove('bg-success');
                uploadProgressBar.classList.add('bg-danger');
            }
        });
    }

    // Thêm nhóm ảnh vào khung chat
    function addImageGroupToChat(imageUrls, sentTime, sender) {
        if (!imageUrls || imageUrls.length === 0) return;

        const messageDiv = document.createElement('div');
        messageDiv.className = `message ${sender}-message mb-3`;

        let imagesContent = '';

        if (imageUrls.length === 1) {
            // Nếu chỉ có 1 ảnh thì hiển thị bình thường
            imagesContent = `<div class="message-image mb-2">
                <a href="${imageUrls[0]}" data-lightbox="msg-${Date.now()}" data-title="Hình ảnh">
                    <img src="${imageUrls[0]}" alt="Hình ảnh" class="img-fluid rounded" style="max-height: 200px;">
                </a>
            </div>`;
        } else if (imageUrls.length === 2) {
            // Nếu có 2 ảnh, hiển thị cạnh nhau 50-50
            imagesContent = `<div class="message-images-grid mb-2">
                <div class="d-flex" style="gap: 2px;">
                    <div style="width: 50%;">
                        <a href="${imageUrls[0]}" data-lightbox="msg-${Date.now()}" data-title="Hình ảnh 1">
                            <img src="${imageUrls[0]}" alt="Hình ảnh 1" class="w-100 rounded" style="height: 120px; object-fit: cover;">
                        </a>
                    </div>
                    <div style="width: 50%;">
                        <a href="${imageUrls[1]}" data-lightbox="msg-${Date.now()}" data-title="Hình ảnh 2">
                            <img src="${imageUrls[1]}" alt="Hình ảnh 2" class="w-100 rounded" style="height: 120px; object-fit: cover;">
                        </a>
                    </div>
                </div>
            </div>`;
        } else if (imageUrls.length === 3) {
            // Nếu có 3 ảnh, hiển thị 2 ảnh trên 1 ảnh dưới
            const msgGroup = `msg-${Date.now()}`;
            imagesContent = `<div class="message-images-grid mb-2">
                <div style="display: grid; grid-template-columns: 1fr 1fr; grid-template-rows: auto auto; gap: 2px;">
                    <div style="grid-column: 1; grid-row: 1;">
                        <a href="${imageUrls[0]}" data-lightbox="${msgGroup}" data-title="Hình ảnh 1">
                            <img src="${imageUrls[0]}" alt="Hình ảnh 1" class="w-100 rounded" style="height: 100px; object-fit: cover;">
                        </a>
                    </div>
                    <div style="grid-column: 2; grid-row: 1;">
                        <a href="${imageUrls[1]}" data-lightbox="${msgGroup}" data-title="Hình ảnh 2">
                            <img src="${imageUrls[1]}" alt="Hình ảnh 2" class="w-100 rounded" style="height: 100px; object-fit: cover;">
                        </a>
                    </div>
                    <div style="grid-column: span 2; grid-row: 2;">
                        <a href="${imageUrls[2]}" data-lightbox="${msgGroup}" data-title="Hình ảnh 3">
                            <img src="${imageUrls[2]}" alt="Hình ảnh 3" class="w-100 rounded" style="height: 100px; object-fit: cover;">
                        </a>
                    </div>
                </div>
            </div>`;
        } else {
            // Nếu có 4+ ảnh, hiển thị 3 ảnh đầu + ảnh cuối với overlay +X
            const remainingCount = imageUrls.length - 3;
            const msgGroup = `msg-${Date.now()}`;

            imagesContent = `<div class="message-images-grid mb-2">
                <div style="display: grid; grid-template-columns: 1fr 1fr; grid-template-rows: auto auto; gap: 2px;">
                    <div style="grid-column: 1; grid-row: 1;">
                        <a href="${imageUrls[0]}" data-lightbox="${msgGroup}" data-title="Hình ảnh 1">
                            <img src="${imageUrls[0]}" alt="Hình ảnh 1" class="w-100 rounded" style="height: 100px; object-fit: cover;">
                        </a>
                    </div>
                    <div style="grid-column: 2; grid-row: 1;">
                        <a href="${imageUrls[1]}" data-lightbox="${msgGroup}" data-title="Hình ảnh 2">
                            <img src="${imageUrls[1]}" alt="Hình ảnh 2" class="w-100 rounded" style="height: 100px; object-fit: cover;">
                        </a>
                    </div>
                    <div style="grid-column: 1; grid-row: 2; position: relative;">
                        <a href="${imageUrls[2]}" data-lightbox="${msgGroup}" data-title="Hình ảnh 3">
                            <img src="${imageUrls[2]}" alt="Hình ảnh 3" class="w-100 rounded" style="height: 100px; object-fit: cover;">
                        </a>
                    </div>
                    <div style="grid-column: 2; grid-row: 2; position: relative;">
                        <a href="${imageUrls[3]}" data-lightbox="${msgGroup}" data-title="Hình ảnh 4" class="position-relative d-block">
                            <img src="${imageUrls[3]}" alt="Hình ảnh 4" class="w-100 rounded" style="height: 100px; object-fit: cover; filter: brightness(50%);">
                            <div class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center text-white" style="font-size: 24px; font-weight: bold;">
                                +${remainingCount}
                            </div>
                        </a>
                    </div>
                </div>
            </div>`;

            // Thêm các ảnh còn lại vào lightbox nhưng không hiển thị
            for (let i = 4; i < imageUrls.length; i++) {
                imagesContent += `<a href="${imageUrls[i]}" data-lightbox="${msgGroup}" data-title="Hình ảnh ${i+1}" style="display: none;"></a>`;
            }
        }

        if (sender === 'client') {
            messageDiv.innerHTML = `
                <div class="d-flex">
                    <div class="message-bubble bg-light p-2 rounded">
                        <div class="message-sender text-muted small">Khách hàng</div>
                        ${imagesContent}
                        <div class="message-time text-muted small">${sentTime}</div>
                    </div>
                </div>
            `;
        } else {
            messageDiv.innerHTML = `
                <div class="d-flex justify-content-end">
                    <div class="message-bubble bg-primary text-white p-2 rounded">
                        <div class="message-sender text-white-50 small">Admin</div>
                        ${imagesContent}
                        <div class="message-time text-white-50 small">${sentTime}</div>
                    </div>
                </div>
            `;
        }

        chatBox.appendChild(messageDiv);
        chatBox.scrollTop = chatBox.scrollHeight;

        // Khởi tạo lại lightbox
        if (typeof lightbox !== 'undefined') {
            lightbox.option({
                'resizeDuration': 200,
                'wrapAround': true,
                'albumLabel': "Hình ảnh %1 / %2"
            });
        }
    }

    // Reset trạng thái upload ảnh
    function resetImageUpload() {
        selectedImageFiles = [];
        imageInput.value = '';
        imagePreviewContainer.style.display = 'none';
        uploadProgressContainer.style.display = 'none';
        uploadProgressBar.style.width = '0%';
        uploadProgressBar.textContent = '0%';
        uploadProgressBar.setAttribute('aria-valuenow', 0);
        uploadProgressBar.classList.remove('bg-danger');
        uploadProgressBar.classList.add('bg-primary');
    }

    // Xử lý upload và gửi nhiều ảnh (không có text)
    function uploadAndSendImages() {
        uploadAndSendImagesWithText("");
    }

    // Xử lý sự kiện chọn ảnh
    imageInput.addEventListener('change', function(e) {
        if (this.files && this.files.length > 0) {
            selectedImageFiles = Array.from(this.files);

            // Xóa tất cả preview cũ
            const previewList = document.getElementById('preview-list');
            previewList.innerHTML = '';

            // Cập nhật counter
            document.getElementById('selected-count').textContent = `${selectedImageFiles.length} ảnh được chọn`;

            // Thêm preview cho mỗi ảnh
            selectedImageFiles.forEach((file, index) => {
                const previewItem = document.createElement('div');
                previewItem.className = 'preview-item position-relative m-1';

                const reader = new FileReader();
                reader.onload = function(e) {
                    previewItem.innerHTML = `
                        <img src="${e.target.result}" alt="Preview ${index + 1}" style="height: 60px; width: 60px; object-fit: cover; border-radius: 4px; border: 1px solid #dee2e6;">
                        <button class="btn btn-sm btn-danger position-absolute top-0 right-0 rounded-circle p-0" style="width: 20px; height: 20px; font-size: 10px; line-height: 0; top: -5px; right: -5px;"
                                onclick="removePreviewImage(${index})">
                            <i class="fas fa-times"></i>
                        </button>
                    `;
                    previewList.appendChild(previewItem);
                };
                reader.readAsDataURL(file);
            });

            // Hiển thị container
            imagePreviewContainer.style.display = 'block';
        }
    });

    // Hàm xóa một ảnh khỏi danh sách preview
    window.removePreviewImage = function(index) {
        if (index >= 0 && index < selectedImageFiles.length) {
            // Xóa file khỏi danh sách
            selectedImageFiles.splice(index, 1);

            // Cập nhật lại UI
            const previewList = document.getElementById('preview-list');
            previewList.innerHTML = '';

            if (selectedImageFiles.length === 0) {
                // Nếu không còn ảnh nào, ẩn container
                imagePreviewContainer.style.display = 'none';
                return;
            }

            // Cập nhật counter
            document.getElementById('selected-count').textContent = `${selectedImageFiles.length} ảnh được chọn`;

            // Tạo lại các preview
            selectedImageFiles.forEach((file, idx) => {
                const previewItem = document.createElement('div');
                previewItem.className = 'preview-item position-relative m-1';

                const reader = new FileReader();
                reader.onload = function(e) {
                    previewItem.innerHTML = `
                        <img src="${e.target.result}" alt="Preview ${idx + 1}" style="height: 60px; width: 60px; object-fit: cover; border-radius: 4px; border: 1px solid #dee2e6;">
                        <button class="btn btn-sm btn-danger position-absolute top-0 right-0 rounded-circle p-0" style="width: 20px; height: 20px; font-size: 10px; line-height: 0; top: -5px; right: -5px;"
                                onclick="removePreviewImage(${idx})">
                            <i class="fas fa-times"></i>
                        </button>
                    `;
                    previewList.appendChild(previewItem);
                };
                reader.readAsDataURL(file);
            });
        }
    };

    // Xử lý sự kiện click nút upload ảnh
    imageUploadBtn.addEventListener('click', function() {
        imageInput.click();
    });

    // Xử lý sự kiện hủy upload ảnh
    cancelUpload.addEventListener('click', function() {
        resetImageUpload();
    });

    // Xử lý sự kiện nút gửi
    sendMessageBtn.addEventListener('click', sendMessage);

    // Xử lý sự kiện nhấn Enter
    messageInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    });

<<<<<<< Updated upstream
=======
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

>>>>>>> Stashed changes
    // Kết nối đến socket khi trang load xong
    connectToSocket();
});
</script>
@endsection
