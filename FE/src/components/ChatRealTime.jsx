import { useEffect, useState, useRef } from "react";
import { BsChatDots, BsXLg, BsEmojiSmile } from "react-icons/bs";
import { IoMdSend } from "react-icons/io";
import { MdImage } from "react-icons/md";
import { getSocket, closeSocket, resetSocket } from "../utils/socketConfig";
import axios from "axios";
import Lightbox from "yet-another-react-lightbox";
import "yet-another-react-lightbox/styles.css";
import { toast } from "react-hot-toast";
import "../styles/chat.css";
import EmojiPicker from 'emoji-picker-react';

// Tạo audio elements toàn cục để khởi tạo sớm
const messageAudio = new Audio('/sounds/notification-sound.mp3');
messageAudio.preload = 'auto';
messageAudio.volume = 0.8;

// Biến để theo dõi tương tác người dùng
let userHasInteracted = false;

// Đăng ký sự kiện tương tác người dùng
document.addEventListener('click', handleUserInteraction, { once: false });
document.addEventListener('keydown', handleUserInteraction, { once: false });
document.addEventListener('touchstart', handleUserInteraction, { once: false });

// Hàm xử lý tương tác người dùng
function handleUserInteraction() {
    if (!userHasInteracted) {
        userHasInteracted = true;
        console.log("Người dùng đã tương tác với trang, có thể phát âm thanh");

        // Kích hoạt audio trước
        messageAudio.play()
            .then(() => {
                messageAudio.pause();
                messageAudio.currentTime = 0;
                console.log("✓ Đã kích hoạt audio");
            })
            .catch(err => console.log("Không thể kích hoạt audio:", err));
    }
}

// Helper function để xử lý việc phát âm thanh thông báo
const playNotificationSound = (soundPath) => {
    try {
        // Sử dụng audio element toàn cục để tránh tạo nhiều instances
        messageAudio.currentTime = 0;

        // Thử phát âm thanh
        if (userHasInteracted) {
            messageAudio.play()
                .then(() => console.log("✓ Âm thanh thông báo được phát thành công"))
                .catch(err => {
                    console.log("Không thể phát âm thanh, lỗi:", err);

                    // Thử lại với tương tác người dùng nếu lỗi
                    const unblockAudio = () => {
                        messageAudio.play()
                            .then(() => {
                                console.log("✓ Đã phát âm thanh sau tương tác");
                                document.removeEventListener('click', unblockAudio);
                            })
                            .catch(e => console.log("Vẫn không thể phát âm thanh:", e));
                    };

                    document.addEventListener('click', unblockAudio, { once: true });
                });
        } else {
            console.log("Người dùng chưa tương tác, đang chờ tương tác để phát âm thanh");

            // Đăng ký phát âm thanh sau tương tác đầu tiên
            const playAfterInteraction = () => {
                userHasInteracted = true;
                messageAudio.play()
                    .then(() => console.log("Đã phát âm thanh sau tương tác đầu tiên"))
                    .catch(e => console.log("Không thể phát âm thanh sau tương tác:", e));

                document.removeEventListener('click', playAfterInteraction);
                document.removeEventListener('keydown', playAfterInteraction);
                document.removeEventListener('touchstart', playAfterInteraction);
            };

            document.addEventListener('click', playAfterInteraction, { once: true });
            document.addEventListener('keydown', playAfterInteraction, { once: true });
            document.addEventListener('touchstart', playAfterInteraction, { once: true });
        }
    } catch (error) {
        console.error("Lỗi khi phát âm thanh:", error);
    }
};

// Hàm thay thế emoji từ text (vd: ":)" thành "🙂")
const replaceTextWithEmojis = (text) => {
    if (!text) return text;

    return text
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
};

