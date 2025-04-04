import { useEffect, useState, useRef } from "react";
import { BsChatDots, BsXLg } from "react-icons/bs";
import { IoMdSend } from "react-icons/io";
import { MdImage } from "react-icons/md";
import { getSocket } from "../utils/socketConfig";
import axios from "axios";
import Lightbox from "yet-another-react-lightbox";
import "yet-another-react-lightbox/styles.css";

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
    // Thêm state để theo dõi quá trình nhóm ảnh (Thêm lại do vẫn được dùng)
    const [imageGroups, setImageGroups] = useState({});
    // State cho lightbox
    const [isLightboxOpen, setIsLightboxOpen] = useState(false);
    const [lightboxIndex, setLightboxIndex] = useState(0);
    const [lightboxImages, setLightboxImages] = useState([]);

    // Thêm ref cho phần messages và image input
    const messagesEndRef = useRef(null);
    const imageInputRef = useRef(null);
    
    // Ref để theo dõi tin nhắn đã xử lý
    const processedImageIds = useRef(new Set());

    // Cải thiện hàm phân tích tin nhắn từ admin
    const analyzeAdminMessages = (message) => {
        // Kiểm tra nếu tin nhắn có chứa "admin" trong ID hoặc từ admin
        return message &&
               (!message.isCurrentUser && userData && // Thêm kiểm tra userData tồn tại
                (message.sender_id.toString() !== userData.id?.toString()));
    };

    // Thêm hàm xử lý và nhóm các tin nhắn ảnh liên tiếp
    const processMessagesWithImageGroups = (messagesArray) => {
        if (!messagesArray || !Array.isArray(messagesArray) || messagesArray.length === 0) {
            return [];
        }

        // Lưu trữ tin nhắn gốc để tham chiếu sau này
        // originalMessages.current = messagesArray; // Tạm thời comment out nếu không dùng

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
                    
                    socketConnection.on("newClientMessage", (data) => {
                        console.log("📩 Nhận tin nhắn mới từ client:", data);
                        setMessages(prev => processMessagesWithImageGroups([...prev, data]));
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

    // Thêm useEffect để lắng nghe sự kiện messagesMarkedAsRead
    useEffect(() => {
        if (socket) {
            socket.on("messagesMarkedAsRead", (data) => {
                console.log("📬 Nhận sự kiện messagesMarkedAsRead:", data);
                if (data.success) {
                    // Cập nhật trạng thái đã đọc cho tất cả tin nhắn
                    setMessages(prevMessages => 
                        prevMessages.map(msg => {
                            // Nếu là tin nhắn của người dùng hiện tại (từ sender_id), đánh dấu là đã đọc
                            if (msg.sender_id === userData?.id) {
                                return { ...msg, is_read: true };
                            }
                            return msg;
                        })
                    );
                    console.log("✅ Đã cập nhật trạng thái tin nhắn thành đã đọc");
                }
            });
        }
        
        return () => {
            if (socket) {
                socket.off("messagesMarkedAsRead");
            }
        };
    }, [socket, userData]);

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
                            id: `group-${Date.now()}-${Math.random().toString(36).substring(2, 9)}`, // Thêm ID duy nhất cho nhóm (GIỮ LẠI TỪ STASH)
                            imageGroup: {
                                urls: bufferedImages.map(msg => msg.image),
                                isUploading: false,
                                uploadProgress: 100,
                            },
                            sender_id: firstImageMsg.sender_id, 
                            sent_at: firstImageMsg.sent_at || new Date().toISOString(), // Lấy thời gian của ảnh đầu tiên (GIỮ LẠI TỪ STASH)
                            isCurrentUser: false,
                            is_read: isOpen
                        };
                        console.log("⏳ Tạo nhóm ảnh từ buffer:", messageToAdd);
                    } else {
                        // Tạo tin nhắn ảnh đơn
                        messageToAdd = bufferedImages[0];
                        messageToAdd.is_read = isOpen; // Cập nhật trạng thái đọc (GIỮ LẠI TỪ STASH)
                        console.log("⏳ Xử lý ảnh đơn từ buffer:", messageToAdd);
                    }
                    // Thêm tin nhắn đã xử lý từ buffer vào state
                    setMessages((prev) => [...prev, messageToAdd]);
                    pendingAdminImagesRef.current = []; // Xóa bộ đệm

                     // Xử lý unread count và thông báo khi buffer được xử lý
                    if (!isOpen) {
                        setUnreadCount(prev => prev + 1);
                        const audio = new Audio('/notification.mp3');
                        audio.play().catch(() => console.log("Không thể phát âm thanh"));
                    } else {
                        markMessagesAsRead(); // Đánh dấu đã đọc nếu chat mở
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
                    // Thêm ảnh vào buffer (GIỮ LẠI TỪ STASH)
                    pendingAdminImagesRef.current.push(data);
                    // Đặt timeout mới để xử lý buffer sau 1.2 giây
                    imageBufferTimeoutRef.current = setTimeout(processAdminImageBuffer, 1200); 
                } else {
                    // Nếu là tin nhắn text từ admin, hoặc tin nhắn từ client (không phải ảnh admin đơn lẻ)
                    // Xử lý buffer ngay lập tức (nếu có ảnh đang chờ)
                    processAdminImageBuffer(); 
                    
                    // Thêm tin nhắn hiện tại vào messages (GIỮ LẠI TỪ STASH)
                    setMessages((prev) => [...prev, data]);

                    // Xử lý unread count và thông báo cho tin nhắn text từ admin
                    if (isAdminMessage && !isImageOnly && !isOpen) {
                        setUnreadCount(prev => prev + 1);
                        const audio = new Audio('/notification.mp3');
                        audio.play().catch(() => console.log("Không thể phát âm thanh"));
                    } else if (isOpen) {
                        // Nếu chat đang mở, đánh dấu đã đọc
                        markMessagesAsRead();
                    }
                }
            };

            // --- Handler cho sự kiện adminMultipleImagesUpload --- 
            const adminMultipleImagesHandler = (data) => {
                console.log("🖼️ Nhận nhiều ảnh từ admin (sự kiện riêng):", data);
                 // Xử lý buffer cũ trước khi thêm nhóm mới (tránh trùng lặp nếu server gửi cả 2)
                 processAdminImageBuffer(); 

                if (data.images && data.images.length > 0) {
                    // Tạo tin nhắn nhóm ảnh
                    const imageGroupMessage = {
                        id: `group-${Date.now()}-${Math.random().toString(36).substring(2, 9)}`, // Thêm ID duy nhất (GIỮ LẠI TỪ STASH)
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
                        const audio = new Audio('/notification.mp3');
                        audio.play().catch(() => console.log("Không thể phát âm thanh"));
                    } else {
                        markMessagesAsRead();
                    }
                }
            };
            
            // --- Đăng ký listeners ---
            socket.on("adminResponse", adminResponseHandler);
            socket.on("adminMultipleImagesUpload", adminMultipleImagesHandler);
        }
        
        // GIỮ LẠI PHẦN CLEANUP TỪ STASH
        return () => {
            if (socket) {
                socket.off("adminResponse");
                socket.off("adminMultipleImagesUpload");
            }
        };
    }, [socket, isOpen]); // GIỮ LẠI DEPENDENCY TỪ STASH

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
    const uploadAndSendMultipleImages = async () => {
        if (selectedImages.length === 0 || !isAuthenticated || !socket || !isConnected) {
            console.error("❌ Không thể gửi ảnh: Chưa chọn ảnh, chưa đăng nhập hoặc mất kết nối");
            setLastError("Không thể gửi ảnh: Chưa chọn ảnh, chưa đăng nhập hoặc mất kết nối");
            return;
        }
        
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
                    
                    xhr.onload = function() {
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
                    
                    xhr.onerror = function() {
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
                    
                    // Gửi request
                    xhr.open('POST', 'http://localhost:3002/upload', true);
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
                        socket.emit("clientMultipleImagesUpload", { images: successfulUrls }, (socketResponse) => {
                            if (socketResponse.success) {
                                console.log("✅ Tất cả ảnh đã được gửi thành công");
                                
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
                                
                                // Lưu nhóm ảnh vào state để tham chiếu sau này
                                setImageGroups(prev => ({
                                    ...prev,
                                    [groupId]: successfulUrls
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
        
        // Nếu có ảnh được chọn, ưu tiên gửi ảnh
        if (selectedImages.length > 0) {
            uploadAndSendMultipleImages();
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
                                            {/* Hiển thị nhóm ảnh nếu có */}
                                            {msg.imageGroup && (
                                                <div className="mb-2 relative">
                                                    {msg.imageGroup.isUploading ? (
                                                        <>
                                                            {/* Hiển thị preview khi đang upload */}
                                                            <div className="flex flex-wrap gap-1 mb-1">
                                                                {msg.imageGroup.previews.map((preview, idx) => (
                                                                    <div key={idx} className="relative">
                                                                        <img 
                                                                            src={preview} 
                                                                            alt={`Preview ${idx + 1}`} 
                                                                            className="h-[80px] w-[80px] object-cover rounded opacity-70"
                                                                        />
                                                                    </div>
                                                                ))}
                                                            </div>
                                                            {/* Progress bar cho upload nhóm */}
                                                            <div className="w-full bg-white bg-opacity-30 h-1 mt-1 rounded overflow-hidden">
                                                                <div 
                                                                    className="h-full bg-white" 
                                                                    style={{width: `${msg.imageGroup.uploadProgress || 0}%`}} 
                                                                ></div>
                                                            </div>
                                                            <p className="text-white text-xs text-center mt-1">
                                                                Đang tải: {msg.imageGroup.uploadProgress || 0}%
                                                            </p>
                                                        </>
                                                    ) : msg.imageGroup.uploadFailed ? (
                                                        <div className="text-center text-sm text-white bg-red-400 p-2 rounded">
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
                                                                            className="cursor-pointer" 
                                                                            onClick={() => openLightbox(msg.imageGroup.urls, 0)}
                                                                        >
                                                                            <img 
                                                                                src={msg.imageGroup.urls[0]} 
                                                                                alt="Hình ảnh" 
                                                                                className="rounded max-w-full max-h-[200px] object-contain"
                                                                            />
                                                                        </div>
                                                                    ) : msg.imageGroup.urls.length === 2 ? (
                                                                        // Nếu có 2 ảnh, hiển thị dạng 50-50
                                                                        <div className="grid grid-cols-2 gap-1">
                                                                            {msg.imageGroup.urls.map((url, idx) => (
                                                                                <div 
                                                                                    key={idx} 
                                                                                    className="cursor-pointer" 
                                                                                    onClick={() => openLightbox(msg.imageGroup.urls, idx)}
                                                                                >
                                                                                    <img 
                                                                                        src={url} 
                                                                                        alt={`Hình ảnh ${idx + 1}`} 
                                                                                        className="w-full h-[100px] object-cover rounded" 
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
                                                                                    className="cursor-pointer" 
                                                                                    onClick={() => openLightbox(msg.imageGroup.urls, idx)}
                                                                                >
                                                                                    <img 
                                                                                        src={url} 
                                                                                        alt={`Hình ảnh ${idx + 1}`} 
                                                                                        className="w-full h-[80px] object-cover rounded" 
                                                                                    />
                                                                                </div>
                                                                            ))}
                                                                            <div 
                                                                                className="col-span-2 cursor-pointer" 
                                                                                onClick={() => openLightbox(msg.imageGroup.urls, 2)}
                                                                            >
                                                                                <img 
                                                                                    src={msg.imageGroup.urls[2]} 
                                                                                    alt="Hình ảnh 3" 
                                                                                    className="w-full h-[80px] object-cover rounded" 
                                                                                />
                                                                            </div>
                                                                        </div>
                                                                    ) : (
                                                                        // Nếu có 4+ ảnh, hiển thị dạng lưới với ảnh cuối "+X"
                                                                        <div className="grid grid-cols-2 gap-1">
                                                                            {msg.imageGroup.urls.slice(0, 2).map((url, idx) => (
                                                                                <div 
                                                                                    key={idx} 
                                                                                    className="cursor-pointer" 
                                                                                    onClick={() => openLightbox(msg.imageGroup.urls, idx)}
                                                                                >
                                                                                    <img 
                                                                                        src={url} 
                                                                                        alt={`Hình ảnh ${idx + 1}`} 
                                                                                        className="w-full h-[80px] object-cover rounded" 
                                                                                    />
                                                                                </div>
                                                                            ))}
                                                                            <div 
                                                                                className="cursor-pointer" 
                                                                                onClick={() => openLightbox(msg.imageGroup.urls, 2)}
                                                                            >
                                                                                <img 
                                                                                    src={msg.imageGroup.urls[2]} 
                                                                                    alt="Hình ảnh 3" 
                                                                                    className="w-full h-[80px] object-cover rounded" 
                                                                                />
                                                                            </div>
                                                                            <div 
                                                                                className="cursor-pointer relative" 
                                                                                onClick={() => openLightbox(msg.imageGroup.urls, 3)}
                                                                            >
                                                                                <img 
                                                                                    src={msg.imageGroup.urls[3]} 
                                                                                    alt="Hình ảnh 4+" 
                                                                                    className="w-full h-[80px] object-cover rounded brightness-50" 
                                                                                />
                                                                                <div className="absolute inset-0 flex items-center justify-center text-white font-bold text-xl">
                                                                                    +{msg.imageGroup.urls.length - 3}
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
                                                    className="mb-2 relative cursor-pointer" 
                                                    onClick={() => openLightbox([msg.image], 0)}
                                                >
                                                    <img 
                                                        src={msg.image} 
                                                        alt="Hình ảnh" 
                                                        className="rounded max-w-full max-h-[200px] object-contain"
                                                    />
                                                    
                                                    {/* Hiển thị progress bar nếu đang upload */}
                                                    {msg.isUploading && (
                                                        <div className="absolute bottom-0 left-0 w-full bg-black bg-opacity-50 p-1">
                                                            <div className="h-1 bg-white rounded overflow-hidden">
                                                                <div 
                                                                    className="h-full bg-green-300" 
                                                                    style={{width: `${msg.uploadProgress || 0}%`}} 
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
                                            {msg.text && <p className="text-sm">{msg.text}</p>}
                                            
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

                    {/* Image Preview */}
                    {imagePreviews.length > 0 && (
                        <div className="px-4 pt-2">
                            <div className="flex flex-wrap gap-2">
                                {imagePreviews.map((preview, index) => (
                                    <div key={index} className="relative inline-block">
                                        <img 
                                            src={preview} 
                                            alt={`Preview ${index + 1}`} 
                                            className="h-20 rounded border border-gray-300 object-cover" 
                                        />
                                        <button 
                                            onClick={() => removeImage(index)}
                                            className="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center shadow"
                                        >
                                            <BsXLg size={10} />
                                        </button>
                                    </div>
                                ))}
                                <button
                                    onClick={cancelImageUpload}
                                    className="text-sm text-red-500 mt-2"
                                >
                                    Hủy tất cả
                                </button>
                            </div>
                        </div>
                    )}

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
                                disabled={!isConnected || isUploading}
                            />
                            
                            {/* Nút chọn ảnh */}
                            <button
                                onClick={() => imageInputRef.current?.click()}
                                disabled={!isConnected || isUploading}
                                className={`w-10 h-10 rounded-full flex items-center justify-center transition-colors ${
                                    isConnected && !isUploading 
                                    ? "bg-blue-500 hover:bg-blue-600 text-white" 
                                    : "bg-gray-300 text-gray-500 cursor-not-allowed"
                                }`}
                            >
                                <MdImage className="text-lg" />
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
                            
                            {/* Nút gửi */}
                            <button
                                onClick={sendMessage}
                                disabled={(!message.trim() && selectedImages.length === 0) || !isConnected || isUploading}
                                className={`w-10 h-10 rounded-full flex items-center justify-center text-white transition-colors ${
                                    (message.trim() || selectedImages.length > 0) && isConnected && !isUploading
                                    ? "bg-green-500 hover:bg-green-600" 
                                    : "bg-gray-300 cursor-not-allowed"
                                }`}
                            >
                                <IoMdSend className="text-lg" />
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
