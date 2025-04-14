/**
 * 📌 Order Notifications JS
 * Xử lý thông báo realtime cho đơn hàng mới và tin nhắn
 */
document.addEventListener('DOMContentLoaded', function () {
    // Lấy token từ các nguồn khác nhau để đảm bảo luôn có token
    let token = localStorage.getItem('admin_token') ||
        sessionStorage.getItem('admin_token') ||
        document.querySelector('meta[name="admin-token"]')?.content;

    // Nếu không tìm thấy token, thử lấy từ cookie
    if (!token) {
        const cookies = document.cookie.split(';');
        for (const cookie of cookies) {
            const [name, value] = cookie.trim().split('=');
            if (name === 'admin_token') {
                token = value;
                break;
            }
        }
    }

    if (!token) {
        console.warn('Không tìm thấy token admin, sẽ thử kết nối socket không xác thực!');
        // Tiếp tục kết nối mà không có token
    }

    // Tạo thẻ audio ẩn cho thông báo đơn hàng trong DOM
    let orderAudioElement = document.getElementById('order-notification-sound');

    // Nếu chưa có thẻ audio, tạo mới và thêm vào body
    if (!orderAudioElement) {
        orderAudioElement = document.createElement('audio');
        orderAudioElement.id = 'order-notification-sound';
        orderAudioElement.src = '/assets/admins/sounds/notify.mp3';
        orderAudioElement.preload = 'auto';
        orderAudioElement.style.display = 'none';
        document.body.appendChild(orderAudioElement);
        console.log('✅ Đã tạo thẻ audio thông báo đơn hàng và thêm vào DOM');
    }

    // Tạo thẻ audio ẩn cho thông báo tin nhắn trong DOM
    let messageAudioElement = document.getElementById('message-notification-sound');

    // Nếu chưa có thẻ audio cho tin nhắn, tạo mới và thêm vào body
    if (!messageAudioElement) {
        messageAudioElement = document.createElement('audio');
        messageAudioElement.id = 'message-notification-sound';
        messageAudioElement.src = '/assets/admins/sounds/notify.mp3';
        messageAudioElement.preload = 'auto';
        messageAudioElement.volume = 0.8; // Tăng âm lượng lên 80%
        messageAudioElement.style.display = 'none';
        document.body.appendChild(messageAudioElement);
        console.log('✅ Đã tạo thẻ audio thông báo tin nhắn và thêm vào DOM');
    }

    // Biến để kiểm tra xem người dùng đã tương tác với trang chưa
    let userInteracted = false;

    // Biến để theo dõi số lần thử phát âm thanh
    let audioPlayAttempts = 0;
    const maxAudioPlayAttempts = 3;

    // Xử lý tương tác người dùng để cho phép phát âm thanh
    function handleUserInteraction(event) {
        if (!userInteracted) {
            userInteracted = true;
            console.log('✅ Người dùng đã tương tác với trang, đang kích hoạt audio...');

            // Kích hoạt cả 2 audio để đảm bảo có thể phát sau này
            try {
                // Tải âm thanh để chuẩn bị phát
                orderAudioElement.load();
                messageAudioElement.load();

                // Phát thử âm thanh không nghe thấy để giải quyết vấn đề tương tác
                const testAudio = function (audioElement, name) {
                    audioElement.volume = 0.01;
                    audioElement.play().then(() => {
                        audioElement.pause();
                        audioElement.currentTime = 0;
                        audioElement.volume = 0.8;
                        console.log(`✅ Đã kích hoạt audio ${name} thành công`);
                    }).catch(e => {
                        console.warn(`⚠️ Không thể kích hoạt audio ${name}:`, e);

                        // Thử lại khi người dùng click
                        const retryActivation = function () {
                            audioElement.play().then(() => {
                                audioElement.pause();
                                audioElement.currentTime = 0;
                                audioElement.volume = 0.8;
                                console.log(`✅ Đã kích hoạt audio ${name} sau khi thử lại`);
                                document.removeEventListener('click', retryActivation);
                            }).catch(err => {
                                console.warn(`⚠️ Vẫn không thể kích hoạt audio ${name} sau khi thử lại:`, err);
                            });
                        };

                        document.addEventListener('click', retryActivation, { once: true });
                    });
                };

                // Kích hoạt cả hai audio
                testAudio(orderAudioElement, 'đơn hàng');
                testAudio(messageAudioElement, 'tin nhắn');

            } catch (e) {
                console.warn('⚠️ Lỗi khi khởi tạo audio:', e);
            }

            // Gỡ bỏ các event listener với once: true để chỉ kích hoạt một lần
            // Các event listeners được thay thế bởi phiên bản khác sau khi đã kích hoạt
        }
    }

    // Đăng ký sự kiện tương tác người dùng
    document.addEventListener('click', handleUserInteraction, { once: true });
    document.addEventListener('keydown', handleUserInteraction, { once: true });
    document.addEventListener('touchstart', handleUserInteraction, { once: true });

    // Kết nối đến Socket Server
    const socketUrl = window.socketServerUrl || 'http://localhost:3002';
    const socket = io(socketUrl, {
        auth: {
            token: token,
            role: 'admin' // Gán role admin để đảm bảo nhận được quyền admin
        },
        transports: ['websocket', 'polling'],
        reconnection: true,
        reconnectionAttempts: 10,
        reconnectionDelay: 2000,
        timeout: 20000
    });

    // Lưu socket vào window để có thể sử dụng ở các trang khác
    window.orderNotificationSocket = socket;

    // Lắng nghe sự kiện kết nối
    socket.on('connect', function () {
        console.log('✅ Đã kết nối đến Socket Server với ID:', socket.id);

        // Đăng ký là admin - thử lại nhiều lần nếu thất bại
        registerAsAdmin();
    });

    // Hàm đăng ký làm admin và thử lại nếu thất bại
    function registerAsAdmin(attempts = 0) {
        socket.emit('adminConnect', {}, function (response) {
            if (response && response.success) {
                console.log('✅ Đã đăng ký làm admin thành công');
                // Lưu trạng thái đã kết nối vào sessionStorage để duy trì giữa các trang
                sessionStorage.setItem('admin_connected', 'true');
            } else {
                console.error('❌ Không thể đăng ký làm admin:', response ? response.error : 'Không có phản hồi');
                // Thử lại tối đa 3 lần
                if (attempts < 3) {
                    console.log(`⏱️ Thử lại lần ${attempts + 1}...`);
                    setTimeout(() => registerAsAdmin(attempts + 1), 2000);
                }
            }
        });
    }

    // Xử lý khi kết nối bị ngắt
    socket.io.on("reconnect_attempt", (attempt) => {
        console.log(`🔄 Đang thử kết nối lại lần ${attempt}...`);
    });

    socket.io.on("reconnect", () => {
        console.log('✅ Đã kết nối lại thành công');
        // Đăng ký lại làm admin sau khi kết nối lại
        registerAsAdmin();
    });

    // Lắng nghe sự kiện lỗi kết nối
    socket.on('connect_error', function (error) {
        console.error('❌ Lỗi kết nối Socket Server:', error.message);
    });

    // Lắng nghe sự kiện ngắt kết nối
    socket.on('disconnect', function (reason) {
        console.log('⚠️ Đã ngắt kết nối khỏi Socket Server:', reason);
        // Xóa trạng thái kết nối trong sessionStorage
        sessionStorage.removeItem('admin_connected');
    });

    // Lắng nghe sự kiện thông báo đơn hàng mới
    socket.on('new_order_notification', function (data) {
        console.log('📣 Nhận thông báo đơn hàng mới:', data);

        // Phát âm thanh thông báo TRƯỚC khi hiển thị toast
        playOrderNotificationSound();

        // Hiển thị toast thông báo
        showOrderNotification(data);

        // Cập nhật UI (số lượng thông báo, v.v.)
        updateNotificationCounter();
    });

    // Lắng nghe sự kiện thông báo hủy đơn hàng
    socket.on('order_cancel_notification', function (data) {
        console.log('📣 Nhận thông báo hủy đơn hàng:', data);

        // Phát âm thanh thông báo
        playOrderNotificationSound();

        // Hiển thị toast thông báo hủy đơn hàng
        showOrderCancelNotification(data);

        // Cập nhật UI (số lượng thông báo, v.v.)
        updateNotificationCounter();
    });

    // Lắng nghe sự kiện thông báo yêu cầu hoàn hàng
    socket.on('order_refund_notification', function (data) {
        console.log('📣 Nhận thông báo yêu cầu hoàn hàng:', data);

        // Phát âm thanh thông báo
        playOrderNotificationSound();

        // Hiển thị toast thông báo yêu cầu hoàn hàng
        showOrderRefundNotification(data);

        // Cập nhật UI (số lượng thông báo, v.v.)
        updateNotificationCounter();
    });

    // Lắng nghe sự kiện thông báo xác nhận đã nhận hàng
    socket.on('order_confirmation_notification', function (data) {
        console.log('📣 Nhận thông báo xác nhận đã nhận hàng:', data);

        // Phát âm thanh thông báo
        playOrderNotificationSound();

        // Hiển thị toast thông báo xác nhận đã nhận hàng
        showOrderConfirmationNotification(data);

        // Cập nhật UI (số lượng thông báo, v.v.)
        updateNotificationCounter();
    });

    // Lắng nghe sự kiện tin nhắn mới từ client
    socket.on('newClientMessage', function (data) {
        console.log('📣 Nhận tin nhắn mới từ client:', data);

        // Phát âm thanh thông báo tin nhắn
        playMessageNotificationSound();

        // Kiểm tra nếu không ở trang chat (URL không chứa '/admin/messages')
        if (!window.location.pathname.includes('/admin/messages')) {
            // Hiển thị toast thông báo tin nhắn mới
            showMessageNotification(data);
        }
    });

    // Lắng nghe sự kiện nhận nhiều ảnh từ client
    socket.on('clientMultipleImagesUpload', function (data) {
        console.log('📣 Nhận thông báo upload ảnh từ client:', data);

        // Phát âm thanh thông báo tin nhắn
        playMessageNotificationSound();

        // Kiểm tra nếu không ở trang chat (URL không chứa '/admin/messages')
        if (!window.location.pathname.includes('/admin/messages')) {
            // Hiển thị toast thông báo tin nhắn mới với ảnh
            showMessageNotification({
                sender: data.sender,
                sender_id: data.sender_id,
                content: 'Đã gửi các ảnh mới'
            });
        }
    });

    /**
     * Hàm hiển thị toast thông báo đơn hàng
     */
    function showOrderNotification(data) {
        if (!data || !data.data) return;

        const orderData = data.data;
        const orderId = orderData.order_id;
        const orderCode = orderData.order_code;
        const userName = orderData.user_name;
        const totalPrice = formatCurrency(orderData.total_price);

        // Tạo thông báo sử dụng Toastify
        Toastify({
            text: `🔔 Đơn hàng mới #${orderCode} từ ${userName} - ${totalPrice}`,
            duration: 10000, // Hiển thị lâu hơn (10 giây)
            close: true,
            gravity: "top",
            position: "right",
            stopOnFocus: true,
            onClick: function () {
                // Chuyển đến trang chi tiết đơn hàng khi click
                window.location.href = `/admin/orders/${orderId}`;
            },
            style: {
                background: "linear-gradient(to right, #ff5f6d, #ffc371)",
                fontWeight: "bold",
                padding: "12px",
                fontSize: "14px",
                borderRadius: "8px",
                boxShadow: "0 4px 12px rgba(0,0,0,0.15)",
                zIndex: 9999
            }
        }).showToast();

        // Nếu người dùng cho phép thông báo trên desktop
        if (Notification && Notification.permission === "granted") {
            const notification = new Notification("Đơn hàng mới!", {
                body: `#${orderCode} từ ${userName} - ${totalPrice}`,
                icon: "/assets/admins/images/favicon.ico"
            });

            notification.onclick = function () {
                window.location.href = `/admin/orders/${orderId}`;
                notification.close();
            };
        }
        // Yêu cầu quyền thông báo nếu chưa được cấp
        else if (Notification && Notification.permission !== "denied") {
            Notification.requestPermission();
        }
    }

    /**
     * Hàm hiển thị toast thông báo hủy đơn hàng
     */
    function showOrderCancelNotification(data) {
        if (!data || !data.data) return;

        const orderData = data.data;
        const orderId = orderData.order_id;
        const orderCode = orderData.order_code;
        const userName = orderData.user_name;
        const totalPrice = formatCurrency(orderData.total_price);
        const message = orderData.message || `Đơn hàng #${orderCode} đã bị hủy bởi khách hàng.`;

        // Tạo thông báo sử dụng Toastify
        Toastify({
            text: `🚫 ${message}`,
            duration: 10000, // Hiển thị lâu hơn (10 giây)
            close: true,
            gravity: "top",
            position: "right",
            stopOnFocus: true,
            onClick: function () {
                // Chuyển đến trang chi tiết đơn hàng khi click
                window.location.href = `/admin/orders/${orderId}`;
            },
            style: {
                background: "linear-gradient(to right, #dc3545, #fd7e14)",
                fontWeight: "bold",
                padding: "12px",
                fontSize: "14px",
                borderRadius: "8px",
                boxShadow: "0 4px 12px rgba(0,0,0,0.15)",
                zIndex: 9999
            }
        }).showToast();

        // Nếu người dùng cho phép thông báo trên desktop
        if (Notification && Notification.permission === "granted") {
            const notification = new Notification("Đơn hàng đã bị hủy!", {
                body: `#${orderCode} - ${userName} - ${totalPrice}`,
                icon: "/assets/admins/images/favicon.ico"
            });

            notification.onclick = function () {
                window.location.href = `/admin/orders/${orderId}`;
                notification.close();
            };
        }
        // Yêu cầu quyền thông báo nếu chưa được cấp
        else if (Notification && Notification.permission !== "denied") {
            Notification.requestPermission();
        }
    }

    /**
     * Hàm hiển thị toast thông báo yêu cầu hoàn hàng
     */
    function showOrderRefundNotification(data) {
        if (!data || !data.data) return;

        const orderData = data.data;
        const orderId = orderData.order_id;
        const orderCode = orderData.order_code;
        const userName = orderData.user_name;
        const totalPrice = formatCurrency(orderData.total_price);
        const reason = orderData.reason || 'Không có lý do cụ thể';
        const message = orderData.message || `Đơn hàng #${orderCode} có yêu cầu hoàn hàng từ khách hàng.`;

        // Tạo thông báo sử dụng Toastify
        Toastify({
            text: `🔄 ${message}`,
            duration: 10000, // Hiển thị lâu hơn (10 giây)
            close: true,
            gravity: "top",
            position: "right",
            stopOnFocus: true,
            onClick: function () {
                // Chuyển đến trang chi tiết đơn hàng khi click
                window.location.href = `/admin/orders/${orderId}`;
            },
            style: {
                background: "linear-gradient(to right, #3498db, #2980b9)",
                fontWeight: "bold",
                padding: "12px",
                fontSize: "14px",
                borderRadius: "8px",
                boxShadow: "0 4px 12px rgba(0,0,0,0.15)",
                zIndex: 9999
            }
        }).showToast();

        // Nếu người dùng cho phép thông báo trên desktop
        if (Notification && Notification.permission === "granted") {
            const notification = new Notification("Yêu cầu hoàn hàng mới!", {
                body: `#${orderCode} - ${userName} - ${totalPrice}\nLý do: ${reason}`,
                icon: "/assets/admins/images/favicon.ico"
            });

            notification.onclick = function () {
                window.location.href = `/admin/orders/${orderId}`;
                notification.close();
            };
        }
        // Yêu cầu quyền thông báo nếu chưa được cấp
        else if (Notification && Notification.permission !== "denied") {
            Notification.requestPermission();
        }
    }

    /**
     * Hàm hiển thị toast thông báo xác nhận đã nhận hàng
     */
    function showOrderConfirmationNotification(data) {
        if (!data || !data.data) return;

        const orderData = data.data;
        const orderId = orderData.order_id;
        const orderCode = orderData.order_code;
        const userName = orderData.user_name;
        const totalPrice = formatCurrency(orderData.total_price);
        const message = orderData.message || `Đơn hàng #${orderCode} đã được xác nhận đã nhận hàng từ khách hàng.`;

        // Tạo thông báo sử dụng Toastify
        Toastify({
            text: `✅ ${message}`,
            duration: 10000, // Hiển thị lâu hơn (10 giây)
            close: true,
            gravity: "top",
            position: "right",
            stopOnFocus: true,
            onClick: function () {
                // Chuyển đến trang chi tiết đơn hàng khi click
                window.location.href = `/admin/orders/${orderId}`;
            },
            style: {
                background: "linear-gradient(to right, #10b981, #14b8a6)",
                fontWeight: "bold",
                padding: "12px",
                fontSize: "14px",
                borderRadius: "8px",
                boxShadow: "0 4px 12px rgba(0,0,0,0.15)",
                zIndex: 9999
            }
        }).showToast();

        // Nếu người dùng cho phép thông báo trên desktop
        if (Notification && Notification.permission === "granted") {
            const notification = new Notification("Đơn hàng đã được xác nhận!", {
                body: `#${orderCode} - ${userName} - ${totalPrice}`,
                icon: "/assets/admins/images/favicon.ico"
            });

            notification.onclick = function () {
                window.location.href = `/admin/orders/${orderId}`;
                notification.close();
            };
        }
        // Yêu cầu quyền thông báo nếu chưa được cấp
        else if (Notification && Notification.permission !== "denied") {
            Notification.requestPermission();
        }
    }

    /**
     * Hàm hiển thị toast thông báo tin nhắn mới
     */
    function showMessageNotification(data) {
        if (!data) return;

        const senderName = data.sender?.name || 'Khách hàng';
        const senderId = data.sender_id;
        const messageContent = data.text || data.content || 'Đã gửi một tin nhắn mới';

        // Tạo thông báo sử dụng Toastify
        Toastify({
            text: `💬 ${senderName}: ${messageContent}`,
            duration: 5000, // Hiển thị 5 giây
            close: true,
            gravity: "top",
            position: "right",
            stopOnFocus: true,
            onClick: function () {
                // Chuyển đến trang chat khi click
                window.location.href = `/admin/messages`;
            },
            style: {
                background: "linear-gradient(to right, #4CAF50, #2196F3)",
                fontWeight: "bold",
                padding: "12px",
                fontSize: "14px",
                borderRadius: "8px",
                boxShadow: "0 4px 12px rgba(0,0,0,0.15)",
                zIndex: 9999
            }
        }).showToast();

        // Nếu người dùng cho phép thông báo trên desktop
        if (Notification && Notification.permission === "granted") {
            const notification = new Notification("Tin nhắn mới!", {
                body: `${senderName}: ${messageContent}`,
                icon: "/assets/admins/images/favicon.ico"
            });

            notification.onclick = function () {
                window.location.href = `/admin/messages`;
                notification.close();
            };
        }
        // Yêu cầu quyền thông báo nếu chưa được cấp
        else if (Notification && Notification.permission !== "denied") {
            Notification.requestPermission();
        }
    }

    /**
     * Hàm cập nhật số lượng thông báo hiển thị trên UI
     */
    function updateNotificationCounter() {
        // Lấy phần tử hiển thị số lượng thông báo
        const counterElement = document.getElementById('notification-counter');
        const notificationIcon = document.querySelector('.notification-badge');

        if (counterElement) {
            // Lấy giá trị hiện tại và tăng lên 1
            let currentCount = parseInt(counterElement.textContent) || 0;
            currentCount += 1;

            // Cập nhật số lượng
            counterElement.textContent = currentCount;

            // Hiển thị badge nếu chưa hiển thị
            if (notificationIcon && notificationIcon.classList.contains('d-none')) {
                notificationIcon.classList.remove('d-none');
            }

            // Thêm hiệu ứng nhấp nháy để thu hút sự chú ý
            if (counterElement.parentElement) {
                counterElement.parentElement.classList.add('blink-notification');
                setTimeout(() => {
                    counterElement.parentElement.classList.remove('blink-notification');
                }, 3000);
            }
        }
    }

    /**
     * Hàm phát âm thanh thông báo đơn hàng
     */
    function playOrderNotificationSound() {
        try {
            // Kích hoạt tương tác người dùng ngay lập tức nếu chưa có
            if (!userInteracted) {
                handleUserInteraction();
            }

            // Reset âm thanh về đầu
            orderAudioElement.currentTime = 0;
            orderAudioElement.volume = 0.8; // Đảm bảo âm lượng đủ lớn

            // Đảm bảo âm thanh được tải
            orderAudioElement.load();

            // Phát âm thanh với nhiều lớp bảo vệ
            const playPromise = orderAudioElement.play();

            if (playPromise !== undefined) {
                playPromise
                    .then(() => {
                        console.log('✅ Đang phát âm thanh thông báo đơn hàng thành công');
                        audioPlayAttempts = 0; // Reset số lần thử
                    })
                    .catch(error => {
                        console.warn('⚠️ Không thể phát âm thanh đơn hàng:', error);
                        audioPlayAttempts++;

                        // Thử lại với tương tác người dùng nếu lỗi
                        if (audioPlayAttempts < maxAudioPlayAttempts) {
                            console.log(`⏱️ Đang thử lại lần ${audioPlayAttempts}...`);

                            // Đăng ký phát âm thanh khi người dùng tương tác tiếp theo
                            const retryAudioPlay = function () {
                                orderAudioElement.play()
                                    .then(() => console.log('✅ Phát âm thanh thông báo đơn hàng thành công sau khi thử lại'))
                                    .catch(e => console.warn('⚠️ Vẫn không thể phát âm thanh:', e));
                                document.removeEventListener('click', retryAudioPlay);
                            };

                            document.addEventListener('click', retryAudioPlay, { once: true });
                        }
                    });
            } else {
                console.warn('⚠️ Không thể phát âm thanh vì trình duyệt không hỗ trợ Promise cho audio.play()');
            }
        } catch (e) {
            console.warn('⚠️ Lỗi khi phát âm thanh thông báo đơn hàng:', e);
        }
    }

    /**
     * Hàm phát âm thanh thông báo tin nhắn
     */
    function playMessageNotificationSound() {
        try {
            // Kích hoạt tương tác người dùng ngay lập tức nếu chưa có
            if (!userInteracted) {
                handleUserInteraction();
            }

            // Reset âm thanh về đầu
            messageAudioElement.currentTime = 0;
            messageAudioElement.volume = 0.8; // Đảm bảo âm lượng đủ lớn

            // Đảm bảo âm thanh được tải
            messageAudioElement.load();

            // Phát âm thanh với nhiều lớp bảo vệ
            const playPromise = messageAudioElement.play();

            if (playPromise !== undefined) {
                playPromise
                    .then(() => {
                        console.log('✅ Đang phát âm thanh thông báo tin nhắn thành công');
                        audioPlayAttempts = 0; // Reset số lần thử
                    })
                    .catch(error => {
                        console.warn('⚠️ Không thể phát âm thanh tin nhắn:', error);
                        audioPlayAttempts++;

                        // Thử lại với tương tác người dùng nếu lỗi
                        if (audioPlayAttempts < maxAudioPlayAttempts) {
                            console.log(`⏱️ Đang thử lại lần ${audioPlayAttempts}...`);

                            // Đăng ký phát âm thanh khi người dùng tương tác tiếp theo
                            const retryAudioPlay = function () {
                                messageAudioElement.play()
                                    .then(() => console.log('✅ Phát âm thanh thông báo tin nhắn thành công sau khi thử lại'))
                                    .catch(e => console.warn('⚠️ Vẫn không thể phát âm thanh:', e));
                                document.removeEventListener('click', retryAudioPlay);
                            };

                            document.addEventListener('click', retryAudioPlay, { once: true });
                        }
                    });
            } else {
                console.warn('⚠️ Không thể phát âm thanh vì trình duyệt không hỗ trợ Promise cho audio.play()');
            }
        } catch (e) {
            console.warn('⚠️ Lỗi khi phát âm thanh thông báo tin nhắn:', e);
        }
    }

    /**
     * Hàm định dạng tiền tệ
     */
    function formatCurrency(amount) {
        return new Intl.NumberFormat('vi-VN', {
            style: 'currency',
            currency: 'VND'
        }).format(amount);
    }
}); 