const ChatRealTime = () => {
    const [message, setMessage] = useState("");
    const [messages, setMessages] = useState([]);
    const [isOpen, setIsOpen] = useState(false);
    const [isConnected, setIsConnected] = useState(false);
    const [isAuthenticated, setIsAuthenticated] = useState(false);
    const [socket, setSocket] = useState(null);
    const [userData, setUserData] = useState(null);
    const [isLoading, setIsLoading] = useState(false);
    const [lastError, setLastError] = useState("");
    const [unreadCount, setUnreadCount] = useState(0);
    // States cho upload ảnh
    const [selectedImages, setSelectedImages] = useState([]);
    const [isUploading, setIsUploading] = useState(false);
    const [imagePreviews, setImagePreviews] = useState([]);
    // State cho lightbox
    const [isLightboxOpen, setIsLightboxOpen] = useState(false);
    const [lightboxIndex, setLightboxIndex] = useState(0);
    const [lightboxImages, setLightboxImages] = useState([]);
    const [socketStatus, setSocketStatus] = useState("disconnected");
    // Thêm state để lưu URL socket server
    const [socketServerUrl] = useState(import.meta.env.VITE_SOCKET_SERVER_URL || "http://localhost:3002");
    // Thêm state cho emoji picker
    const [showEmojiPicker, setShowEmojiPicker] = useState(false);
    // Thêm ref cho phần messages và image input
    const messagesEndRef = useRef(null);

    const imageInputRef = useRef(null);
    // Ref để theo dõi tin nhắn đã xử lý
    const processedImageIds = useRef(new Set());
    // Cải thiện hàm phân tích tin nhắn từ admin
    const analyzeAdminMessages = (message) => {
        // Kiểm tra nếu tin nhắn có chứa "admin" trong ID hoặc từ admin
        return message &&
            (!message.isCurrentUser && userData && // Kiểm tra nếu userData tồn tại
                (message.sender_id.toString() !== userData.id?.toString()));
    };

    // Hàm xử lý và nhóm các tin nhắn ảnh liên tiếp
    const processMessagesWithImageGroups = (messagesArray) => {
        if (!messagesArray || !Array.isArray(messagesArray) || messagesArray.length === 0) {
            return [];
        }
        // Clone mảng tin nhắn để không ảnh hưởng đến mảng gốc
        const messages = [...messagesArray];
        const processedMessages = [];
        let i = 0;

        // Reset processed IDs mỗi lần chạy để đảm bảo nhóm lại đúng
        processedImageIds.current = new Set();

        while (i < messages.length) {
            const currentMsg = messages[i];

            // Nếu tin nhắn hiện tại đã có nhóm ảnh, giữ nguyên
            if (currentMsg.imageGroup) {
                processedMessages.push(currentMsg);
                i++;
                continue;
            }

            // Bỏ qua nếu đã xử lý trong lần chạy này
            const msgId = currentMsg.id || `msg-${currentMsg.sender_id}-${currentMsg.sent_at}-${Math.random()}`;
            if (processedImageIds.current.has(msgId)) {
                i++; // Chỉ tăng i, không push lại vào processedMessages
                continue;
            }


            // Kiểm tra xem tin nhắn hiện tại có phải là ảnh không
            if (currentMsg.image && !currentMsg.text) {
                // Tạo ID duy nhất cho tin nhắn này nếu chưa có
                // const msgId = currentMsg.id || `msg-${currentMsg.sender_id}-${currentMsg.sent_at}-${Math.random()}`; // Đã có ở trên

                // Bắt đầu một nhóm ảnh mới
                const imageInfo = [{ url: currentMsg.image, msg: currentMsg }]; // Lưu cả msg gốc
                const senderID = currentMsg.sender_id;
                const isAdmin = analyzeAdminMessages(currentMsg); // Phân tích lại
                const isCurrentUser = currentMsg.isCurrentUser || (userData && currentMsg.sender_id === userData.id); // Kiểm tra userData
                const baseSentTime = new Date(currentMsg.sent_at); // Dùng Date object để so sánh
                const isRead = currentMsg.is_read;
                let nextIndex = i + 1;

                // Thêm ID tin nhắn vào danh sách đã xử lý
                processedImageIds.current.add(msgId);

                // Kiểm tra các tin nhắn tiếp theo có phải là ảnh từ cùng người gửi không
                while (
                    nextIndex < messages.length &&
                    messages[nextIndex].image &&
                    !messages[nextIndex].text &&
                    messages[nextIndex].sender_id === senderID &&
                    !messages[nextIndex].imageGroup
                ) {
                    const nextMsgId = messages[nextIndex].id || `msg-${messages[nextIndex].sender_id}-${messages[nextIndex].sent_at}-${Math.random()}`;

                    // Bỏ qua nếu đã xử lý hoặc là tin nhắn nhóm
                    if (processedImageIds.current.has(nextMsgId)) {
                        nextIndex++;
                        continue;
                    }

                    // Kiểm tra ngưỡng thời gian (ví dụ: 1.5 phút = 90000 ms)
                    const timeDiff = Math.abs(new Date(messages[nextIndex].sent_at) - baseSentTime);
                    if (timeDiff >= 90000) {
                        break; // Dừng nếu quá ngưỡng thời gian
                    }

                    imageInfo.push({ url: messages[nextIndex].image, msg: messages[nextIndex] });
                    processedImageIds.current.add(nextMsgId);
                    nextIndex++;

                    // Giới hạn tối đa 10 ảnh trong một nhóm
                    if (imageInfo.length >= 10) break;
                }

                // Nếu có nhiều hơn 1 ảnh, tạo một nhóm
                if (imageInfo.length > 1) {
                    const groupId = `group-${isAdmin ? 'admin' : 'user'}-${baseSentTime.getTime()}-${Math.random().toString(36).substring(2, 9)}`;
                    const groupMessage = {
                        id: groupId,
                        sender_id: senderID,
                        isCurrentUser: isCurrentUser,
                        isAdmin: isAdmin, // Thêm thuộc tính isAdmin
                        sent_at: currentMsg.sent_at, // Giữ thời gian của tin nhắn đầu tiên
                        is_read: isRead, // Có thể cần logic phức tạp hơn để xác định is_read cho nhóm
                        imageGroup: {
                            urls: imageInfo.map(info => info.url),
                            groupId: groupId
                        }
                    };

                    processedMessages.push(groupMessage);
                    i = nextIndex; // Bỏ qua các tin nhắn đã được nhóm
                } else {
                    // Nếu chỉ có 1 ảnh, giữ nguyên tin nhắn gốc
                    processedMessages.push(currentMsg);
                    i++;
                }
            } else {
                // Nếu không phải ảnh, thêm vào kết quả bình thường
                processedMessages.push(currentMsg);
                processedImageIds.current.add(msgId); // Đánh dấu đã xử lý
                i++;
            }
        }

        return processedMessages;
    };

    // Refs cho cơ chế đệm ảnh từ admin (GIỮ LẠI KHAI BÁO NÀY)
    const pendingAdminImagesRef = useRef([]);
    const imageBufferTimeoutRef = useRef(null);

    // Hàm lấy lịch sử chat từ server
    const loadChatHistory = async (userId) => {
        if (!userId) return;

        try {
            setIsLoading(true);
            const token = localStorage.getItem("authToken");

            console.log("🔍 Đang tải lịch sử cho user:", userId);
            console.log("🔑 Token:", token?.substring(0, 15) + "...");

            // Gọi API lấy lịch sử tin nhắn
            const response = await axios.get(
                `http://localhost:8000/api/messages/user/${userId}`,
                {
                    headers: {
                        "Authorization": `Bearer ${token}`,
                        "Accept": "application/json",
                        "Content-Type": "application/json"
                    }
                }
            );

            console.log("✅ Phản hồi API:", response.status, response.statusText);

            if (response.data && Array.isArray(response.data)) {
                console.log("📜 Lịch sử tin nhắn:", response.data);

                // Đảm bảo tất cả tin nhắn có trạng thái is_read và isCurrentUser
                const messagesWithStatus = response.data.map(msg => ({
                    ...msg,
                    is_read: typeof msg.is_read === 'boolean' ? msg.is_read : false,
                    isCurrentUser: userData && msg.sender_id === userData.id,
                    isAdmin: !msg.sender_id || (userData && msg.sender_id !== userData.id) // Thêm logic xác định admin
                }));

                // Xử lý nhóm ảnh trước khi set messages
                const groupedMessages = processMessagesWithImageGroups(messagesWithStatus);
                setMessages(groupedMessages);

                // Đếm tin nhắn chưa đọc
                const unread = messagesWithStatus.filter(msg =>
                    !msg.isCurrentUser && !msg.is_read // Chỉ đếm tin nhắn từ người khác và chưa đọc
                ).length;

                setUnreadCount(unread);
                console.log("📬 Số tin nhắn chưa đọc:", unread);

                // Cuộn xuống cuối sau khi load xong
                setTimeout(() => {
                    scrollToBottom();
                }, 100);
            } else {
                console.warn("⚠️ Dữ liệu không đúng định dạng:", response.data);
            }
        } catch (error) {
            console.error("❌ Lỗi khi tải lịch sử tin nhắn:", error.message);
            if (error.response) {
                console.error("📌 Chi tiết lỗi:", error.response.data);
                console.error("📌 Status:", error.response.status);
                console.error("📌 Headers:", error.response.headers);
            } else if (error.request) {
                console.error("📌 Không nhận được phản hồi:", error.request);
            }
        } finally {
            setIsLoading(false);
        }
    };

    // Hàm kiểm tra và kết nối socket 
    const connectSocket = () => {
        try {
            console.log("🔌 Đang kiểm tra kết nối socket...");
            const socketConnection = getSocket();

            if (socketConnection) {
                setSocket(socketConnection);

                // Kiểm tra nếu socket đã kết nối
                if (socketConnection.connected) {
                    console.log("✅ Socket đã được kết nối sẵn:", socketConnection.id);
                    setIsConnected(true);
                    setLastError("");
                } else {
                    console.log("⏳ Socket đã khởi tạo nhưng đang kết nối...");
                    // Không cập nhật trạng thái kết nối ở đây, để khi connect event được kích hoạt
                }

                // Thiết lập sự kiện kết nối
                socketConnection.on("connect", () => {
                    console.log("✅ Socket kết nối thành công:", socketConnection.id);
                    setIsConnected(true);
                    setLastError("");

                    // Thông báo server rằng client đã kết nối
                    socketConnection.emit("clientConnect");
                    console.log("📣 Đã gửi sự kiện clientConnect");

                    // Tải lịch sử tin nhắn khi kết nối thành công
                    if (userData && userData.id) {
                        loadChatHistory(userData.id);
                    }
                });

                // Thiết lập sự kiện ngắt kết nối
                socketConnection.on("disconnect", (reason) => {
                    console.log("❌ Socket ngắt kết nối, lý do:", reason);
                    setIsConnected(false);
                    setLastError(`Mất kết nối với server: ${reason}`);
                });

                // Thiết lập sự kiện lỗi kết nối
                socketConnection.on("connect_error", (error) => {
                    console.error("❌ Lỗi kết nối socket:", error.message);
                    setIsConnected(false);

                    // Xử lý lỗi xác thực đặc biệt
                    if (error.message.includes("Authentication error") || error.message.includes("Invalid token")) {
                        console.log("🔑 Lỗi xác thực, có thể token đã hết hạn. Thử làm mới token...");

                        // Thử làm mới token
                        tryRefreshToken();
                    } else {
                        setLastError("Lỗi kết nối: " + error.message);
                    }
                });

                // Ping server 5 giây một lần để kiểm tra kết nối
                const pingInterval = setInterval(() => {
                    if (socketConnection.connected) {
                        socketConnection.emit("ping", {}, (response) => {
                            if (response && response.success) {
                                // Cập nhật trạng thái kết nối
                                if (!isConnected) {
                                    console.log("✅ Kết nối đã được khôi phục");
                                    setIsConnected(true);
                                    setLastError("");
                                }
                            }
                        });
                    }
                }, 5000);

                // Lưu trữ interval để clear khi component unmount
                socketConnection.pingInterval = pingInterval;

                return socketConnection;
            } else {
                setLastError("Không thể khởi tạo kết nối socket");
                console.error("❌ Không thể khởi tạo kết nối socket");
                return null;
            }
        } catch (error) {
            setLastError(error.message);
            console.error("❌ Lỗi khi kết nối socket:", error);
            return null;
        }
    };

    // Hàm thử làm mới token khi xảy ra lỗi xác thực
    const tryRefreshToken = async () => {
        try {
            console.log("🔄 Đang thử làm mới token...");
            setLastError("Đang làm mới phiên đăng nhập...");

            const refreshToken = localStorage.getItem("refreshToken");
            if (!refreshToken) {
                throw new Error("Không tìm thấy refresh token");
            }

            // Gọi API làm mới token
            const response = await axios.post("http://localhost:8000/api/users/refresh-token", {
                refresh_token: refreshToken
            });

            if (response.data.status === "success") {
                // Lưu token mới vào localStorage
                const newToken = response.data.data.access_token;
                const newRefreshToken = response.data.data.refresh_token;

                localStorage.setItem("authToken", newToken);
                localStorage.setItem("refreshToken", newRefreshToken);

                console.log("✅ Làm mới token thành công, thử kết nối lại socket...");
                setLastError("");

                // Kích hoạt sự kiện auth-change để các component khác cập nhật
                window.dispatchEvent(new Event("auth-change"));

                // Đóng và kết nối lại socket với token mới
                if (socket) {
                    socket.disconnect();
                }
                // Đợi một chút trước khi kết nối lại
                setTimeout(() => {
                    connectSocket();
                }, 1000);
            } else {
                throw new Error("Làm mới token thất bại");
            }
        } catch (error) {
            console.error("❌ Lỗi khi làm mới token:", error);
            setLastError(`Lỗi kết nối: ${error.message}. Vui lòng đăng nhập lại.`);

            // Xóa thông tin đăng nhập
            localStorage.removeItem("authToken");
            localStorage.removeItem("refreshToken");
            localStorage.removeItem("userData");

            // Kích hoạt sự kiện auth-change để các component khác cập nhật
            window.dispatchEvent(new Event("auth-change"));
        }
    };

    useEffect(() => {
        // Kiểm tra đăng nhập
        const authToken = localStorage.getItem("authToken");
        const userDataStr = localStorage.getItem("userData");
        let socketConnection = null;

        if (authToken && userDataStr) {
            try {
                const parsedUserData = JSON.parse(userDataStr);
                setUserData(parsedUserData);
                setIsAuthenticated(true);

                // Khởi tạo kết nối socket
                socketConnection = connectSocket();

                if (socketConnection) {
                    // Thêm sự kiện lắng nghe tin nhắn mới
                    socketConnection.on("newClientMessage", (data) => {
                        console.log("📩 Nhận tin nhắn mới từ client:", data);

                        // Xử lý tin nhắn mới
                        setMessages(prev => processMessagesWithImageGroups([...prev, data]));

                        // Cập nhật unreadCount nếu chat đang đóng
                        if (!isOpen) {
                            setUnreadCount(prev => prev + 1);
                            // Phát âm thanh thông báo
                            playNotificationSound('/sounds/notification-sound.mp3');
                        }
                    });
                }
            } catch (error) {
                console.error("❌ Lỗi xử lý dữ liệu người dùng:", error);
                setLastError("Lỗi xử lý dữ liệu: " + error.message);
                setIsAuthenticated(false);
            }
        } else {
            console.log("❌ Người dùng chưa đăng nhập");
            setIsAuthenticated(false);
        }

        // Cleanup khi unmount
        return () => {
            if (socketConnection) {
                console.log("🧹 Dọn dẹp các sự kiện socket và đóng kết nối");
                socketConnection.off("connect");
                socketConnection.off("disconnect");
                socketConnection.off("connect_error");
                socketConnection.off("newClientMessage");

                // Xóa interval kiểm tra kết nối
                if (socketConnection.pingInterval) {
                    clearInterval(socketConnection.pingInterval);
                }

                // Đóng kết nối socket
                socketConnection.disconnect();
            }
        };
    }, [isOpen]); // Thêm isOpen vào dependencies để useEffect được gọi lại khi isOpen thay đổi

    // Thêm effect mới để theo dõi trạng thái chat box và cập nhật trạng thái đã đọc tin nhắn
    useEffect(() => {
        // Chỉ thực hiện khi chat box mở và có socket kết nối
        if (isOpen && socket && userData?.id) {
            // Đánh dấu tin nhắn đã đọc
            markMessagesAsRead();

            // Xử lý tin nhắn trong chat hiện tại
            setMessages(prev => prev.map(msg => ({
                ...msg,
                is_read: true
            })));
        }
    }, [isOpen, socket, userData]);

    // Theo dõi sự thay đổi đăng nhập
    useEffect(() => {
        const handleAuthChange = () => {
            const authToken = localStorage.getItem("authToken");
            const userDataStr = localStorage.getItem("userData");

            if (authToken && userDataStr) {
                try {
                    const parsedUserData = JSON.parse(userDataStr);
                    setUserData(parsedUserData);
                    setIsAuthenticated(true);

                    // Tải lại lịch sử khi đăng nhập
                    loadChatHistory(parsedUserData.id);
                } catch (error) {
                    console.error("❌ Lỗi xử lý dữ liệu người dùng:", error);
                    setIsAuthenticated(false);
                }
            } else {
                setIsAuthenticated(false);
                setIsOpen(false);
                setMessages([]);
            }
        };

        window.addEventListener("auth-change", handleAuthChange);
        window.addEventListener("storage", handleAuthChange);

        return () => {
            window.removeEventListener("auth-change", handleAuthChange);
            window.removeEventListener("storage", handleAuthChange);
        };
    }, []);

    // Khi mở chat, tải lịch sử
    useEffect(() => {
        if (isOpen && isAuthenticated && userData?.id) {
            loadChatHistory(userData.id);
        }
    }, [isOpen, isAuthenticated, userData]);

    // Thêm useEffect để lắng nghe sự kiện auth-change để kết nối lại socket
    useEffect(() => {
        const handleAuthChangeForSocket = () => {
            console.log("🔄 Nhận sự kiện auth-change, đang khởi tạo lại socket...");

            // Kiểm tra token
            const authToken = localStorage.getItem("authToken");
            const userDataStr = localStorage.getItem("userData");

            if (authToken && userDataStr) {
                try {
                    const parsedUserData = JSON.parse(userDataStr);
                    setUserData(parsedUserData);
                    setIsAuthenticated(true);

                    // Khởi tạo kết nối socket mới
                    const socketConnection = connectSocket();

                    if (socketConnection) {
                        console.log("🔌 Đã khởi tạo lại socket sau sự kiện auth-change");
                    }
                } catch (error) {
                    console.error("❌ Lỗi khi xử lý sự kiện auth-change:", error);
                    setLastError("Lỗi khi xử lý sự kiện auth-change: " + error.message);
                }
            }
        };

        // Đăng ký sự kiện
        window.addEventListener("auth-change", handleAuthChangeForSocket);

        return () => {
            window.removeEventListener("auth-change", handleAuthChangeForSocket);
        };
    }, []);

    // Thêm useEffect để lắng nghe sự kiện messagesMarkedAsRead
    useEffect(() => {
        if (socket) {
            socket.on("messagesMarkedAsRead", (data) => {
                console.log("📬 Nhận sự kiện messagesMarkedAsRead từ server:", data);
                if (data.success && userData) { // Thêm kiểm tra userData tồn tại
                    // Cập nhật trạng thái đã đọc cho các tin nhắn NHẬN được
                    setMessages(prevMessages =>
                        prevMessages.map(msg => {
                            // Nếu tin nhắn được gửi ĐẾN người dùng hiện tại, đánh dấu là đã đọc
                            if (msg.receiver_id === userData.id || (!msg.isCurrentUser && !msg.isAdmin)) { // Check if received by current user
                                return { ...msg, is_read: true };
                            }
                            return msg;
                        })
                    );
                    console.log("✅ Đã cập nhật trạng thái tin nhắn thành đã đọc");
                }
            });

            // Thêm sự kiện lắng nghe tin nhắn của client đã được admin đọc
            socket.on("clientMessagesReadByAdmin", () => {
                console.log("📬 Nhận sự kiện clientMessagesReadByAdmin từ server: Admin đã đọc tin nhắn của bạn");

                // Cập nhật trạng thái đã đọc cho các tin nhắn GỬI ĐI từ client
                setMessages(prevMessages =>
                    prevMessages.map(msg => {
                        // Chỉ cập nhật cho tin nhắn do người dùng hiện tại gửi (isCurrentUser = true)
                        if (msg.isCurrentUser || (userData && msg.sender_id === userData.id)) {
                            return { ...msg, is_read: true };
                        }
                        return msg;
                    })
                );

                console.log("✅ Đã cập nhật trạng thái tin nhắn của client thành đã đọc bởi admin");
            });
        }

        return () => {
            if (socket) {
                socket.off("messagesMarkedAsRead");
                socket.off("clientMessagesReadByAdmin");
            }
        };
    }, [socket, userData]);

    // Thêm sự kiện khi mở chat box
    useEffect(() => {
        if (isOpen && isAuthenticated && userData?.id) {
            // Đánh dấu tin nhắn đã đọc khi mở chatbox
            markMessagesAsRead();
            // Reset số lượng tin nhắn chưa đọc ngay khi mở chat
            setUnreadCount(0);
            console.log("✅ Đã reset số tin nhắn chưa đọc khi mở chat");
        }
    }, [isOpen]);

    // Lắng nghe tin nhắn mới, xử lý đệm ảnh admin
    useEffect(() => {
        if (socket && userData) { // Đảm bảo userData tồn tại để so sánh sender_id

            // --- Hàm xử lý bộ đệm ảnh từ admin --- 
            const processAdminImageBuffer = () => {
                const bufferedImages = pendingAdminImagesRef.current;
                if (bufferedImages.length > 0) {
                    let messageToAdd;
                    if (bufferedImages.length > 1) {
                        // Tạo tin nhắn nhóm ảnh
                        const firstImageMsg = bufferedImages[0];
                        messageToAdd = {
                            id: `group-${Date.now()}-${Math.random().toString(36).substring(2, 9)}`, // Thêm ID duy nhất cho nhóm
                            imageGroup: {
                                urls: bufferedImages.map(msg => msg.image),
                                isUploading: false,
                                uploadProgress: 100,
                            },
                            sender_id: firstImageMsg.sender_id,
                            sent_at: firstImageMsg.sent_at || new Date().toISOString(), // Lấy thời gian của ảnh đầu tiên
                            isCurrentUser: false,
                            is_read: isOpen
                        };
                        console.log("⏳ Tạo nhóm ảnh từ buffer:", messageToAdd);
                    } else {
                        // Tạo tin nhắn ảnh đơn
                        messageToAdd = bufferedImages[0];
                        messageToAdd.is_read = isOpen; // Cập nhật trạng thái đọc
                        console.log("⏳ Xử lý ảnh đơn từ buffer:", messageToAdd);
                    }
                    // Thêm tin nhắn đã xử lý từ buffer vào state
                    setMessages((prev) => [...prev, messageToAdd]);
                    pendingAdminImagesRef.current = []; // Xóa bộ đệm

                    // Xử lý unread count và thông báo khi buffer được xử lý
                    if (!isOpen) {
                        setUnreadCount(prev => prev + 1);
                        // Phát âm thanh thông báo
                        playNotificationSound('/sounds/notification-sound.mp3');
                    } else {
                        // Nếu chat đang mở, đánh dấu đã đọc
                        markMessagesAsRead();
                        // Reset unreadCount
                        setUnreadCount(0);
                    }
                }
                // Xóa timeout ref sau khi xử lý
                if (imageBufferTimeoutRef.current) {
                    clearTimeout(imageBufferTimeoutRef.current);
                    imageBufferTimeoutRef.current = null;
                }
            };

            // --- Handler cho sự kiện adminResponse --- 
            const adminResponseHandler = (data) => {
                console.log("📩 Nhận phản hồi từ admin:", data);

                // Kiểm tra xem có phải tin nhắn từ admin không (sender_id tồn tại và khác user hiện tại)
                const isAdminMessage = data.sender_id && userData && data.sender_id !== userData.id;
                const isImageOnly = !!(data.image && !data.text); // Dùng !! để đảm bảo là boolean

                if (isAdminMessage && isImageOnly) {
                    // Nếu là ảnh đơn từ admin -> đưa vào buffer
                    console.log("⏳ Thêm ảnh admin vào buffer:", data);
                    // Xóa timeout cũ nếu có
                    if (imageBufferTimeoutRef.current) {
                        clearTimeout(imageBufferTimeoutRef.current);
                    }
                    // Thêm ảnh vào buffer
                    pendingAdminImagesRef.current.push(data);
                    // Đặt timeout mới để xử lý buffer sau 1.2 giây
                    imageBufferTimeoutRef.current = setTimeout(processAdminImageBuffer, 1200);
                } else {
                    // Nếu là tin nhắn text từ admin, hoặc tin nhắn từ client (không phải ảnh admin đơn lẻ)
                    // Xử lý buffer ngay lập tức (nếu có ảnh đang chờ)
                    processAdminImageBuffer();

                    // Thêm tin nhắn hiện tại vào messages
                    setMessages((prev) => [...prev, data]);

                    // Xử lý unread count và thông báo cho tin nhắn text từ admin
                    if (isAdminMessage && !isOpen) {
                        setUnreadCount(prev => prev + 1);
                        // Phát âm thanh thông báo
                        playNotificationSound('/sounds/notification-sound.mp3');
                    } else if (isOpen) {
                        // Nếu chat đang mở, đánh dấu đã đọc
                        markMessagesAsRead();
                        // Reset unreadCount
                        setUnreadCount(0);
                    }
                }
            };

            // --- Handler cho sự kiện adminMultipleImagesUpload --- 
            const adminMultipleImagesHandler = (data) => {
                console.log("🖼️ Nhận nhiều ảnh từ admin (sự kiện riêng):", data);
                // Xử lý buffer cũ trước khi thêm nhóm mới (tránh trùng lặp nếu server gửi cả 2)
                processAdminImageBuffer();

                if (data.images && data.images.length > 0 && data.sender_id !== userData?.id) { // Check if from admin
                    // Tạo tin nhắn nhóm ảnh
                    const imageGroupMessage = {
                        id: `group-${Date.now()}-${Math.random().toString(36).substring(2, 9)}`, // Thêm ID duy nhất
                        imageGroup: {
                            urls: data.images,
                            isUploading: false,
                            uploadProgress: 100
                        },
                        sender_id: data.sender_id, // Lấy sender_id từ data sự kiện
                        sent_at: data.sent_at || new Date().toISOString(),
                        isCurrentUser: false,
                        is_read: isOpen
                    };

                    setMessages((prev) => [...prev, imageGroupMessage]);

                    // Xử lý unread count và thông báo
                    if (!isOpen) {
                        setUnreadCount(prev => prev + 1);
                        // Phát âm thanh thông báo
                        playNotificationSound('/sounds/notification-sound.mp3');
                    } else {
                        markMessagesAsRead();
                        // Reset unreadCount
                        setUnreadCount(0);
                    }
                }
            };

            // --- Đăng ký listeners ---
            socket.on("adminResponse", adminResponseHandler);
            socket.on("adminMultipleImagesUpload", adminMultipleImagesHandler);
        }

        // ƯU TIÊN CLEANUP VÀ DEPENDENCY TỪ STASH
        return () => {
            if (socket) {
                socket.off("adminResponse");
                socket.off("adminMultipleImagesUpload");
            }
        };
    }, [socket, isOpen]);

    // Thêm useEffect mới để lắng nghe các sự kiện socket từ socketConfig
    useEffect(() => {
        const handleSocketConnected = (event) => {
            console.log("✅ Nhận sự kiện socket-connected");
            setIsConnected(true);
            setLastError("");
        };

        const handleSocketDisconnected = (event) => {
            console.log("❌ Nhận sự kiện socket-disconnected:", event.detail?.reason);
            setIsConnected(false);
            setLastError(`Mất kết nối: ${event.detail?.reason || 'Lỗi không xác định'}`);
        };

        const handleSocketError = (event) => {
            console.log("❌ Nhận sự kiện socket-error:", event.detail?.message);
            setIsConnected(false);
            setLastError(`Lỗi: ${event.detail?.message || 'Lỗi không xác định'}`);
        };

        const handleSocketConnecting = () => {
            console.log("⏳ Nhận sự kiện socket-connecting");
            // Không đặt isConnected = false ở đây để tránh nhấp nháy UI
            // setLastError("Đang kết nối...");
        };

        // Đăng ký các sự kiện
        window.addEventListener("socket-connected", handleSocketConnected);
        window.addEventListener("socket-disconnected", handleSocketDisconnected);
        window.addEventListener("socket-error", handleSocketError);
        window.addEventListener("socket-connecting", handleSocketConnecting);

        // Kiểm tra trạng thái kết nối hiện tại
        if (socket && socket.connected) {
            setIsConnected(true);
            setLastError("");
        }

        // Cleanup khi unmount
        return () => {
            window.removeEventListener("socket-connected", handleSocketConnected);
            window.removeEventListener("socket-disconnected", handleSocketDisconnected);
            window.removeEventListener("socket-error", handleSocketError);
            window.removeEventListener("socket-connecting", handleSocketConnecting);
        };
    }, [socket]);

    // Hàm xử lý khi chọn ảnh
    const handleImageSelect = (e) => {
        if (e.target.files && e.target.files.length > 0) {
            const files = Array.from(e.target.files);

            // Kiểm tra loại file và kích thước
            const validFiles = files.filter(file => {
                // Kiểm tra loại file
                if (!file.type.startsWith('image/')) {
                    setLastError("Chỉ cho phép tải lên file ảnh");
                    return false;
                }

                // Kiểm tra kích thước file (giới hạn 5MB)
                if (file.size > 5 * 1024 * 1024) {
                    setLastError("Kích thước ảnh không được vượt quá 5MB");
                    return false;
                }

                return true;
            });

            if (validFiles.length === 0) {
                return;
            }

            setSelectedImages(validFiles);

            // Tạo preview URLs
            const newPreviews = [];
            validFiles.forEach(file => {
                const reader = new FileReader();
                reader.onload = (e) => {
                    newPreviews.push(e.target.result);
                    if (newPreviews.length === validFiles.length) {
                        setImagePreviews([...newPreviews]);
                    }
                };
                reader.readAsDataURL(file);
            });
        }
    };

    // Hàm hủy upload ảnh
    const cancelImageUpload = () => {
        setSelectedImages([]);
        setImagePreviews([]);
        if (imageInputRef.current) {
            imageInputRef.current.value = "";
        }
    };

    // Hàm xóa một ảnh cụ thể khỏi danh sách
    const removeImage = (index) => {
        setSelectedImages(prev => prev.filter((_, i) => i !== index));
        setImagePreviews(prev => prev.filter((_, i) => i !== index));
    };

    // Hàm upload nhiều ảnh và gửi tin nhắn
    const uploadAndSendMultipleImages = async (textMessage = null) => {
        if (selectedImages.length === 0 || !isAuthenticated || !socket || !isConnected) {
            console.error("❌ Không thể gửi ảnh: Chưa chọn ảnh, chưa đăng nhập hoặc mất kết nối");
            setLastError("Không thể gửi ảnh: Chưa chọn ảnh, chưa đăng nhập hoặc mất kết nối");
            return;
        }

        // Kiểm tra xem có tin nhắn văn bản kèm theo không
        const hasText = textMessage && textMessage.trim() !== '';

        try {
            setIsUploading(true);

            // Tạo ID nhóm cho lần gửi nhiều ảnh này
            const groupId = Date.now().toString();

            // Thêm một tin nhắn tạm thời cho cả nhóm ảnh
            setMessages(prev => [...prev, {
                imageGroup: {
                    previews: [...imagePreviews],
                    isUploading: true,
                    uploadProgress: 0,
                    groupId: groupId
                },
                sender_id: userData?.id,
                sent_at: new Date().toISOString(),
                isCurrentUser: true,
                is_read: false
            }]);

            // Nếu có văn bản, hiển thị tin nhắn văn bản ngay lập tức
            if (hasText) {
                const textDisplayMessage = {
                    text: textMessage,
                    sender_id: userData?.id,
                    sent_at: new Date().toISOString(),
                    isCurrentUser: true,
                    is_read: false
                };

                setMessages(prev => [...prev, textDisplayMessage]);
            }

            // Tạo mảng promises cho việc upload từng ảnh
            const uploadPromises = selectedImages.map((file, index) => {
                return new Promise((resolve, reject) => {
                    // Tạo FormData để upload ảnh
                    const formData = new FormData();
                    formData.append('image', file);

                    // Lấy token xác thực
                    const token = localStorage.getItem("authToken");

                    // Tạo request với XMLHttpRequest để theo dõi tiến trình
                    const xhr = new XMLHttpRequest();

                    // Cập nhật tiến trình upload
                    xhr.upload.addEventListener('progress', (event) => {
                        if (event.lengthComputable) {
                            const progress = Math.round((event.loaded / event.total) * 100);

                            // Cập nhật trạng thái upload trong tin nhắn nhóm
                            setMessages(prev =>
                                prev.map(msg =>
                                    msg.imageGroup && msg.imageGroup.groupId === groupId
                                        ? {
                                            ...msg,
                                            imageGroup: {
                                                ...msg.imageGroup,
                                                uploadProgress: Math.min(
                                                    msg.imageGroup.uploadProgress + (progress / selectedImages.length),
                                                    99
                                                )
                                            }
                                        }
                                        : msg
                                )
                            );
                        }
                    });

                    xhr.onload = function () {
                        if (xhr.status >= 200 && xhr.status < 300) {
                            try {
                                const response = JSON.parse(xhr.responseText);
                                if (response.success) {
                                    resolve(response.file.url);
                                } else {
                                    reject(new Error(response.message || "Upload thất bại"));
                                }
                            } catch (error) {
                                console.error("❌ Lỗi phân tích dữ liệu:", error.message);
                                reject(new Error("Lỗi phân tích dữ liệu phản hồi"));
                            }
                        } else {
                            reject(new Error(xhr.statusText || "HTTP error"));
                        }
                    };

                    xhr.onerror = function () {
                        console.error(`❌ Lỗi khi upload ảnh ${index}`);
                        // Đánh dấu thất bại trong tin nhắn nhóm
                        setMessages(prev =>
                            prev.map(msg =>
                                msg.imageGroup && msg.imageGroup.groupId === groupId
                                    ? {
                                        ...msg,
                                        imageGroup: {
                                            ...msg.imageGroup,
                                            isUploading: false,
                                            uploadFailed: true
                                        }
                                    }
                                    : msg
                            )
                        );
                        reject(new Error("Lỗi kết nối"));
                    };

                    // Gửi request - Sử dụng socketServerUrl thay vì hardcode
                    xhr.open('POST', `${socketServerUrl}/upload`, true);
                    xhr.setRequestHeader('Authorization', `Bearer ${token}`);
                    xhr.setRequestHeader('Accept', 'application/json');
                    xhr.send(formData);
                });
            });

            // Chờ tất cả ảnh upload xong
            Promise.all(uploadPromises.map(p => p.catch((err) => {
                console.error("Lỗi upload:", err.message);
                return null;
            })))
                .then(results => {
                    // Lọc ra các URL thành công (loại bỏ các lỗi)
                    const successfulUrls = results.filter(result => typeof result === 'string');

                    if (successfulUrls.length > 0) {
                        // Gửi tất cả URL ảnh thành công qua socket
                        const sendData = {
                            images: successfulUrls,
                            text: hasText ? textMessage : null
                        };

                        // Log data trước khi gửi để debug
                        console.log("🚀 Dữ liệu gửi đi:", sendData);

                        socket.emit("clientMultipleImagesUpload", sendData, (socketResponse) => {
                            if (socketResponse.success) {
                                console.log("✅ Tất cả ảnh đã được gửi thành công");
                                console.log("✅ Tin nhắn văn bản kèm theo:", hasText ? textMessage : "không có");

                                // Cập nhật tin nhắn tạm thời thành tin nhắn thật với URLs từ server
                                setMessages(prev => prev.map(msg => {
                                    // Nếu là tin nhắn nhóm với groupId phù hợp
                                    if (msg.imageGroup && msg.imageGroup.groupId === groupId) {
                                        return {
                                            ...msg,
                                            imageGroup: {
                                                urls: successfulUrls,
                                                isUploading: false,
                                                uploadProgress: 100
                                            }
                                        };
                                    }
                                    return msg;
                                }));

                                setLastError("");
                            } else {
                                console.error("❌ Lỗi gửi ảnh:", socketResponse.error);
                                setLastError("Lỗi gửi ảnh: " + (socketResponse.error || "Không xác định"));

                                // Đánh dấu tin nhắn nhóm là upload thất bại
                                setMessages(prev =>
                                    prev.map(msg =>
                                        msg.imageGroup && msg.imageGroup.groupId === groupId
                                            ? {
                                                ...msg,
                                                imageGroup: {
                                                    ...msg.imageGroup,
                                                    isUploading: false,
                                                    uploadFailed: true
                                                }
                                            }
                                            : msg
                                    )
                                );
                            }
                        });
                    }
                })
                .finally(() => {
                    // Reset form
                    setSelectedImages([]);
                    setImagePreviews([]);
                    if (imageInputRef.current) {
                        imageInputRef.current.value = "";
                    }
                    setIsUploading(false);

                    // Xóa nội dung tin nhắn văn bản nếu có
                    if (hasText) {
                        setMessage("");
                    }
                });

        } catch (uploadError) {
            console.error("❌ Lỗi khi gửi nhiều ảnh:", uploadError.message);
            setLastError(`Lỗi gửi ảnh: ${uploadError.message}`);
            setIsUploading(false);
        }
    };

    const sendMessage = () => {
        if (!isAuthenticated || !socket || !isConnected) {
            console.error("❌ Không thể gửi tin nhắn: Chưa đăng nhập hoặc mất kết nối");
            setLastError("Không thể gửi tin nhắn: Chưa đăng nhập hoặc mất kết nối");
            return;
        }

        const currentMessage = message.trim();
        const hasTextMessage = currentMessage !== "";

        // Nếu có ảnh được chọn, gửi cả ảnh và văn bản (nếu có)
        if (selectedImages.length > 0) {
            uploadAndSendMultipleImages(hasTextMessage ? currentMessage : null);
            return;
        }

        // Nếu chỉ có văn bản (không có ảnh), gửi tin nhắn văn bản thông thường
        if (hasTextMessage) {
            const messageData = {
                text: currentMessage
            };

            // Phân biệt giữa admin và client
            if (userData?.role === "admin") {
                // Cần thêm userId của người nhận
                // Hiện tại chưa có người nhận cụ thể
                console.error("❌ Admin cần chọn người nhận trước khi gửi tin nhắn");
                setLastError("Admin cần chọn người nhận trước khi gửi tin nhắn");
                return;
            } else {
                // Người dùng thường gửi tin nhắn
                socket.emit("clientMessage", messageData, (response) => {
                    if (response.success) {
                        console.log("✅ Tin nhắn đã được gửi thành công");
                        setLastError("");
                    } else {
                        console.error("❌ Lỗi gửi tin nhắn:", response.error);
                        setLastError("Lỗi gửi tin nhắn: " + (response.error || "Không xác định"));
                    }
                });
            }

            // Hiển thị tin nhắn ngay lập tức ở UI
            const displayMessage = {
                text: currentMessage,
                sender_id: userData?.id,
                sent_at: new Date().toISOString(),
                isCurrentUser: true,
                is_read: false // Tin nhắn mới gửi luôn ở trạng thái chưa đọc
            };

            setMessages((prev) => [...prev, displayMessage]);
            setMessage("");
        }
    };

    const handleKeyPress = (e) => {
        if (e.key === "Enter" && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    };

    // Cải thiện hàm scroll
    const scrollToBottom = () => {
        if (messagesEndRef.current) {
            const chatBox = messagesEndRef.current.parentElement;
            if (chatBox) {
                chatBox.scrollTo({
                    top: chatBox.scrollHeight,
                    behavior: 'smooth'
                });
            }
        }
    };

    // Thêm useEffect để xử lý scroll khi có tin nhắn mới
    useEffect(() => {
        if (!isLoading && messages.length > 0) {
            scrollToBottom();
        }
    }, [messages, isLoading]);

    // Mở lightbox với index ảnh cụ thể
    const openLightbox = (images, index) => {
        try {
            const formattedImages = images.map(url => ({ src: url }));
            setLightboxImages(formattedImages);
            setLightboxIndex(index);
            setIsLightboxOpen(true);
        } catch (err) {
            console.error("Lỗi khi mở lightbox:", err);
        }
    };

    // Cập nhật hàm đánh dấu tin nhắn đã đọc
    const markMessagesAsRead = async () => {
        if (!isAuthenticated || !userData?.id) return;

        try {
            const token = localStorage.getItem("authToken");

            // Gọi API đánh dấu tất cả tin nhắn là đã đọc
            await axios.patch(
                `http://localhost:8000/api/messages/read-all/${userData.id}`,
                {},
                {
                    headers: {
                        "Authorization": `Bearer ${token}`,
                        "Accept": "application/json",
                        "Content-Type": "application/json"
                    }
                }
            );

            // Cập nhật UI
            setUnreadCount(0); // Đặt rõ số tin nhắn chưa đọc là 0
            setMessages(prev => prev.map(msg => {
                // Chỉ đánh dấu tin nhắn nhận được (không phải của người dùng hiện tại) là đã đọc
                if (msg.sender_id !== userData?.id && !msg.isCurrentUser) {
                    return { ...msg, is_read: true };
                }
                // Giữ nguyên trạng thái is_read cho tin nhắn người dùng đã gửi
                return msg;
            }));

            console.log("✅ Đã đánh dấu tin nhắn nhận được là đã đọc và reset unreadCount");
        } catch (error) {
            console.error("❌ Lỗi khi đánh dấu tin nhắn đã đọc:", error.message);
        }
    };

    // Thêm effect để đánh dấu tin nhắn đã đọc khi tải lần đầu
    useEffect(() => {
        if (messages.length > 0 && isAuthenticated && userData?.id) {
            // Đếm số tin nhắn chưa đọc để hiển thị badge
            const unreadMessages = messages.filter(msg =>
                !msg.is_read && (!msg.isCurrentUser && msg.sender_id !== userData.id)
            );

            // Cập nhật số tin nhắn chưa đọc
            setUnreadCount(unreadMessages.length);

            // Tự động đánh dấu tin nhắn đã đọc nếu chat box đang mở
            if (isOpen) {
                // markMessagesAsRead(); // <---- Loại bỏ dòng này
                // setUnreadCount(0); // <---- Loại bỏ dòng này
            }

            console.log(`✅ Đã cập nhật số tin nhắn chưa đọc: ${unreadMessages.length}`);
        }
    }, [messages, isAuthenticated, userData, isOpen]);

    // Thêm hàm xử lý khi chọn emoji
    const handleEmojiClick = (emojiData) => {
        const emoji = emojiData.emoji;
        setMessage(prev => prev + emoji);
    };

    // Thêm hàm ẩn emoji picker khi click bên ngoài
    const emojiPickerRef = useRef(null);

    useEffect(() => {
        function handleClickOutside(event) {
            if (emojiPickerRef.current && !emojiPickerRef.current.contains(event.target)) {
                setShowEmojiPicker(false);
            }
        }

        document.addEventListener('mousedown', handleClickOutside);
        return () => {
            document.removeEventListener('mousedown', handleClickOutside);
        };
    }, []);

    // Nếu không đăng nhập, không hiển thị box chat
    if (!isAuthenticated) {
        return null;
    }

    return (
        <div className="fixed bottom-5 left-5 z-50">
            {/* Chat Bubble Button - Cải thiện với hiệu ứng mới */}
            <button
                onClick={() => {
                    setIsOpen(!isOpen);
                    // Nếu đang mở chat box, gọi markMessagesAsRead và reset số tin nhắn chưa đọc
                    if (!isOpen && isAuthenticated) {
                        setUnreadCount(0); // Reset số tin nhắn chưa đọc ngay lập tức
                        markMessagesAsRead(); // Gọi API đánh dấu đã đọc và update UI
                    }
                }}
                className={`w-16 h-16 rounded-full flex items-center justify-center text-white shadow-lg transition-all duration-300 chat-bubble-button ripple-button ${isOpen
                    ? "bg-gradient-to-r from-red-500 to-red-600"
                    : "bg-gradient-to-r from-emerald-500 to-green-600"
                    }`}
            >
                {isOpen ? <BsXLg className="text-2xl" /> : <BsChatDots className="text-2xl" />}
                {!isConnected && !isOpen && (
                    <span className="absolute -bottom-1 -right-1 bg-yellow-500 w-4 h-4 rounded-full animate-pulse shadow-md"></span>
                )}
            </button>

            {/* Badge hiển thị số tin nhắn chưa đọc - Di chuyển ra ngoài button */}
            {!isOpen && unreadCount > 0 && (
                <span className="absolute -top-2 -right-2 bg-red-500 text-white w-6 h-6 rounded-full text-xs flex items-center justify-center animate-pulse shadow-lg">
                    {unreadCount}
                </span>
            )}

            {/* Chat Box - Cải thiện với hiệu ứng glass morphism */}
            {isOpen && (
                <div className="absolute bottom-24 left-0 w-[380px] h-[520px] rounded-2xl shadow-2xl flex flex-col overflow-hidden animate-slideIn glass-effect">
                    {/* Header - Gradient */}
                    <div className="bg-gradient-to-r from-green-500 to-emerald-600 text-white p-4 flex items-center justify-between rounded-t-xl">
                        <h3 className="text-lg font-semibold flex items-center">
                            <BsChatDots className="mr-2" /> Hỗ Trợ Trực Tuyến
                        </h3>
                        <div className="flex items-center">
                            <span className="flex items-center text-sm mr-2 bg-white bg-opacity-20 px-3 py-1 rounded-full">
                                {isConnected ? (
                                    <>
                                        <span className="w-2 h-2 rounded-full mr-2 bg-white animate-pulse"></span>
                                        Online
                                    </>
                                ) : (
                                    <>
                                        <span className="w-2 h-2 rounded-full mr-2 bg-yellow-300 animate-pulse"></span>
                                        {lastError ? "Mất kết nối" : "Đang kết nối..."}
                                    </>
                                )}
                            </span>
                        </div>
                    </div>

                    {/* Error message - Cải thiện với thiết kế nhẹ nhàng hơn */}
                    {lastError && (
                        <div className="bg-red-50 text-red-700 p-3 text-xs border-l-4 border-red-500 flex items-center">
                            <span className="mr-2">⚠️</span> {lastError}
                        </div>
                    )}

                    {/* Messages - Cải thiện với background và hiệu ứng */}
                    <div className="flex-1 p-4 overflow-y-auto overflow-x-hidden bg-gradient-to-b from-gray-50 to-white space-y-4 chat-box">
                        {isLoading ? (
                            <div className="h-full flex flex-col items-center justify-center text-gray-500 space-y-3">
                                <div className="typing-indicator">
                                    <span></span>
                                    <span></span>
                                    <span></span>
                                </div>
                                <p>Đang tải tin nhắn...</p>
                            </div>
                        ) : messages.length === 0 ? (
                            <div className="h-full flex flex-col items-center justify-center text-gray-500">
                                <img src="/images/welcome-chat.svg" alt="Welcome" className="w-32 h-32 mb-4 opacity-70" onError={(e) => e.target.style.display = 'none'} />
                                <p className="bg-gray-100 p-3 rounded-lg shadow-sm">Bắt đầu cuộc trò chuyện với chúng tôi</p>
                            </div>
                        ) : (
                            <>
                                {messages.map((msg, index) => (
                                    <div key={index} className={`flex ${msg.sender_id === userData?.id || msg.isCurrentUser ? "justify-end" : "justify-start"} message-appear chat-message-container`}>
                                        <div
                                            className={`max-w-[85%] rounded-2xl p-3 shadow-sm chat-message-content ${msg.sender_id === userData?.id || msg.isCurrentUser
                                                ? "bg-gradient-to-r from-green-500 to-emerald-600 text-white rounded-br-none sparkle-effect"
                                                : "bg-white border border-gray-100 text-gray-800 rounded-bl-none"
                                                }`}
                                        >
                                            {/* Hiển thị nhóm ảnh nếu có */}
                                            {msg.imageGroup && (
                                                <div className="mb-2 relative chat-image">
                                                    {msg.imageGroup.isUploading ? (
                                                        <>
                                                            {/* Hiển thị preview khi đang upload */}
                                                            <div className="flex flex-wrap gap-1 mb-1">
                                                                {msg.imageGroup.previews.map((preview, idx) => (
                                                                    <div key={idx} className="relative">
                                                                        <img
                                                                            src={preview}
                                                                            alt={`Preview ${idx + 1}`}
                                                                            className="h-[80px] w-[80px] object-cover rounded-lg opacity-70"
                                                                        />
                                                                    </div>
                                                                ))}
                                                            </div>
                                                            {/* Progress bar cho upload nhóm */}
                                                            <div className="w-full bg-white bg-opacity-30 h-2 mt-1 rounded-full overflow-hidden">
                                                                <div
                                                                    className="h-full bg-white"
                                                                    style={{ width: `${msg.imageGroup.uploadProgress || 0}%` }}
                                                                ></div>
                                                            </div>
                                                            <p className="text-white text-xs text-center mt-1">
                                                                Đang tải: {msg.imageGroup.uploadProgress || 0}%
                                                            </p>
                                                        </>
                                                    ) : msg.imageGroup.uploadFailed ? (
                                                        <div className="text-center text-sm text-white bg-red-400 p-2 rounded-lg">
                                                            Tải ảnh thất bại, vui lòng thử lại
                                                        </div>
                                                    ) : (
                                                        <>
                                                            {/* Hiển thị ảnh đã tải thành công */}
                                                            {msg.imageGroup.urls && (
                                                                <>
                                                                    {msg.imageGroup.urls.length === 1 ? (
                                                                        // Nếu chỉ có 1 ảnh, hiển thị to hơn
                                                                        <div
                                                                            className="cursor-pointer transition-transform hover:scale-105"
                                                                            onClick={() => openLightbox(msg.imageGroup.urls, 0)}
                                                                        >
                                                                            <img
                                                                                src={msg.imageGroup.urls[0]}
                                                                                alt="Hình ảnh"
                                                                                className="rounded-lg max-w-full max-h-[200px] object-contain"
                                                                            />
                                                                        </div>
                                                                    ) : msg.imageGroup.urls.length === 2 ? (
                                                                        // Nếu có 2 ảnh, hiển thị dạng 50-50
                                                                        <div className="grid grid-cols-2 gap-1">
                                                                            {msg.imageGroup.urls.map((url, idx) => (
                                                                                <div
                                                                                    key={idx}
                                                                                    className="cursor-pointer transition-transform hover:scale-105"
                                                                                    onClick={() => openLightbox(msg.imageGroup.urls, idx)}
                                                                                >
                                                                                    <img
                                                                                        src={url}
                                                                                        alt={`Hình ảnh ${idx + 1}`}
                                                                                        className="w-full h-[100px] object-cover rounded-lg"
                                                                                    />
                                                                                </div>
                                                                            ))}
                                                                        </div>
                                                                    ) : msg.imageGroup.urls.length === 3 ? (
                                                                        // Nếu có 3 ảnh, hiển thị 2 ảnh trên, 1 ảnh dưới
                                                                        <div className="grid grid-cols-2 gap-1">
                                                                            {msg.imageGroup.urls.slice(0, 2).map((url, idx) => (
                                                                                <div
                                                                                    key={idx}
                                                                                    className="cursor-pointer transition-transform hover:scale-105"
                                                                                    onClick={() => openLightbox(msg.imageGroup.urls, idx)}
                                                                                >
                                                                                    <img
                                                                                        src={url}
                                                                                        alt={`Hình ảnh ${idx + 1}`}
                                                                                        className="w-full h-[80px] object-cover rounded-lg"
                                                                                    />
                                                                                </div>
                                                                            ))}
                                                                            <div
                                                                                className="col-span-2 cursor-pointer transition-transform hover:scale-105"
                                                                                onClick={() => openLightbox(msg.imageGroup.urls, 2)}
                                                                            >
                                                                                <img
                                                                                    src={msg.imageGroup.urls[2]}
                                                                                    alt="Hình ảnh 3"
                                                                                    className="w-full h-[80px] object-cover rounded-lg"
                                                                                />
                                                                            </div>
                                                                        </div>
                                                                    ) : msg.imageGroup.urls.length === 4 ? (
                                                                        // Nếu có 4 ảnh, hiển thị dạng lưới 2x2
                                                                        <div className="grid grid-cols-2 gap-1">
                                                                            {msg.imageGroup.urls.slice(0, 4).map((url, idx) => (
                                                                                <div
                                                                                    key={idx}
                                                                                    className="cursor-pointer transition-transform hover:scale-105"
                                                                                    onClick={() => openLightbox(msg.imageGroup.urls, idx)}
                                                                                >
                                                                                    <img
                                                                                        src={url}
                                                                                        alt={`Hình ảnh ${idx + 1}`}
                                                                                        className="w-full h-[80px] object-cover rounded-lg"
                                                                                    />
                                                                                </div>
                                                                            ))}
                                                                        </div>
                                                                    ) : (
                                                                        // Nếu có 5+ ảnh, hiển thị 4 ảnh với ảnh cuối "+X"
                                                                        <div className="grid grid-cols-2 gap-1">
                                                                            {msg.imageGroup.urls.slice(0, 3).map((url, idx) => (
                                                                                <div
                                                                                    key={idx}
                                                                                    className="cursor-pointer transition-transform hover:scale-105"
                                                                                    onClick={() => openLightbox(msg.imageGroup.urls, idx)}
                                                                                >
                                                                                    <img
                                                                                        src={url}
                                                                                        alt={`Hình ảnh ${idx + 1}`}
                                                                                        className="w-full h-[80px] object-cover rounded-lg"
                                                                                    />
                                                                                </div>
                                                                            ))}
                                                                            <div
                                                                                className="cursor-pointer relative transition-transform hover:scale-105"
                                                                                onClick={() => openLightbox(msg.imageGroup.urls, 3)}
                                                                            >
                                                                                <img
                                                                                    src={msg.imageGroup.urls[3]}
                                                                                    alt="Hình ảnh 4+"
                                                                                    className="w-full h-[80px] object-cover rounded-lg brightness-50"
                                                                                />
                                                                                <div className="absolute inset-0 flex items-center justify-center text-white font-bold text-xl">
                                                                                    +{msg.imageGroup.urls.length - 4}
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    )}
                                                                </>
                                                            )}
                                                        </>
                                                    )}
                                                </div>
                                            )}

                                            {/* Hiển thị ảnh đơn lẻ (cho tin nhắn cũ) */}
                                            {msg.image && !msg.imageGroup && (
                                                <div
                                                    className="mb-2 relative cursor-pointer transition-transform hover:scale-105 chat-image"
                                                    onClick={() => openLightbox([msg.image], 0)}
                                                >
                                                    <img
                                                        src={msg.image}
                                                        alt="Hình ảnh"
                                                        className="rounded-lg max-w-full max-h-[200px] object-contain"
                                                    />

                                                    {/* Hiển thị progress bar nếu đang upload */}
                                                    {msg.isUploading && (
                                                        <div className="absolute bottom-0 left-0 w-full bg-black bg-opacity-50 p-1 rounded-b-lg">
                                                            <div className="h-1 bg-white rounded-full overflow-hidden">
                                                                <div
                                                                    className="h-full bg-green-300"
                                                                    style={{ width: `${msg.uploadProgress || 0}%` }}
                                                                ></div>
                                                            </div>
                                                            <p className="text-white text-xs text-center mt-1">
                                                                {msg.uploadProgress || 0}%
                                                            </p>
                                                        </div>
                                                    )}

                                                    {/* Hiển thị icon lỗi nếu upload thất bại */}
                                                    {msg.uploadFailed && (
                                                        <div className="absolute top-0 right-0 bg-red-500 text-white p-1 rounded-full text-xs">
                                                            !
                                                        </div>
                                                    )}
                                                </div>
                                            )}

                                            {/* Hiển thị text nếu có */}
                                            {msg.text && <p className="text-sm">{replaceTextWithEmojis(msg.text)}</p>}

                                            <div className="flex justify-between items-center mt-1">
                                                <span className="text-xs opacity-70">
                                                    {new Date(msg.sent_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}
                                                </span>
                                                {(msg.sender_id === userData?.id || msg.isCurrentUser) && (
                                                    <span className={`text-xs ${msg.is_read ? "message-read" : "message-unread"}`}>
                                                        {msg.is_read ? "Đã xem" : "Đã gửi"}
                                                    </span>
                                                )}
                                            </div>
                                        </div>
                                    </div>
                                ))}
                                <div ref={messagesEndRef} />
                            </>
                        )}
                    </div>

                    {/* Image Preview - Cải thiện với thiết kế hiện đại */}
                    {imagePreviews.length > 0 && (
                        <div className="px-4 pt-2 bg-gray-50">
                            <div className="flex flex-wrap gap-2">
                                {imagePreviews.map((preview, index) => (
                                    <div key={index} className="relative inline-block">
                                        <img
                                            src={preview}
                                            alt={`Preview ${index + 1}`}
                                            className="h-20 rounded-lg border border-gray-200 object-cover shadow-sm transition-transform hover:scale-105"
                                        />
                                        <button
                                            onClick={() => removeImage(index)}
                                            className="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center shadow-md transition-transform hover:scale-110"
                                        >
                                            <BsXLg size={12} />
                                        </button>
                                    </div>
                                ))}
                                <button
                                    onClick={cancelImageUpload}
                                    className="text-sm text-red-500 mt-2 hover:text-red-700 transition-colors"
                                >
                                    Hủy tất cả
                                </button>
                            </div>
                        </div>
                    )}

                    {/* Emoji Picker */}
                    {showEmojiPicker && (
                        <div
                            ref={emojiPickerRef}
                            className="absolute bottom-20 left-4 z-10 animate__animated animate__fadeIn"
                        >
                            <EmojiPicker
                                onEmojiClick={handleEmojiClick}
                                skinTonesDisabled
                                searchDisabled={false}
                                width={320}
                                height={350}
                            />
                        </div>
                    )}

                    {/* Input Area - Cải thiện với thiết kế hiện đại */}
                    <div className="p-4 bg-white border-t border-gray-100">
                        <div className="flex items-center gap-2">
                            <textarea
                                value={message}
                                onChange={(e) => setMessage(e.target.value)}
                                onKeyPress={handleKeyPress}
                                placeholder="Nhập tin nhắn..."
                                className="flex-1 resize-none rounded-full px-5 py-3 border border-gray-200 focus:outline-none focus:border-green-500 focus:ring-2 focus:ring-green-200 text-sm min-h-[45px] max-h-[100px] shadow-sm transition-all"
                                rows="1"
                                disabled={!isConnected || isUploading}
                            />

                            {/* Nút Emoji - Mới thêm */}
                            <button
                                onClick={() => setShowEmojiPicker(!showEmojiPicker)}
                                disabled={!isConnected || isUploading}
                                className={`w-12 h-12 rounded-full flex items-center justify-center transition-all duration-300 shadow-md hover:shadow-lg ${isConnected && !isUploading
                                    ? "bg-gradient-to-r from-yellow-400 to-yellow-500 text-white hover:scale-110"
                                    : "bg-gray-200 text-gray-500 cursor-not-allowed"
                                    }`}
                            >
                                <BsEmojiSmile className="text-xl" />
                            </button>

                            {/* Nút chọn ảnh - Cải thiện với hiệu ứng */}
                            <button
                                onClick={() => imageInputRef.current?.click()}
                                disabled={!isConnected || isUploading}
                                className={`w-12 h-12 rounded-full flex items-center justify-center transition-all duration-300 shadow-md hover:shadow-lg ${isConnected && !isUploading
                                    ? "bg-gradient-to-r from-blue-500 to-blue-600 text-white hover:scale-110"
                                    : "bg-gray-200 text-gray-500 cursor-not-allowed"
                                    }`}
                            >
                                <MdImage className="text-xl" />
                            </button>

                            {/* Input file ẩn - hỗ trợ chọn nhiều file */}
                            <input
                                type="file"
                                ref={imageInputRef}
                                onChange={handleImageSelect}
                                accept="image/*"
                                multiple // Thêm multiple để cho phép chọn nhiều ảnh
                                className="hidden"
                            />

                            {/* Nút gửi - Cải thiện với hiệu ứng */}
                            <button
                                onClick={sendMessage}
                                disabled={(!message.trim() && selectedImages.length === 0) || !isConnected || isUploading}
                                className={`w-12 h-12 rounded-full flex items-center justify-center text-white transition-all duration-300 shadow-md hover:shadow-lg ${(message.trim() || selectedImages.length > 0) && isConnected && !isUploading
                                    ? "bg-gradient-to-r from-green-500 to-emerald-600 hover:scale-110"
                                    : "bg-gray-200 text-gray-400 cursor-not-allowed"
                                    }`}
                            >
                                <IoMdSend className="text-xl" />
                            </button>
                        </div>
                    </div>
                </div>
            )}

            {/* Lightbox component */}
            <Lightbox
                open={isLightboxOpen}
                close={() => setIsLightboxOpen(false)}
                slides={lightboxImages}
                index={lightboxIndex}
            />
        </div>
    );
};

export default ChatRealTime;