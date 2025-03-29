import React, { useState, useEffect } from "react";
import { Link, useNavigate } from "react-router-dom";
import { useSelector, useDispatch } from "react-redux";
import { clearSelectedItems } from "../../../store/cartSlice";
import axios from "axios";

const Payment = () => {
  const navigate = useNavigate();
  const dispatch = useDispatch();
  const { selectedItems, shipping, discount } = useSelector(
    (state) => state.cart
  );
  const [paymentMethod, setPaymentMethod] = useState("");
  const [address, setAddress] = useState({
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

  useEffect(() => {
    // Nếu không có sản phẩm được chọn, quay lại trang giỏ hàng
    if (!selectedItems || selectedItems.length === 0) {
      navigate("/cart");
    }

    // Lấy danh sách tỉnh/thành phố khi component được mount
    fetchProvinces();
  }, [selectedItems, navigate]);

  // Theo dõi thay đổi tỉnh/thành để lấy quận/huyện
  useEffect(() => {
    if (address.province) {
      fetchDistricts(address.province);
    } else {
      setDistricts([]);
      setAddress((prev) => ({ ...prev, district: "", ward: "" }));
    }
  }, [address.province]);

  // Theo dõi thay đổi quận/huyện để lấy phường/xã
  useEffect(() => {
    if (address.district) {
      fetchWards(address.district);
      // Lấy phương thức vận chuyển dựa trên quận/huyện đã chọn
      fetchShippingMethods(address.district);
    } else {
      setWards([]);
      setAddress((prev) => ({ ...prev, ward: "" }));
      setShippingMethods([]);
    }
  }, [address.district]);

  // Hàm lấy danh sách tỉnh/thành phố từ API
  const fetchProvinces = async () => {
    try {
      const response = await axios.get("https://provinces.open-api.vn/api/p/");
      setProvinces(response.data);
    } catch (error) {
      console.error("Lỗi khi lấy danh sách tỉnh/thành:", error);
    }
  };

  // Hàm lấy danh sách quận/huyện từ API
  const fetchDistricts = async (provinceCode) => {
    try {
      const response = await axios.get(
        `https://provinces.open-api.vn/api/p/${provinceCode}?depth=2`
      );
      setDistricts(response.data.districts);
    } catch (error) {
      console.error("Lỗi khi lấy danh sách quận/huyện:", error);
    }
  };

  // Hàm lấy danh sách phường/xã từ API
  const fetchWards = async (districtCode) => {
    try {
      const response = await axios.get(
        `https://provinces.open-api.vn/api/d/${districtCode}?depth=2`
      );
      setWards(response.data.wards);
    } catch (error) {
      console.error("Lỗi khi lấy danh sách phường/xã:", error);
    }
  };

  // Hàm lấy phương thức vận chuyển
  const fetchShippingMethods = async (districtCode) => {
    // Mô phỏng lấy phương thức vận chuyển dựa trên quận/huyện
    // Trong thực tế, bạn sẽ gọi API từ backend để lấy các phương thức vận chuyển có sẵn
    setShippingMethods([
      { id: 1, name: "Giao hàng tiêu chuẩn", price: 30000, days: "3-5 ngày" },
      { id: 2, name: "Giao hàng nhanh", price: 45000, days: "1-2 ngày" },
    ]);

    // Mặc định chọn phương thức đầu tiên
    if (!selectedShipping && setShippingMethods.length > 0) {
      setSelectedShipping("1");
    }
  };

  const formatPrice = (price) => {
    return new Intl.NumberFormat("vi-VN", {
      style: "currency",
      currency: "VND",
    }).format(price);
  };

  const calculateSubtotal = () => {
    return selectedItems.reduce((total, item) => {
      return (
        total +
        (item.product.discount_price || item.product.price) * item.quantity
      );
    }, 0);
  };

  const calculateTotal = () => {
    return calculateSubtotal() - discount + shipping;
  };

  const handlePayment = async () => {
    if (!paymentMethod) {
      setError("Vui lòng chọn phương thức thanh toán");
      return;
    }

    if (
      !address.name ||
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

    try {
      // Lấy tên tỉnh/huyện/xã từ mã
      const provinceName =
        provinces.find((p) => p.code == address.province)?.name || "";
      const districtName =
        districts.find((d) => d.code == address.district)?.name || "";
      const wardName = wards.find((w) => w.code == address.ward)?.name || "";

      // Chuẩn bị dữ liệu tối giản theo OrderRequest
      const miniOrderData = {
        // Thông tin bắt buộc theo OrderRequest
        user_name: address.name,
        user_email: "customer@example.com", // giá trị mặc định
        user_phone: address.phone,
        user_address: `${address.address}, ${wardName}, ${districtName}, ${provinceName}`,
        payment_method_id: 1, // mặc định là COD (1)

        // Thêm các trường đơn hàng cơ bản
        items: selectedItems.map((item) => ({
          product_id: item.product.id,
          quantity: item.quantity,
        })),
      };

      console.log("Dữ liệu gửi đi tối giản:", miniOrderData);

      const token = localStorage.getItem("authToken");

      // Gọi API với dữ liệu tối giản
      const response = await axios.post(
        "http://localhost:8000/api/orders",
        miniOrderData,
        {
          headers: {
            Authorization: `Bearer ${token}`,
          },
        }
      );

      console.log("Phản hồi từ server:", response.data);

      if (response.data.status === "success") {
        // Xóa các sản phẩm đã chọn khỏi Redux store
        dispatch(clearSelectedItems());

        // Chuyển đến trang thành công
        navigate("/order-success");
      }
    } catch (err) {
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
                {districts.map((district) => (
                  <option key={district.code} value={district.code}>
                    {district.name}
                  </option>
                ))}
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
              {loading ? "Đang xử lý..." : "Hoàn tất đơn hàng"}
            </button>
          </div>
        </div>

        <div className="bg-white p-6 rounded-lg shadow-md border">
          <h3 className="font-semibold">Đơn hàng của bạn</h3>
          <div className="mt-4 space-y-4">
            {selectedItems.map((item) => (
              <div
                key={`${item.product.id}-${JSON.stringify(
                  item.variant_details
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
                    {formatPrice(
                      item.product.discount_price || item.product.price
                    )}{" "}
                    x {item.quantity}
                  </div>
                </div>
                <div className="font-medium">
                  {formatPrice(
                    (item.product.discount_price || item.product.price) *
                      item.quantity
                  )}
                </div>
              </div>
            ))}
          </div>

          {/* <div className="mt-4">
            <label className="block text-sm font-semibold">Mã giảm giá</label>
            <div className="flex mt-1">
              <input
                type="text"
                className="w-full border p-2 rounded-l-lg"
                placeholder="Nhập mã"
              />
              <button className="bg-gray-300 px-4 py-2 rounded-r-lg">
                Sử dụng
              </button>
            </div>
          </div> */}

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
              <span>{formatPrice(shipping)}</span>
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
