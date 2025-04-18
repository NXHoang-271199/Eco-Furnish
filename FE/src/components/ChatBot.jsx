import { useState, useRef, useEffect } from 'react';
// import axios from 'axios'; // Xóa import axios mặc định
import axiosInstance from '../utils/axiosConfig'; // Import axiosInstance đã cấu hình
import { cn } from "../lib/utils";
import { Button } from "./ui/button";
import { Avatar, AvatarFallback, AvatarImage } from "./ui/avatar";
import { Textarea } from "./ui/textarea";
import '../styles/chatbot.css'; // Import CSS mới
import { toast, Toaster } from 'react-hot-toast';
import VariantSelectionModal from './VariantSelectionModal';

// Icons import (sử dụng từ react-icons như cũ)
import {
  FaRobot,
  FaPaperPlane,
  FaTimes,
  FaTrash,
  FaShoppingCart,
  FaExternalLinkAlt,
  FaPaperclip,
  FaMicrophone
} from 'react-icons/fa';

// Animation Loading cho tin nhắn
const MessageLoading = () => {
  return (
    <div className="flex space-x-2 justify-center items-center h-8">
      <div className="w-2 h-2 rounded-full loading-dot"></div>
      <div className="w-2 h-2 rounded-full loading-dot"></div>
      <div className="w-2 h-2 rounded-full loading-dot"></div>
    </div>
  );
};

// Components cho Chat Bubble
const ChatBubble = ({
  variant = "received",
  className,
  children,
}) => {
  return (
    <div
      className={cn(
        "flex items-start gap-3 mb-4",
        variant === "sent" && "flex-row-reverse",
        className,
      )}
    >
      {children}
    </div>
  );
};

