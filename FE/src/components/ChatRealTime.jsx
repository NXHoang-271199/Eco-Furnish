import { useEffect, useState } from "react";
import { io } from "socket.io-client";
import { BsChatDots, BsXLg } from "react-icons/bs";
import { IoMdSend } from "react-icons/io";

const socket = io("http://127.0.0.1:3001", {
    transports: ["websocket"],
});

const ChatRealTime = () => {
    const [message, setMessage] = useState("");
    const [messages, setMessages] = useState([]);
    const [isOpen, setIsOpen] = useState(false);
    const [isConnected, setIsConnected] = useState(false);
    const [userType, setUserType] = useState("client"); // "client" | "admin"
    const [userId, setUserId] = useState(null);

    useEffect(() => {
        socket.on("connect", () => {
            setIsConnected(true);
            console.log("✅ Connected to server");

            // Giả sử client tự động đăng nhập với tên mặc định
            const userData = { name: "User " + Math.floor(Math.random() * 1000) };
            socket.emit("clientConnect", userData);
        });

        socket.on("disconnect", () => {
            setIsConnected(false);
            console.log("❌ Disconnected from server");
        });

        socket.on("newClientMessage", (data) => {
            setMessages((prev) => [...prev, data]);
        });

        socket.on("adminResponse", (data) => {
            setMessages((prev) => [...prev, data]);
        });

        socket.on("newClientConnected", (data) => {
            console.log("👤 New client connected:", data);
        });

        return () => {
            socket.off("newClientMessage");
            socket.off("adminResponse");
            socket.off("newClientConnected");
            socket.off("connect");
            socket.off("disconnect");
        };
    }, []);

    const sendMessage = () => {
        if (message.trim() !== "") {
            const messageData = {
                text: message,
                sender: userType,
                userId: socket.id,
                timestamp: new Date().toLocaleTimeString()
            };

            if (userType === "client") {
                socket.emit("clientMessage", messageData);
            } else {
                socket.emit("adminMessage", messageData);
            }

            setMessages((prev) => [...prev, messageData]);
            setMessage("");
        }
    };

    const handleKeyPress = (e) => {
        if (e.key === "Enter" && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    };

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
                {!isOpen && messages.length > 0 && (
                    <span className="absolute -top-1 -right-1 bg-red-500 text-white w-5 h-5 rounded-full text-xs flex items-center justify-center">
                        {messages.length}
                    </span>
                )}
            </button>

            {/* Chat Box */}
            {isOpen && (
                <div className="absolute bottom-20 left-0 w-[350px] h-[500px] bg-white rounded-lg shadow-2xl flex flex-col overflow-hidden animate-slideIn">
                    {/* Header */}
                    <div className="bg-green-500 text-white p-4 flex items-center justify-between">
                        <h3 className="text-lg font-semibold">Chat Room</h3>
                        <span className="flex items-center text-sm">
                            <span
                                className={`w-2 h-2 rounded-full mr-2 ${isConnected ? "bg-white" : "bg-red-400"}`}
                            ></span>
                            {isConnected ? "Online" : "Connecting..."}
                        </span>
                    </div>

                    {/* Messages */}
                    <div className="flex-1 p-4 overflow-y-auto bg-gray-50 space-y-4">
                        {messages.map((msg, index) => (
                            <div key={index} className={`flex ${msg.sender === userType ? "justify-end" : "justify-start"}`}>
                                <div
                                    className={`max-w-[80%] rounded-lg p-3 ${
                                        msg.sender === userType ? "bg-green-500 text-white rounded-br-sm" : "bg-gray-200 text-gray-800 rounded-bl-sm"
                                    }`}
                                >
                                    <p className="text-sm">{msg.text}</p>
                                    <span className="text-xs opacity-70 mt-1 block">{msg.timestamp}</span>
                                </div>
                            </div>
                        ))}
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
