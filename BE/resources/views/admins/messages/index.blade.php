@extends('layouts.admin')

@section('content')
<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-gradient-primary text-white">
        <h5 class="m-0 d-flex align-items-center">
            <i class="fas fa-comments me-2"></i> Quản lý tin nhắn
        </h5>
    </div>
    <div class="card-body p-0">
        <div class="chat-container row g-0">
            <div class="col-md-4 border-end">
                <div class="card border-0">
                    <div class="card-header d-flex justify-content-between align-items-center bg-light py-3">
                        <h6 class="m-0 fw-bold">
                            <i class="fas fa-users me-2"></i> Danh sách người dùng
                        </h6>
                        <span id="connection-status" class="badge bg-success animate__animated animate__pulse animate__infinite">
                            <i class="fas fa-circle me-1" style="font-size: 0.6rem;"></i>
                            Đang kết nối...
                        </span>
                    </div>
                    <div class="card-body p-0 user-list-container" style="height: 75vh; overflow-y: auto;">
                        <ul id="user-list" class="list-group list-group-flush user-list">
                            <li class="list-group-item text-center text-muted py-4 animate__animated animate__fadeIn">
                                <i class="fas fa-user-clock fa-2x mb-2 text-light"></i>
                                <p>Chưa có người dùng kết nối</p>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="card border-0 h-100 d-flex flex-column">
                    <div class="card-header d-flex justify-content-between align-items-center bg-light py-3">
                        <h6 class="m-0 fw-bold">
                            <i class="fas fa-comment-dots me-2"></i> Cuộc trò chuyện
                        </h6>
                        <span id="chatting-with" class="badge bg-secondary">
                            <i class="fas fa-user me-1"></i>
                            Chưa chọn người dùng
                        </span>
                    </div>
                    <div class="card-body p-0 d-flex flex-column" style="height: 75vh;">
                        <div id="chat-box" class="chat-messages p-3" style="flex: 1; overflow-y: auto; background-color: #f8f9fa;">
                            <div class="text-center text-muted py-5">
                                <i class="fas fa-comments fa-3x mb-3 text-light"></i>
                                <p>Chọn một người dùng từ danh sách để bắt đầu trò chuyện</p>
                            </div>
                        </div>
                        <div class="message-input-container p-3 border-top">
                            <div class="input-group">
                                <input type="text" id="messageInput" class="form-control border-end-0"
                                       placeholder="Nhập tin nhắn..." disabled>
                                <button id="imageUploadBtn" class="btn btn-outline-secondary border-start-0 border-end-0"
                                        type="button" disabled>
                                    <i class="fas fa-image"></i>
                                </button>
                                <!-- Thêm nút emoji picker -->
                                <button id="emojiBtn" class="btn btn-outline-secondary border-start-0 border-end-0"
                                        type="button" disabled>
                                    <i class="fas fa-smile"></i>
                                </button>
                                <button id="sendMessageBtn" class="btn btn-primary px-4" disabled>
                                    <i class="fas fa-paper-plane me-1"></i> Gửi
                                </button>

                                <!-- Thêm input ẩn để upload ảnh -->
                                <input type="file" id="imageInput" accept="image/*" multiple style="display: none;">
                            </div>

                            <!-- Thêm container emoji picker -->
                            <div id="emoji-picker-container" class="mt-2 animate__animated animate__fadeIn" style="display: none;">
                                <div class="card border">
                                    <div class="card-body p-2">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <h6 class="m-0">Emoji</h6>
                                            <button id="close-emoji" class="btn btn-sm btn-light">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                        <div class="emoji-grid">
                                            <!-- Emoji phổ biến -->
                                            <button class="emoji-btn" data-emoji="😊">😊</button>
                                            <button class="emoji-btn" data-emoji="😃">😃</button>
                                            <button class="emoji-btn" data-emoji="😍">😍</button>
                                            <button class="emoji-btn" data-emoji="😘">😘</button>
                                            <button class="emoji-btn" data-emoji="🙂">🙂</button>
                                            <button class="emoji-btn" data-emoji="😉">😉</button>
                                            <button class="emoji-btn" data-emoji="😁">😁</button>
                                            <button class="emoji-btn" data-emoji="😎">😎</button>
                                            <button class="emoji-btn" data-emoji="😢">😢</button>
                                            <button class="emoji-btn" data-emoji="😔">😔</button>
                                            <button class="emoji-btn" data-emoji="❤️">❤️</button>
                                            <button class="emoji-btn" data-emoji="👍">👍</button>
                                            <button class="emoji-btn" data-emoji="👋">👋</button>
                                            <button class="emoji-btn" data-emoji="🎉">🎉</button>
                                            <button class="emoji-btn" data-emoji="✅">✅</button>
                                            <button class="emoji-btn" data-emoji="⭐">⭐</button>
                                            <button class="emoji-btn" data-emoji="🔥">🔥</button>
                                            <button class="emoji-btn" data-emoji="👌">👌</button>
                                            <button class="emoji-btn" data-emoji="🤔">🤔</button>
                                            <button class="emoji-btn" data-emoji="😴">😴</button>
                                            <!-- Thêm emoji mới -->
                                            <button class="emoji-btn" data-emoji="😂">😂</button>
                                            <button class="emoji-btn" data-emoji="🤣">🤣</button>
                                            <button class="emoji-btn" data-emoji="😅">😅</button>
                                            <button class="emoji-btn" data-emoji="😆">😆</button>
                                            <button class="emoji-btn" data-emoji="🥰">🥰</button>
                                            <button class="emoji-btn" data-emoji="😇">😇</button>
                                            <button class="emoji-btn" data-emoji="😋">😋</button>
                                            <button class="emoji-btn" data-emoji="😜">😜</button>
                                            <button class="emoji-btn" data-emoji="🤪">🤪</button>
                                            <button class="emoji-btn" data-emoji="😝">😝</button>
                                            <button class="emoji-btn" data-emoji="🤩">🤩</button>
                                            <button class="emoji-btn" data-emoji="😡">😡</button>
                                            <button class="emoji-btn" data-emoji="🥺">🥺</button>
                                            <button class="emoji-btn" data-emoji="😭">😭</button>
                                            <button class="emoji-btn" data-emoji="😤">😤</button>
                                            <button class="emoji-btn" data-emoji="🙏">🙏</button>
                                            <button class="emoji-btn" data-emoji="👏">👏</button>
                                            <button class="emoji-btn" data-emoji="💪">💪</button>
                                            <button class="emoji-btn" data-emoji="🤝">🤝</button>
                                            <button class="emoji-btn" data-emoji="💯">💯</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Thêm phần hiển thị trạng thái upload -->
                            <div id="upload-progress-container" class="mt-2 animate__animated animate__fadeIn" style="display: none;">
                                <div class="progress" style="height: 10px;">
                                    <div id="upload-progress-bar" class="progress-bar progress-bar-striped progress-bar-animated bg-success"
                                        role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%">
                                        0%
                                    </div>
                                </div>
                                <div id="upload-status" class="small text-muted mt-1">Đang chuẩn bị tệp...</div>
                            </div>

                            <!-- Thêm phần preview ảnh -->
                            <div id="image-preview-container" class="mt-2 animate__animated animate__fadeIn" style="display: none;">
                                <div class="card border">
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
</div>

