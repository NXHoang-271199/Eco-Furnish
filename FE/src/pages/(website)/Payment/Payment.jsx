import React, { useState, useEffect } from "react";
import { Link, useNavigate } from "react-router-dom";
import { useSelector, useDispatch } from "react-redux";
import { clearSelectedItems } from "../../../store/cartSlice";
import axios from "axios";
import { useLocation } from "react-router-dom";

const Payment = () => {
  const navigate = useNavigate();
  const { state } = useLocation(); // Lấy dữ liệu từ state của navigate
  const selectedProducts = state?.selectedProducts || []; // Lấy selectedProducts từ state
  const total = state?.total || 0; // Lấy tổng tiền từ state
  const [userName, setUserName] = useState("");
  const [paymentMethod, setPaymentMethod] = useState("");
  const [address, setAddress] = useState({
    name: "",
    email: "",
    phone: "",
    address: "",
    province: "",
    district: "",
    ward: "",
    name: "",
  });
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState("");
  const [discountCode, setDiscountCode] = useState(""); // State lưu mã giảm giá người dùng nhập
  const [discountError, setDiscountError] = useState(""); // State lưu lỗi khi áp dụng mã giảm giá
  const [isVerifying, setIsVerifying] = useState(false); // State kiểm tra xem mã giảm giá đang được xác minh hay không
  const [discountAmount, setDiscountAmount] = useState(0); // Lưu giá trị giảm giá (số)
  const [voucherId, setVoucherId] = useState(null); // Lưu ID của voucher được áp dụng
  const [paymentMethods, setPaymentMethods] = useState([]);
  useEffect(() => {
    // Lấy địa chỉ từ localStorage khi component mount
    const savedAddress = JSON.parse(localStorage.getItem("userAddress")) || {};
    setAddress((prev) => ({
      ...prev,
      ...savedAddress,
    }));

    // Lấy thông tin người dùng từ localStorage
    const userData = JSON.parse(localStorage.getItem("userData")) || {};
    setUserName(userData.name || "Khách"); // Nếu không có tên thì hiển thị "Khách"

    // Kiểm tra nếu không có sản phẩm được chọn
    if (!selectedProducts || selectedProducts.length === 0) {
      navigate("/cart");
    }

    getPaymentMethod();
  }, [selectedProducts, navigate]);

  const formatPrice = (price) => {
    return new Intl.NumberFormat("vi-VN", {
      style: "currency",
      currency: "VND",
    }).format(price);
  };

  const calculateSubtotal = () => {
    return selectedProducts.reduce((total, item) => {
      return total + (Number(item.total_price) || 0);
    }, 0);
  };

  const calculateTotal = () => {
    return calculateSubtotal() - discountAmount;
  };

  const handleApplyDiscount = async () => {
    try {
      setIsVerifying(true);
      setDiscountError("");

      const token = localStorage.getItem("authToken");
      const response = await axios.post(
        "http://localhost:8000/api/check-voucher",
        {
          voucher_code: discountCode,
          subtotal: calculateSubtotal(),
        },
        {
          headers: {
            Authorization: `Bearer ${token}`,
            "Content-Type": "application/json",
          },
        }
      );

      if (response.data.status === "success") {
        setDiscountAmount(response.data.discount_amount);
        setVoucherId(response.data.voucher_id);
      } else {
        setDiscountError(response.data.message || "Mã giảm giá không hợp lệ");
        setDiscountAmount(0);
        setVoucherId(null);
      }
    } catch (err) {
      console.error("Lỗi khi áp dụng mã giảm giá:", err);
      setDiscountError(
        err.response?.data?.message || "Có lỗi xảy ra khi áp dụng mã giảm giá"
      );
      setDiscountAmount(0);
      setVoucherId(null);
    } finally {
      setIsVerifying(false);
    }
  };

  const getPaymentMethod = async () => {
    const token = localStorage.getItem("authToken");
    if (token) {
      try {
        const response = await axios.get(
          `http://localhost:8000/api/payment-methods`,
          {
            headers: {
              Authorization: `Bearer ${token}`,
            },
          }
        );
        setPaymentMethods(response.data.data);
      } catch (error) {
        console.error("Lỗi khi lấy phương thức thanh toán:", error);
      }
    }
  };

  const handlePayment = async () => {
    if (!paymentMethod) {
      setError("Vui lòng chọn phương thức thanh toán");
      return;
    }

    if (
      !address.name ||
      !address.name ||
      !address.email ||
      !address.phone ||
      !address.address ||
      !address.province ||
      !address.district ||
      !address.ward
    ) {
      setError("Vui lòng điền đầy đủ thông tin giao hàng");
      return;
    }

    if (!selectedShipping) {
      setError("Vui lòng chọn phương thức vận chuyển");
      return;
    }

    setLoading(true);
    setError("");

    // Lưu địa chỉ vào localStorage
    localStorage.setItem("userAddress", JSON.stringify(address));

    // Lưu thông tin đơn hàng vào localStorage
    localStorage.setItem(
      "orderInfo",
      JSON.stringify({
        products: selectedProducts,
        total: calculateTotal(),
        payment_method: paymentMethod,
        order_date: new Date().toISOString(),
      })
    );

    try {
      const token = localStorage.getItem("authToken");

      // Gọi API với dữ liệu tối giản
      const orderData = {
        cart_items: selectedProducts.map((item) => item.id),
        user_name: address.name,
        user_email: address.email,
        user_address: `${address.address}, ${address.ward}, ${address.district}, ${address.province}`,
        user_phone: address.phone,
        payment_method_id: Number(paymentMethod), // Sử dụng ID 1 cho MoMo như trong ảnh
      };

      // Thêm voucher_id nếu đã áp dụng mã giảm giá
      if (voucherId && discountAmount > 0) {
        orderData.voucher_id = voucherId;
      }

      // Gọi trực tiếp API orders - KHÔNG gọi payment/process
      const response = await axios.post(
        "http://localhost:8000/api/orders",
        orderData,
        {
          headers: {
            Authorization: `Bearer ${token}`,
            "Content-Type": "application/json",
          },
        }
      );

      console.log("Phản hồi từ server:", response.data);

      // Kiểm tra response
      console.log("Order response:", response.data);

      if (response.status === 200 || response.status === 201) {
        const selectedMethod = paymentMethods.find(
          (method) => method.id === Number(paymentMethod)
        );
        if (selectedMethod.name === "MoMo") {
          if (response.data && response.data.payUrl) {
            window.location.href = response.data.payUrl;
          } else {
            setError("Không tìm thấy đường dẫn thanh toán");
          }
        } else if (selectedMethod.name === "VNPAY") {
          if (response.data && response.data.data) {
            window.location.href = response.data.data;
          } else {
            setError("Không nhận được đường dẫn thanh toán từ VNPAY");
          }
        } else {
          navigate("/order-success");
        }
      }
    } catch (err) {
      console.error("Lỗi khi gọi API:", err);
      console.error("Lỗi chi tiết:", err);

      // Hiển thị thông báo lỗi
      if (err.response) {
        console.log("Response data:", err.response.data);
        console.log("Response status:", err.response.status);

        setError(err.response.data?.message || "Không thể hoàn tất đơn hàng");
      } else {
        setError("Có lỗi xảy ra khi xử lý đơn hàng. Vui lòng thử lại sau");
      }
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="my-20">
      <div className="max-w-6xl mx-auto py-10 px-6 grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div className="lg:col-span-2 bg-white p-6 rounded-lg shadow-md border">
          <h2 className="text-xl font-bold">Eco-Furnish</h2>
          {/* <p className="text-gray-600">Giỏ hàng - Thông tin giao hàng</p> */}

          <div className="mt-4 border-b pb-4">
            <h3 className="font-semibold">Thông tin giao hàng</h3>
            {/* <p className="text-sm text-gray-600">{userName}</p> */}
            <div className="mt-2">
              <input
                type="text"
                className="w-full border rounded-lg p-2"
                placeholder="Họ tên người nhận"
                value={address.name}
                onChange={(e) =>
                  setAddress({ ...address, name: e.target.value })
                }
                required
              />
            </div>
            <div className="mt-2">
              <input
                type="text"
                className="w-full border rounded-lg p-2"
                placeholder="Tên người nhận"
                value={address.name}
                onChange={(e) =>
                  setAddress({ ...address, name: e.target.value })
                }
              />
            </div>
            <div className="mt-2">
              <input
                type="email"
                className="w-full border rounded-lg p-2"
                placeholder="Email nguoi nhan"
                value={address.email}
                onChange={(e) =>
                  setAddress({ ...address, email: e.target.value })
                }
              />
            </div>
            <div className="mt-2">
              <input
                type="phone"
                className="w-full border rounded-lg p-2"
                placeholder="Số điện thoại"
                value={address.phone}
                onChange={(e) =>
                  setAddress({ ...address, phone: e.target.value })
                }
              />
            </div>
            <div className="mt-2">
              <input
                type="text"
                className="w-full border rounded-lg p-2"
                placeholder="Địa chỉ"
                value={address.address}
                onChange={(e) =>
                  setAddress({ ...address, address: e.target.value })
                }
              />
            </div>
            <div className="mt-2 grid grid-cols-1 md:grid-cols-3 gap-2">
              <select
                className="w-full border rounded-lg p-2"
                value={address.province}
                onChange={(e) =>
                  setAddress({ ...address, province: e.target.value })
                }
              >
                <option value="">Chọn tỉnh/thành</option>
                <option value="Hà Nội">Hà Nội</option>
                <option value="TP.HCM">TP.HCM</option>
                {/* Thêm các tỉnh/thành khác */}
                {provinces.map((province) => (
                  <option key={province.code} value={province.code}>
                    {province.name}
                  </option>
                ))}
              </select>
              <select
                className="w-full border rounded-lg p-2"
                value={address.district}
                onChange={(e) =>
                  setAddress({ ...address, district: e.target.value })
                }
                disabled={!address.province}
              >
                <option value="">Chọn quận/huyện</option>
                <option value="Quận 1">Quận 1</option>
                <option value="Quận 2">Quận 2</option>
                {/* Thêm các quận/huyện khác */}
              </select>
              <select
                className="w-full border rounded-lg p-2"
                value={address.ward}
                onChange={(e) =>
                  setAddress({ ...address, ward: e.target.value })
                }
                disabled={!address.district}
              >
                <option value="">Chọn phường/xã</option>
                <option value="Phường 1">Phường 1</option>
                <option value="Phường 2">Phường 2</option>
                {/* Thêm các phường/xã khác */}
                {wards.map((ward) => (
                  <option key={ward.code} value={ward.code}>
                    {ward.name}
                  </option>
                ))}
              </select>
            </div>
          </div>

          <div className="mt-4 border-b pb-4">
            <h3 className="font-semibold">Phương thức vận chuyển</h3>
            {address.district ? (
              <div className="mt-2 space-y-2">
                {shippingMethods.map((method) => (
                  <label
                    key={method.id}
                    className="flex items-center justify-between border p-3 rounded-lg cursor-pointer hover:bg-gray-50"
                  >
                    <div className="flex items-center">
                      <input
                        type="radio"
                        name="shipping"
                        value={method.id}
                        checked={selectedShipping == method.id}
                        onChange={(e) => setSelectedShipping(e.target.value)}
                        className="mr-2"
                      />
                      <div>
                        <p className="font-medium">{method.name}</p>
                        <p className="text-sm text-gray-600">
                          Giao hàng trong {method.days}
                        </p>
                      </div>
                    </div>
                    <span className="font-medium">
                      {formatPrice(method.price)}
                    </span>
                  </label>
                ))}
              </div>
            ) : (
              <p className="text-gray-600 text-sm">
                Vui lòng chọn quận / huyện để có danh sách phương thức vận
                chuyển.
              </p>
            )}
          </div>

          <div className="mt-4">
            <h3 className="font-semibold">Phương thức thanh toán</h3>
            <div className="mt-2 space-y-2">
              {paymentMethods.length > 0 ? (
                paymentMethods.map((method) => (
                  <label
                    key={method.id}
                    className="flex items-center space-x-2 border p-3 rounded-lg cursor-pointer"
                  >
                    <input
                      type="radio"
                      name="payment"
                      value={method.id} // Sử dụng ID từ API
                      checked={paymentMethod === method.id.toString()} // So sánh với ID
                      onChange={(e) => setPaymentMethod(e.target.value)}
                    />
                    <span>
                      {method.name === "Tiền mặt"
                        ? "Thanh toán khi nhận hàng"
                        : method.name}
                    </span>{" "}
                    {/* Giả sử API trả về field "name" */}
                  </label>
                ))
              ) : (
                <p>Đang tải phương thức thanh toán...</p>
              )}
            </div>
          </div>

          {error && <div className="mt-4 text-red-500 text-sm">{error}</div>}

          <div className="mt-4 flex justify-between">
            <Link to="/cart" className="text-gray-600 hover:text-gray-900">
              Giỏ hàng
            </Link>
            <button
              onClick={handlePayment}
              disabled={loading}
              className="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 disabled:opacity-50"
            >
              {loading ? "Đang xử lý..." : "Thanh toán đơn hàng"}
            </button>
          </div>
        </div>

        <div className="bg-white p-6 rounded-lg shadow-md border">
          <h3 className="font-semibold">Đơn hàng của bạn</h3>
          <div className="mt-4">
            <div className="flex item-center">
              <input
                type="text"
                value={discountCode}
                onChange={(e) => setDiscountCode(e.target.value)}
                placeholder="Mã giảm giá"
                className="flex-1 px-4 py-2.5 border border-gray-300 rounded-l-xl focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent transition-all duration-300"
                disabled={isVerifying}
              />
              <button
                onClick={handleApplyDiscount}
                disabled={isVerifying || !discountCode}
                className="relative px-6 py-2.5 bg-gradient-to-r from-amber-500 to-amber-600 text-white rounded-r-xl overflow-hidden transform hover:scale-105 transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:scale-100"
              >
                <span className="absolute inset-0 w-full h-full bg-white opacity-0 hover:opacity-10 transition-opacity duration-300"></span>
                <span className="relative flex items-center gap-2">
                  {isVerifying ? (
                    <svg className="animate-spin h-4 w-4" viewBox="0 0 24 24">
                      <circle
                        className="opacity-25"
                        cx="12"
                        cy="12"
                        r="10"
                        stroke="currentColor"
                        strokeWidth="4"
                        fill="none"
                      />
                      <path
                        className="opacity-75"
                        fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                      />
                    </svg>
                  ) : (
                    "Áp dụng"
                  )}
                </span>
              </button>
            </div>
            {discountError && (
              <div className="text-red-500 text-sm mt-2">{discountError}</div>
            )}
          </div>
          <div className="mt-4 space-y-4">
            {selectedProducts.map((item) => {
              const price = item.product_variant
                ? item.product_variant.discount_price ||
                  item.product_variant.price
                : item.product.discount_price || item.product.price;

              return (
                <div
                  key={`${item.product.id}-${JSON.stringify(
                    item.product_variant?.variant_details
                  )}`}
                  className="flex items-center space-x-4"
                >
                  <div className="relative w-16 h-16 bg-gray-200 rounded-lg overflow-hidden">
                    <img
                      src={`http://localhost:8000/storage/${item.product.image_thumnail}`}
                      alt={item.product.name}
                      className="w-full h-full object-cover"
                      onError={(e) => {
                        e.target.src =
                          "https://via.placeholder.com/80x80?text=No+Image";
                      }}
                    />
                  </div>
                  <div className="flex-1">
                    <p className="font-medium">{item.product.name}</p>
                    <div className="mt-1 space-x-2">
                      {item.variant_details &&
                        Array.isArray(item.variant_details) &&
                        item.variant_details.map((variant, index) => (
                          <span
                            key={index}
                            className="inline-flex items-center px-2 py-1 rounded-lg text-xs font-medium bg-gray-100"
                          >
                            {variant.name}: {variant.value}
                          </span>
                        ))}
                    </div>
                    <div className="mt-1 text-sm text-gray-500">
                      {/* {formatPrice(
                        item.product.discount_price || item.product.price
                      )}{" "}
                      x {item.quantity} */}
                      {formatPrice(price)} x {item.quantity}
                    </div>
                  </div>
                  <div className="font-medium">
                    {/* {formatPrice(
                      (item.product.discount_price || item.product.price) *
                        item.quantity
                    )} */}
                    {formatPrice(item.total_price)}
                  </div>
                </div>
              );
            })}
          </div>

          <div className="mt-6 border-t pt-4">
            <div className="flex justify-between text-gray-600">
              <span>Tạm tính</span>
              <span>{formatPrice(calculateSubtotal())}</span>
            </div>
            {discountAmount > 0 && (
              <div className="flex justify-between text-green-600 mt-2">
                <span>Giảm giá</span>
                <span>-{formatPrice(discountAmount)}</span>
              </div>
            )}
            <div className="flex justify-between text-gray-600 mt-2">
              <span>Phí vận chuyển</span>
              <span>free</span>
            </div>
            <div className="flex justify-between font-bold text-lg mt-4 pt-4 border-t">
              <span>Tổng cộng</span>
              <span>{formatPrice(calculateTotal())}</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default Payment;
