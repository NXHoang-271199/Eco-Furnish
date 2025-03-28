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
  });
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState("");

  useEffect(() => {
    // Nếu không có sản phẩm được chọn, quay lại trang giỏ hàng
    if (!selectedItems || selectedItems.length === 0) {
      navigate("/cart");
    }
  }, [selectedItems, navigate]);

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

    try {
      const token = localStorage.getItem("authToken");
      const response = await axios.post(
        "http://localhost:8000/api/orders",
        {
          items: selectedItems.map((item) => ({
            product_id: item.product.id,
            quantity: item.quantity,
            variant_details: item.variant_details,
          })),
          shipping_address: `${address.address}, ${address.ward}, ${address.district}, ${address.province}`,
          phone: address.phone,
          payment_method: paymentMethod,
          shipping_fee: shipping,
          discount: discount,
        },
        {
          headers: {
            Authorization: `Bearer ${token}`,
          },
        }
      );

      if (response.data.status === "success") {
        // Xóa các sản phẩm đã chọn khỏi Redux store
        dispatch(clearSelectedItems());

        // Nếu thanh toán online, chuyển hướng đến trang thanh toán
        if (["MoMo", "VNPAY"].includes(paymentMethod)) {
          window.location.href = response.data.payment_url;
        } else {
          navigate("/order-success");
        }
      }
    } catch (err) {
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
            <p className="text-sm text-gray-600">
              Đinh Tấn Đạt
              {/* (dinhtandat11112003@gmail.com) */}
            </p>
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
                {/* Thêm các option tỉnh/thành */}
              </select>
              <select
                className="w-full border rounded-lg p-2"
                value={address.district}
                onChange={(e) =>
                  setAddress({ ...address, district: e.target.value })
                }
              >
                <option value="">Chọn quận/huyện</option>
                {/* Thêm các option quận/huyện */}
              </select>
              <select
                className="w-full border rounded-lg p-2"
                value={address.ward}
                onChange={(e) =>
                  setAddress({ ...address, ward: e.target.value })
                }
              >
                <option value="">Chọn phường/xã</option>
                {/* Thêm các option phường/xã */}
              </select>
            </div>
          </div>

          <div className="mt-4 border-b pb-4">
            <h3 className="font-semibold">Phương thức vận chuyển</h3>
            <p className="text-gray-600 text-sm">
              Vui lòng chọn quận / huyện để có danh sách phương thức vận chuyển.
            </p>
          </div>

          <div className="mt-4">
            <h3 className="font-semibold">Phương thức thanh toán</h3>
            <div className="mt-2 space-y-2">
              <label className="flex items-center space-x-2 border p-3 rounded-lg cursor-pointer">
                <input
                  type="radio"
                  name="payment"
                  value="COD"
                  checked={paymentMethod === "COD"}
                  onChange={(e) => setPaymentMethod(e.target.value)}
                />
                <span>Thanh toán khi nhận hàng (COD)</span>
              </label>
              <label className="flex items-center space-x-2 border p-3 rounded-lg cursor-pointer">
                <input
                  type="radio"
                  name="payment"
                  value="VNPAY"
                  checked={paymentMethod === "VNPAY"}
                  onChange={(e) => setPaymentMethod(e.target.value)}
                />
                <span>Thanh toán online qua VNPAY</span>
              </label>
              <label className="flex items-center space-x-2 border p-3 rounded-lg cursor-pointer">
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
