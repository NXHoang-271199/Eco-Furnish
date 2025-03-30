import { useEffect, useState, useRef } from "react";
import { BsChatDots, BsXLg } from "react-icons/bs";
import { IoMdSend } from "react-icons/io";
import { getSocket, isSocketConnected, resetSocket } from "../utils/socketConfig";
import axios from "axios";

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

    // Thêm ref cho phần messages
    const messagesEndRef = useRef(null);

    // Hàm lấy lịch sử chat từ server
    const loadChatHistory = async (userId) => {
        if (!userId) return;
        
        try {
            setIsLoading(true);
            const token = localStorage.getItem("authToken");
            
            console.log("🔍 Đang tải lịch sử cho user:", userId);
            console.log("🔑 Token:", token?.substring(0, 15) + "...");
            
            // Gọi API lấy lịch sử tin nhắn - đảm bảo đúng đường dẫn
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
                setMessages(response.data);
                
                // Đếm tin nhắn chưa đọc
                const unread = response.data.filter(msg => 
                    msg.receiver_id === userId && !msg.is_read
                ).length;
                
                setUnreadCount(unread);
                console.log("📬 Số tin nhắn chưa đọc:", unread);
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
                setIsConnected(socketConnection.connected);
                console.log("✅ Đã thiết lập kết nối socket", socketConnection.id);
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

    useEffect(() => {
        // Kiểm tra đăng nhập
        const authToken = localStorage.getItem("authToken");
        const userDataStr = localStorage.getItem("userData");
        
        if (authToken && userDataStr) {
            try {
                const parsedUserData = JSON.parse(userDataStr);
                setUserData(parsedUserData);
                setIsAuthenticated(true);
                
                // Khởi tạo kết nối socket
                const socketConnection = connectSocket();
                
                if (socketConnection) {
                    // Kiểm tra kết nối
                    socketConnection.on("connect", () => {
                        console.log("✅ Socket kết nối thành công");
                        setIsConnected(true);
                        setLastError("");
                        
                        // Thông báo server rằng client đã kết nối
                        socketConnection.emit("clientConnect");
                        console.log("📣 Đã gửi sự kiện clientConnect");
                        
                        // Tải lịch sử tin nhắn khi kết nối thành công
                        loadChatHistory(parsedUserData.id);
                    });
                    
                    socketConnection.on("disconnect", () => {
                        console.log("❌ Socket ngắt kết nối");
                        setIsConnected(false);
                        setLastError("Mất kết nối với server");
                    });
                    
                    socketConnection.on("connect_error", (error) => {
                        console.error("❌ Lỗi kết nối socket:", error.message);
                        setIsConnected(false);
                        setLastError("Lỗi kết nối: " + error.message);
                    });
                    
                    // Nhận tin nhắn
                    socketConnection.on("newClientMessage", (data) => {
                        console.log("📩 Nhận tin nhắn mới từ client:", data);
                        setMessages((prev) => [...prev, data]);
                    });
                    
                    socketConnection.on("adminResponse", (data) => {
                        console.log("📩 Nhận phản hồi từ admin:", data);
                        setMessages((prev) => [...prev, data]);
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
            if (socket) {
                socket.off("connect");
                socket.off("disconnect");
                socket.off("connect_error");
                socket.off("newClientMessage");
                socket.off("adminResponse");
            }
        };
    }, []);
    
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

    // Lắng nghe sự kiện auth-change để kết nối lại socket
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

    // Hàm đánh dấu tin nhắn đã đọc
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
            setUnreadCount(0);
            setMessages(prev => prev.map(msg => ({
                ...msg,
                is_read: true
            })));
            
            console.log("✅ Đã đánh dấu tất cả tin nhắn là đã đọc");
        } catch (error) {
            console.error("❌ Lỗi khi đánh dấu tin nhắn đã đọc:", error.message);
        }
    };

    // Thêm sự kiện khi mở chat box
    useEffect(() => {
        if (isOpen && isAuthenticated && userData?.id) {
            // Đánh dấu tin nhắn đã đọc khi mở chatbox
            markMessagesAsRead();
        }
    }, [isOpen]);

    // Lắng nghe tin nhắn mới từ admin
    useEffect(() => {
        if (socket) {
            socket.on("adminResponse", (data) => {
                console.log("📩 Nhận phản hồi từ admin:", data);
                setMessages((prev) => [...prev, data]);
                
                // Tăng số lượng tin nhắn chưa đọc nếu chat box đang đóng
                if (!isOpen) {
                    setUnreadCount(prev => prev + 1);
                    
                    // Thông báo âm thanh nếu có thể
                    const audio = new Audio('/notification.mp3');
                    audio.play().catch(e => console.log("Không thể phát âm thanh"));
                } else {
                    // Nếu chat box đang mở, đánh dấu là đã đọc
                    markMessagesAsRead();
                }
            });
        }
        
        return () => {
            if (socket) {
                socket.off("adminResponse");
            }
        };
    }, [socket, isOpen]);

    const sendMessage = () => {
        if (!isAuthenticated || !socket || !isConnected) {
            console.error("❌ Không thể gửi tin nhắn: Chưa đăng nhập hoặc mất kết nối");
            setLastError("Không thể gửi tin nhắn: Chưa đăng nhập hoặc mất kết nối");
            return;
        }
        
        if (message.trim() !== "") {
            const messageData = {
                text: message
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
                text: message,
                sender_id: userData?.id,
                sent_at: new Date().toISOString(),
                isCurrentUser: true
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

    // Hàm kiểm tra kết nối
    const checkConnection = () => {
        const currentSocket = socket || connectSocket();
        
        if (currentSocket) {
            console.log("🔌 Trạng thái kết nối:", currentSocket.connected ? "Connected" : "Disconnected");
            setIsConnected(currentSocket.connected);
            
            if (!currentSocket.connected) {
                console.log("🔄 Đang cố gắng kết nối lại...");
                const newSocket = resetSocket();
                setSocket(newSocket);
                
                if (newSocket) {
                    setTimeout(() => {
                        setIsConnected(newSocket.connected);
                        console.log("🔌 Trạng thái kết nối mới:", newSocket.connected ? "Connected" : "Disconnected");
                    }, 1000);
                }
            }
        } else {
            console.error("❌ Không có kết nối socket");
            setLastError("Không có kết nối socket");
        }
    };

    // Hàm scroll xuống cuối cuộc hội thoại
    const scrollToBottom = () => {
        messagesEndRef.current?.scrollIntoView({ behavior: "smooth" });
    };

    // Cuộn xuống khi có tin nhắn mới
    useEffect(() => {
        scrollToBottom();
    }, [messages]);

    // Nếu không đăng nhập, không hiển thị box chat
    if (!isAuthenticated) {
        return null;
    }

    return (
        <div className="fixed bottom-5 left-5 z-50">
            {/* Chat Bubble Button */}
            <button
                onClick={() => setIsOpen(!isOpen)}
                className={`w-14 h-14 rounded-full flex items-center justify-center text-white shadow-lg transition-all duration-300 hover:scale-110 ${
                    isOpen ? "bg-red-500 hover:bg-red-600" : "bg-green-500 hover:bg-green-600"
                }`}
            >
                {isOpen ? <BsXLg className="text-2xl" /> : <BsChatDots className="text-2xl" />}
                {!isOpen && unreadCount > 0 && (
                    <span className="absolute -top-1 -right-1 bg-red-500 text-white w-5 h-5 rounded-full text-xs flex items-center justify-center animate-pulse">
                        {unreadCount}
                    </span>
                )}
            </button>

            {/* Chat Box */}
            {isOpen && (
                <div className="absolute bottom-20 left-0 w-[350px] h-[500px] bg-white rounded-lg shadow-2xl flex flex-col overflow-hidden animate-slideIn">
                    {/* Header */}
                    <div className="bg-green-500 text-white p-4 flex items-center justify-between">
                        <h3 className="text-lg font-semibold">Chat Room</h3>
                        <div className="flex items-center">
                            <span className="flex items-center text-sm mr-2">
                                <span
                                    className={`w-2 h-2 rounded-full mr-2 ${isConnected ? "bg-white" : "bg-red-400"}`}
                                ></span>
                                {isConnected ? "Online" : "Connecting..."}
                            </span>
                            <button
                                onClick={checkConnection}
                                className="text-xs bg-blue-400 text-white px-2 py-1 rounded mr-1"
                                title="Kiểm tra kết nối"
                            >
                                Test
                            </button>
                            {!isConnected && (
                                <button
                                    onClick={() => {
                                        const newSocket = resetSocket();
                                        if (newSocket) {
                                            setSocket(newSocket);
                                            setTimeout(() => setIsConnected(isSocketConnected()), 500);
                                        }
                                    }}
                                    className="text-xs bg-white text-green-600 px-2 py-1 rounded"
                                >
                                    Kết nối lại
                                </button>
                            )}
                        </div>
                    </div>

                    {/* Error message */}
                    {lastError && (
                        <div className="bg-red-100 text-red-700 p-2 text-xs">
                            Lỗi: {lastError}
                        </div>
                    )}

                    {/* Messages */}
                    <div className="flex-1 p-4 overflow-y-auto bg-gray-50 space-y-4">
                        {isLoading ? (
                            <div className="h-full flex items-center justify-center text-gray-500">
                                <p>Đang tải tin nhắn...</p>
                            </div>
                        ) : messages.length === 0 ? (
                            <div className="h-full flex items-center justify-center text-gray-500">
                                <p>Bắt đầu cuộc trò chuyện với chúng tôi</p>
                            </div>
                        ) : (
                            <>
                                {messages.map((msg, index) => (
                                    <div key={index} className={`flex ${msg.sender_id === userData?.id || msg.isCurrentUser ? "justify-end" : "justify-start"}`}>
                                        <div
                                            className={`max-w-[80%] rounded-lg p-3 ${
                                                msg.sender_id === userData?.id || msg.isCurrentUser ? "bg-green-500 text-white rounded-br-sm" : "bg-gray-200 text-gray-800 rounded-bl-sm"
                                            }`}
                                        >
                                            <p className="text-sm">{msg.text}</p>
                                            <div className="flex justify-between items-center mt-1">
                                                <span className="text-xs opacity-70">
                                                    {new Date(msg.sent_at).toLocaleTimeString()}
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

                    {/* Input Area */}
                    <div className="p-4 bg-white border-t border-gray-200">
                        <div className="flex items-center gap-2">
                            <textarea
                                value={message}
                                onChange={(e) => setMessage(e.target.value)}
                                onKeyPress={handleKeyPress}
                                placeholder="Nhập tin nhắn..."
                                className="flex-1 resize-none rounded-full px-4 py-2 border border-gray-300 focus:outline-none focus:border-green-500 text-sm min-h-[40px] max-h-[100px]"
                                rows="1"
                                disabled={!isConnected}
                            />
                            <button
                                onClick={sendMessage}
                                disabled={!message.trim() || !isConnected}
                                className={`w-10 h-10 rounded-full flex items-center justify-center text-white transition-colors ${
                                    message.trim() && isConnected ? "bg-green-500 hover:bg-green-600" : "bg-gray-300 cursor-not-allowed"
                                }`}
                            >
                                <IoMdSend className="text-lg" />
                            </button>
                        </div>
                    </div>
                </div>
            )}
        </div>
    );
};

export default ChatRealTime;
