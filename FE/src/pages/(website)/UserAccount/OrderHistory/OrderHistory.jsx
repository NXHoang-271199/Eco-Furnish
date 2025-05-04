import { useState, useEffect, useMemo } from "react";
import {
  FiSearch,
  FiEye,
  FiInfo,
  FiPackage,
  FiClock,
  FiCalendar,
  FiMapPin,
} from "react-icons/fi";
import { Link, useNavigate } from "react-router-dom";
import axiosInstance from "../../../../utils/axiosConfig";
import { motion, AnimatePresence } from "framer-motion";

const OrderHistory = () => {
  const [activeTab, setActiveTab] = useState("all");
  const [searchQuery, setSearchQuery] = useState("");
  const [orders, setOrders] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const navigate = useNavigate();

  // Ánh xạ trạng thái đơn hàng - Cập nhật theo định dạng chuỗi từ API
  const statusMap = {
    all: "Tất cả",
    "Chưa Xác Nhận": "Chờ xác nhận",
    "Đã Xác Nhận": "Đã xác nhận",
    "Đang Chuẩn Bị Hàng": "Đang chuẩn bị hàng",
    "Đang Giao": "Đang vận chuyển",
    "Đã Giao": "Đã giao",
    "Đã Nhận": "Hoàn thành",
    "Hủy Đơn": "Đã hủy",
    "Hoàn Hàng": "Hoàn hàng",
  };

  // Ánh xạ tab sang giá trị trạng thái API
  const tabToStatusMap = {
    all: "all",
    pending: "Chưa Xác Nhận",
    confirmed: "Đã Xác Nhận",
    preparing: "Đang Chuẩn Bị Hàng",
    shipping: "Đang Giao",
    delivered: "Đã Giao",
    completed: "Đã Nhận",
    cancelled: "Hủy Đơn",
    returned: "Hoàn Hàng",
  };

  // Danh sách các tab
  const tabs = [
    { id: "all", name: "Tất cả", icon: <FiPackage /> },
    { id: "pending", name: "Chờ xác nhận", icon: <FiClock /> },
    { id: "shipping", name: "Đang vận chuyển", icon: <FiPackage /> },
    { id: "delivered", name: "Đã giao", icon: <FiPackage /> },
    { id: "completed", name: "Hoàn thành", icon: <FiPackage /> },
    { id: "cancelled", name: "Đã hủy", icon: <FiPackage /> },
    { id: "returned", name: "Hoàn hàng", icon: <FiPackage /> },
  ];

  // Hàm lấy tên trạng thái hiển thị từ trạng thái API
  const getStatusName = (statusValue) => {
    return statusMap[statusValue] || "Không xác định";
  };

  // Thêm hàm format tiền tệ
  const formatCurrency = (amount) => {
    let numericAmount = amount;
    // Cố gắng chuyển đổi nếu là chuỗi số
    if (typeof amount === "string") {
      numericAmount = Number.parseFloat(amount.replace(/[^\d.-]/g, "")); // Loại bỏ ký tự không phải số trước khi parse
    }

    if (typeof numericAmount !== "number" || isNaN(numericAmount)) {
      console.warn("formatCurrency received invalid amount:", amount); // Log giá trị không hợp lệ
      return "0 ₫";
    }
    return new Intl.NumberFormat("vi-VN", {
      style: "currency",
      currency: "VND",
      minimumFractionDigits: 0,
      maximumFractionDigits: 0,
    }).format(numericAmount);
  };

  // Hàm tính tổng tiền đơn hàng
  const calculateOrderTotal = (orderItems) => {
    if (!Array.isArray(orderItems)) return 0;
    return orderItems.reduce((total, item) => {
      const itemTotal = (item.price || 0) * (item.quantity || 0);
      return total + itemTotal;
    }, 0);
  };

  // Lấy dữ liệu đơn hàng từ API
  useEffect(() => {
    const fetchOrders = async () => {
      try {
        setLoading(true);
        const token = localStorage.getItem("authToken");
        const refreshToken = localStorage.getItem("refreshToken");
        const userData = localStorage.getItem("userData");

        console.log("Token hiện tại:", token);
        console.log("Refresh token hiện tại:", refreshToken);
        console.log("userData", userData);

        if (!token || !userData) {
          // navigate("/sign-in", {
          //   state: {
          //     from: "/account/list_order",
          //     message: "Vui lòng đăng nhập để xem đơn hàng",
          //   },
          // });

          return;
        }

        const response = await axiosInstance.get(`/orders`, {
          withCredentials: true,
        });

        // Kiểm tra phản hồi thành công và có dữ liệu hợp lệ
        if (response.data?.status === "success" && response.data?.data?.data) {
          setOrders(response.data.data.data); // Dữ liệu đơn hàng tồn tại
          setError(null); // Xóa lỗi nếu tải thành công
        } else if (response.data?.status === "success") {
          // Phản hồi thành công nhưng không có đơn hàng
          setOrders([]); // Đặt danh sách rỗng
          setError(null); // Không có lỗi
        } else {
          // Các trường hợp lỗi khác từ API (status không phải success hoặc cấu trúc không đúng)
          console.error(
            "API trả về lỗi hoặc định dạng không mong đợi:",
            response.data
          );
          setError("Không thể tải dữ liệu đơn hàng. Vui lòng thử lại sau."); // Thông báo lỗi chung
        }
      } catch (error) {
        console.error("Lỗi khi lấy đơn hàng:", error);

        // Xử lý lỗi cụ thể (ví dụ: hết hạn token)
        if (
          error.message === "Phiên đăng nhập đã hết hạn, vui lòng đăng nhập lại"
        ) {
          setError(error.message);
          console.log("Lỗi:", error.message);
          // Đợi 2 giây rồi chuyển hướng
          setTimeout(() => {
            // navigate("/sign-in");
          }, 2000);
        } else if (error.response) {
          // Xử lý lỗi từ phản hồi của server (ví dụ: 4xx, 5xx)
          console.error("Lỗi phản hồi từ server:", error.response.data);
          setError(`Lỗi ${error.response.status}: Không thể tải dữ liệu.`);
        } else if (error.request) {
          // Lỗi không nhận được phản hồi
          console.error("Không nhận được phản hồi:", error.request);
          setError("Không thể kết nối đến máy chủ. Vui lòng kiểm tra mạng.");
        } else {
          // Lỗi khác khi thiết lập request
          console.error("Lỗi thiết lập request:", error.message);
          setError("Đã xảy ra lỗi không mong muốn. Vui lòng thử lại sau.");
        }
      } finally {
        setLoading(false);
      }
    };

    fetchOrders();
  }, [navigate]);

  // Lọc và tìm kiếm đơn hàng
  const filteredOrders = useMemo(() => {
    return orders.filter((order) => {
      // Lọc theo tab trạng thái
      const selectedStatus = tabToStatusMap[activeTab];
      const statusMatch =
        activeTab === "all" || order.order_status === selectedStatus;

      // Lọc theo từ khóa tìm kiếm (Mã đơn hàng hoặc Tên sản phẩm)
      const searchTerm = searchQuery.toLowerCase();
      const searchMatch =
        searchQuery === "" ||
        order.order_code.toLowerCase().includes(searchTerm) ||
        (order.order_items &&
          order.order_items.some((item) =>
            item.product?.name?.toLowerCase().includes(searchTerm)
          ));

      return statusMatch && searchMatch;
    });
  }, [orders, activeTab, searchQuery]);

  // Hàm xác định màu dựa trên trạng thái
  const getStatusColor = (status) => {
    switch (status) {
      case "Chưa Xác Nhận":
        return "bg-amber-100 text-amber-800 border border-amber-300";
      case "Đã Xác Nhận":
        return "bg-sky-100 text-sky-800 border border-sky-300";
      case "Đang Chuẩn Bị Hàng":
        return "bg-indigo-100 text-indigo-800 border border-indigo-300";
      case "Đang Giao":
        return "bg-violet-100 text-violet-800 border border-violet-300";
      case "Đã Giao":
        return "bg-teal-100 text-teal-800 border border-teal-300";
      case "Đã Nhận":
        return "bg-emerald-100 text-emerald-800 border border-emerald-300";
      case "Hủy Đơn":
        return "bg-rose-100 text-rose-800 border border-rose-300";
      case "Hoàn Hàng":
        return "bg-slate-100 text-slate-800 border border-slate-300";
      default:
        return "bg-slate-100 text-slate-800 border border-slate-300";
    }
  };

  // Animation variants
  const containerVariants = {
    hidden: { opacity: 0 },
    visible: {
      opacity: 1,
      transition: {
        staggerChildren: 0.1,
      },
    },
  };

  const itemVariants = {
    hidden: { y: 20, opacity: 0 },
    visible: {
      y: 0,
      opacity: 1,
      transition: {
        type: "spring",
        stiffness: 100,
        damping: 12,
      },
    },
  };

  return (
    <div className="max-w-6xl mx-auto bg-white rounded-lg shadow-md overflow-hidden">
      {/* Header */}
      <div className="bg-gradient-to-r from-orange-500 to-orange-600 p-6 text-white">
        <h1 className="text-2xl font-bold">Lịch sử đơn hàng</h1>
        <p className="text-orange-100">
          Quản lý và theo dõi tất cả đơn hàng của bạn
        </p>
      </div>

      {/* Tab Navigation */}
      <div className="flex border-b overflow-x-auto no-scrollbar bg-white sticky top-0 z-10 shadow-sm">
        {tabs.map((tab) => (
          <button
            key={tab.id}
            onClick={() => setActiveTab(tab.id)}
            className={`px-4 py-4 whitespace-nowrap font-medium flex items-center transition-all duration-200 relative ${
              activeTab === tab.id
                ? "text-orange-500"
                : "text-gray-600 hover:text-orange-400 hover:bg-orange-50"
            }`}
          >
            <span className="mr-2">{tab.icon}</span>
            {tab.name}
            {activeTab === tab.id && (
              <motion.div
                className="absolute bottom-0 left-0 right-0 h-0.5 bg-orange-500"
                layoutId="activeTab"
                initial={{ opacity: 0 }}
                animate={{ opacity: 1 }}
                transition={{ duration: 0.3 }}
              />
            )}
          </button>
        ))}
      </div>

      {/* Search Bar */}
      <div className="p-4 border-b bg-gray-50">
        <div className="relative max-w-2xl mx-auto">
          <input
            type="text"
            placeholder="Tìm theo Mã đơn hàng hoặc Tên sản phẩm"
            value={searchQuery}
            onChange={(e) => setSearchQuery(e.target.value)}
            className="w-full pl-12 pr-4 py-3 border rounded-full focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent shadow-sm transition-all duration-200"
          />
          <div className="absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400 bg-white rounded-full p-1">
            <FiSearch className="text-xl text-orange-500" />
          </div>
        </div>
      </div>

      {/* Order List */}
      <div className="divide-y bg-gray-50 min-h-[300px]">
        <AnimatePresence>
          {loading ? (
            <div className="flex items-center justify-center p-12">
              <div className="relative w-20 h-20">
                <div className="absolute top-0 left-0 w-full h-full border-4 border-gray-200 rounded-full"></div>
                <div className="absolute top-0 left-0 w-full h-full border-4 border-t-orange-500 rounded-full animate-spin"></div>
              </div>
              <p className="ml-4 text-lg text-gray-600">Đang tải đơn hàng...</p>
            </div>
          ) : error ? (
            <div className="p-8 text-center">
              <div className="inline-flex items-center justify-center w-16 h-16 rounded-full bg-red-100 text-red-500 mb-4">
                <FiInfo className="w-8 h-8" />
              </div>
              <p className="text-red-500 text-lg font-medium">{error}</p>
              <button
                onClick={() => window.location.reload()}
                className="mt-4 px-4 py-2 bg-orange-500 text-white rounded-md hover:bg-orange-600 transition-colors"
              >
                Thử lại
              </button>
            </div>
          ) : orders.length === 0 ? (
            <div className="p-12 text-center">
              <div className="inline-flex items-center justify-center w-20 h-20 rounded-full bg-orange-100 text-orange-500 mb-4">
                <FiPackage className="w-10 h-10" />
              </div>
              <p className="text-gray-600 text-lg">Bạn chưa có đơn hàng nào</p>
              <Link
                to="/products"
                className="mt-4 inline-block px-6 py-2 bg-orange-500 text-white rounded-md hover:bg-orange-600 transition-colors"
              >
                Mua sắm ngay
              </Link>
            </div>
          ) : filteredOrders.length === 0 ? (
            <div className="p-12 text-center">
              <div className="inline-flex items-center justify-center w-20 h-20 rounded-full bg-orange-100 text-orange-500 mb-4">
                <FiSearch className="w-10 h-10" />
              </div>
              <p className="text-gray-600 text-lg">
                Không có đơn hàng nào phù hợp với tìm kiếm của bạn.
              </p>
              <button
                onClick={() => setSearchQuery("")}
                className="mt-4 px-6 py-2 bg-orange-500 text-white rounded-md hover:bg-orange-600 transition-colors"
              >
                Xóa tìm kiếm
              </button>
            </div>
          ) : (
            <motion.div
              variants={containerVariants}
              initial="hidden"
              animate="visible"
              className="p-4 grid gap-4 md:gap-6"
            >
              {filteredOrders.map((order) => (
                <motion.div
                  key={order.id}
                  variants={itemVariants}
                  className="bg-white rounded-lg shadow-sm overflow-hidden border border-gray-200 hover:shadow-md transition-shadow duration-200"
                >
                  {/* Order Header */}
                  <div className="p-4 bg-gradient-to-r from-gray-50 to-white border-b">
                    <div className="flex flex-wrap justify-between items-start gap-2">
                      <div>
                        <div className="flex items-center">
                          <span className="font-medium text-lg text-gray-800">
                            #{order.order_code}
                          </span>
                          <span
                            className={`ml-3 inline-flex items-center px-3 py-1 text-xs font-medium rounded-full ${getStatusColor(
                              order.order_status
                            )}`}
                          >
                            {getStatusName(order.order_status)}
                          </span>
                        </div>

                        {order.created_at && (
                          <div className="flex items-center text-sm text-gray-500 mt-1">
                            <FiCalendar className="mr-1 text-gray-400" />
                            {new Date(order.created_at).toLocaleDateString(
                              "vi-VN",
                              {
                                year: "numeric",
                                month: "long",
                                day: "numeric",
                                hour: "2-digit",
                                minute: "2-digit",
                              }
                            )}
                          </div>
                        )}
                      </div>

                      <Link
                        to={`/account/order_detail/${order.id}`}
                        className="flex items-center justify-center bg-orange-500 text-white px-4 py-2 rounded-md text-sm hover:bg-orange-600 transition-colors shadow-sm"
                      >
                        <FiEye className="mr-2" /> Xem chi tiết
                      </Link>
                    </div>

                    {order.address && (
                      <div className="mt-3 flex items-start text-sm text-gray-600">
                        <FiMapPin className="mr-2 mt-0.5 flex-shrink-0 text-gray-400" />
                        <span>
                          {order.address?.address_line}, {order.address?.ward},{" "}
                          {order.address?.district}, {order.address?.province}
                        </span>
                      </div>
                    )}
                  </div>

                  {/* Product List */}
                  {order.order_items && order.order_items.length > 0 && (
                    <div className="p-4">
                      <h4 className="text-sm font-medium mb-2 text-gray-700 flex items-center">
                        <FiPackage className="mr-2 text-orange-500" /> Sản phẩm
                      </h4>
                      <ul className="divide-y divide-gray-100 bg-gray-50 rounded-lg overflow-hidden">
                        {order.order_items.map((item, index) => (
                          <li
                            key={item.id}
                            className="flex items-center justify-between p-3 hover:bg-gray-100 transition-colors"
                          >
                            <div className="flex items-center">
                              {item.product?.image_thumnail ? (
                                <img
                                  src={
                                    item.product.image_thumnail.startsWith(
                                      "http"
                                    )
                                      ? item.product.image_thumnail
                                      : `http://localhost:8000/storage/${item.product.image_thumnail}`
                                  }
                                  alt={item.product?.name || "Sản phẩm"}
                                  className="w-12 h-12 object-cover rounded-md mr-3 border border-gray-200"
                                  onError={(e) => {
                                    e.target.src =
                                      "https://via.placeholder.com/100x100?text=No+Image";
                                  }}
                                />
                              ) : (
                                <div className="w-12 h-12 bg-gray-100 rounded-md flex items-center justify-center text-gray-400 mr-3 border border-gray-200">
                                  <FiPackage size={20} />
                                </div>
                              )}
                              <div>
                                <span className="text-sm font-medium text-gray-800">
                                  {item.product?.name || "Tên sản phẩm"}
                                </span>
                                <span className="block text-xs text-gray-500">
                                  Số lượng: {item.quantity}
                                </span>
                              </div>
                            </div>
                            <span className="text-sm font-medium text-orange-600">
                              {formatCurrency(item.total_price)}
                            </span>
                          </li>
                        ))}
                      </ul>
                    </div>
                  )}

                  {/* Total Amount */}
                  <div className="bg-gray-50 p-4 border-t">
                    <div className="flex justify-between items-center">
                      <span className="text-gray-600 font-medium">
                        Tổng tiền:
                      </span>
                      <span className="text-xl font-bold text-orange-600">
                        {formatCurrency(
                          order.total_price ||
                            calculateOrderTotal(order.order_items)
                        )}
                      </span>
                    </div>
                  </div>
                </motion.div>
              ))}
            </motion.div>
          )}
        </AnimatePresence>
      </div>
    </div>
  );
};

export default OrderHistory;
