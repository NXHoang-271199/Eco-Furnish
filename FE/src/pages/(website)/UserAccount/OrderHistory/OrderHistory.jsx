import React, { useState } from "react";
import { FiSearch, FiMessageCircle, FiEye, FiInfo } from "react-icons/fi";

const OrderHistory = () => {
  const [activeTab, setActiveTab] = useState("all");
  const [searchQuery, setSearchQuery] = useState("");

  // Dữ liệu mẫu đơn hàng
  const orderData = [
    {
      id: "123456789",
      shop: {
        name: "Đồng Phục Y Tế, Spa Bạch Mai",
        isFavorite: true,
      },
      status: "completed",
      items: [
        {
          id: 1,
          name: "[Giá Xưởng] Áo Sơ Mi Trắng Đồng Phục Học Sinh Tiểu Học, Trung Học Cơ Sở, THPT, Áo Trắng Đi Học Chất Liệu Kate Lon Loại 1",
          image: "/images/product1.jpg",
          variant: "Tay Dài Không Túi,Số 9 (L): 51-57kg",
          price: 75000,
          originalPrice: 120000,
          quantity: 1,
        },
      ],
      totalAmount: 76500,
      deliveryStatus: "Giao hàng thành công",
      orderStatus: "HOÀN THÀNH",
    },
    {
      id: "987654321",
      shop: {
        name: "Bapcase.vn",
        isFavorite: true,
      },
      status: "completed",
      items: [
        {
          id: 2,
          name: "Ốp lưng điện thoại iphone flower art cạnh vuông 6/6plus/6s/6splus/7/7plus/8/8plus/x/xs/11/12/13/14/pro/max/promax/plus",
          image: "/images/product2.jpg",
          variant: "[4-11-3] Xám Xsmax",
          price: 7900,
          originalPrice: 9800,
          quantity: 1,
        },
      ],
      totalAmount: 7900,
      deliveryStatus: "Giao hàng thành công",
      orderStatus: "HOÀN THÀNH",
    },
  ];

  // Lọc đơn hàng theo tab
  const filterOrders = () => {
    if (activeTab === "all") return orderData;

    const statusMap = {
      pending: "pending",
      shipping: "shipping",
      delivered: "delivered",
      completed: "completed",
      cancelled: "cancelled",
      returned: "returned",
    };

    return orderData.filter((order) => order.status === statusMap[activeTab]);
  };

  const filteredOrders = filterOrders();

  return (
    <div className="bg-gray-100 min-h-screen py-6 px-4">
      <h1 className="text-2xl font-semibold mb-4">Đơn hàng</h1>
      <div className="max-w-6xl mx-auto bg-white rounded-md shadow-sm">
        {/* Tab Navigation */}
        <div className="flex border-b overflow-x-auto no-scrollbar">
          <button
            onClick={() => setActiveTab("all")}
            className={`px-4 py-3 whitespace-nowrap font-medium ${
              activeTab === "all"
                ? "text-orange-500 border-b-2 border-orange-500"
                : "text-gray-600"
            }`}
          >
            Tất cả
          </button>
          <button
            onClick={() => setActiveTab("pending")}
            className={`px-4 py-3 whitespace-nowrap font-medium ${
              activeTab === "pending"
                ? "text-orange-500 border-b-2 border-orange-500"
                : "text-gray-600"
            }`}
          >
            Chờ thanh toán
          </button>
          <button
            onClick={() => setActiveTab("shipping")}
            className={`px-4 py-3 whitespace-nowrap font-medium ${
              activeTab === "shipping"
                ? "text-orange-500 border-b-2 border-orange-500"
                : "text-gray-600"
            }`}
          >
            Vận chuyển
          </button>
          <button
            onClick={() => setActiveTab("delivered")}
            className={`px-4 py-3 whitespace-nowrap font-medium ${
              activeTab === "delivered"
                ? "text-orange-500 border-b-2 border-orange-500"
                : "text-gray-600"
            }`}
          >
            Chờ giao hàng
          </button>
          <button
            onClick={() => setActiveTab("completed")}
            className={`px-4 py-3 whitespace-nowrap font-medium ${
              activeTab === "completed"
                ? "text-orange-500 border-b-2 border-orange-500"
                : "text-gray-600"
            }`}
          >
            Hoàn thành
          </button>
          <button
            onClick={() => setActiveTab("cancelled")}
            className={`px-4 py-3 whitespace-nowrap font-medium ${
              activeTab === "cancelled"
                ? "text-orange-500 border-b-2 border-orange-500"
                : "text-gray-600"
            }`}
          >
            Đã hủy
          </button>
          <button
            onClick={() => setActiveTab("returned")}
            className={`px-4 py-3 whitespace-nowrap font-medium ${
              activeTab === "returned"
                ? "text-orange-500 border-b-2 border-orange-500"
                : "text-gray-600"
            }`}
          >
            Trả hàng/Hoàn tiền
          </button>
        </div>

        {/* Search Bar */}
        <div className="p-4 border-b">
          <div className="relative">
            <input
              type="text"
              placeholder="Bạn có thể tìm kiếm theo tên Shop, ID đơn hàng hoặc Tên Sản phẩm"
              className="w-full pl-10 pr-4 py-2 border rounded-md focus:outline-none focus:ring-1 focus:ring-orange-500"
              value={searchQuery}
              onChange={(e) => setSearchQuery(e.target.value)}
            />
            <FiSearch className="absolute left-3 top-3 text-gray-400" />
          </div>
        </div>

        {/* Order List */}
        <div className="divide-y">
          {filteredOrders.length > 0 ? (
            filteredOrders.map((order) => (
              <div key={order.id} className="p-4">
                {/* Shop Header */}
                <div className="flex justify-between items-center mb-3">
                  <div className="flex items-center">
                    {order.shop.isFavorite && (
                      <span className="bg-red-100 text-orange-500 text-xs px-2 py-0.5 rounded mr-2">
                        Yêu thích
                      </span>
                    )}
                    <span className="font-medium">{order.shop.name}</span>
                  </div>
                  <div className="flex space-x-2">
                    <button className="flex items-center bg-orange-500 text-white px-3 py-1 rounded-sm text-sm">
                      <FiMessageCircle className="mr-1" />
                      Chat
                    </button>
                    <button className="flex items-center border border-gray-300 text-gray-600 px-3 py-1 rounded-sm text-sm">
                      <FiEye className="mr-1" />
                      Xem Shop
                    </button>
                  </div>
                </div>

                {/* Delivery Status */}
                <div className="flex justify-between border-b pb-3 mb-3">
                  <div className="flex items-center text-gray-500">
                    <FiInfo className="mr-1" />
                    <span>{order.deliveryStatus}</span>
                  </div>
                  <div className="text-orange-500 font-medium">
                    {order.orderStatus}
                  </div>
                </div>

                {/* Products */}
                {order.items.map((item) => (
                  <div key={item.id} className="flex py-3">
                    <div className="w-16 h-16 flex-shrink-0">
                      <img
                        src={item.image || "https://via.placeholder.com/64"}
                        alt={item.name}
                        className="w-full h-full object-cover border"
                      />
                    </div>
                    <div className="ml-3 flex-grow">
                      <div className="text-sm line-clamp-2">{item.name}</div>
                      <div className="text-xs text-gray-500 mt-1">
                        Phân loại hàng: {item.variant}
                      </div>
                      <div className="text-xs text-gray-500">
                        x{item.quantity}
                      </div>
                    </div>
                    <div className="ml-4 text-right">
                      <div className="text-sm text-gray-500">
                        <span className="line-through">
                          ₫{item.originalPrice.toLocaleString()}
                        </span>
                      </div>
                      <div className="text-sm">
                        ₫{item.price.toLocaleString()}
                      </div>
                    </div>
                  </div>
                ))}

                {/* Order Total */}
                <div className="flex justify-end items-center border-t pt-3">
                  <div className="text-gray-600 mr-2">Thành tiền:</div>
                  <div className="text-xl text-orange-500 font-medium">
                    ₫{order.totalAmount.toLocaleString()}
                  </div>
                </div>

                {/* Action Buttons */}
                <div className="flex justify-end mt-4 space-x-2">
                  <button className="px-4 py-2 bg-orange-500 text-white rounded">
                    Mua Lại
                  </button>
                  <button className="px-4 py-2 border border-gray-300 rounded text-gray-700">
                    Liên Hệ Người Bán
                  </button>
                </div>
              </div>
            ))
          ) : (
            <div className="p-10 text-center text-gray-500">
              Không tìm thấy đơn hàng nào
            </div>
          )}
        </div>
      </div>
    </div>
  );
};

export default OrderHistory;
