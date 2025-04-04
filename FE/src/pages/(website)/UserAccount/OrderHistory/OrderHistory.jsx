import React, { useState, useEffect } from "react";
import { FiSearch, FiMessageCircle, FiEye, FiInfo } from "react-icons/fi";
import { Link, useNavigate } from "react-router-dom";
import axiosInstance from "../../../../utils/axiosConfig";

const OrderHistory = () => {
  const [activeTab, setActiveTab] = useState("all");
  const [searchQuery, setSearchQuery] = useState("");
  const [orders, setOrders] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const navigate = useNavigate();

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

        if (!token || !refreshToken || !userData) {
          navigate("/sign-in", {
            state: {
              from: "/account/list_order",
              message: "Vui lòng đăng nhập để xem đơn hàng",
            },
          });
          return;
        }

        const response = await axiosInstance.get("/orders");

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
          <div class="p-4">
            <div class="flex justify-between items-center mb-3">
              <div class="flex items-center">
                <span class="font-medium">Mã đơn hàng: DH123456</span>
              </div>
              <div class="flex space-x-2">
                <a
                  href="/account/orders/1"
                  class="flex items-center bg-orange-500 text-white px-3 py-1 rounded-sm text-sm"
                >
                  <i data-feather="eye" class="mr-1"></i> Chi tiết
                </a>
              </div>
            </div>

            <div class="flex justify-between border-b pb-3 mb-3">
              <div class="flex items-center text-gray-500">
                <i data-feather="info" class="mr-1"></i>
                <span>Trạng thái thanh toán: Đã thanh toán</span>
              </div>
              <div class="text-orange-500 font-medium">Đã giao</div>
            </div>

            {/* <!-- Products --> */}
            <div class="flex py-3">
              <div class="w-16 h-16 flex-shrink-0">
                <img
                  src="https://via.placeholder.com/64"
                  alt="Sản phẩm mẫu"
                  class="w-full h-full object-cover border"
                />
              </div>
              <div class="ml-3 flex-grow">
                <div class="text-sm line-clamp-2">
                  Tên sản phẩm mẫu rất dài để kiểm tra line-clamp
                </div>
                <div class="text-xs text-gray-500 mt-1">Màu: Đen, Size: M</div>
                <div class="text-xs text-gray-500 mt-1">Số lượng: x2</div>
              </div>
              <div class="ml-4 text-right">
                <div class="text-sm text-gray-500 line-through">₫500,000</div>
                <div class="text-sm">₫400,000</div>
              </div>
            </div>

            {/* <!-- Order Total --> */}
            <div class="flex justify-end items-center border-t pt-3">
              <div class="mr-3 text-sm text-gray-600">Giảm giá: ₫100,000</div>
              <div class="text-gray-600 mr-2">Thành tiền:</div>
              <div class="text-xl text-orange-500 font-medium">₫800,000</div>
            </div>

            {/* <!-- Action Buttons --> */}
            <div class="flex justify-end mt-4 space-x-2">
              <a
                href="/account/orders/1/confirm"
                class="px-4 py-2 bg-green-500 text-white rounded"
              >
                Xác Nhận Đã Nhận
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default OrderHistory;
