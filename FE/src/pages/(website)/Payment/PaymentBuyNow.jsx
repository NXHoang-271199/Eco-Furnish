import React, { useState, useEffect } from "react";
import { Link, useNavigate } from "react-router-dom";
import { useSelector, useDispatch } from "react-redux";
import { clearSelectedItems } from "../../../store/cartSlice";
import axios from "axios";
import { useLocation } from "react-router-dom";
import axiosInstance from "../../../utils/axiosConfig";
const PaymentBuyNow = () => {
  const navigate = useNavigate();
  const { state } = useLocation(); // Lấy dữ liệu từ state của navigate
  const selectedProducts =
    state?.selectedProducts ||
    JSON.parse(localStorage.getItem("tempSelectedProducts")) ||
    []; // Lấy selectedProducts từ state
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
  });
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState("");
  const [discountCode, setDiscountCode] = useState(""); // State lưu mã giảm giá người dùng nhập
  const [discountError, setDiscountError] = useState(""); // State lưu lỗi khi áp dụng mã giảm giá
  const [isVerifying, setIsVerifying] = useState(false); // State kiểm tra xem mã giảm giá đang được xác minh hay không
  const [discountAmount, setDiscountAmount] = useState(0); // Lưu giá trị giảm giá (số)
  const [voucherId, setVoucherId] = useState(null); // Lưu ID của voucher được áp dụng
  const [paymentMethods, setPaymentMethods] = useState([]);
  const [orderCode, setOrderCode] = useState("");

  const [provinces, setProvinces] = useState([]);
  const [districts, setDistricts] = useState([]);
  const [wards, setWards] = useState([]);

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

    console.log("Selected Products:", state?.selectedProducts);
    console.log("Total:", state?.total);

    // Kiểm tra nếu không có sản phẩm được chọn
    if (!selectedProducts || selectedProducts.length === 0) {
      navigate("/products");
    }

    getPaymentMethod();
    fetchProvinces();
  }, [selectedProducts, navigate, state]);

  // Lấy danh sách tỉnh/thành phố
  const fetchProvinces = async () => {
    try {
      const response = await axios.get("https://provinces.open-api.vn/api/p/");
      setProvinces(response.data);
    } catch (err) {
      console.error("Lỗi khi lấy danh sách tỉnh/thành phố:", err);
    }
  };

  // Lấy danh sách quận/huyện dựa trên tỉnh/thành phố
  const fetchDistricts = async (provinceCode) => {
    try {
      const response = await axios.get(
        `https://provinces.open-api.vn/api/p/${provinceCode}?depth=2`
      );
      setDistricts(response.data.districts);
      setWards([]); // Reset danh sách phường/xã khi chọn tỉnh mới
      setAddress((prev) => ({ ...prev, district: "", ward: "" })); // Reset quận/huyện và phường/xã
    } catch (err) {
      console.error("Lỗi khi lấy danh sách quận/huyện:", err);
    }
  };

  // Lấy danh sách phường/xã dựa trên quận/huyện
  const fetchWards = async (districtCode) => {
    try {
      const response = await axios.get(
        `https://provinces.open-api.vn/api/d/${districtCode}?depth=2`
      );
      setWards(response.data.wards);
      setAddress((prev) => ({ ...prev, ward: "" })); // Reset phường/xã khi chọn quận/huyện mới
    } catch (err) {
      console.error("Lỗi khi lấy danh sách phường/xã:", err);
    }
  };

  // Xử lý khi thay đổi tỉnh/thành phố
  const handleProvinceChange = (e) => {
    const selectedProvinceCode = e.target.value;
    const selectedProvince = provinces.find(
      (p) => p.code === Number(selectedProvinceCode)
    );
    setAddress((prev) => ({ ...prev, province: selectedProvince?.name || "" }));
    if (selectedProvinceCode) {
      fetchDistricts(selectedProvinceCode);
    } else {
      setDistricts([]);
      setWards([]);
    }
  };

  // Xử lý khi thay đổi quận/huyện
  const handleDistrictChange = (e) => {
    const selectedDistrictCode = e.target.value;
    const selectedDistrict = districts.find(
      (d) => d.code === Number(selectedDistrictCode)
    );
    setAddress((prev) => ({ ...prev, district: selectedDistrict?.name || "" }));
    if (selectedDistrictCode) {
      fetchWards(selectedDistrictCode);
    } else {
      setWards([]);
    }
  };

  // Xử lý khi thay đổi phường/xã
  const handleWardChange = (e) => {
    const selectedWardCode = e.target.value;
    const selectedWard = wards.find((w) => w.code === Number(selectedWardCode));
    setAddress((prev) => ({ ...prev, ward: selectedWard?.name || "" }));
  };

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
      const response = await axiosInstance.post(
        "/check-voucher",
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
        const response = await axiosInstance.get("/payment-methods", {
          headers: {
            Authorization: `Bearer ${token}`,
          },
        });
        setPaymentMethods(response.data.data);
      } catch (error) {
        console.error("Lỗi khi lấy phương thức thanh toán:", error);
      }
    }
  };

  const handlePaymentBuyNow = async () => {
    if (!paymentMethod) {
      setError("Vui lòng chọn phương thức thanh toán");
      return;
    }

    if (
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

    setLoading(true);
    setError("");

    // Lưu địa chỉ vào localStorage
    // localStorage.setItem("userAddress", JSON.stringify(address));

    try {
      const token = localStorage.getItem("authToken");

      // Lấy thông tin sản phẩm duy nhất từ selectedProducts
      const singleProductItem = selectedProducts[0];
      if (!singleProductItem) {
        setError("Không tìm thấy thông tin sản phẩm để mua ngay.");
        setLoading(false);
        return;
      }

      const orderData = {
        product_id: singleProductItem.product.id,
        product_variant_id: singleProductItem.product_variant
          ? singleProductItem.product_variant.id
          : null,
        quantity: singleProductItem.quantity,
        user_name: address.name,
        user_email: address.email,
        user_address: `${address.address}, ${address.ward}, ${address.district}, ${address.province}`,
        user_phone: address.phone,
        payment_method_id: Number(paymentMethod),
        voucher_id: voucherId && discountAmount > 0 ? voucherId : null,
      };

      // Thêm voucher_id nếu đã áp dụng mã giảm giá
      if (voucherId && discountAmount > 0) {
        orderData.voucher_id = voucherId;
      }

      // Gọi API orders/buy-now
      const response = await axiosInstance.post("/orders/buy-now", orderData, {
        headers: {
          Authorization: `Bearer ${token}`,
          "Content-Type": "application/json",
        },
      });

      // Lấy mã đơn hàng từ response API
      const newOrderCode =
        response.data.order?.order_code ||
        response.data.order_code ||
        response.data.data?.order_code;
      if (!newOrderCode) {
        console.error(
          "Không thể lấy mã đơn hàng từ response API:",
          response.data
        );
      }
      setOrderCode(newOrderCode);

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
          localStorage.setItem(
            "orderInfo",
            JSON.stringify({
              order_code: newOrderCode,
              total: calculateTotal(),
              payment_method: selectedMethod.name,
              order_date: new Date().toISOString(),
            })
          );
          navigate("/order-success");
        }
      }
    } catch (err) {
      console.error("Lỗi khi gọi API:", err);
      setError(
        err.response?.data?.message || "Có lỗi xảy ra khi xử lý đơn hàng"
      );
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
                value={
                  provinces.find((p) => p.name === address.province)?.code || ""
                }
                onChange={handleProvinceChange}
              >
                <option value="">Chọn tỉnh/thành</option>
                {provinces.map((province) => (
                  <option key={province.code} value={province.code}>
                    {province.name}
                  </option>
                ))}
              </select>
              <select
                className="w-full border rounded-lg p-2"
                value={
                  districts.find((d) => d.name === address.district)?.code || ""
                }
                onChange={handleDistrictChange}
                disabled={!address.province}
              >
                <option value="">Chọn quận/huyện</option>
                {districts.map((district) => (
                  <option key={district.code} value={district.code}>
                    {district.name}
                  </option>
                ))}
              </select>
              <select
                className="w-full border rounded-lg p-2"
                value={wards.find((w) => w.name === address.ward)?.code || ""}
                onChange={handleWardChange}
                disabled={!address.district}
              >
                <option value="">Chọn phường/xã</option>
                {wards.map((ward) => (
                  <option key={ward.code} value={ward.code}>
                    {ward.name}
                  </option>
                ))}
              </select>
            </div>
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
              onClick={handlePaymentBuyNow}
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
                  item.product_variant.price ||
                  0
                : item.product.discount_price || item.product.price || 0;
              return (
                <div
                  key={`${item.product.id}-${JSON.stringify(
                    item.product_variant?.variant_details
                  )}`}
                  className="flex items-center space-x-4"
                >
                  <div className="relative w-16 h-16 bg-gray-200 rounded-lg overflow-hidden">
                    <img
                      src={`http://localhost:8000/storage/${item.product.image_thumbnail}`}
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
                    <div className="mt-1 text-sm text-gray-500">
                      {formatPrice(price)} x {item.quantity}
                    </div>
                  </div>
                  <div className="font-medium">
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

export default PaymentBuyNow;
