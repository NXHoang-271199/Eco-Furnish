import { useState, useRef, useEffect } from 'react';
import axios from 'axios';
import { FaRobot, FaPaperPlane, FaTimes, FaTrash, FaShoppingCart, FaExternalLinkAlt } from 'react-icons/fa';

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
            // Debug thêm thông tin danh mục
            console.log('Categories from API:', response.data.categories);
            console.log('Products from API:', response.data.products);

            // Kiểm tra dữ liệu trả về để tránh lỗi null
            let botReply = 'Xin lỗi, đã xảy ra lỗi khi xử lý tin nhắn của bạn.';
            let products = [];
            let searchKeywords = '';
            let isProductSearch = false;
            let categories = [];

            if (response.data && typeof response.data.reply === 'string') {
                botReply = response.data.reply;
            }

            // Kiểm tra xem có phải là tìm kiếm sản phẩm không
            if (response.data && response.data.hasOwnProperty('has_products')) {
                // Chỉ đánh dấu là tìm kiếm sản phẩm nếu có từ khóa tìm kiếm
                if (response.data.hasOwnProperty('search_keywords') && response.data.search_keywords.trim() !== '') {
                    isProductSearch = true;
                    searchKeywords = response.data.search_keywords;
                }

                // Kiểm tra xem có sản phẩm được trả về không
                if (response.data.has_products && Array.isArray(response.data.products)) {
                    products = response.data.products;
                }

                // Lấy danh mục từ phản hồi API
                if (response.data.hasOwnProperty('categories') && Array.isArray(response.data.categories)) {
                    categories = response.data.categories;
                }
            }

            const botMessage = {
                text: botReply,
                sender: 'bot',
                timestamp: new Date().toISOString(),
                products: products,
                isProductSearch: isProductSearch,
                searchKeywords: searchKeywords,
                categories: categories
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

    // Component hiển thị sản phẩm
    const ProductCard = ({ product }) => {
        const formatPrice = (price) => {
            return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(price);
        };

        return (
            <div className="border rounded-lg overflow-hidden mb-2 bg-white shadow-sm hover:shadow-md transition-shadow">
                <div className="flex">
                    <div className="w-20 h-20 flex-shrink-0 bg-gray-100 flex items-center justify-center">
                        {product.image ? (
                            <img
                                src={`/storage/${product.image}`}
                                alt={product.name}
                                className="w-full h-full object-cover"
                                onError={(e) => {
                                    console.log("Lỗi tải ảnh:", e.target.src);
                                    e.target.onerror = null;
                                    // Sử dụng Bootstrap icons
                                    e.target.parentNode.innerHTML = '<div class="flex items-center justify-center w-full h-full text-gray-400"><svg width="32" height="32" fill="currentColor" viewBox="0 0 16 16"><path d="M6.002 5.5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0z"/><path d="M2.002 1a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V3a2 2 0 0 0-2-2h-12zm12 1a1 1 0 0 1 1 1v6.5l-3.777-1.947a.5.5 0 0 0-.577.093l-3.71 3.71-2.66-1.772a.5.5 0 0 0-.63.062L1.002 12V3a1 1 0 0 1 1-1h12z"/></svg></div>';
                                }}
                            />
                        ) : (
                            <div className="flex items-center justify-center w-full h-full text-gray-400">
                                <svg width="32" height="32" fill="currentColor" viewBox="0 0 16 16">
                                    <path d="M6.002 5.5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0z" />
                                    <path d="M2.002 1a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V3a2 2 0 0 0-2-2h-12zm12 1a1 1 0 0 1 1 1v6.5l-3.777-1.947a.5.5 0 0 0-.577.093l-3.71 3.71-2.66-1.772a.5.5 0 0 0-.63.062L1.002 12V3a1 1 0 0 1 1-1h12z" />
                                </svg>
                            </div>
                        )}
                    </div>
                    <div className="p-2 flex-1">
                        <h4 className="font-medium text-sm text-gray-800 line-clamp-1">{product.name}</h4>
                        <p className="text-xs text-gray-500 mb-1">{product.category}</p>
                        <div className="flex items-center">
                            {product.discount_price ? (
                                <>
                                    <span className="text-sm font-bold text-red-600">{formatPrice(product.discount_price)}</span>
                                    <span className="text-xs text-gray-400 line-through ml-1">{formatPrice(product.price)}</span>
                                </>
                            ) : (
                                <span className="text-sm font-bold text-gray-700">{formatPrice(product.price)}</span>
                            )}
                        </div>
                    </div>
                </div>
                <div className="bg-gray-50 p-2 flex justify-between border-t">
                    <a
                        href={`/product/${product.id}`}
                        target="_blank"
                        rel="noopener noreferrer"
                        className="text-xs text-blue-600 hover:text-blue-800 flex items-center"
                    >
                        <FaExternalLinkAlt className="mr-1" size={10} />
                        Xem chi tiết
                    </a>
                    <a
                        href={`/cart/add/${product.id}`}
                        className="text-xs text-green-600 hover:text-green-800 flex items-center"
                    >
                        <FaShoppingCart className="mr-1" size={10} />
                        Thêm vào giỏ
                    </a>
                </div>
            </div>
        );
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

                                        {/* Hiển thị sản phẩm nếu có */}
                                        {msg.products && msg.products.length > 0 && (
                                            <div className="mt-3 pt-3 border-t border-gray-300">
                                                <p className="text-xs font-medium mb-2">Sản phẩm gợi ý cho bạn:</p>

                                                {/* Nếu có thông tin danh mục, hiển thị theo từng danh mục */}
                                                {msg.categories && msg.categories.length > 0 ? (
                                                    msg.categories.map((category) => {
                                                        // Lọc sản phẩm theo danh mục hiện tại
                                                        const categoryProducts = msg.products.filter(
                                                            (product) => product.category === category
                                                        );

                                                        if (categoryProducts.length === 0) return null;

                                                        return (
                                                            <div key={category} className="mb-3">
                                                                <h4 className="text-xs font-medium text-gray-700 bg-gray-100 p-1 rounded mb-2">
                                                                    {category}
                                                                </h4>
                                                                <div className="space-y-2">
                                                                    {categoryProducts.map((product) => (
                                                                        <ProductCard key={product.id} product={product} />
                                                                    ))}
                                                                </div>
                                                            </div>
                                                        );
                                                    })
                                                ) : (
                                                    // Nếu không có thông tin danh mục, hiển thị tất cả sản phẩm
                                                    <div className="space-y-2">
                                                        {msg.products.map((product) => (
                                                            <ProductCard key={product.id} product={product} />
                                                        ))}
                                                    </div>
                                                )}
                                            </div>
                                        )}

                                        {/* Hiển thị thông báo khi không tìm thấy sản phẩm */}
                                        {msg.isProductSearch && msg.products && msg.products.length === 0 && (
                                            <div className="mt-3 pt-3 border-t border-gray-300">
                                                <div className="bg-yellow-50 border-l-4 border-yellow-400 p-2 rounded">
                                                    <div className="flex">
                                                        <div className="flex-shrink-0">
                                                            <svg className="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                                <path fillRule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clipRule="evenodd" />
                                                            </svg>
                                                        </div>
                                                        <div className="ml-3">
                                                            <p className="text-xs text-yellow-700">
                                                                Không tìm thấy sản phẩm nào phù hợp với từ khóa "{msg.searchKeywords}".
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        )}

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