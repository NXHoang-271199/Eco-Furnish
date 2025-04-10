/**
 * 📌 Order Notifications JS
 * Xử lý thông báo realtime cho đơn hàng mới
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

    // Tạo thẻ audio ẩn trong DOM thay vì chỉ tạo đối tượng Audio
    let notificationAudioElement = document.getElementById('notification-sound');

    // Nếu chưa có thẻ audio, tạo mới và thêm vào body
    if (!notificationAudioElement) {
        notificationAudioElement = document.createElement('audio');
        notificationAudioElement.id = 'notification-sound';
        notificationAudioElement.src = '/assets/admins/sounds/notify.mp3';
        notificationAudioElement.preload = 'auto';
        notificationAudioElement.style.display = 'none';
        document.body.appendChild(notificationAudioElement);
        console.log('✅ Đã tạo thẻ audio và thêm vào DOM');
    }

    // Biến để kiểm tra xem người dùng đã tương tác với trang chưa
    let userInteracted = false;

    // Xử lý tương tác người dùng để cho phép phát âm thanh
    function handleUserInteraction() {
        if (!userInteracted) {
            userInteracted = true;
            // Khởi tạo audio context sau khi có tương tác
            try {
                // Tải âm thanh để chuẩn bị phát
                notificationAudioElement.load();
                console.log('✅ Đã khởi tạo audio sau tương tác người dùng');

                // Phát một âm thanh không nghe thấy để giải quyết vấn đề tương tác
                notificationAudioElement.volume = 0.01;
                notificationAudioElement.play().then(() => {
                    notificationAudioElement.pause();
                    notificationAudioElement.currentTime = 0;
                    notificationAudioElement.volume = 0.5;
                    console.log('✅ Đã kích hoạt audio với tương tác người dùng');
                }).catch(e => {
                    console.warn('⚠️ Không thể kích hoạt audio:', e);
                });
            } catch (e) {
                console.warn('⚠️ Lỗi khi khởi tạo audio:', e);
            }

            // Gỡ bỏ các event listener vì không cần nữa
            document.removeEventListener('click', handleUserInteraction);
            document.removeEventListener('keydown', handleUserInteraction);
            document.removeEventListener('touchstart', handleUserInteraction);
        }
    }

    // Đăng ký sự kiện tương tác người dùng
    document.addEventListener('click', handleUserInteraction);
    document.addEventListener('keydown', handleUserInteraction);
    document.addEventListener('touchstart', handleUserInteraction);

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
        playNotificationSound();

        // Hiển thị toast thông báo
        showOrderNotification(data);

        // Cập nhật UI (số lượng thông báo, v.v.)
        updateNotificationCounter();
    });

    /**
     * Hàm hiển thị toast thông báo
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
     * Hàm phát âm thanh thông báo - sử dụng file âm thanh từ thư mục sounds
     */
    function playNotificationSound() {
        try {
            // Kiểm tra xem người dùng đã tương tác với trang chưa
            if (!userInteracted) {
                console.warn('⚠️ Không thể phát âm thanh vì chưa có tương tác người dùng');
                return; // Không cố gắng phát âm thanh nếu chưa có tương tác
            }

            // Sử dụng thẻ audio đã được thêm vào DOM
            notificationAudioElement.currentTime = 0; // Reset thời gian để phát lại từ đầu

            // Đảm bảo âm lượng phù hợp
            notificationAudioElement.volume = 0.5;

            // Phát âm thanh 
            const playPromise = notificationAudioElement.play();

            if (playPromise !== undefined) {
                playPromise
                    .then(() => {
                        console.log('✅ Đang phát âm thanh thông báo từ file MP3');
                    })
                    .catch(error => {
                        console.warn('⚠️ Không thể phát âm thanh từ file MP3:', error);
                        // Không sử dụng Web Audio API nữa
                    });
            }
        } catch (e) {
            console.warn('⚠️ Lỗi khi phát âm thanh thông báo:', e);
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