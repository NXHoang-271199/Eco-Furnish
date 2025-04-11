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
                                    <div id="preview-list" class="d-flex flex-wrap gap-2 mb-2" style="max-height: 150px; overflow-y: auto;">
                                        <!-- Thumbnails sẽ được thêm vào đây bằng JS -->
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span id="selected-count" class="text-muted small">0 ảnh được chọn</span>
                                        <button id="cancel-upload" class="btn btn-sm btn-danger">
                                            <i class="fas fa-times"></i> Hủy tất cả
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
    const previewList = document.getElementById('preview-list');
    const cancelUpload = document.getElementById('cancel-upload');
    const selectedCountSpan = document.getElementById('selected-count');

    let currentUserId = null;
    let socket = null;
    let connectionAttempts = 0;
    const maxConnectionAttempts = 3;
    let adminToken = null;
    let selectedImageFiles = [];
    let pendingClientImages = [];
    let clientImageBufferTimeout = null;

    // Lấy admin token ngay khi trang tải xong
    adminToken = "{{ Auth::user()->createToken('admin-token')->plainTextToken }}";
    console.log('Token admin được tạo:', adminToken.substring(0, 15) + '...');

    // Debug info function (Sẽ xóa các lời gọi đến hàm này)
    function addDebugInfo(message, type = 'info') {
        console.log(`[DEBUG] ${message}`);
        // const debugDiv = document.createElement('div');
        // debugDiv.className = `alert alert-${type === 'error' ? 'danger' : 'info'} mt-2 mb-2 p-2 text-small`;
        // debugDiv.innerText = message;
        // debugDiv.style.fontSize = '12px';
        // chatBox.appendChild(debugDiv);
        // chatBox.scrollTop = chatBox.scrollHeight;
    }

    // Kết nối đến Socket.IO server
    function connectToSocket() {
        connectionStatus.textContent = "Đang kết nối...";
        connectionStatus.classList.remove('bg-success', 'bg-danger');
        connectionStatus.classList.add('bg-warning');

        try {
            // Tạo token admin mới
            const adminToken = "{{ Auth::user()->createToken('admin-token')->plainTextToken }}";

            // Debug token info
            addDebugInfo(`Token: ${adminToken.substring(0, 15)}...`);

            // Thử tất cả các cách kết nối có thể
            const possibleUrls = [
                'http://localhost:3001',
                'http://127.0.0.1:3001'
            ];

            // Chọn URL đầu tiên trong danh sách
            const serverUrl = possibleUrls[0];

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
                connectionStatus.textContent = "Đã kết nối";
                connectionStatus.classList.remove('bg-warning', 'bg-danger');
                connectionStatus.classList.add('bg-success');
                connectionAttempts = 0;

                // Đăng ký là admin
                socket.emit("adminConnect", { token: adminToken }, (response) => {
                    if (response && response.success) {
                    } else {
                        reconnectWithDelay();
                    }
                });
            });

            // Thêm hàm retry kết nối
            function reconnectWithDelay() {
                if (connectionAttempts < maxConnectionAttempts) {
                    connectionAttempts++;
                    const delay = connectionAttempts * 2000; // Tăng thời gian delay mỗi lần thử
                    setTimeout(connectToSocket, delay);
                } else {
                    addDebugInfo("❌ Đã vượt quá số lần thử kết nối. Vui lòng tải lại trang.", "error");
                }
            }

            // Sự kiện ngắt kết nối
            socket.on("disconnect", (reason) => {
                console.log("Mất kết nối đến Socket.IO server:", reason);
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
                connectionStatus.textContent = "Lỗi kết nối";
                connectionStatus.classList.remove('bg-success', 'bg-warning');
                connectionStatus.classList.add('bg-danger');

                // Thử kết nối lại với polling nếu websocket thất bại
                if (error.message.includes('websocket')) {
                    socket.io.opts.transports = ['polling', 'websocket'];
                }

                reconnectWithDelay();
            });

            // Nhận danh sách người dùng hiện tại
            socket.on("currentUsers", (users) => {
                console.log("Nhận danh sách users:", users);
                updateUserList(users);
            });

            // Người dùng mới kết nối
            socket.on("newClientConnected", (user) => {
                console.log("Người dùng mới kết nối:", user);

                // Thêm người dùng vào danh sách nếu chưa có
                if (!document.getElementById(`user-${user.userId}`)) {
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

            // Nhận tin nhắn từ client (SỬ DỤNG LOGIC ĐỆM)
            socket.on("newClientMessage", (message) => {
                console.log("📩 Nhận tin nhắn mới từ client:", message);
                // addDebugInfo(`Nhận tin nhắn mới: sender=${message.sender_id}, text=${message.text ? 'có' : 'không'}, image=${message.image ? 'có' : 'không'}`);

                const isImageOnly = !!(message.image && !message.text); // Đảm bảo boolean

                if (isImageOnly) {
                    // Nếu là ảnh đơn từ client -> đưa vào buffer
                    console.log("⏳ [Admin Buffer] Thêm ảnh client vào buffer:", message);
                    if (clientImageBufferTimeout) {
                        clearTimeout(clientImageBufferTimeout);
                    }
                    pendingClientImages.push(message);
                    clientImageBufferTimeout = setTimeout(processClientImageBuffer, 1200); // Tăng timeout lên 1.2s
                } else {
                    // Nếu là tin nhắn text hoặc có cả ảnh và text
                    console.log("⏳ [Admin Buffer] Xử lý buffer do có text hoặc tin nhắn không phải ảnh đơn.");
                    processClientImageBuffer(); // Xử lý buffer ngay lập tức

                    // Hiển thị tin nhắn hiện tại (text hoặc ảnh+text)
                    if (currentUserId === message.sender_id) {
                        addMessageToChat(message, 'client');
                    } else {
                        // Highlight user và thêm badge nếu không phải chat hiện tại
                        highlightUserWithNewMessage(message.sender_id);
                    }
                }
            });

            // Xử lý khi client gửi nhiều ảnh (Đổi tên sự kiện thành clientMultipleImagesUpload)
            socket.on("clientMultipleImagesUpload", (data) => {
                console.log("🖼️ Nhận nhiều ảnh từ client (sự kiện clientMultipleImagesUpload):", data);
                // addDebugInfo(`Nhận ${data.images?.length || 0} ảnh từ client ${data.sender_id} qua sự kiện nhóm.`);

                // Xử lý buffer cũ trước khi hiển thị nhóm mới (tránh trùng lặp)
                console.log("🖼️ [Admin Buffer] Xử lý buffer trước khi hiển thị nhóm ảnh mới.");
                processClientImageBuffer();

                if (data.images && data.images.length > 0 && data.sender_id) {
                    if (currentUserId === data.sender_id) {
                         console.log("🖼️ Hiển thị nhóm ảnh nhận được.");
                        // Sử dụng addImageGroupToChat để hiển thị nhóm ảnh
                        addImageGroupToChat(
                            data.images,
                            new Date(data.sent_at || Date.now()).toLocaleString(),
                            'client',
                            data.sender // Truyền sender object nếu client gửi kèm
                        );
                    } else {
                        // Highlight user nếu không phải chat hiện tại
                         highlightUserWithNewMessage(data.sender_id);
                    }
                } else {
                    // Highlight user nếu không phải chat hiện tại
                     highlightUserWithNewMessage(data.sender_id);
                }
            });

            // Tin nhắn lỗi từ server
            socket.on("error", (data) => {
                console.error("Lỗi từ server:", data.message);
            });
        } catch (error) {
            console.error("Lỗi khởi tạo socket:", error);
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

            // *** Thêm: Gửi sự kiện báo admin đã xem chat của user này ***
            if (socket && socket.connected) {
                console.log(`📣 Admin đang xem chat của user: ${userId}`);
                socket.emit('adminViewedClientChat', { clientId: userId });
            }
            // *** Kết thúc thêm ***
        });

        userList.appendChild(li);
    }

    // Tải tin nhắn cũ
    async function loadMessages(userId) {
        if (!userId) {
            // addDebugInfo("❌ Không có userId để tải tin nhắn", "error");
            return;
        }

        chatBox.innerHTML = '<div class="text-center"><div class="spinner-border text-primary" role="status"></div><p>Đang tải tin nhắn...</p></div>';

        let retryCount = 0;
        const maxRetries = 3;

        // addDebugInfo(`User ID yêu cầu: ${userId}`);

        async function tryLoadMessages() {
            try {
                // Gọi API để lấy tin nhắn
                // addDebugInfo(`Đang gọi API tin nhắn cho user ${userId}...`);

                const apiUrl = `/api/messages/user/${userId}`;
                // addDebugInfo(`API URL: ${apiUrl}`);

                const response = await fetch(apiUrl, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'Authorization': `Bearer ${adminToken}`
                    }
                });

                if (!response.ok) {
                    // addDebugInfo(`❌ Lỗi HTTP: ${response.status}`, "error");
                    // addDebugInfo(`❌ Phản hồi: ${responseText}`, "error");

                    if (response.status === 401) {
                        // addDebugInfo("❌ Token hết hạn hoặc không hợp lệ. Vui lòng tải lại trang.", "error");
                        chatBox.innerHTML = '<div class="alert alert-danger">Token hết hạn hoặc không hợp lệ. Vui lòng tải lại trang.</div>';
                        return;
                    } else if (response.status === 403) {
                        // addDebugInfo("❌ Không có quyền xem tin nhắn của người dùng này. Mã lỗi: 403", "error");
                        chatBox.innerHTML = '<div class="alert alert-danger">Không có quyền xem tin nhắn của người dùng này.</div>';
                        return;
                    }
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                // Parse JSON từ responseText
                let messages;
                try {
                    messages = await response.json();
                } catch (parseError) {
                    // addDebugInfo(`❌ Lỗi parse JSON: ${parseError.message}`, "error");
                    // addDebugInfo(`❌ Dữ liệu: ${responseText.substring(0, 100)}...`, "error");
                    throw new Error(`Lỗi parse JSON: ${parseError.message}`);
                }

                if (!Array.isArray(messages)) {
                    console.error('Dữ liệu không đúng định dạng:', messages);
                    // addDebugInfo(`Lỗi: Dữ liệu không đúng định dạng`, "error");
                    chatBox.innerHTML = '<div class="alert alert-danger">Lỗi: Dữ liệu không đúng định dạng</div>';
                    return;
                }

                chatBox.innerHTML = '';

                if (messages.length === 0) {
                    chatBox.innerHTML = '<div class="text-center text-muted"><p>Chưa có tin nhắn nào</p></div>';
                    return;
                }

                // Phân nhóm tin nhắn (Cập nhật để nhóm ảnh)
                function groupMessagesForDisplay(messages) {
                    const sortedMessages = [...messages].sort((a, b) =>
                        new Date(a.sent_at) - new Date(b.sent_at)
                    );

                    const groups = [];
                    let currentGroup = null;
                    const timeThreshold = 15000; // 15 giây để nhóm tin nhắn

                    sortedMessages.forEach(msg => {
                    const isSentByAdmin = msg.sender_id.toString() === "{{ Auth::id() }}";
                    const messageType = isSentByAdmin ? 'admin' : 'client';
                        const msgTime = new Date(msg.sent_at);
                        const isImageOnly = msg.image && !msg.text;

                        // Điều kiện để bắt đầu một nhóm mới
                        const startNewGroup = !currentGroup ||
                                              currentGroup.type !== messageType ||
                                              msgTime - currentGroup.lastTime > timeThreshold ||
                                              (currentGroup.isImageGroup && !isImageOnly) || // Đang nhóm ảnh mà gặp text -> nhóm mới
                                              (!currentGroup.isImageGroup && isImageOnly); // Đang nhóm text mà gặp ảnh -> nhóm mới

                        if (startNewGroup) {
                            currentGroup = {
                                type: messageType,
                                sender: msg.sender,
                                messages: [msg],
                                lastTime: msgTime,
                                isImageGroup: isImageOnly, // Đánh dấu nếu nhóm này chỉ chứa ảnh
                                images: isImageOnly ? [msg.image] : [] // Lưu trữ URL ảnh nếu là nhóm ảnh
                            };
                            groups.push(currentGroup);
                        } else {
                            // Thêm vào nhóm hiện tại
                            currentGroup.messages.push(msg);
                            currentGroup.lastTime = msgTime;
                            // Nếu là nhóm ảnh, thêm URL ảnh
                            if (currentGroup.isImageGroup && isImageOnly) {
                                currentGroup.images.push(msg.image);
                            } else {
                                // Nếu nhóm đang là ảnh mà gặp text, hoặc ngược lại, nó không còn là nhóm chỉ ảnh nữa
                                currentGroup.isImageGroup = false;
                                currentGroup.images = []; // Xóa danh sách ảnh
                            }
                        }
                    });

                    return groups;
                }

                // Hiển thị các nhóm tin nhắn
                const groupedMessages = groupMessagesForDisplay(messages);
                groupedMessages.forEach(group => {
                    // Nếu group chỉ chứa ảnh, dùng addImageGroupToChat
                    if (group.isImageGroup) {
                        // Truyền thêm group.sender vào hàm
                        addImageGroupToChat(group.images, group.lastTime.toLocaleString(), group.type, group.sender);
                    } else {
                        // Nếu group chứa cả text và ảnh (hoặc chỉ text)
                        // Hiển thị từng message trong group
                        group.messages.forEach(msg => {
                            addMessageToChat(msg, group.type);
                        });
                        // Hoặc có thể tùy chỉnh hiển thị group này nếu muốn
                        // displayMessageGroup(group); // Sử dụng hàm cũ nếu muốn
                    }
                });

                // Cuộn xuống dưới cùng
                chatBox.scrollTop = chatBox.scrollHeight;

                // Log dữ liệu tin nhắn để debug
                console.log("Dữ liệu tin nhắn:", messages);

            } catch (error) {
                console.error('Lỗi khi tải tin nhắn:', error);
                // addDebugInfo(`Lỗi khi tải tin nhắn: ${error.message}`, "error");

                if (retryCount < maxRetries) {
                    retryCount++;
                    const delay = retryCount * 2000;
                    // addDebugInfo(`Đang thử tải lại tin nhắn sau ${delay/1000}s (lần ${retryCount})...`);
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

    // Hàm highlight user và thêm badge thông báo mới
    function highlightUserWithNewMessage(senderId, senderName) {
         const userElement = document.getElementById(`user-${senderId}`);
         if (userElement) {
             // Thêm class warning để thay đổi màu nền
             userElement.classList.add('list-group-item-warning');
             // Chỉ thêm badge nếu chưa có
             if (!userElement.querySelector('.new-message-badge')) {
                 const badge = document.createElement('span');
                 badge.className = 'badge bg-danger float-end new-message-badge';
                 badge.textContent = 'Mới';
                 // Chèn vào trước badge thời gian (nếu có) để không bị đẩy xuống
                 const timeBadge = userElement.querySelector('.badge.bg-primary');
                 if (timeBadge) {
                     userElement.insertBefore(badge, timeBadge);
                 } else {
                     // Nếu không có time badge, thêm vào cuối
                     userElement.appendChild(badge);
                 }
             }
         } else {
             // Log nếu không tìm thấy user (có thể cần refresh list)
             // addDebugInfo(`Không tìm thấy user ${senderId} để highlight`, "warning");
         }
    }

    // Thêm hàm mới để hiển thị nhóm ảnh (Cập nhật tham số và logic senderName)
    function addImageGroupToChat(imageUrls, sentTime, senderType, senderInfo) {
        if (!imageUrls || imageUrls.length === 0) return;

        const messageDiv = document.createElement('div');
        // Sử dụng senderType cho class
        messageDiv.className = `message ${senderType}-message mb-3`;

        let imagesContent = '';
        const msgGroup = `msg-${Date.now()}-${Math.random().toString(36).substring(2, 7)}`; // Tạo ID group duy nhất

        if (imageUrls.length === 1) {
            // 1 ảnh: Hiển thị lớn
            imagesContent = `<div class="message-image mb-2">
                <a href="${imageUrls[0]}" data-lightbox="${msgGroup}" data-title="Hình ảnh">
                    <img src="${imageUrls[0]}" alt="Hình ảnh" class="img-fluid rounded" style="max-height: 200px; cursor: pointer;">
                </a>
            </div>`;
        } else if (imageUrls.length === 2) {
            // 2 ảnh: Hiển thị 50-50
            imagesContent = `<div class="message-images-grid mb-2">
                <div class="d-flex" style="gap: 2px;">
                    <div style="width: 50%;">
                        <a href="${imageUrls[0]}" data-lightbox="${msgGroup}" data-title="Hình ảnh 1">
                            <img src="${imageUrls[0]}" alt="Hình ảnh 1" class="w-100 rounded" style="height: 120px; object-fit: cover; cursor: pointer;">
                        </a>
                    </div>
                    <div style="width: 50%;">
                        <a href="${imageUrls[1]}" data-lightbox="${msgGroup}" data-title="Hình ảnh 2">
                            <img src="${imageUrls[1]}" alt="Hình ảnh 2" class="w-100 rounded" style="height: 120px; object-fit: cover; cursor: pointer;">
                        </a>
                    </div>
                </div>
            </div>`;
        } else if (imageUrls.length === 3) {
            // 3 ảnh: 2 trên, 1 dưới
            imagesContent = `<div class="message-images-grid mb-2">
                <div style="display: grid; grid-template-columns: 1fr 1fr; grid-template-rows: auto auto; gap: 2px;">
                    <div style="grid-column: 1; grid-row: 1;">
                        <a href="${imageUrls[0]}" data-lightbox="${msgGroup}" data-title="Hình ảnh 1">
                            <img src="${imageUrls[0]}" alt="Hình ảnh 1" class="w-100 rounded" style="height: 100px; object-fit: cover; cursor: pointer;">
                        </a>
                    </div>
                    <div style="grid-column: 2; grid-row: 1;">
                        <a href="${imageUrls[1]}" data-lightbox="${msgGroup}" data-title="Hình ảnh 2">
                            <img src="${imageUrls[1]}" alt="Hình ảnh 2" class="w-100 rounded" style="height: 100px; object-fit: cover; cursor: pointer;">
                        </a>
                    </div>
                    <div style="grid-column: span 2; grid-row: 2;">
                        <a href="${imageUrls[2]}" data-lightbox="${msgGroup}" data-title="Hình ảnh 3">
                            <img src="${imageUrls[2]}" alt="Hình ảnh 3" class="w-100 rounded" style="height: 100px; object-fit: cover; cursor: pointer;">
                        </a>
                    </div>
                </div>
            </div>`;
        } else { // 4+ ảnh
            const remainingCount = imageUrls.length - 3;
            imagesContent = `<div class="message-images-grid mb-2">
                <div style="display: grid; grid-template-columns: 1fr 1fr; grid-template-rows: auto auto; gap: 2px;">
                    <div style="grid-column: 1; grid-row: 1;">
                        <a href="${imageUrls[0]}" data-lightbox="${msgGroup}" data-title="Hình ảnh 1">
                            <img src="${imageUrls[0]}" alt="Hình ảnh 1" class="w-100 rounded" style="height: 100px; object-fit: cover; cursor: pointer;">
                        </a>
                    </div>
                    <div style="grid-column: 2; grid-row: 1;">
                        <a href="${imageUrls[1]}" data-lightbox="${msgGroup}" data-title="Hình ảnh 2">
                            <img src="${imageUrls[1]}" alt="Hình ảnh 2" class="w-100 rounded" style="height: 100px; object-fit: cover; cursor: pointer;">
                        </a>
                    </div>
                    <div style="grid-column: 1; grid-row: 2; position: relative;">
                        <a href="${imageUrls[2]}" data-lightbox="${msgGroup}" data-title="Hình ảnh 3">
                            <img src="${imageUrls[2]}" alt="Hình ảnh 3" class="w-100 rounded" style="height: 100px; object-fit: cover; cursor: pointer;">
                        </a>
                    </div>
                    <div style="grid-column: 2; grid-row: 2; position: relative;">
                        <a href="${imageUrls[3]}" data-lightbox="${msgGroup}" data-title="Hình ảnh 4" class="position-relative d-block">
                            <img src="${imageUrls[3]}" alt="Hình ảnh 4" class="w-100 rounded" style="height: 100px; object-fit: cover; filter: brightness(50%); cursor: pointer;">
                            <div class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center text-white" style="font-size: 1.1rem; font-weight: bold; background-color: rgba(0,0,0,0.4); border-radius: var(--bs-border-radius);">
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

        // Sử dụng senderInfo để lấy tên, fallback về 'Khách hàng' nếu không có hoặc là admin
        const senderName = senderType === 'client' ? (senderInfo?.name || 'Khách hàng') : 'Admin';

        // Sử dụng senderType để quyết định layout
        if (senderType === 'client') {
            messageDiv.innerHTML = `
                <div class="d-flex">
                    <div class="message-bubble bg-light p-2 rounded">
                        <div class="message-sender text-muted small">${senderName}</div>
                        ${imagesContent}
                        <div class="message-time text-muted small">${sentTime}</div>
                    </div>
                </div>
            `;
        } else { // senderType === 'admin'
            messageDiv.innerHTML = `
                <div class="d-flex justify-content-end">
                    <div class="message-bubble bg-primary text-white p-2 rounded">
                        <div class="message-sender text-white-50 small">${senderName}</div>
                        ${imagesContent}
                        <div class="message-time text-white-50 small">${sentTime}</div>
                    </div>
                </div>
            `;
        }

        chatBox.appendChild(messageDiv);
        chatBox.scrollTop = chatBox.scrollHeight;

        // Khởi tạo lại lightbox nếu cần
        if (typeof lightbox !== 'undefined') {
            lightbox.option({
                'resizeDuration': 200,
                'wrapAround': true,
                'albumLabel': "Hình ảnh %1 / %2"
            });
        }
    }

    // Thêm tin nhắn vào khung chat (Cập nhật để chỉ xử lý text hoặc ảnh đơn)
    function addMessageToChat(message, senderType) { // Đổi tên tham số sender thành senderType cho rõ ràng
        const messageDiv = document.createElement('div');
        // Sử dụng senderType cho class
        messageDiv.className = `message ${senderType}-message mb-3`;

        let messageContent = '';
        const sentTime = message.sent_at || new Date().toLocaleString();
         // Lấy tên từ message.sender, fallback nếu cần
        const senderName = senderType === 'client' ? (message.sender?.name || 'Khách hàng') : 'Admin';

        // Nếu là tin nhắn ảnh đơn lẻ
        if (message.image && !message.text) {
            const msgGroup = `msg-${Date.now()}-${Math.random().toString(36).substring(2, 7)}`;
            messageContent += `<div class="message-image mb-2">
                <a href="${message.image}" data-lightbox="${msgGroup}" data-title="Hình ảnh">
                    <img src="${message.image}" alt="Hình ảnh" class="img-fluid rounded" style="max-height: 200px; cursor: pointer;">
                </a>
            </div>`;
        } else if (message.text) {
            // Nếu có văn bản thì hiển thị văn bản
            messageContent += `<div class="message-text">${message.text}</div>`;
        }

        // Phần còn lại của hàm giữ nguyên...
        // Sử dụng senderType để quyết định layout
        if (senderType === 'client') {
            messageDiv.innerHTML = `
                <div class="d-flex">
                    <div class="message-bubble bg-light p-2 rounded">
                        <div class="message-sender text-muted small">${senderName}</div>
                        ${messageContent}
                        <div class="message-time text-muted small">${sentTime}</div>
                    </div>
                </div>
            `;
        } else { // senderType === 'admin'
            messageDiv.innerHTML = `
                <div class="d-flex justify-content-end">
                    <div class="message-bubble bg-primary text-white p-2 rounded">
                        <div class="message-sender text-white-50 small">${senderName}</div>
                        ${messageContent}
                        <div class="message-time text-white-50 small">${sentTime}</div>
                    </div>
                </div>
            `;
        }

        chatBox.appendChild(messageDiv);
        chatBox.scrollTop = chatBox.scrollHeight;

        // Khởi tạo lại lightbox nếu cần
        if (message.image && typeof lightbox !== 'undefined') {
            lightbox.option({
                'resizeDuration': 200,
                'wrapAround': true,
                'albumLabel': "Hình ảnh %1 / %2"
            });
        }
    }

    // Bật chức năng chat
    function enableChat() {
        messageInput.disabled = false;
        sendMessageBtn.disabled = false;
        imageUploadBtn.disabled = false;
        messageInput.focus();

        // Cập nhật trạng thái nút gửi dựa trên nội dung hiện tại
        setTimeout(updateSendButtonState, 100); // Thêm timeout để đảm bảo các phần tử UI đã cập nhật
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
        console.log('⚡ Gọi hàm sendMessage()');

        const text = messageInput.value.trim();
        const hasImages = selectedImageFiles.length > 0;

        // Kiểm tra điều kiện để gửi
        if (!currentUserId) {
            console.log('❌ Không thể gửi: chưa chọn người dùng');
            return;
        }

        if (!text && !hasImages) {
            console.log('❌ Không thể gửi: không có nội dung tin nhắn');
            return;
        }

        if (!socket || !socket.connected) {
            console.log('❌ Không thể gửi: mất kết nối socket');
            alert('Mất kết nối đến server, vui lòng tải lại trang');
            return;
        }

        console.log('✅ Điều kiện gửi tin nhắn OK, đang gửi...');

        // Ưu tiên gửi ảnh nếu có
        if (hasImages) {
            console.log('📸 Phát hiện có ảnh, gửi ảnh...');
            uploadAndSendAdminImages();
        } else if (text) {
            // Gửi tin nhắn văn bản
            console.log('💬 Gửi tin nhắn văn bản:', text);
            const messageData = {
                text: text,
                userId: currentUserId
            };

            socket.emit('adminMessage', messageData, (response) => {
                if (response.success) {
                    console.log('✅ Tin nhắn văn bản đã được gửi thành công');
                    addMessageToChat({ text: text, sent_at: new Date().toLocaleString() }, 'admin');
                    messageInput.value = ''; // Clear input sau khi gửi thành công
                    updateSendButtonState();
                } else {
                    console.error('❌ Lỗi khi gửi tin nhắn văn bản:', response.error);
                    alert('Không thể gửi tin nhắn văn bản: ' + response.error);
                }
            });
        }
    }

    // Hàm upload và gửi nhiều ảnh từ Admin
    async function uploadAndSendAdminImages() {
        if (selectedImageFiles.length === 0 || !currentUserId || !socket?.connected) {
            // addDebugInfo("❌ Không thể gửi ảnh: Chưa chọn ảnh, chưa chọn người dùng hoặc mất kết nối", "error");
            return;
        }

        // Disable nút gửi và upload
        sendMessageBtn.disabled = true;
        imageUploadBtn.disabled = true;
        messageInput.disabled = true;
        uploadProgressContainer.style.display = 'block';
        uploadStatus.textContent = `Đang chuẩn bị upload 0/${selectedImageFiles.length}...`;
        uploadProgressBar.style.width = '0%';
        uploadProgressBar.textContent = '0%';

        const uploadedUrls = [];
        let failedUploads = 0;
        let totalProgress = 0;
        const totalFiles = selectedImageFiles.length;

        const uploadPromises = selectedImageFiles.map((file, index) => {
            return new Promise(async (resolve, reject) => {
                const formData = new FormData();
                formData.append('image', file);

                try {
                    // Sử dụng fetch để upload (hoặc XMLHttpRequest nếu cần theo dõi progress chi tiết hơn)
                    const response = await fetch('http://localhost:3001/upload', {
                        method: 'POST',
                        headers: {
                            'Authorization': `Bearer ${adminToken}`,
                            'Accept': 'application/json',
                            // 'Content-Type': 'multipart/form-data' // Fetch tự đặt header này
                        },
                        body: formData
                    });

                    const result = await response.json();

                    if (response.ok && result.success && result.file?.url) {
                        uploadedUrls.push(result.file.url);
                        totalProgress += (100 / totalFiles); // Cập nhật tiến trình tổng thể
                        uploadProgressBar.style.width = `${Math.min(totalProgress, 100)}%`;
                        uploadProgressBar.textContent = `${Math.round(Math.min(totalProgress, 100))}%`;
                        uploadStatus.textContent = `Đã upload ${uploadedUrls.length}/${totalFiles}...`;
                        resolve(result.file.url);
                    } else {
                        console.error(`Lỗi upload ảnh ${index + 1}:`, result.message || response.statusText);
                        failedUploads++;
                        reject(new Error(result.message || `HTTP error ${response.status}`));
                    }
                } catch (error) {
                    console.error(`Lỗi mạng khi upload ảnh ${index + 1}:`, error);
                    failedUploads++;
                    reject(error);
                }
            });
        });

        try {
            // Chờ tất cả các promise hoàn thành (hoặc thất bại)
            await Promise.allSettled(uploadPromises);

            if (uploadedUrls.length > 0) {
                // Gửi sự kiện adminMultipleImagesUpload qua socket
                const messageData = {
                    userId: currentUserId, // ID người nhận
                    images: uploadedUrls
                };

                socket.emit('adminMultipleImagesUpload', messageData, (response) => {
                    if (response.success) {
                        console.log('✅ Nhóm ảnh đã được gửi thành công qua socket');
                        // Hiển thị nhóm ảnh đã gửi trong chat của admin
                        addImageGroupToChat(uploadedUrls, new Date().toLocaleString(), 'admin', { name: 'Admin' }); // Truyền senderInfo đơn giản
                        // if (failedUploads > 0) {
                        //     addDebugInfo(`⚠️ ${failedUploads}/${totalFiles} ảnh upload thất bại.`, "warning");
                        // }
                    } else {
                        console.error('❌ Lỗi khi gửi nhóm ảnh qua socket:', response.error);
                        // addDebugInfo(`Lỗi gửi nhóm ảnh qua socket: ${response.error}`, "error");
                        alert('Không thể gửi nhóm ảnh qua socket: ' + response.error);
                    }
                });
            } else {
                 //  addDebugInfo(`❌ Upload tất cả ${totalFiles} ảnh thất bại.`, "error");
                 alert(`Upload tất cả ${totalFiles} ảnh thất bại.`);
            }

        } catch (error) {
            // Lỗi không mong muốn trong quá trình xử lý promises
            console.error("Lỗi không mong muốn khi xử lý upload:", error);
            // addDebugInfo("Lỗi không mong muốn khi xử lý upload.", "error");
        } finally {
            // Reset trạng thái sau khi hoàn tất (hoặc lỗi)
            selectedImageFiles = [];
            imageInput.value = null;
            updateImagePreview();
            uploadProgressContainer.style.display = 'none';
            // Kích hoạt lại các nút
            enableChat(); // Hàm enableChat đã có sẵn
            updateSendButtonState();
        }
    }

    // Xử lý sự kiện nút gửi (đã được sửa ở trên)
    sendMessageBtn.addEventListener('click', sendMessage);

    // Xử lý sự kiện chọn ảnh
    imageInput.addEventListener('change', function(event) {
        const files = event.target.files;
        if (!files || files.length === 0) return;

        selectedImageFiles = Array.from(files);
        updateImagePreview();
    });

    // Hàm cập nhật khu vực preview
    function updateImagePreview() {
        previewList.innerHTML = ''; // Xóa preview cũ
        selectedImageFiles.forEach((file, index) => {
            const reader = new FileReader();
            reader.onload = function(e) {
                const previewItem = document.createElement('div');
                previewItem.className = 'preview-item position-relative';
                previewItem.innerHTML = `
                    <img src="${e.target.result}" alt="${file.name}" style="max-height: 60px; max-width: 60px; object-fit: cover; border-radius: 4px;">
                    <button class="btn btn-sm btn-danger position-absolute top-0 end-0 p-0 m-0"
                            style="width: 18px; height: 18px; line-height: 1; font-size: 10px;"
                            data-index="${index}"
                            title="Xóa ảnh này">
                        <i class="fas fa-times"></i>
                    </button>
                `;
                previewList.appendChild(previewItem);
            }
            reader.readAsDataURL(file);
        });

        selectedCountSpan.textContent = `${selectedImageFiles.length} ảnh được chọn`;
        imagePreviewContainer.style.display = selectedImageFiles.length > 0 ? 'block' : 'none';

        // Kích hoạt/Vô hiệu hóa nút gửi dựa trên việc có ảnh hoặc text
        updateSendButtonState();
    }

    // Xử lý xóa ảnh đơn lẻ từ preview
    previewList.addEventListener('click', function(event) {
        if (event.target.closest('button') && event.target.closest('button').dataset.index !== undefined) {
            const indexToRemove = parseInt(event.target.closest('button').dataset.index, 10);
            selectedImageFiles.splice(indexToRemove, 1); // Xóa file khỏi mảng
            updateImagePreview(); // Cập nhật lại preview
        }
    });

    // Xử lý hủy toàn bộ ảnh đã chọn
    cancelUpload.addEventListener('click', function() {
        selectedImageFiles = [];
        imageInput.value = null; // Reset input file
        updateImagePreview();
    });

    // Hàm cập nhật trạng thái nút Gửi
    function updateSendButtonState() {
        const hasText = messageInput.value.trim().length > 0;
        const hasImages = selectedImageFiles.length > 0;
        const hasContent = hasText || hasImages;

        // Chỉ kích hoạt nút khi có nội dung để gửi VÀ đã chọn người dùng
        sendMessageBtn.disabled = !hasContent || !currentUserId || !socket?.connected;

        console.log('🔄 Cập nhật trạng thái nút gửi:', {
            hasText,
            hasImages,
            currentUserId: !!currentUserId,
            socketConnected: !!socket?.connected,
            buttonDisabled: sendMessageBtn.disabled
        });

        // Kích hoạt/vô hiệu hóa nút upload ảnh dựa trên việc đã chọn người dùng và socket đã kết nối
        imageUploadBtn.disabled = !currentUserId || !socket?.connected;
    }

    // Gọi updateSendButtonState khi input text thay đổi
    messageInput.addEventListener('input', updateSendButtonState);

    // Cũng gọi updateSendButtonState khi nhấp vào input để đảm bảo trạng thái nút luôn cập nhật
    messageInput.addEventListener('focus', updateSendButtonState);

    // Xử lý sự kiện nhấn phím Enter để gửi tin nhắn
    messageInput.addEventListener('keypress', function(e) {
        if (e.key === "Enter" && !e.shiftKey) {
            console.log('🔑 Phát hiện phím Enter, đang thử gửi tin nhắn...');
            e.preventDefault(); // Ngăn xuống dòng

            // Kiểm tra trạng thái nút gửi
            if (!sendMessageBtn.disabled) {
                console.log('✅ Nút gửi đang được kích hoạt, thực hiện gửi tin nhắn...');
                sendMessage();
            } else {
                console.log('❌ Nút gửi đang bị vô hiệu hóa, không thể gửi tin nhắn');
            }
        }
    });

    // Kích hoạt nút upload ảnh
    imageUploadBtn.addEventListener('click', function() {
        imageInput.click(); // Mở hộp thoại chọn file
    });

    // Hàm xử lý bộ đệm ảnh từ client
    function processClientImageBuffer() {
        const bufferedCount = pendingClientImages.length;
        console.log(`⏳ [Admin Buffer] Gọi processClientImageBuffer - Số ảnh trong buffer: ${bufferedCount}`);
        if (bufferedCount > 0) {
            console.log(`⏳ [Admin Buffer] Đang xử lý ${bufferedCount} ảnh trong buffer.`);
            if (bufferedCount > 1) {
                // Tạo nhóm ảnh
                console.log("⏳ [Admin Buffer] Tạo nhóm ảnh.");
                const firstImageMsg = pendingClientImages[0];
                if (currentUserId === firstImageMsg.sender_id) {
                    addImageGroupToChat(
                        pendingClientImages.map(msg => msg.image),
                        new Date(firstImageMsg.sent_at || Date.now()).toLocaleString(),
                        'client',
                        firstImageMsg.sender // Truyền sender object nếu có
                    );
                    console.log("✅ [Admin Buffer] Đã hiển thị nhóm ảnh.");
                } else {
                     highlightUserWithNewMessage(firstImageMsg.sender_id);
                     console.log(`✨ [Admin Buffer] Đã highlight user ${firstImageMsg.sender_id} cho nhóm ảnh.`);
                }
            } else {
                // Xử lý ảnh đơn
                console.log("⏳ [Admin Buffer] Xử lý ảnh đơn.");
                const singleImageMsg = pendingClientImages[0];
                 if (currentUserId === singleImageMsg.sender_id) {
                    addMessageToChat(singleImageMsg, 'client');
                     console.log("✅ [Admin Buffer] Đã hiển thị ảnh đơn.");
                 } else {
                     highlightUserWithNewMessage(singleImageMsg.sender_id);
                     console.log(`✨ [Admin Buffer] Đã highlight user ${singleImageMsg.sender_id} cho ảnh đơn.`);
                 }
            }
            pendingClientImages = []; // Xóa bộ đệm
            console.log("🗑️ [Admin Buffer] Đã xóa bộ đệm.");
        }
        if (clientImageBufferTimeout) {
            clearTimeout(clientImageBufferTimeout);
            clientImageBufferTimeout = null;
            console.log("⏱️ [Admin Buffer] Đã xóa timeout.");
        }
    }

    // Kết nối đến socket khi trang load xong
    connectToSocket();
});
</script>
@endsection
