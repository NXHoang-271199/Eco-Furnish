import React, { useState, useEffect } from "react";
import { useParams, Link } from "react-router-dom";
import axios from "axios";
import { FiArrowLeft, FiInfo, FiPhone, FiMail, FiMapPin } from "react-icons/fi";

const OrderDetail = () => {
  const { id } = useParams();
  const [order, setOrder] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    const fetchOrderDetail = async () => {
      try {
        setLoading(true);
        const token = localStorage.getItem("authToken");
        const response = await axios.get(
          `http://localhost:8000/api/orders/${id}`,
          {
            headers: {
              Authorization: `Bearer ${token}`,
            },
          }
        );

        if (response.data.status === "success") {
          setOrder(response.data.data);
        }
      } catch (error) {
        console.error("Lỗi khi lấy chi tiết đơn hàng:", error);
        setError("Không thể tải thông tin đơn hàng. Vui lòng thử lại sau.");
      } finally {
        setLoading(false);
      }
    };

    fetchOrderDetail();
  }, [id]);

  // Chuyển đổi mã trạng thái thanh toán thành text
  const getPaymentStatusText = (statusCode) => {
    switch (statusCode) {
      case 0:
        return "Chưa thanh toán";
      case 1:
        return "Đã thanh toán";
      case 2:
        return "Đang chờ thanh toán";
      default:
        return "Không xác định";
    }
  };

  // Hiển thị các thuộc tính của biến thể sản phẩm
  const renderVariantDetails = (variant) => {
    if (
      !variant ||
      !variant.variant_details ||
      variant.variant_details.length === 0
    ) {
      return null;
    }

    return variant.variant_details.map((detail, index) => (
      <div key={index} className="text-sm text-gray-600">
        <span className="font-medium">{detail.name}:</span> {detail.value}
      </div>
    ));
  };

  // Xử lý hủy đơn hàng
  const handleCancelOrder = async () => {
    if (!window.confirm("Bạn có chắc chắn muốn hủy đơn hàng này?")) {
      return;
    }

    try {
      setLoading(true);
      const token = localStorage.getItem("authToken");
      const response = await axios.post(
        `http://localhost:8000/api/orders/${id}/cancel`,
        {},
        {
          headers: {
            Authorization: `Bearer ${token}`,
          },
        }
      );

      if (response.data.status === "success") {
        alert("Đơn hàng đã được hủy thành công!");
        // Cập nhật lại thông tin đơn hàng
        const updatedOrder = { ...order, order_status: "Hủy Đơn" };
        setOrder(updatedOrder);
      }
    } catch (error) {
      console.error("Lỗi khi hủy đơn hàng:", error);
      alert("Không thể hủy đơn hàng. Vui lòng thử lại sau.");
    } finally {
      setLoading(false);
    }
  };

  // Xử lý xác nhận đã nhận hàng
  const handleConfirmOrder = async () => {
    if (!window.confirm("Bạn đã nhận được hàng và muốn xác nhận?")) {
      return;
    }

    try {
      setLoading(true);
      const token = localStorage.getItem("authToken");
      const response = await axios.post(
        `http://localhost:8000/api/orders/${id}/confirm`,
        {},
        {
          headers: {
            Authorization: `Bearer ${token}`,
          },
        }
      );

      if (response.data.status === "success") {
        alert("Đã xác nhận nhận hàng thành công!");
        // Cập nhật lại thông tin đơn hàng
        const updatedOrder = { ...order, order_status: "Đã Nhận" };
        setOrder(updatedOrder);
      }
    } catch (error) {
      console.error("Lỗi khi xác nhận đơn hàng:", error);
      alert("Không thể xác nhận đơn hàng. Vui lòng thử lại sau.");
    } finally {
      setLoading(false);
    }
  };

  // Xử lý yêu cầu hoàn hàng
  const handleRequestRefund = async () => {
    const reason = prompt("Vui lòng nhập lý do hoàn hàng:");
    if (reason === null) return; // Người dùng nhấn hủy

    try {
      setLoading(true);
      const token = localStorage.getItem("authToken");
      const response = await axios.post(
        `http://localhost:8000/api/orders/${id}/request-refund`,
        { reason },
        {
          headers: {
            Authorization: `Bearer ${token}`,
          },
        }
      );

      if (response.data.status === "success") {
        alert("Yêu cầu hoàn hàng đã được gửi!");
      }
    } catch (error) {
      console.error("Lỗi khi yêu cầu hoàn hàng:", error);
      alert(
        error.response?.data?.message ||
          "Không thể gửi yêu cầu hoàn hàng. Vui lòng thử lại sau."
      );
    } finally {
      setLoading(false);
    }
  };

  if (loading) {
    return (
      <div className="flex justify-center items-center h-screen">
        <div className="animate-spin rounded-full h-10 w-10 border-t-2 border-b-2 border-orange-500"></div>
      </div>
    );
  }

  if (error || !order) {
    return (
      <div className="bg-gray-100 min-h-screen py-6 px-4">
        <div className="max-w-4xl mx-auto bg-white rounded-md shadow-sm p-8 text-center">
          <p className="text-red-500">{error || "Không tìm thấy đơn hàng"}</p>
          <Link
            to="/account/orders"
            className="mt-4 inline-flex items-center px-4 py-2 bg-orange-500 text-white rounded"
          >
            <FiArrowLeft className="mr-2" />
            Quay lại danh sách đơn hàng
          </Link>
        </div>
      </div>
    );
  }

  return (
    <div className="bg-gray-100 min-h-screen py-6 px-4">
      <div className="max-w-4xl mx-auto">
        <div className="mb-4 flex items-center">
          <Link
            to="/account/orders"
            className="inline-flex items-center text-orange-500"
          >
            <FiArrowLeft className="mr-1" />
            Quay lại danh sách đơn hàng
          </Link>
        </div>

        <div className="bg-white rounded-md shadow-sm p-6 mb-4">
          <h1 className="text-2xl font-semibold mb-4 flex justify-between">
            <span>Chi tiết đơn hàng: {order.order_code}</span>
            <span className="text-orange-500">{order.order_status}</span>
          </h1>

          <div className="mb-6 pb-4 border-b">
            <div className="flex items-start mb-2">
              <FiInfo className="text-orange-500 mt-1 mr-2" />
              <div>
                <h3 className="font-medium">Trạng thái đơn hàng</h3>
                <p>Thanh toán: {getPaymentStatusText(order.payment_status)}</p>
                <p>Đơn hàng: {order.order_status}</p>
                <p>Ngày đặt: {new Date(order.created_at).toLocaleString()}</p>
              </div>
            </div>
          </div>

          <div className="mb-6 pb-4 border-b">
            <div className="flex items-start mb-2">
              <FiMapPin className="text-orange-500 mt-1 mr-2" />
              <div>
                <h3 className="font-medium">Địa chỉ giao hàng</h3>
                <p className="font-medium">{order.user_name}</p>
                <p>{order.user_address}</p>
              </div>
            </div>
            <div className="flex items-start mt-3">
              <FiPhone className="text-orange-500 mt-1 mr-2" />
              <div>
                <p>{order.user_phone}</p>
              </div>
            </div>
            <div className="flex items-start mt-3">
              <FiMail className="text-orange-500 mt-1 mr-2" />
              <div>
                <p>{order.user_email}</p>
              </div>
            </div>
          </div>

          <div className="mb-6">
            <h3 className="font-medium mb-3">Sản phẩm đã đặt</h3>
            <div className="divide-y">
              {order.order_items.map((item) => (
                <div key={item.id} className="py-4 flex">
                  <div className="w-20 h-20 flex-shrink-0">
                    <img
                      src={`${import.meta.env.VITE_API_STORAGE_URL}/${
                        item.image_url
                      }`}
                      alt={item.product_name}
                      className="w-full h-full object-cover border"
                      onError={(e) => {
                        e.target.onerror = null;
                        e.target.src = "https://via.placeholder.com/80";
                      }}
                    />
                  </div>
                  <div className="ml-4 flex-grow">
                    <Link
                      to={`/products/${item.product_id}`}
                      className="text-lg hover:text-orange-500 transition-colors"
                    >
                      {item.product_name}
                    </Link>
                    {item.product_variant && (
                      <div className="mt-2">
                        {renderVariantDetails(item.product_variant)}
                      </div>
                    )}
                    <div className="mt-2 text-sm">
                      Số lượng: {item.quantity}
                    </div>
                  </div>
                  <div className="text-right">
                    <div className="text-lg font-medium text-orange-500">
                      ₫{parseInt(item.price).toLocaleString()}
                    </div>
                    {item.product_variant &&
                      item.product_variant.discount_price && (
                        <div className="text-sm text-gray-500 line-through">
                          ₫
                          {parseInt(
                            item.product_variant.price
                          ).toLocaleString()}
                        </div>
                      )}
                  </div>
                </div>
              ))}
            </div>
          </div>

          <div className="border-t pt-4">
            <div className="flex justify-between text-gray-600 mb-1">
              <span>Tổng tiền hàng:</span>
              <span>
                ₫
                {order.order_items
                  .reduce((sum, item) => sum + parseFloat(item.total_price), 0)
                  .toLocaleString()}
              </span>
            </div>
            {order.discount_amount > 0 && (
              <div className="flex justify-between text-gray-600 mb-1">
                <span>Giảm giá:</span>
                <span>
                  -₫{parseInt(order.discount_amount).toLocaleString()}
                </span>
              </div>
            )}
            <div className="flex justify-between font-medium text-lg mt-2">
              <span>Thành tiền:</span>
              <span className="text-orange-500">
                ₫{parseInt(order.total_price).toLocaleString()}
              </span>
            </div>
          </div>

          {/* Action buttons */}
          <div className="flex justify-end space-x-3 mt-6">
            {order.order_status === "Chưa Xác Nhận" && (
              <button
                onClick={handleCancelOrder}
                className="px-4 py-2 border border-red-500 text-red-500 rounded hover:bg-red-50"
                disabled={loading}
              >
                Hủy Đơn
              </button>
            )}
            {order.order_status === "Đã Giao" && (
              <>
                <button
                  onClick={handleRequestRefund}
                  className="px-4 py-2 border border-gray-300 text-gray-700 rounded hover:bg-gray-50"
                  disabled={loading}
                >
                  Yêu Cầu Hoàn Hàng
                </button>
                <button
                  onClick={handleConfirmOrder}
                  className="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600"
                  disabled={loading}
                >
                  Xác Nhận Đã Nhận
                </button>
              </>
            )}
            {order.order_status === "Đã Nhận" && (
              <Link
                to={`/products/${order.order_items[0].product_id}`}
                className="px-4 py-2 bg-orange-500 text-white rounded hover:bg-orange-600"
              >
                Mua Lại
              </Link>
            )}
          </div>
        </div>
      </div>
    </div>
  );
};

export default OrderDetail;
