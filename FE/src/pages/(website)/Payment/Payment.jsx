import axios from "axios";
import React, { useEffect, useState } from "react";
import { Link } from "react-router-dom";

const Payment = () => {
  const [showCart, setShowCart] = useState([]); // Lưu danh sách sản phẩm
  const [totalCartPrice, setTotalCartPrice] = useState(0); // Lưu tổng tiền
  const [loading, setLoading] = useState(true); // Trạng thái loading
  const [isAuthorized, setIsAuthorized] = useState(false); // Kiểm tra xem user có được phép thanh toán không
  const [error, setError] = useState(null); // Lưu lỗi nếu có

  // Lấy dữ liệu từ localStorage
  const token = localStorage.getItem("authToken");
  const userDataStr = localStorage.getItem("userData");

  // Kiểm tra token và userData
  if (!token || !userDataStr) {
    window.location.href = "/sign-in";
  }

  // Phân tích dữ liệu người dùng từ localStorage
  const userData = JSON.parse(userDataStr);
  const localUserId = userData.id; // Lấy user_id từ localStorage

  // Hàm lấy dữ liệu giỏ hàng từ API và so sánh user_id
  const handleShowCart = async () => {
    try {
      const response = await axios.get("http://127.0.0.1:8000/api/cart", {
        headers: {
          Authorization: `Bearer ${token}`,
          "Content-Type": "application/json",
        },
      });

      const { cart, items, total_cart_price } = response.data;

      // So sánh user_id từ localStorage với cart.user_id
      if (localUserId === cart.user_id) {
        setIsAuthorized(true); // Nếu khớp, cho phép hiển thị giỏ hàng và thanh toán
        setShowCart(items || []); // Lưu danh sách sản phẩm
        setTotalCartPrice(total_cart_price || 0); // Lưu tổng tiền
      } else {
        setError("Bạn không có quyền truy cập giỏ hàng này.");
        setIsAuthorized(false);
      }
      setLoading(false);
    } catch (error) {
      setLoading(false);
      console.error("Lỗi khi lấy dữ liệu giỏ hàng:", error);
      if (error.response?.status === 401) {
        setError("Phiên đăng nhập đã hết hạn. Vui lòng đăng nhập lại.");
        localStorage.clear();
        setTimeout(() => {
          window.location.href = "/sign-in";
        }, 2000);
      } else if (error.response?.status === 404) {
        setError("Giỏ hàng không tồn tại.");
        setIsAuthorized(false);
      } else {
        setError("Không thể tải dữ liệu giỏ hàng. Vui lòng thử lại sau.");
        setIsAuthorized(false);
      }
    }
  };

  // Gọi API khi component mount
  useEffect(() => {
    handleShowCart();
  }, []);

  // Hàm định dạng giá tiền
  const formatPrice = (price) => {
    return new Intl.NumberFormat("vi-VN", {
      style: "currency",
      currency: "VND",
    }).format(price);
  };

  return (
    <div className="my-20">
      <div className="max-w-6xl mx-auto py-10 px-6 grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div className="lg:col-span-2 bg-white p-6 rounded-lg shadow-md border">
          <div className="mt-4 border-b pb-4">
            <h3 className="font-semibold">Thông tin giao hàng</h3>
            <p className="text-sm text-gray-600">{userData.name}</p>
            <div className="mt-2">
              <input
                type="text"
                className="w-full border rounded-lg p-2"
                placeholder="Thêm địa chỉ mới..."
              />
            </div>
            <div className="mt-2 grid grid-cols-1 md:grid-cols-2 gap-2">
              <input
                type="text"
                className="w-full border rounded-lg p-2"
                placeholder="Số điện thoại"
              />
              <input
                type="text"
                className="w-full border rounded-lg p-2"
                placeholder="Địa chỉ"
              />
            </div>
            <div className="mt-2 grid grid-cols-1 md:grid-cols-3 gap-2">
              <select className="w-full border rounded-lg p-2">
                <option>Chọn quận/huyện</option>
              </select>
              <select className="w-full border rounded-lg p-2">
                <option>Chọn phường/xã</option>
              </select>
            </div>
          </div>

          <div className="mt-4">
            <h3 className="font-semibold">Phương thức thanh toán</h3>
            <div className="mt-2 space-y-2">
              <label className="flex items-center space-x-2 border p-3 rounded-lg cursor-pointer">
                <input type="radio" name="payment" />
                <span>Thanh toán chuyển khoản qua ngân hàng</span>
              </label>
              <label className="flex items-center space-x-2 border p-3 rounded-lg cursor-pointer">
                <input type="radio" name="payment" />
                <span>Thanh toán quẹt thẻ khi giao hàng (POS)</span>
              </label>
              <label className="flex items-center space-x-2 border p-3 rounded-lg cursor-pointer">
                <input type="radio" name="payment" />
                <span>Thanh toán online qua VNPAY</span>
              </label>
              <label className="flex items-center space-x-2 border p-3 rounded-lg cursor-pointer">
                <input type="radio" name="payment" />
                <span>Ví MoMo</span>
              </label>
            </div>
          </div>

          <div className="mt-4 flex justify-between">
            <button className="text-gray-600">
              <Link to="/cart">Giỏ hàng</Link>
            </button>
            <button
              className={`px-4 py-2 rounded-lg ${
                isAuthorized && showCart.length > 0
                  ? "bg-blue-600 text-white"
                  : "bg-gray-400 text-gray-200 cursor-not-allowed"
              }`}
              disabled={!isAuthorized || showCart.length === 0}
            >
              <Link
                to={
                  isAuthorized && showCart.length > 0 ? "/order-success" : "#"
                }
              >
                Hoàn tất đơn hàng
              </Link>
            </button>
          </div>
        </div>

        <div className="bg-white p-6 rounded-lg shadow-md border">
          <h3 className="font-semibold">Giỏ hàng</h3>
          {error && <p className="text-red-500">{error}</p>}
          {loading ? (
            <p className="text-gray-600">Đang tải giỏ hàng...</p>
          ) : !isAuthorized ? (
            <p className="text-red-500">
              Bạn không có quyền truy cập giỏ hàng này.
            </p>
          ) : showCart.length === 0 ? (
            <p className="text-gray-600">Giỏ hàng trống</p>
          ) : (
            <div className="mt-2 space-y-4">
              {showCart.map((item) => (
                <div key={item.id} className="flex items-center space-x-4">
                  <div className="w-16 h-16 bg-gray-200">
                    <img
                      src={`http://127.0.0.1:8000/storage/${item.product.image_thumnail}`}
                      alt={item.product.name}
                      className="w-full h-full object-cover"
                      onError={(e) =>
                        (e.target.src =
                          "https://via.placeholder.com/80x80?text=No+Image")
                      }
                    />
                  </div>
                  <div>
                    <p className="font-medium">{item.product.name}</p>
                    {item.product_variant &&
                      item.product_variant.variant_details && (
                        <div className="text-sm text-gray-600">
                          {item.product_variant.variant_details.map(
                            (variant, index) => (
                              <span key={index}>
                                {variant.name}: {variant.value}
                                {index <
                                  item.product_variant.variant_details.length -
                                    1 && ", "}
                              </span>
                            )
                          )}
                        </div>
                      )}
                    <p className="text-gray-600">
                      {formatPrice(item.total_price)} (x{item.quantity})
                    </p>
                  </div>
                </div>
              ))}
            </div>
          )}

          {isAuthorized && showCart.length > 0 && (
            <div className="mt-4 border-t pt-4">
              <p className="flex justify-between">
                <span>Tạm tính</span>
                <span>{formatPrice(totalCartPrice)}</span>
              </p>
              <p className="flex justify-between font-bold text-lg">
                <span>Tổng cộng</span>
                <span>{formatPrice(totalCartPrice)}</span>
              </p>
            </div>
          )}
        </div>
      </div>
    </div>
  );
};

export default Payment;
