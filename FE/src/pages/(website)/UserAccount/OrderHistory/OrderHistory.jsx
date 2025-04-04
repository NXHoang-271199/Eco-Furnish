import React, { useState, useEffect } from "react";
import { FiSearch, FiMessageCircle, FiEye, FiInfo } from "react-icons/fi";
import { Link, useNavigate } from "react-router-dom";
import axiosInstance from "../../../../utils/axiosConfig";
import axios from "axios";
const OrderHistory = () => {
  const [activeTab, setActiveTab] = useState("all");
  const [searchQuery, setSearchQuery] = useState("");
  const [orders, setOrders] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const navigate = useNavigate();

  const baseURL = "http://localhost:8000/api";
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
        console.log(error);

        const response = await axiosInstance.get(`/orders`, {
          withCredentials: true,
        });
        console.log(response.data);

        if (response.data.status === "success") {
          setOrders(response.data.data.data || []);
        }
      } catch (error) {
        console.error("Lỗi khi lấy đơn hàng:", error);

        if (
          error.message === "Phiên đăng nhập đã hết hạn, vui lòng đăng nhập lại"
        ) {
          setError(error.message);
          console.log("Lỗi:", error.message);
          // Đợi 2 giây rồi chuyển hướng
          setTimeout(() => {
            // navigate("/sign-in");
          }, 2000);
        } else {
          setError("Không thể tải dữ liệu đơn hàng. Vui lòng thử lại sau.");
        }
      } finally {
        setLoading(false);
      }
    };

    fetchOrders();
  }, [navigate]);

  return (
    <div class="bg-gray-100 min-h-screen py-6 px-4">
      <h1 class="text-2xl font-semibold mb-4">Đơn hàng</h1>
      <div class="max-w-6xl mx-auto bg-white rounded-md shadow-sm">
        {/* <!-- Tab Navigation --> */}
        <div class="flex border-b overflow-x-auto no-scrollbar">
          <button class="px-4 py-3 whitespace-nowrap font-medium text-orange-500 border-b-2 border-orange-500">
            Tất cả
          </button>
          <button class="px-4 py-3 whitespace-nowrap font-medium text-gray-600">
            Chờ xác nhận
          </button>
          <button class="px-4 py-3 whitespace-nowrap font-medium text-gray-600">
            Đang vận chuyển
          </button>
          <button class="px-4 py-3 whitespace-nowrap font-medium text-gray-600">
            Đã giao
          </button>
          <button class="px-4 py-3 whitespace-nowrap font-medium text-gray-600">
            Hoàn thành
          </button>
          <button class="px-4 py-3 whitespace-nowrap font-medium text-gray-600">
            Đã hủy
          </button>
          <button class="px-4 py-3 whitespace-nowrap font-medium text-gray-600">
            Hoàn hàng
          </button>
        </div>

        {/* <!-- Search Bar --> */}
        <div class="p-4 border-b">
          <div class="relative">
            <input
              type="text"
              placeholder="Bạn có thể tìm kiếm theo ID đơn hàng hoặc Tên Sản phẩm"
              class="w-full pl-10 pr-4 py-2 border rounded-md focus:outline-none focus:ring-1 focus:ring-orange-500"
            />
            <i
              data-feather="search"
              class="absolute left-3 top-3 text-gray-400"
            ></i>
          </div>
        </div>

        {/* <!-- Order List --> */}
        <div class="divide-y">
          {loading ? (
            <p>Đang tải...</p>
          ) : error ? (
            <p className="text-red-500">{error}</p>
          ) : orders.length === 0 ? (
            <p>Không có đơn hàng nào.</p>
          ) : (
            orders.map((order) => (
              <div key={order.id} className="p-4">
                <div className="flex justify-between items-center mb-3">
                  <span className="font-medium">
                    Mã đơn hàng: {order.order_code}
                  </span>
                  <Link
                    to={`/account/order_detail/${order.id}`}
                    className="flex items-center bg-orange-500 text-white px-3 py-1 rounded-sm text-sm"
                  >
                    <FiEye className="mr-1" /> Chi tiết
                  </Link>
                </div>
                {/* Thêm các thông tin khác từ order */}
              </div>
            ))
          )}
        </div>
      </div>
    </div>
  );
};

export default OrderHistory;
