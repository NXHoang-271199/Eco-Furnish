import React, { useState, useEffect, useMemo } from "react";
import { FiSearch, FiMessageCircle, FiEye, FiInfo } from "react-icons/fi";
import { Link, useNavigate } from "react-router-dom";
import axiosInstance from "../../../../utils/axiosConfig";
// import axios from "axios"; // Xóa import không cần thiết

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
    "Hoàn Hàng": "Hoàn hàng"
  };

  // Ánh xạ tab sang giá trị trạng thái API
  const tabToStatusMap = {
    all: "all",
    "pending": "Chưa Xác Nhận",
    "confirmed": "Đã Xác Nhận",
    "preparing": "Đang Chuẩn Bị Hàng",
    "shipping": "Đang Giao",
    "delivered": "Đã Giao",
    "completed": "Đã Nhận",
    "cancelled": "Hủy Đơn",
    "returned": "Hoàn Hàng"
  };

  // Danh sách các tab
  const tabs = [
    { id: "all", name: "Tất cả" },
    { id: "pending", name: "Chờ xác nhận" },
    { id: "shipping", name: "Đang vận chuyển" },
    { id: "delivered", name: "Đã giao" },
    { id: "completed", name: "Hoàn thành" },
    { id: "cancelled", name: "Đã hủy" },
    { id: "returned", name: "Hoàn hàng" },
  ];

  // Hàm lấy tên trạng thái hiển thị từ trạng thái API
  const getStatusName = (statusValue) => {
    return statusMap[statusValue] || "Không xác định";
  };

  // Thêm hàm format tiền tệ
  const formatCurrency = (amount) => {
    let numericAmount = amount;
    // Cố gắng chuyển đổi nếu là chuỗi số
    if (typeof amount === 'string') {
      numericAmount = parseFloat(amount.replace(/[^\d.-]/g, '')); // Loại bỏ ký tự không phải số trước khi parse
    }

    if (typeof numericAmount !== 'number' || isNaN(numericAmount)) {
      console.warn("formatCurrency received invalid amount:", amount); // Log giá trị không hợp lệ
      return '0 ₫';
    }
    return new Intl.NumberFormat('vi-VN', {
      style: 'currency',
      currency: 'VND',
      minimumFractionDigits: 0,
      maximumFractionDigits: 0
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
        // console.log(error); // Xóa console log không cần thiết

        const response = await axiosInstance.get(`/orders`, {
          withCredentials: true,
        });
        // console.log("Dữ liệu đơn hàng:", response.data);

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
          console.error("API trả về lỗi hoặc định dạng không mong đợi:", response.data);
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
        activeTab === "all" ||
        order.order_status === selectedStatus;

      // Lọc theo từ khóa tìm kiếm (Mã đơn hàng hoặc Tên sản phẩm)
      const searchTerm = searchQuery.toLowerCase();
      const searchMatch =
        searchQuery === "" ||
        order.order_code.toLowerCase().includes(searchTerm) ||
        (order.order_items && order.order_items.some(item =>
          item.product?.name?.toLowerCase().includes(searchTerm)
        ));

      return statusMatch && searchMatch;
    });
  }, [orders, activeTab, searchQuery]);

  // Hàm xác định màu dựa trên trạng thái
  const getStatusColor = (status) => {
    switch (status) {
      case "Chưa Xác Nhận":
        return "bg-yellow-200 text-yellow-800";
      case "Đã Xác Nhận":
        return "bg-blue-200 text-blue-800";
      case "Đang Chuẩn Bị Hàng":
        return "bg-indigo-200 text-indigo-800";
      case "Đang Giao":
        return "bg-purple-200 text-purple-800";
      case "Đã Giao":
        return "bg-teal-200 text-teal-800";
      case "Đã Nhận":
        return "bg-green-200 text-green-800";
      case "Hủy Đơn":
        return "bg-red-200 text-red-800";
      case "Hoàn Hàng":
        return "bg-gray-200 text-gray-800";
      default:
        return "bg-gray-200 text-gray-800";
    }
  };

  return (
    // <div className="bg-gray-100 min-h-screen px-4">
    <div className="max-w-6xl mx-auto bg-white rounded-md shadow-sm">
      {/* <!-- Tab Navigation --> */}
      <div className="flex border-b overflow-x-auto no-scrollbar">
        {tabs.map((tab) => (
          <button
            key={tab.id}
            onClick={() => setActiveTab(tab.id)}
            className={`px-4 py-3 whitespace-nowrap font-medium ${activeTab === tab.id
              ? "text-orange-500 border-b-2 border-orange-500"
              : "text-gray-600"
              }`}
          >
            {tab.name}
          </button>
        ))}
      </div>

      {/* <!-- Search Bar --> */}
      <div className="p-4 border-b">
        <div className="relative">
          <input
            type="text"
            placeholder="Tìm theo Mã đơn hàng hoặc Tên sản phẩm"
            value={searchQuery}
            onChange={(e) => setSearchQuery(e.target.value)}
            className="w-full pl-10 pr-4 py-2 border rounded-md focus:outline-none focus:ring-1 focus:ring-orange-500"
          />
          <FiSearch className="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400" />
        </div>
      </div>

      {/* <!-- Order List --> */}
      <div className="divide-y">
        {loading ? (
          <p className="p-4 text-center">Đang tải...</p>
        ) : error ? (
          <p className="p-4 text-center text-red-500">{error}</p>
        ) : orders.length === 0 ? (
          <p className="p-4 text-center">Bạn chưa có đơn hàng nào</p>
        ) : filteredOrders.length === 0 ? (
          <p className="p-4 text-center">Không có đơn hàng nào phù hợp.</p>
        ) : (
          filteredOrders.map((order) => (
            <div key={order.id} className="p-4 border-b last:border-b-0">
              <div className="flex justify-between items-start mb-3">
                <div>
                  <span className="font-medium block mb-1">
                    Mã đơn hàng: {order.order_code}
                  </span>
                  {order.address && (
                    <p className="text-sm text-gray-600 mb-1">
                      Địa chỉ: {order.address?.address_line}, {order.address?.ward}, {order.address?.district}, {order.address?.province}
                    </p>
                  )}
                  {order.created_at && (
                    <p className="text-sm text-gray-500">
                      Ngày đặt: {new Date(order.created_at).toLocaleDateString('vi-VN')}
                    </p>
                  )}
                </div>
                <div className="text-right">
                  <span className={`inline-block px-2 py-1 text-xs rounded-full mb-2 ${getStatusColor(order.order_status)}`}>
                    {getStatusName(order.order_status)}
                  </span>
                  <Link
                    to={`/account/order_detail/${order.id}`}
                    className="flex items-center justify-end bg-orange-500 text-white px-3 py-1 rounded-sm text-sm hover:bg-orange-600"
                  >
                    <FiEye className="mr-1" /> Chi tiết
                  </Link>
                </div>
              </div>

              {/* Product List */}
              {order.order_items && order.order_items.length > 0 && (
                <div className="mb-3">
                  <h4 className="text-sm font-medium mb-1 text-gray-700">Sản phẩm:</h4>
                  <ul className="divide-y divide-gray-200 border rounded-md">
                    {order.order_items.map((item) => (
                      <li key={item.id} className="flex items-center justify-between p-2">
                        <div className="flex items-center">
                          <div>
                            <span className="text-sm font-medium">{item.product?.name || 'Tên sản phẩm'}</span>
                            <span className="block text-xs text-gray-500">SL: {item.quantity}</span>
                          </div>
                        </div>
                        <span className="text-sm font-medium text-orange-600">
                          {/* Log item nếu giá không hợp lệ */}
                          {typeof item.price !== 'number' && typeof item.price !== 'string' && console.log('Invalid item price data:', item)}
                          {formatCurrency(item.price)}
                        </span>
                      </li>
                    ))}
                  </ul>
                </div>
              )}

              {/* Total Amount */}
              <div className="text-right font-semibold text-lg">
                Tổng tiền: {formatCurrency(order.total_amount || calculateOrderTotal(order.order_items))}
              </div>
            </div>
          ))
        )}
      </div>
    </div>
    // </div>
  );
};

export default OrderHistory;