<!-- Thêm thư viện Toastify cho các thông báo toast -->
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>

<!-- Thêm thư viện Animate.css để có các hiệu ứng animation -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

<script src="https://cdn.socket.io/4.6.0/socket.io.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/lightbox2@2.11.3/dist/js/lightbox.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/lightbox2@2.11.3/dist/css/lightbox.min.css" rel="stylesheet" />

<style>
/* Custom CSS cho giao diện chat */
.chat-container {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    background-color: #fff;
    border-radius: 0.25rem;
}

/* User list styling */
.user-list .user-item {
    transition: all 0.2s ease;
    padding: 0.75rem 1rem;
    border-left: 3px solid transparent;
    cursor: pointer;
}

.user-list .user-item:hover {
    background-color: rgba(13, 110, 253, 0.05);
}

.user-list .user-item.active {
    background-color: rgba(13, 110, 253, 0.1);
    border-left: 3px solid #0d6efd;
}

/* Thêm: Đảm bảo màu chữ của tên người dùng đủ tối khi được chọn */
.user-list .user-item.active .user-name {
    color: #212529; /* Màu tối mặc định của Bootstrap */
    font-weight: 600; /* Làm đậm hơn một chút */
}

.user-list .user-item.list-group-item-warning {
    border-left: 3px solid #ffc107;
    animation: pulse 2s infinite;
}

/* Message bubbles */
.message-bubble {
    max-width: 80%;
    border-radius: 1rem !important;
    padding: 0.75rem 1rem !important;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
    position: relative;
    transition: all 0.3s ease;
}

/* Emoji grid styling */
.emoji-grid {
    display: grid;
    grid-template-columns: repeat(10, 1fr);
    gap: 5px;
}

.emoji-btn {
    width: 32px;
    height: 32px;
    font-size: 18px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: transparent;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    transition: all 0.2s;
}

.emoji-btn:hover {
    background-color: #f1f1f1;
    transform: scale(1.2);
}

.client-message .message-bubble {
    background-color: #f1f0f0 !important;
    border-top-left-radius: 0.2rem !important;
}

