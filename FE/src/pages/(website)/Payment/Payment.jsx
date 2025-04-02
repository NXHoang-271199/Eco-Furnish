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
  const [retryCount, setRetryCount] = useState(0);
  const MAX_RETRIES = 2;

  // Thêm state cho dữ liệu địa chỉ
  const [provinces, setProvinces] = useState([]);
  const [districts, setDistricts] = useState([]);
  const [wards, setWards] = useState([]);
  const [shippingMethods, setShippingMethods] = useState([]);
  const [selectedShipping, setSelectedShipping] = useState("");
  const [discount, setDiscount] = useState(0);

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
  }, [selectedProducts, navigate]);

  const formatPrice = (price) => {
    return new Intl.NumberFormat("vi-VN", {
      style: "currency",
      currency: "VND",
    }).format(price);
  };

  const calculateSubtotal = () => {
    return selectedProducts.reduce((total, item) => {
      return total + (Number(item.total_price) || 0); // Sử dụng total_price từ API
    }, 0);
  };

  const calculateTotal = () => {
    return calculateSubtotal() - (discount || 0);
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
        payment_method_id: 1, // Sử dụng ID 1 cho MoMo như trong ảnh
      };

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
        if (paymentMethod === "MoMo") {
          // Nếu trong response có payUrl (như hình ảnh của bạn), redirect đến đó
          if (response.data && response.data.payUrl) {
            window.location.href = response.data.payUrl;
          } else {
            // Nếu không có payUrl, có thể cần kiểm tra cấu trúc response
            console.error(
              "Không tìm thấy payUrl trong response:",
              response.data
            );
            setError("Không tìm thấy đường dẫn thanh toán");
          }
        } else if (paymentMethod === "VNPAY") {
          // Xử lý VNPAY nếu cần
          if (response.data && response.data.data) {
            window.location.href = response.data.data;
          } else {
            setError("Không nhận được đường dẫn thanh toán từ VNPAY");
          }
        } else {
          // Thanh toán COD, chuyển hướng trực tiếp
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
              <label className="flex items-center space-x-2 border p-3 rounded-lg cursor-pointer hover:bg-gray-50">
                <input
                  type="radio"
                  name="payment"
                  value="COD"
                  checked={paymentMethod === "COD"}
                  onChange={(e) => setPaymentMethod(e.target.value)}
                />
                <span>Thanh toán khi nhận hàng (COD)</span>
              </label>
              <label className="flex items-center space-x-2 border p-3 rounded-lg cursor-pointer hover:bg-gray-50">
                <input
                  type="radio"
                  name="payment"
                  value="VNPAY"
                  checked={paymentMethod === "VNPAY"}
                  onChange={(e) => setPaymentMethod(e.target.value)}
                />
                <span>Thanh toán online qua VNPAY</span>
              </label>
              <label className="flex items-center space-x-2 border p-3 rounded-lg cursor-pointer hover:bg-gray-50">
                <input
                  type="radio"
                  name="payment"
                  value="MoMo"
                  checked={paymentMethod === "MoMo"}
                  onChange={(e) => setPaymentMethod(e.target.value)}
                />
                <span>Ví MoMo</span>
              </label>
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
            {discount > 0 && (
              <div className="flex justify-between text-green-600 mt-2">
                <span>Giảm giá</span>
                <span>-{formatPrice(discount)}</span>
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
