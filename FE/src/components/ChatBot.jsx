import { useState, useRef, useEffect } from 'react';
import axios from 'axios';
import { FaRobot, FaPaperPlane, FaTimes, FaTrash } from 'react-icons/fa';

const ChatBot = () => {
    const [isOpen, setIsOpen] = useState(false);
    const [messages, setMessages] = useState([]);
    const [input, setInput] = useState('');
    const [isLoading, setIsLoading] = useState(false);
    const messagesEndRef = useRef(null);

    // Load chat history from localStorage when component mounts
    useEffect(() => {
        const savedMessages = localStorage.getItem('chatHistory');
        if (savedMessages) {
            try {
                setMessages(JSON.parse(savedMessages));
            } catch (error) {
                console.error('Error parsing saved messages:', error);
                localStorage.removeItem('chatHistory');
            }
        }
    }, []);

    // Save messages to localStorage whenever they change
    useEffect(() => {
        if (messages.length > 0) {
            localStorage.setItem('chatHistory', JSON.stringify(messages));
        }
    }, [messages]);

    const toggleChat = () => {
        setIsOpen(!isOpen);
    };

    const handleInputChange = (e) => {
        setInput(e.target.value);
    };

    const scrollToBottom = () => {
        messagesEndRef.current?.scrollIntoView({ behavior: 'smooth' });
    };

    useEffect(() => {
        scrollToBottom();
    }, [messages]);

    const clearChat = () => {
        setMessages([]);
        localStorage.removeItem('chatHistory');
    };

    const sendMessage = async (e) => {
        e.preventDefault();
        if (input.trim() === '') return;

        const userMessage = {
            text: input,
            sender: 'user',
            timestamp: new Date().toISOString()
        };

        setMessages([...messages, userMessage]);
        setInput('');
        setIsLoading(true);

        try {
            // Tạo một bản sao của input để sử dụng trong API call
            const messageToSend = input.trim();

            console.log('Sending message to API:', messageToSend);

            // Sử dụng đường dẫn tương đối để tận dụng proxy trong Vite
            const response = await axios.post('/api/chat', {
                message: messageToSend
            }, {
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            });

            console.log('API response:', response.data);

            // Kiểm tra dữ liệu trả về để tránh lỗi null
            let botReply = 'Xin lỗi, đã xảy ra lỗi khi xử lý tin nhắn của bạn.';

            if (response.data && typeof response.data.reply === 'string') {
                botReply = response.data.reply;
            }

            const botMessage = {
                text: botReply,
                sender: 'bot',
                timestamp: new Date().toISOString()
            };

            setMessages(prevMessages => [...prevMessages, botMessage]);
        } catch (error) {
            console.error('Error sending message:', error);
            console.error('Error details:', error.response ? error.response.data : 'No response data');

            const errorMessage = {
                text: `Xin lỗi, đã xảy ra lỗi khi xử lý tin nhắn của bạn. Vui lòng thử lại sau. (${error.message})`,
                sender: 'bot',
                timestamp: new Date().toISOString()
            };

            setMessages(prevMessages => [...prevMessages, errorMessage]);
        } finally {
            setIsLoading(false);
        }
    };

    // Handle Enter key press
    const handleKeyPress = (e) => {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage(e);
        }
    };

    return (
        <div className="fixed bottom-5 right-5 z-50">
            {/* Chat Button */}
            <button
                onClick={toggleChat}
                className="bg-blue-600 hover:bg-blue-700 text-white rounded-full p-4 shadow-lg flex items-center justify-center"
                aria-label="Chat với trợ lý AI"
            >
                {isOpen ? <FaTimes size={20} /> : <FaRobot size={20} />}
            </button>

            {/* Chat Window */}
            {isOpen && (
                <div className="absolute bottom-16 right-0 w-80 sm:w-96 bg-white rounded-lg shadow-xl flex flex-col overflow-hidden border border-gray-200">
                    {/* Chat Header */}
                    <div className="bg-blue-600 text-white p-3 flex items-center">
                        <FaRobot className="mr-2" />
                        <h3 className="font-medium">Trợ lý AI</h3>
                        <div className="ml-auto flex items-center">
                            {messages.length > 0 && (
                                <button
                                    onClick={clearChat}
                                    className="text-white hover:text-gray-200 mr-3"
                                    aria-label="Xóa lịch sử chat"
                                    title="Xóa lịch sử chat"
                                >
                                    <FaTrash size={14} />
                                </button>
                            )}
                            <button
                                onClick={toggleChat}
                                className="text-white hover:text-gray-200"
                                aria-label="Đóng chat"
                            >
                                <FaTimes />
                            </button>
                        </div>
                    </div>

                    {/* Chat Messages */}
                    <div className="flex-1 p-3 overflow-y-auto max-h-96 bg-gray-50">
                        {messages.length === 0 ? (
                            <div className="text-center text-gray-500 py-8">
                                <FaRobot className="mx-auto mb-2 text-gray-400" size={24} />
                                <p>Xin chào! Tôi có thể giúp gì cho bạn?</p>
                                <p className="text-sm mt-2">Hãy đặt câu hỏi về sản phẩm, dịch vụ hoặc bất kỳ thông tin nào bạn cần.</p>
                            </div>
                        ) : (
                            messages.map((msg, index) => (
                                <div
                                    key={index}
                                    className={`mb-3 flex ${msg.sender === 'user' ? 'justify-end' : 'justify-start'}`}
                                >
                                    <div
                                        className={`max-w-[80%] rounded-lg p-3 ${msg.sender === 'user'
                                            ? 'bg-blue-600 text-white rounded-br-none'
                                            : 'bg-gray-200 text-gray-800 rounded-bl-none'
                                            }`}
                                    >
                                        <p className="whitespace-pre-wrap">{msg.text || 'Không có nội dung'}</p>
                                        <span className="text-xs opacity-70 block mt-1">
                                            {new Date(msg.timestamp).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}
                                        </span>
                                    </div>
                                </div>
                            ))
                        )}
                        {isLoading && (
                            <div className="flex justify-start mb-3">
                                <div className="bg-gray-200 text-gray-800 rounded-lg rounded-bl-none p-3 max-w-[80%]">
                                    <div className="flex space-x-1">
                                        <div className="w-2 h-2 bg-gray-500 rounded-full animate-bounce"></div>
                                        <div className="w-2 h-2 bg-gray-500 rounded-full animate-bounce delay-75"></div>
                                        <div className="w-2 h-2 bg-gray-500 rounded-full animate-bounce delay-150"></div>
                                    </div>
                                </div>
                            </div>
                        )}
                        <div ref={messagesEndRef} />
                    </div>

                    {/* Chat Input */}
                    <form onSubmit={sendMessage} className="border-t border-gray-200 p-3 flex">
                        <input
                            type="text"
                            value={input}
                            onChange={handleInputChange}
                            onKeyPress={handleKeyPress}
                            placeholder="Nhập tin nhắn..."
                            className="flex-1 border border-gray-300 rounded-l-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            disabled={isLoading}
                        />
                        <button
                            type="submit"
                            className={`bg-blue-600 text-white px-4 rounded-r-lg flex items-center justify-center ${isLoading || input.trim() === '' ? 'opacity-50 cursor-not-allowed' : 'hover:bg-blue-700'
                                }`}
                            disabled={isLoading || input.trim() === ''}
                        >
                            <FaPaperPlane />
                        </button>
                    </form>
                </div>
            )}
        </div>
    );
};

export default ChatBot; 