.admin-message .message-bubble {
    background: linear-gradient(135deg, #0d6efd, #0a58ca) !important;
    border-top-right-radius: 0.2rem !important;
}

/* Animations */
.message {
    animation: fadeInUp 0.3s ease;
}

/* Preview image container */
.preview-item {
    transition: transform 0.2s;
}

.preview-item:hover {
    transform: scale(1.05);
}

/* Custom animation */
@keyframes pulse {
    0% {
        box-shadow: 0 0 0 0 rgba(255, 193, 7, 0.4);
    }
    70% {
        box-shadow: 0 0 0 5px rgba(255, 193, 7, 0);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(255, 193, 7, 0);
    }
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>

<!-- Script kích hoạt tương tác người dùng cho âm thanh -->
<script>
// Kích hoạt audio elements từ order-notifications.js
document.addEventListener('DOMContentLoaded', function() {
    // Tạo tương tác người dùng giả khi trang chat được tải
    const triggerAudioActivation = () => {
        // Kích hoạt tất cả các audio element
        const audioElements = document.querySelectorAll('audio');
        audioElements.forEach(audio => {
            try {
                // Kích hoạt với âm lượng nhỏ
                audio.volume = 0.01;
                audio.play().then(() => {
                    audio.pause();
                    audio.currentTime = 0;
                    audio.volume = 0.8;
                    console.log('✅ Audio đã được kích hoạt trong trang chat');
                }).catch(e => console.log('⚠️ Không thể kích hoạt audio:', e));
            } catch (e) {
                console.warn('⚠️ Lỗi khi khởi tạo audio trong trang chat:', e);
            }
        });

        // Kích hoạt audio events từ order-notifications.js
        const event = new MouseEvent('click', {
            view: window,
            bubbles: true,
            cancelable: true
        });
        document.dispatchEvent(event);
    };

    // Kích hoạt sau khi trang tải xong và sau tương tác người dùng đầu tiên
    setTimeout(triggerAudioActivation, 1000);
    document.addEventListener('click', triggerAudioActivation, { once: true });
});
</script>

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

    // Elements for emoji picker
    const emojiBtn = document.getElementById('emojiBtn');
    const emojiPickerContainer = document.getElementById('emoji-picker-container');
    const closeEmojiBtn = document.getElementById('close-emoji');
    const emojiButtons = document.querySelectorAll('.emoji-btn');

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
                'http://localhost:3002',
                'http://127.0.0.1:3002'
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
                socket.emit("adminConnect", {}, (response) => {
                    if (response && response.success) {
                        console.log("✅ Đăng ký làm admin thành công");
                        // Nếu response có danh sách người dùng, cập nhật ngay
                        if (response.userList && Array.isArray(response.userList)) {
                            console.log("📋 Đã nhận danh sách người dùng từ đăng ký admin:", response.userList);
                            updateUserList(response.userList);
                        } else {
                            console.log("⏳ Chờ cập nhật danh sách người dùng từ sự kiện currentUsers...");
                            // Chủ động tải lại danh sách người dùng sau 3 giây nếu không nhận được sự kiện
                            setTimeout(() => {
                                if (userList.innerHTML.includes('Chưa có người dùng kết nối') && socket.connected) {
                                    console.log("🔄 Gửi yêu cầu lấy danh sách người dùng...");
                                    socket.emit("adminConnect", {}, () => {
                                        console.log("🔄 Đã gửi lại yêu cầu adminConnect");
                                    });
                                }
                            }, 3000);
                        }
                    } else {
                        console.error("❌ Đăng ký admin thất bại:", response ? response.error : "Không có phản hồi");
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

                // Không cần phát âm thanh thông báo và hiển thị toast ở đây nữa
                // vì đã được xử lý bởi order-notifications.js khi ở bất kỳ trang nào

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

                // Không cần phát âm thanh thông báo và hiển thị toast ở đây nữa
                // vì đã được xử lý bởi order-notifications.js khi ở bất kỳ trang nào

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
            userList.innerHTML = `
                <li class="list-group-item text-center text-muted py-4 animate__animated animate__fadeIn">
                    <i class="fas fa-user-clock fa-2x mb-2 text-light"></i>
                    <p>Chưa có người dùng kết nối</p>
                </li>`;
            return;
        }

        userList.innerHTML = '';
        users.forEach((user, index) => {
            // Thêm animation delay theo thứ tự
            setTimeout(() => {
                addUserToList(user);
            }, index * 100); // Delay tăng dần cho mỗi người dùng
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
        li.className = 'list-group-item d-flex justify-content-between align-items-center user-item animate__animated animate__fadeInLeft';

        // Tạo avatar ngẫu nhiên nếu không có avatar
        const colors = ['#3498db', '#2ecc71', '#e74c3c', '#f39c12', '#9b59b6', '#1abc9c'];
        const randomColor = colors[Math.floor(Math.random() * colors.length)];
        const initials = name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();

        li.innerHTML = `
            <div class="d-flex align-items-center">
                <div class="user-avatar me-2" style="width: 35px; height: 35px; background-color: ${randomColor};
                     border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold;">
                    ${initials}
                </div>
                <span class="user-name">${name}</span>
            </div>
            <span class="badge bg-secondary px-2 py-1 rounded-pill">
                <i class="fas fa-clock me-1"></i>${new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}
            </span>
        `;

        // Thêm sự kiện click để chọn người dùng
        li.addEventListener('click', () => {
            // Tìm user đang active trước đó (nếu có)
            const previouslyActiveUser = userList.querySelector('.user-item.active');
            if (previouslyActiveUser && previouslyActiveUser !== li) {
                previouslyActiveUser.classList.remove('active');
                // Không xóa warning hoặc badge của user cũ ở đây, chỉ bỏ active
            }

            // Xóa highlight và badge của user vừa được click
            li.classList.remove('list-group-item-warning');
            const badge = li.querySelector('.new-message-badge');
            if (badge) badge.remove();

            // Highlight user được chọn với animation
            li.classList.add('active');
            li.classList.add('animate__pulse');
            setTimeout(() => {
                li.classList.remove('animate__pulse');
            }, 500);

            // Lưu user_id hiện tại
            currentUserId = userId;

            // Hiển thị tên người dùng đang chat với hiệu ứng
            chattingWith.classList.add('animate__animated', 'animate__fadeIn');
            chattingWith.textContent = name;
            chattingWith.innerHTML = `<i class="fas fa-user me-1"></i> ${name}`;
                setTimeout(() => {
                chattingWith.classList.remove('animate__animated', 'animate__fadeIn');
                }, 1000);

            // Kích hoạt ô nhập tin nhắn
            enableChat();

            // Hiển thị loading trước khi tải tin nhắn
            chatBox.innerHTML = `
                <div class="text-center p-5 animate__animated animate__fadeIn">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2">Đang tải tin nhắn...</p>
                </div>`;

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

                // Hiển thị các nhóm tin nhắn với hiệu ứng delay
                const groupedMessages = groupMessagesForDisplay(messages);

                // Sử dụng setTimeout để tạo hiệu ứng kéo dài hiển thị tin nhắn
                groupedMessages.forEach((group, index) => {
                    setTimeout(() => {
                        // Nếu group chỉ chứa ảnh, dùng addImageGroupToChat
                        if (group.isImageGroup) {
                            // Truyền thêm group.sender vào hàm và group.lastTime (Date object) trực tiếp
                            addImageGroupToChat(group.images, group.lastTime, group.type, group.sender);
                        } else {
                            // Nếu group chứa cả text và ảnh (hoặc chỉ text)
                            // Hiển thị từng message trong group
                            group.messages.forEach(msg => {
                                addMessageToChat(msg, group.type); // msg đã có sent_at
                            });
                        }

                        // Cuộn xuống dưới
                        chatBox.scrollTop = chatBox.scrollHeight;
                    }, index * 20); // Delay 20ms giữa các nhóm tin nhắn
                });

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
             // Thêm class warning để thay đổi màu nền và hiệu ứng pulse
             userElement.classList.add('list-group-item-warning');

             // Thêm hiệu ứng đập nhanh 3 lần
             userElement.classList.add('animate__animated', 'animate__headShake');
            setTimeout(() => {
                 userElement.classList.remove('animate__animated', 'animate__headShake');
            }, 1000);

             // Chỉ thêm badge nếu chưa có
             if (!userElement.querySelector('.new-message-badge')) {
                 const badge = document.createElement('span');
                 badge.className = 'badge bg-danger rounded-pill new-message-badge animate__animated animate__fadeIn ms-1';
                 badge.innerHTML = '<i class="fas fa-bell me-1"></i> Mới';

                 // Chèn vào trước badge thời gian (nếu có) để không bị đẩy xuống
                 const timeBadge = userElement.querySelector('.badge.bg-secondary');
                 if (timeBadge) {
                     userElement.querySelector('.d-flex').appendChild(badge);
                 } else {
                     // Nếu không có time badge, thêm vào cuối
                     userElement.querySelector('.d-flex').appendChild(badge);
                 }

                 // Phát âm thanh thông báo (nếu cần)
                 try {
                     const notificationSound = new Audio('/assets/sounds/notification.mp3');
                     notificationSound.volume = 0.5;
                     notificationSound.play();
                 } catch (e) {
                     console.warn('Không thể phát âm thanh thông báo:', e);
                 }
             }
         } else {
             // Log nếu không tìm thấy user (có thể cần refresh list)
             console.warn(`Không tìm thấy user ${senderId} để highlight`);
         }
    }

    // Thêm hàm mới để hiển thị nhóm ảnh (Cập nhật tham số và logic senderName)
    function addImageGroupToChat(imageUrls, sentTimeRaw, senderType, senderInfo) {
        if (!imageUrls || imageUrls.length === 0) return;

        const formattedTime = formatMessageTime(sentTimeRaw);

        const messageDiv = document.createElement('div');
        // Sử dụng senderType cho class và thêm hiệu ứng animation
        messageDiv.className = `message ${senderType}-message mb-3 animate__animated animate__fadeInUp animate__faster`;

        let imagesContent = '';
        const msgGroup = `msg-${Date.now()}-${Math.random().toString(36).substring(2, 7)}`; // Tạo ID group duy nhất

        if (imageUrls.length === 1) {
            // 1 ảnh: Hiển thị lớn
            imagesContent = `<div class="message-image mb-2">
                <a href="${imageUrls[0]}" data-lightbox="${msgGroup}" data-title="Hình ảnh">
                    <img src="${imageUrls[0]}" alt="Hình ảnh" class="img-fluid rounded shadow-sm"
                         style="max-height: 200px; cursor: pointer; transition: all 0.3s ease;">
                </a>
            </div>`;
        } else if (imageUrls.length === 2) {
            // 2 ảnh: Hiển thị 50-50
            imagesContent = `<div class="message-images-grid mb-2">
                <div class="d-flex" style="gap: 4px;">
                    <div style="width: 50%;">
                        <a href="${imageUrls[0]}" data-lightbox="${msgGroup}" data-title="Hình ảnh 1">
                            <img src="${imageUrls[0]}" alt="Hình ảnh 1" class="w-100 rounded shadow-sm"
                                 style="height: 120px; object-fit: cover; cursor: pointer; transition: all 0.3s ease;">
                        </a>
                    </div>
                    <div style="width: 50%;">
                        <a href="${imageUrls[1]}" data-lightbox="${msgGroup}" data-title="Hình ảnh 2">
                            <img src="${imageUrls[1]}" alt="Hình ảnh 2" class="w-100 rounded shadow-sm"
                                 style="height: 120px; object-fit: cover; cursor: pointer; transition: all 0.3s ease;">
                        </a>
                    </div>
                </div>
            </div>`;
        } else if (imageUrls.length === 3) {
            // 3 ảnh: 2 trên, 1 dưới
            imagesContent = `<div class="message-images-grid mb-2">
                <div style="display: grid; grid-template-columns: 1fr 1fr; grid-template-rows: auto auto; gap: 4px;">
                    <div style="grid-column: 1; grid-row: 1;">
                        <a href="${imageUrls[0]}" data-lightbox="${msgGroup}" data-title="Hình ảnh 1">
                            <img src="${imageUrls[0]}" alt="Hình ảnh 1" class="w-100 rounded shadow-sm"
                                 style="height: 100px; object-fit: cover; cursor: pointer; transition: all 0.3s ease;">
                        </a>
                    </div>
                    <div style="grid-column: 2; grid-row: 1;">
                        <a href="${imageUrls[1]}" data-lightbox="${msgGroup}" data-title="Hình ảnh 2">
                            <img src="${imageUrls[1]}" alt="Hình ảnh 2" class="w-100 rounded shadow-sm"
                                 style="height: 100px; object-fit: cover; cursor: pointer; transition: all 0.3s ease;">
                        </a>
                    </div>
                    <div style="grid-column: span 2; grid-row: 2;">
                        <a href="${imageUrls[2]}" data-lightbox="${msgGroup}" data-title="Hình ảnh 3">
                            <img src="${imageUrls[2]}" alt="Hình ảnh 3" class="w-100 rounded shadow-sm"
                                 style="height: 100px; object-fit: cover; cursor: pointer; transition: all 0.3s ease;">
                        </a>
                    </div>
                </div>
            </div>`;
        } else { // 4+ ảnh
            const remainingCount = imageUrls.length - 3;
            imagesContent = `<div class="message-images-grid mb-2">
                <div style="display: grid; grid-template-columns: 1fr 1fr; grid-template-rows: auto auto; gap: 4px;">
                    <div style="grid-column: 1; grid-row: 1;">
                        <a href="${imageUrls[0]}" data-lightbox="${msgGroup}" data-title="Hình ảnh 1">
                            <img src="${imageUrls[0]}" alt="Hình ảnh 1" class="w-100 rounded shadow-sm"
                                 style="height: 100px; object-fit: cover; cursor: pointer; transition: all 0.3s ease;">
                        </a>
                    </div>
                    <div style="grid-column: 2; grid-row: 1;">
                        <a href="${imageUrls[1]}" data-lightbox="${msgGroup}" data-title="Hình ảnh 2">
                            <img src="${imageUrls[1]}" alt="Hình ảnh 2" class="w-100 rounded shadow-sm"
                                 style="height: 100px; object-fit: cover; cursor: pointer; transition: all 0.3s ease;">
                        </a>
                    </div>
                    <div style="grid-column: 1; grid-row: 2; position: relative;">
                        <a href="${imageUrls[2]}" data-lightbox="${msgGroup}" data-title="Hình ảnh 3">
                            <img src="${imageUrls[2]}" alt="Hình ảnh 3" class="w-100 rounded shadow-sm"
                                 style="height: 100px; object-fit: cover; cursor: pointer; transition: all 0.3s ease;">
                        </a>
                    </div>
                    <div style="grid-column: 2; grid-row: 2; position: relative;">
                        <a href="${imageUrls[3]}" data-lightbox="${msgGroup}" data-title="Hình ảnh 4" class="position-relative d-block">
                            <img src="${imageUrls[3]}" alt="Hình ảnh 4" class="w-100 rounded shadow-sm"
                                 style="height: 100px; object-fit: cover; filter: brightness(50%); cursor: pointer; transition: all 0.3s ease;">
                            <div class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center text-white rounded"
                                 style="font-size: 1.1rem; font-weight: bold; background-color: rgba(0,0,0,0.4);">
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

        // Thêm xử lý emoji cho text nếu có kèm theo tin nhắn văn bản
        let textMessage = '';
        if (senderInfo && senderInfo.text) {
            const textWithEmojis = senderInfo.text
                .replace(/:D/g, '😃')
                .replace(/:\)/g, '🙂')
                .replace(/:\(/g, '😔')
                .replace(/<3/g, '❤️')
                .replace(/:P/g, '😛')
                .replace(/;\)/g, '😉')
                .replace(/:\|/g, '😐')
                .replace(/:o/g, '😮')
                .replace(/:O/g, '😮')
                .replace(/8\)/g, '😎')
                .replace(/:'\(/g, '😢');
            textMessage = `<div class="message-text mb-2">${textWithEmojis}</div>`;
        }

        // Sử dụng senderType để quyết định layout với kiểu dáng mới
        if (senderType === 'client') {
            messageDiv.innerHTML = `
                <div class="d-flex">
                    <div class="message-bubble bg-light p-2 rounded">
                        <div class="message-sender text-primary fw-semibold small mb-1">${senderName}</div>
                        ${textMessage}
                        ${imagesContent}
                        <div class="message-time text-muted small mt-1">${formattedTime}</div>
                    </div>
                </div>
            `;
        } else { // senderType === 'admin'
            messageDiv.innerHTML = `
                <div class="d-flex justify-content-end">
                    <div class="message-bubble bg-primary text-white p-2 rounded">
                        <div class="message-sender text-white-50 small mb-1">${senderName}</div>
                        ${textMessage}
                        ${imagesContent}
                        <div class="message-time text-white-50 small mt-1">${formattedTime}</div>
                    </div>
                </div>
            `;
        }

        chatBox.appendChild(messageDiv);

        // Thêm hiệu ứng cuộn mượt
        setTimeout(() => {
            chatBox.scrollTo({
                top: chatBox.scrollHeight,
                behavior: 'smooth'
            });
        }, 100);

        // Khởi tạo lại lightbox với các tùy chọn mới
        if (typeof lightbox !== 'undefined') {
            lightbox.option({
                'resizeDuration': 200,
                'wrapAround': true,
                'albumLabel': "Hình ảnh %1 / %2",
                'fadeDuration': 300,
                'imageFadeDuration': 300
            });
        }
    }

    // Thêm tin nhắn vào khung chat (Cập nhật để chỉ xử lý text hoặc ảnh đơn)
    function addMessageToChat(message, senderType) { // Đổi tên tham số sender thành senderType cho rõ ràng
        const messageDiv = document.createElement('div');
        // Sử dụng senderType cho class và thêm hiệu ứng animate__animated
        messageDiv.className = `message ${senderType}-message mb-3 animate__animated animate__fadeInUp animate__faster`;

        let messageContent = '';
        const formattedTime = formatMessageTime(message.sent_at); // Định dạng thời gian

         // Lấy tên từ message.sender, fallback nếu cần
        const senderName = senderType === 'client' ? (message.sender?.name || 'Khách hàng') : 'Admin';

        // Nếu là tin nhắn ảnh đơn lẻ
        if (message.image && !message.text) {
            const msgGroup = `msg-${Date.now()}-${Math.random().toString(36).substring(2, 7)}`;
            messageContent += `<div class="message-image mb-2">
                <a href="${message.image}" data-lightbox="${msgGroup}" data-title="Hình ảnh">
                    <img src="${message.image}" alt="Hình ảnh" class="img-fluid rounded shadow-sm" style="max-height: 200px; cursor: pointer; transition: transform 0.2s ease;">
                </a>
            </div>`;
        } else if (message.text) {
            // Xử lý tin nhắn văn bản, thêm emojis tự động
            const textWithEmojis = message.text
                .replace(/:D/g, '😃')
                .replace(/:\)/g, '🙂')
                .replace(/:\(/g, '😔')
                .replace(/<3/g, '❤️');
            // Nếu có văn bản thì hiển thị văn bản với kiểu dáng mới
            messageContent += `<div class="message-text">${textWithEmojis}</div>`;
        }

        // Phần còn lại của hàm với định dạng mới
        if (senderType === 'client') {
            messageDiv.innerHTML = `
                <div class="d-flex">
                    <div class="message-bubble bg-light p-2 rounded">
                        <div class="message-sender text-primary fw-semibold small mb-1">${senderName}</div>
                        ${messageContent}
                        <div class="message-time text-muted small mt-1">${formattedTime}</div>
                    </div>
                </div>
            `;
        } else { // senderType === 'admin'
            messageDiv.innerHTML = `
                <div class="d-flex justify-content-end">
                    <div class="message-bubble bg-primary text-white p-2 rounded">
                        <div class="message-sender text-white-50 small mb-1">${senderName}</div>
                        ${messageContent}
                        <div class="message-time text-white-50 small mt-1">${formattedTime}</div>
                    </div>
                </div>
            `;
        }

        chatBox.appendChild(messageDiv);

        // Thêm hiệu ứng cuộn mượt
        setTimeout(() => {
            chatBox.scrollTo({
                top: chatBox.scrollHeight,
                behavior: 'smooth'
            });
        }, 100);

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
        emojiBtn.disabled = false; // Thêm dòng này để bật nút emoji

        // Thêm hiệu ứng focus cho input
        messageInput.classList.add('animate__animated', 'animate__fadeIn');
                setTimeout(() => {
            messageInput.classList.remove('animate__animated', 'animate__fadeIn');
            messageInput.focus();
        }, 500);

        // Thêm hiệu ứng cho nút gửi và upload
        sendMessageBtn.classList.add('animate__animated', 'animate__fadeIn');
        imageUploadBtn.classList.add('animate__animated', 'animate__fadeIn');
                            setTimeout(() => {
            sendMessageBtn.classList.remove('animate__animated', 'animate__fadeIn');
            imageUploadBtn.classList.remove('animate__animated', 'animate__fadeIn');
        }, 500);
    }

    // Tắt chức năng chat
    function disableChat() {
        messageInput.disabled = true;
        sendMessageBtn.disabled = true;
        imageUploadBtn.disabled = true;
        emojiBtn.disabled = true; // Thêm dòng này để tắt nút emoji
        currentUserId = null;
        chattingWith.innerHTML = '<i class="fas fa-user me-1"></i> Chưa chọn người dùng';
    }

    // Gửi tin nhắn (Cập nhật để xử lý cả text và ảnh)
    function sendMessage() {
        const text = messageInput.value.trim();
        const hasImages = selectedImageFiles.length > 0;

        if (!currentUserId || (!text && !hasImages)) return;

        // Thêm hiệu ứng cho nút gửi
        sendMessageBtn.classList.add('animate__animated', 'animate__pulse');
        setTimeout(() => {
            sendMessageBtn.classList.remove('animate__animated', 'animate__pulse');
        }, 300);

        // Gửi ảnh và/hoặc văn bản
        if (hasImages) {
            uploadAndSendAdminImages(text); // Truyền text vào hàm uploadAndSendAdminImages
        } else if (text) {
            // Gửi tin nhắn văn bản
            const messageData = {
                text: text,
                userId: currentUserId
            };

            // Disable nút gửi tạm thời để tránh gửi nhiều lần
            sendMessageBtn.disabled = true;

            // Hiệu ứng đang gửi
            const originalText = sendMessageBtn.innerHTML;
            sendMessageBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Đang gửi';

            socket.emit('adminMessage', messageData, (response) => {
                // Khôi phục nút gửi
                sendMessageBtn.innerHTML = originalText;
                sendMessageBtn.disabled = false;

                if (response.success) {
                    console.log('Tin nhắn văn bản đã được gửi thành công');
                    // Gửi đối tượng Date() thay vì toLocaleString()
                    addMessageToChat({ text: text, sent_at: new Date() }, 'admin');

                    // Hiệu ứng xóa text input
                    messageInput.value = ''; // Clear input sau khi gửi thành công
                    messageInput.classList.add('animate__animated', 'animate__fadeOut');
                setTimeout(() => {
                        messageInput.classList.remove('animate__animated', 'animate__fadeOut');
                        messageInput.focus();
                    }, 300);

                    updateSendButtonState();
                } else {
                    console.error('Lỗi khi gửi tin nhắn văn bản:', response.error);

                    // Hiển thị thông báo lỗi với Toastify
                Toastify({
                        text: `Không thể gửi tin nhắn: ${response.error}`,
                        duration: 3000,
                    close: true,
                    gravity: "top",
                        position: "right",
                    backgroundColor: "linear-gradient(to right, #ff5f6d, #ffc371)",
                    stopOnFocus: true
                }).showToast();
                }
            });
        }
    }

    // Hàm upload và gửi nhiều ảnh từ Admin
    async function uploadAndSendAdminImages(textMessage = '') {
        if (selectedImageFiles.length === 0 || !currentUserId || !socket?.connected) {
            return;
        }

        // Lưu tin nhắn văn bản (nếu có)
        const hasText = textMessage && textMessage.trim() !== '';

        // Disable nút gửi và upload
        sendMessageBtn.disabled = true;
        imageUploadBtn.disabled = true;
        messageInput.disabled = true;

        // Hiển thị progress bar với animation
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
                    // Hiển thị tên file đang upload
                    uploadStatus.textContent = `Đang upload: ${file.name} (${index + 1}/${totalFiles})`;

                    // Sử dụng fetch để upload
                    const response = await fetch('http://localhost:3002/upload', {
                        method: 'POST',
                        headers: {
                            'Authorization': `Bearer ${adminToken}`,
                            'Accept': 'application/json',
                        },
                        body: formData
                    });

                    const result = await response.json();

                    if (response.ok && result.success && result.file?.url) {
                        uploadedUrls.push(result.file.url);
                        totalProgress += (100 / totalFiles); // Cập nhật tiến trình tổng thể

                        // Cập nhật progress bar với animation
                        uploadProgressBar.style.width = `${Math.min(totalProgress, 100)}%`;
                        uploadProgressBar.textContent = `${Math.round(Math.min(totalProgress, 100))}%`;
                        uploadStatus.textContent = `Đã upload ${uploadedUrls.length}/${totalFiles}: ${file.name}`;

                        // Thêm hiệu ứng cho progress bar
                        uploadProgressBar.classList.add('animate__animated', 'animate__pulse');
        setTimeout(() => {
                            uploadProgressBar.classList.remove('animate__animated', 'animate__pulse');
                        }, 300);

                        resolve(result.file.url);
        } else {
                        console.error(`Lỗi upload ảnh ${index + 1}:`, result.message || response.statusText);
                        failedUploads++;
                        uploadStatus.textContent = `Lỗi khi upload: ${file.name}`;
                        reject(new Error(result.message || `HTTP error ${response.status}`));
                    }
                } catch (error) {
                    console.error(`Lỗi mạng khi upload ảnh ${index + 1}:`, error);
                    failedUploads++;
                    uploadStatus.textContent = `Lỗi mạng khi upload: ${file.name}`;
                    reject(error);
                }
            });
        });

        try {
            // Chờ tất cả các promise hoàn thành (hoặc thất bại)
            await Promise.allSettled(uploadPromises);

            if (uploadedUrls.length > 0) {
                // Cập nhật trạng thái hoàn thành
                uploadStatus.textContent = `Đã upload thành công ${uploadedUrls.length}/${totalFiles} ảnh. Đang gửi...`;

                // Gửi sự kiện adminMultipleImagesUpload qua socket
                const messageData = {
                    userId: currentUserId, // ID người nhận
                    images: uploadedUrls,
                    text: hasText ? textMessage : null // Thêm text vào messageData nếu có
                };

                socket.emit('adminMultipleImagesUpload', messageData, (response) => {
                    if (response.success) {
                        console.log('✅ Nhóm ảnh đã được gửi thành công qua socket');

                        // Nếu có cả tin nhắn văn bản
                        if (hasText) {
                            // Hiển thị tin nhắn văn bản
                            addMessageToChat({ text: textMessage, sent_at: new Date() }, 'admin');
                            console.log('✅ Tin nhắn văn bản kèm theo đã được gửi thành công');
                        }

                        // Hiển thị nhóm ảnh đã gửi trong chat của admin
                        addImageGroupToChat(uploadedUrls, new Date(), 'admin', { name: 'Admin' });

                        // Xóa nội dung input sau khi gửi thành công
                        messageInput.value = '';

                        // Hiển thị thông báo thành công
                        Toastify({
                            text: `Đã gửi ${uploadedUrls.length} ảnh${hasText ? ' và tin nhắn văn bản' : ''} thành công!`,
                            duration: 3000,
                            close: true,
                            gravity: "top",
                            position: "right",
                            backgroundColor: "linear-gradient(to right, #00b09b, #96c93d)",
                            stopOnFocus: true
                        }).showToast();

                        if (failedUploads > 0) {
                            // Thông báo về ảnh thất bại
                            Toastify({
                                text: `${failedUploads}/${totalFiles} ảnh upload thất bại.`,
                                duration: 3000,
                                close: true,
                                gravity: "top",
                                position: "right",
                                backgroundColor: "linear-gradient(to right, #ff9966, #ff5e62)",
                                stopOnFocus: true
                            }).showToast();
                        }
                    } else {
                        console.error('❌ Lỗi khi gửi nhóm ảnh qua socket:', response.error);

                        // Thông báo lỗi
                        Toastify({
                            text: `Không thể gửi nhóm ảnh: ${response.error}`,
                            duration: 3000,
                            close: true,
                            gravity: "top",
                            position: "center",
                            backgroundColor: "linear-gradient(to right, #ff5f6d, #ffc371)",
                            stopOnFocus: true
                        }).showToast();
                    }
                });
                } else {
                // Thông báo thất bại
                    Toastify({
                    text: `Upload tất cả ${totalFiles} ảnh thất bại.`,
                    duration: 3000,
                        close: true,
                        gravity: "top",
                        position: "center",
                        backgroundColor: "linear-gradient(to right, #ff5f6d, #ffc371)",
                        stopOnFocus: true
                    }).showToast();
            }

        } catch (error) {
            // Lỗi không mong muốn trong quá trình xử lý promises
            console.error("Lỗi không mong muốn khi xử lý upload:", error);

            // Thông báo lỗi
                Toastify({
                text: "Lỗi không mong muốn khi xử lý upload.",
                duration: 3000,
                    close: true,
                    gravity: "top",
                    position: "center",
                    backgroundColor: "linear-gradient(to right, #ff5f6d, #ffc371)",
                    stopOnFocus: true
                }).showToast();
        } finally {
            // Reset trạng thái sau khi hoàn tất (hoặc lỗi)
            selectedImageFiles = [];
            imageInput.value = null;
            updateImagePreview();

            // Ẩn progress bar với hiệu ứng
                setTimeout(() => {
                uploadProgressContainer.classList.add('animate__fadeOut');
                setTimeout(() => {
                    uploadProgressContainer.style.display = 'none';
                    uploadProgressContainer.classList.remove('animate__fadeOut');
                }, 500);
                }, 1000);

            // Kích hoạt lại các nút
            enableChat();
            updateSendButtonState();
        }
    }

    // Xử lý sự kiện nút gửi (đã được sửa ở trên)
    sendMessageBtn.addEventListener('click', sendMessage);

    // Thêm xử lý sự kiện phím Enter cho ô nhập tin nhắn
    messageInput.addEventListener('keydown', function(event) {
        // Kiểm tra nếu phím Enter được nhấn và không giữ phím Shift (để hỗ trợ xuống dòng khi nhấn Shift+Enter)
        if (event.key === 'Enter' && !event.shiftKey) {
            event.preventDefault(); // Ngăn không cho xuống dòng mặc định
            sendMessage(); // Gọi hàm gửi tin nhắn
        }
    });

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
        sendMessageBtn.disabled = (!hasText && !hasImages) || !currentUserId;
        // Có thể thêm logic disable nút upload nếu đã chọn ảnh...
        imageUploadBtn.disabled = !currentUserId;
    }

    // Gọi updateSendButtonState khi input text thay đổi
    messageInput.addEventListener('input', updateSendButtonState);

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
                        // Truyền timestamp gốc (nếu có) hoặc thời gian hiện tại
                        firstImageMsg.sent_at || new Date(),
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

    // Hàm trợ giúp định dạng thời gian
    function formatMessageTime(timestamp) {
        if (!timestamp) {
            // Nếu không có timestamp, trả về thời gian hiện tại đã định dạng
            return new Date().toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' }) + ' ' + new Date().toLocaleDateString('vi-VN');
        }
        try {
            const date = new Date(timestamp);
            // Kiểm tra xem date có hợp lệ không
            if (isNaN(date.getTime())) {
                 console.warn("Invalid timestamp received:", timestamp);
                 // Trả về timestamp gốc hoặc một chuỗi báo lỗi nếu không hợp lệ
                 return timestamp.toString();
            }
            const timeString = date.toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' });
            const dateString = date.toLocaleDateString('vi-VN', { day: '2-digit', month: '2-digit', year: 'numeric' });
            return `${timeString} ${dateString}`;
        } catch (error) {
            console.error("Error formatting time:", timestamp, error);
            // Fallback về định dạng mặc định hoặc timestamp gốc nếu có lỗi
            return new Date(timestamp).toLocaleString('vi-VN'); // Thử fallback
        }
    }

    // Xử lý sự kiện hiển thị/ẩn emoji picker
    emojiBtn.addEventListener('click', function() {
        emojiPickerContainer.style.display = emojiPickerContainer.style.display === 'none' ? 'block' : 'none';

        // Thêm hiệu ứng fade in nếu hiển thị
        if (emojiPickerContainer.style.display === 'block') {
            emojiPickerContainer.classList.add('animate__fadeIn');
            setTimeout(() => {
                emojiPickerContainer.classList.remove('animate__fadeIn');
            }, 500);
        }
    });

    // Xử lý sự kiện đóng emoji picker
    closeEmojiBtn.addEventListener('click', function() {
        emojiPickerContainer.style.display = 'none';
    });

    // Xử lý sự kiện chọn emoji
    emojiButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const emoji = this.getAttribute('data-emoji');

            // Thêm emoji vào vị trí con trỏ hiện tại
            const cursorPosition = messageInput.selectionStart;
            const textBeforeCursor = messageInput.value.substring(0, cursorPosition);
            const textAfterCursor = messageInput.value.substring(cursorPosition);

            messageInput.value = textBeforeCursor + emoji + textAfterCursor;

            // Di chuyển con trỏ sau emoji vừa chèn
            messageInput.selectionStart = cursorPosition + emoji.length;
            messageInput.selectionEnd = cursorPosition + emoji.length;

            // Focus lại vào input
            messageInput.focus();

            // Ẩn emoji picker sau khi chọn
            emojiPickerContainer.style.display = 'none';
        });
    });

    // Xử lý đóng emoji picker khi click ra ngoài
    document.addEventListener('click', function(event) {
        // Kiểm tra nếu click bên ngoài cả emoji picker và nút emoji
        if (emojiPickerContainer.style.display === 'block' &&
            !emojiPickerContainer.contains(event.target) &&
            event.target !== emojiBtn &&
            !emojiBtn.contains(event.target)) {
            emojiPickerContainer.style.display = 'none';
        }
    });

    // Kết nối đến socket khi trang load xong
    connectToSocket();
});
</script>
@endsection