const ChatBubbleMessage = ({
  variant = "received",
  isLoading: initialIsLoading,
  className,
  children,
  products,
  isProductSearch,
  searchKeywords,
  categories,
  timestamp,
}) => {
  const formatPrice = (price) => {
    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(price);
  };

  // Hàm để theo dõi khi người dùng xem chi tiết sản phẩm
  const handleViewProduct = (product) => {
    window.trackProductView(product.id, product.name, product.category);
  };

  // State để quản lý modal chọn biến thể
  const [variantModalOpen, setVariantModalOpen] = useState(false);
  const [selectedProduct, setSelectedProduct] = useState(null);
  // Thêm state isLoading cho component này
  const [isLoadingState, setIsLoadingState] = useState(initialIsLoading || false);

  // Hàm theo dõi khi người dùng thêm sản phẩm vào giỏ hàng
  const handleAddToCart = async (event, product) => {
    event.preventDefault();
    console.log('Đang thêm vào giỏ:', product);

    // Kiểm tra xem sản phẩm có biến thể hay không
    if (product.has_variants) {
      // Đảm bảo dữ liệu đầy đủ trước khi mở modal
      try {
        // Luôn lấy dữ liệu sản phẩm đầy đủ từ API để đảm bảo có thông tin biến thể mới nhất
        setIsLoadingState(true);
        
        // Tạo một promise với timeout để tránh request treo quá lâu
        const fetchProductWithTimeout = async (productId, timeoutMs = 10000) => {
          return Promise.race([
            axiosInstance.get(`/products/${productId}`),
            new Promise((_, reject) => 
              setTimeout(() => reject(new Error('Request timeout')), timeoutMs)
            )
          ]);
        };
        
        const response = await fetchProductWithTimeout(product.id);
        
        if (response && response.data && response.data.data) {
          // Cập nhật sản phẩm với dữ liệu đầy đủ
          const fullProduct = {...response.data.data};
          console.log('Đã lấy thêm dữ liệu sản phẩm:', fullProduct);
          
          // Kiểm tra dữ liệu biến thể
          if (!fullProduct.variants || !Array.isArray(fullProduct.variants) || fullProduct.variants.length === 0) {
            setIsLoadingState(false);
            toast.error("Không thể tải thông tin biến thể sản phẩm");
            return;
          }
          
          // Đảm bảo rằng mỗi biến thể có thông tin variant_details đầy đủ và quantity là số
          let hasValidVariants = false;
          
          // Clone mảng variants để tránh lỗi với prop read-only
          fullProduct.variants = fullProduct.variants.map(variant => {
            // Tạo bản sao của variant
            const variantCopy = {...variant};
            
            // Đảm bảo quantity là số
            if (variantCopy.quantity === undefined || variantCopy.quantity === null) {
              variantCopy.quantity = 0;
            } else if (typeof variantCopy.quantity === 'string') {
              variantCopy.quantity = parseInt(variantCopy.quantity) || 0;
            }
            
            // Kiểm tra variant_details
            if (variantCopy.variant_details && Array.isArray(variantCopy.variant_details) && variantCopy.variant_details.length > 0) {
              hasValidVariants = true;
            }
            
            return variantCopy;
          });
          
          if (!hasValidVariants) {
            setIsLoadingState(false);
            toast.error("Thông tin biến thể sản phẩm không hợp lệ");
            return;
          }
          
          console.log('Opening variant modal with product:', fullProduct);
          setSelectedProduct(fullProduct);
          setVariantModalOpen(true);
        } else {
          toast.error("Không thể tải thông tin sản phẩm");
        }
        setIsLoadingState(false);
      } catch (error) {
        console.error('Lỗi khi lấy thông tin sản phẩm:', error);
        
        // Hiển thị thông báo lỗi cụ thể hơn
        let errorMessage = 'Không thể tải thông tin sản phẩm. Vui lòng thử lại sau.';
        
        if (error.message === 'Request timeout') {
          errorMessage = 'Kết nối tới máy chủ quá lâu. Vui lòng kiểm tra kết nối và thử lại.';
        } else if (error.response) {
          if (error.response.status === 404) {
            errorMessage = 'Không tìm thấy thông tin sản phẩm.';
          } else if (error.response.status === 401) {
            errorMessage = 'Phiên đăng nhập đã hết hạn. Vui lòng đăng nhập lại.';
          }
        }
        
        toast.error(errorMessage);
        setIsLoadingState(false);
      }
      return;
    }

    try {
      // Gọi API để thêm vào giỏ hàng sử dụng axiosInstance
      const response = await axiosInstance.post('/cart/add', {
        product_id: product.id,
        quantity: 1, // Mặc định số lượng là 1
      }, {
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        }
      });

      console.log('API add to cart response:', response.data);
      toast.success('Đã thêm sản phẩm vào giỏ hàng!');

    } catch (error) {
      console.error('Lỗi khi thêm vào giỏ hàng:', error.response ? error.response.data : error.message);
      toast.error(`Lỗi: ${error.response?.data?.message || error.message}`);
    }

    // Vẫn theo dõi hành động
    window.trackAddToCart(product.id, product.name, product.category);
  };

  // Xử lý khi thêm thành công từ modal
  const handleVariantAddToCart = (product, variantId, quantity) => {
    // Theo dõi hành động
    window.trackAddToCart(product.id, product.name, product.category);
  };

  // Product Card component (nâng cấp giao diện)
  const ProductCard = ({ product }) => {
    return (
      <div className="border rounded-lg overflow-hidden mb-2 bg-white shadow-sm product-card">
        <div className="flex">
          <div className="w-20 h-20 flex-shrink-0 bg-gray-100 flex items-center justify-center overflow-hidden">
            {product.image || product.image_thumnail ? (
              <img
                src={`/storage/${product.image || product.image_thumnail}`}
                alt={product.name}
                className="w-full h-full object-cover product-image"
                onError={(e) => {
                  console.log("Lỗi tải ảnh:", e.target.src);
                  e.target.onerror = null;
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
            href={`/product-detail/${product.id}`}
            target="_blank"
            rel="noopener noreferrer"
            className="text-xs text-blue-600 hover:text-blue-800 flex items-center transition-all duration-200 hover:translate-x-1"
            onClick={() => handleViewProduct(product)}
          >
            <FaExternalLinkAlt className="mr-1" size={10} />
            Xem chi tiết
          </a>
          <a
            href="#"
            onClick={(e) => handleAddToCart(e, product)}
            className="text-xs text-green-600 hover:text-green-800 flex items-center transition-all duration-200 hover:translate-y-[-2px]"
          >
            <FaShoppingCart className="mr-1" size={10} />
            Thêm vào giỏ
          </a>
        </div>
      </div>
    );
  };

  return (
    <>
      <div
        className={cn(
          "rounded-2xl p-3 max-w-[85%] shadow-sm message-bubble",
          variant === "sent"
            ? "user-bubble"
            : "bot-bubble",
          className
        )}
      >
        {isLoadingState ? (
          <MessageLoading />
        ) : (
          <div>
            <div className="text-sm whitespace-pre-wrap">{children}</div>

            {/* Hiển thị sản phẩm nếu có */}
            {products && products.length > 0 && (
              <div className="mt-3 pt-3 border-t border-gray-300">
                <p className="text-xs font-medium mb-2">👇Sản phẩm gợi ý cho bạn👇:</p>

                {/* Nếu có thông tin danh mục, hiển thị theo từng danh mục */}
                {categories && categories.length > 0 ? (
                  categories.map((category) => {
                    // Lọc sản phẩm theo danh mục hiện tại
                    const categoryProducts = products.filter(
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
                    {products.map((product) => (
                      <ProductCard key={product.id} product={product} />
                    ))}
                  </div>
                )}
              </div>
            )}

            {/* Hiển thị thông báo khi không tìm thấy sản phẩm */}
            {isProductSearch && products && products.length === 0 && (
              <div className="mt-3 pt-3 border-t border-gray-300">
                <div className="bg-yellow-50 p-2 rounded search-notification">
                  <div className="flex">
                    <div className="flex-shrink-0">
                      <svg className="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fillRule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clipRule="evenodd" />
                      </svg>
                    </div>
                    <div className="ml-3">
                      <p className="text-xs text-yellow-700">
                        Không tìm thấy sản phẩm nào phù hợp với từ khóa "{searchKeywords}".
                      </p>
                    </div>
                  </div>
                </div>
              </div>
            )}

            {timestamp && (
              <span className="text-xs opacity-70 block mt-1 message-timestamp">
                {new Date(timestamp).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}
              </span>
            )}
          </div>
        )}
      </div>

      {/* Modal chọn biến thể */}
      <VariantSelectionModal
        isOpen={variantModalOpen}
        onClose={() => setVariantModalOpen(false)}
        product={selectedProduct}
        onAddToCart={handleVariantAddToCart}
      />
    </>
  );
};

const ChatBubbleAvatar = ({
  src,
  fallback = "AI",
  className,
  isUser = false,
}) => {
  return (
    <Avatar className={cn("h-9 w-9", isUser ? "avatar-user" : "avatar-bot", className)}>
      {src && <AvatarImage src={src} alt={fallback} />}
      <AvatarFallback className={cn("text-white text-xs font-medium",
        isUser ? "bg-gradient-to-br from-purple-400 to-purple-600" : "bg-gradient-to-br from-blue-400 to-blue-600")}>
        {fallback}
      </AvatarFallback>
    </Avatar>
  );
};

const ChatBot = () => {
  const [isOpen, setIsOpen] = useState(false);
  const [messages, setMessages] = useState([]);
  const [input, setInput] = useState('');
  const [isLoading, setIsLoading] = useState(false);
  const messagesEndRef = useRef(null);
  const [welcomeMessageSent, setWelcomeMessageSent] = useState(false);

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

  // Theo dõi hoạt động của người dùng và lưu trữ vào localStorage
  const trackUserActivity = (activity) => {
    try {
      // Lấy dữ liệu hoạt động hiện có
      const storedActivities = localStorage.getItem('userActivities') || '[]';
      const activities = JSON.parse(storedActivities);

      // Thêm hoạt động mới và giới hạn số lượng hoạt động lưu trữ
      const newActivities = [activity, ...activities].slice(0, 30);

      // Lưu lại vào localStorage
      localStorage.setItem('userActivities', JSON.stringify(newActivities));

      // Chia sẻ dữ liệu này với các component khác thông qua localStorage event
      const event = new CustomEvent('userActivityUpdate', {
        detail: { activity, activities: newActivities }
      });
      window.dispatchEvent(event);
    } catch (error) {
      console.error('Error tracking user activity:', error);
    }
  };

  // Phương thức công khai để các component khác có thể sử dụng
  useEffect(() => {
    // Đính kèm phương thức vào window để các component khác có thể gọi
    window.trackProductView = (productId, productName, category) => {
      trackUserActivity({
        type: 'view_product',
        productId,
        productName,
        category,
        timestamp: new Date().toISOString()
      });
    };

    window.trackAddToCart = (productId, productName, category) => {
      trackUserActivity({
        type: 'add_to_cart',
        productId,
        productName,
        category,
        timestamp: new Date().toISOString()
      });
    };

    window.trackProductSearch = (keyword) => {
      trackUserActivity({
        type: 'search_product',
        keyword,
        timestamp: new Date().toISOString()
      });
    };

    // Cleanup function
    return () => {
      delete window.trackProductView;
      delete window.trackAddToCart;
      delete window.trackProductSearch;
    };
  }, []);

  // Tự động gửi tin nhắn chào khi mở chatbot
  useEffect(() => {
    if (isOpen && !welcomeMessageSent && messages.length === 0) {
      const welcomeMessage = {
        text: 'Xin chào! Tôi là trợ lý AI của Eco-Furnish. Tôi có thể giúp bạn tìm kiếm sản phẩm, cung cấp thông tin về chất liệu, hoặc gợi ý sản phẩm phù hợp với nhu cầu của bạn. Bạn cần hỗ trợ gì không?',
        sender: 'bot',
        timestamp: new Date().toISOString()
      };
      setMessages([welcomeMessage]);
      setWelcomeMessageSent(true);
    }
  }, [isOpen, welcomeMessageSent, messages.length]);

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
    setWelcomeMessageSent(false);
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

      // Lấy hoạt động gần đây của người dùng để gửi làm context
      const userActivities = localStorage.getItem('userActivities') || '{}';
      const activities = JSON.parse(userActivities);

      // Sử dụng đường dẫn tương đối để tận dụng proxy trong Vite
      const response = await axiosInstance.post('/chat', {
        message: messageToSend,
        userActivities: activities // Gửi toàn bộ đối tượng activities
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

          // Theo dõi hoạt động tìm kiếm sản phẩm
          window.trackProductSearch(response.data.search_keywords);
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

  return (
    <div className="fixed bottom-5 right-5 z-50">
      <Toaster position="top-right" />
      {/* Chat Button */}
      <Button
        onClick={toggleChat}
        className={cn(
          "w-14 h-14 rounded-full shadow-lg flex items-center justify-center bot-button",
          "hover:shadow-blue-300/30 transition-all duration-200 ripple-effect"
        )}
        aria-label="Chat với trợ lý AI"
      >
        {isOpen ? <FaTimes size={20} /> : <FaRobot size={20} />}
      </Button>

      {/* Chat Window */}
      {isOpen && (
        <div className={cn(
          "absolute bottom-16 right-0 w-80 sm:w-96 bg-white rounded-xl shadow-2xl",
          "flex flex-col overflow-hidden border border-gray-200 chatbot-container chatbot-animation"
        )}>
          {/* Chat Header */}
          <div className="chatbot-header text-white p-4 flex items-center">
            <ChatBubbleAvatar fallback="AI" className="mr-3" />
            <div>
              <h3 className="font-medium">Trợ lý AI</h3>
              <p className="text-xs text-blue-100">Eco-Furnish</p>
            </div>
            <div className="ml-auto flex items-center gap-2">
              {messages.length > 0 && (
                <Button
                  onClick={clearChat}
                  size="icon"
                  variant="ghost"
                  className="text-white hover:text-white hover:bg-blue-600/50 h-8 w-8 ripple-effect"
                  aria-label="Xóa lịch sử chat"
                  title="Xóa lịch sử chat"
                >
                  <FaTrash size={14} />
                </Button>
              )}
              <Button
                onClick={toggleChat}
                size="icon"
                variant="ghost"
                className="text-white hover:text-white hover:bg-blue-600/50 h-8 w-8 ripple-effect"
                aria-label="Đóng chat"
              >
                <FaTimes size={14} />
              </Button>
            </div>
          </div>

          {/* Chat Messages */}
          <div className="flex-1 p-3 overflow-y-auto max-h-96 message-container chatbot-messages custom-scrollbar smooth-scroll">
            {messages.length === 0 ? (
              <div className="text-center text-gray-500 py-8 empty-state pulse-effect">
                <div className="w-16 h-16 mx-auto mb-3 rounded-full bg-blue-50 flex items-center justify-center">
                  <FaRobot className="text-blue-500" size={24} />
                </div>
                <p className="font-medium">Xin chào! Tôi có thể giúp gì cho bạn?</p>
                <p className="text-sm mt-2">Hãy đặt câu hỏi về sản phẩm, dịch vụ hoặc bất kỳ thông tin nào bạn cần.</p>
              </div>
            ) : (
              messages.map((msg, index) => (
                <ChatBubble
                  key={index}
                  variant={msg.sender === 'user' ? 'sent' : 'received'}
                >
                  <ChatBubbleAvatar
                    fallback={msg.sender === 'user' ? 'Bạn' : 'AI'}
                    isUser={msg.sender === 'user'}
                  />
                  <ChatBubbleMessage
                    variant={msg.sender === 'user' ? 'sent' : 'received'}
                    products={msg.products}
                    isProductSearch={msg.isProductSearch}
                    searchKeywords={msg.searchKeywords}
                    categories={msg.categories}
                    timestamp={msg.timestamp}
                  >
                    {msg.text || 'Không có nội dung'}
                  </ChatBubbleMessage>
                </ChatBubble>
              ))
            )}
            {isLoading && (
              <ChatBubble variant="received">
                <ChatBubbleAvatar fallback="AI" />
                <ChatBubbleMessage variant="received" isLoading />
              </ChatBubble>
            )}
            <div ref={messagesEndRef} />
          </div>

          {/* Chat Input */}
          <form onSubmit={sendMessage} className="border-t border-gray-200 p-3">
            <div className="relative rounded-xl overflow-hidden flex input-container">
              <Textarea
                value={input}
                onChange={handleInputChange}
                onKeyPress={handleKeyPress}
                placeholder="Nhập tin nhắn..."
                className="min-h-[50px] max-h-24 py-3 pl-3 pr-10 flex-1 resize-none border-0 focus-visible:ring-0 text-sm"
                disabled={isLoading}
              />

              <div className="flex items-center absolute right-2 bottom-1.5">
                <Button
                  type="submit"
                  size="icon"
                  className={cn(
                    "rounded-full ml-1 h-9 w-9 text-white transition-colors send-button glow-effect",
                    (isLoading || input.trim() === '') && "opacity-50 cursor-not-allowed"
                  )}
                  disabled={isLoading || input.trim() === ''}
                >
                  <FaPaperPlane size={14} />
                </Button>
              </div>
            </div>
          </form>
        </div>
      )}
    </div>
  );
};

export default ChatBot;