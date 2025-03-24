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

    // Kết nối đến Socket.IO server
    function connectToSocket() {
        socket = io("http://127.0.0.1:3001", {
            transports: ["websocket", "polling"],
            reconnectionAttempts: 5,
            reconnectionDelay: 1000,
            timeout: 10000
        });

        // Sự kiện kết nối thành công
        socket.on("connect", () => {
            console.log("Đã kết nối đến Socket.IO server");
            connectionStatus.textContent = "Đã kết nối";
            connectionStatus.classList.remove('bg-warning', 'bg-danger');
            connectionStatus.classList.add('bg-success');

            // Đăng ký là admin
            socket.emit("adminConnect", {
                name: "Admin {{ Auth::user()->name ?? 'Administrator' }}",
                id: socket.id
            });
        });

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
                    }
                }
            }
        });
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

        const li = document.createElement('li');
        li.id = `user-${user.id}`;
        li.className = 'list-group-item d-flex justify-content-between align-items-center user-item';
        li.innerHTML = `
            <span>${user.name || 'Khách hàng không rõ'}</span>
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
            currentUserId = user.id;

            // Hiển thị tên người dùng đang chat
            chattingWith.textContent = user.name || 'Khách hàng không rõ';

            // Kích hoạt ô nhập tin nhắn
            enableChat();

            // Tải tin nhắn cũ (nếu có)
            loadMessages(user.id);
        });

        userList.appendChild(li);
    }

    // Tải tin nhắn cũ
    function loadMessages(userId) {
        chatBox.innerHTML = '<div class="text-center"><div class="spinner-border text-primary" role="status"></div><p>Đang tải tin nhắn...</p></div>';

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
                    const type = msg.type === 'admin' ? 'admin' : 'client';
                    addMessageToChat({
                        text: msg.text,
                        sent_at: new Date(msg.sent_at || msg.created_at).toLocaleString()
                    }, type);
                });

                // Cuộn xuống dưới cùng
                chatBox.scrollTop = chatBox.scrollHeight;
            })
            .catch(error => {
                console.error('Lỗi khi tải tin nhắn:', error);
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
    }

    // Thêm tin nhắn vào khung chat
    function addMessageToChat(message, sender) {
        const messageDiv = document.createElement('div');
        messageDiv.className = `message ${sender}-message mb-3`;

        if (sender === 'client') {
            messageDiv.innerHTML = `
                <div class="d-flex">
                    <div class="message-bubble bg-light p-2 rounded">
                        <div class="message-text">${message.text}</div>
                        <div class="message-time text-muted small">${message.sent_at || new Date().toLocaleString()}</div>
                    </div>
                </div>
            `;
        } else {
            messageDiv.innerHTML = `
                <div class="d-flex justify-content-end">
                    <div class="message-bubble bg-primary text-white p-2 rounded">
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

    // Kết nối đến socket khi trang load xong
    connectToSocket();
    });
</script>
@endsection
