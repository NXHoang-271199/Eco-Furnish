import { useState, useEffect } from "react";
import { FiTrash2 } from "react-icons/fi";
import { FaCartArrowDown } from "react-icons/fa";
import { Link, useNavigate } from "react-router-dom";
import { motion, AnimatePresence } from "framer-motion";
import axios from "axios";
import { toast, Toaster } from "react-hot-toast";
import { useDispatch } from "react-redux";
import {
  setSelectedItems,
  setSelectedProducts,
} from "../../../store/cartSlice";

const Cart = () => {
  const navigate = useNavigate(); // Hook để điều hướng giữa các trang trong React Router
  const [discountCode, setDiscountCode] = useState(""); // State lưu mã giảm giá người dùng nhập
  const [discountError, setDiscountError] = useState(""); // State lưu lỗi khi áp dụng mã giảm giá
  const [isVerifying, setIsVerifying] = useState(false); // State kiểm tra xem mã giảm giá đang được xác minh hay không
  const [localSelectedItems, setLocalSelectedItems] = useState([]); // State lưu danh sách các sản phẩm được chọn trong giỏ hàng
  const [isUpdatingQuantity, setIsUpdatingQuantity] = useState(false); // State kiểm tra xem số lượng đang được cập nhật hay không (hiện bị comment)
  const [stockError, setStockError] = useState(""); // State lưu lỗi liên quan đến tồn kho
  const dispatch = useDispatch();

  // code dat
  const [cart, setCart] = useState({ items: [] });
  const [message, setMessage] = useState("");

  useEffect(() => {
    fetchCart();
  }, []);

  // Hàm lấy giỏ hàng
  const fetchCart = async () => {
    const token = localStorage.getItem("authToken");
    const userData = JSON.parse(localStorage.getItem("userData"));
    if (!token || !userData) {
      navigate("/sign-in");
      return;
    }

    try {
      const response = await axios.get("http://localhost:8000/api/cart", {
        headers: {
          Authorization: `Bearer ${token}`, // Gửi token trong header
        },
      });
      console.log("cart:", response.data);

      setCart(response.data);

      // Reset danh sách các sản phẩm đã chọn khi giỏ hàng thay đổi
      setLocalSelectedItems([]);
    } catch (error) {
      console.error("Lỗi giỏ hàng:", error);
      toast.error("Không thể tải giỏ hàng. Vui lòng thử lại sau.");
    }
  };

  // Hàm định dạng giá tiền sang định dạng tiền tệ Việt Nam (VND)
  const formatPrice = (price) => {
    return new Intl.NumberFormat("vi-VN", {
      style: "currency",
      currency: "VND",
    }).format(price);
  };

  // Hàm xử lý thay đổi số lượng sản phẩm trong giỏ hàng
  const handleUpdateQuantity = async (item, newQuantity) => {
    if (newQuantity < 1) return;

    setIsUpdatingQuantity(true);
    setStockError("");

    const token = localStorage.getItem("authToken");
    try {
      const cartData = {
        quantity: newQuantity,
      };
      const response = await axios.put(
        `http://localhost:8000/api/cart/update/${item.id}`,
        cartData,
        {
          headers: {
            Authorization: `Bearer ${token}`,
          },
        }
      );

      if (response.status === 200) {
        // Cập nhật giỏ hàng sau khi thành công
        await fetchCart();
        toast.success("Cập nhật số lượng thành công");
      }
    } catch (error) {
      console.error("Lỗi cập nhật số lượng:", error);
      if (
        error.response &&
        error.response.data &&
        error.response.data.message
      ) {
        setStockError(error.response.data.message);
        toast.error(error.response.data.message);
      } else {
        toast.error("Không thể cập nhật số lượng");
      }
    } finally {
      setIsUpdatingQuantity(false);
    }
  };

  // Hàm xóa một sản phẩm khỏi giỏ hàng
  const handleRemoveItem = async (cartId) => {
    const token = localStorage.getItem("authToken");
    try {
      const response = await axios.delete(
        `http://localhost:8000/api/cart/remove/${cartId}`,
        {
          headers: {
            Authorization: `Bearer ${token}`,
          },
        }
      );
      if (response.status === 200) {
        toast.success("Đã xóa sản phẩm khỏi giỏ hàng");
        await fetchCart();
      }
    } catch (error) {
      console.error("Lỗi xử lý:", error);
      toast.error("Không thể xóa sản phẩm. Vui lòng thử lại sau.");
    }
  };

  // Hàm xóa toàn bộ giỏ hàng
  const handleClearCart = async (e) => {
    e.preventDefault();
    const token = localStorage.getItem("authToken");
    try {
      const response = await axios.delete(
        "http://localhost:8000/api/cart/clear",
        {
          headers: {
            Authorization: `Bearer ${token}`,
          },
        }
      );
      if (response.status === 200) {
        toast.success("Đã xóa toàn bộ giỏ hàng");
        await fetchCart();
      }
    } catch (error) {
      console.error("Lỗi xử lý:", error);
      toast.error("Không thể xóa giỏ hàng. Vui lòng thử lại sau.");
    }
  };

  // Hàm tính tổng tất cả giá tiền trong giỏ hàng
  const calculateTotal = () => {
    return cart.items
      ? cart.items.reduce((total, item) => {
          const price = item.product_variant
            ? item.product_variant.discount_price || item.product_variant.price
            : item.product.discount_price || item.product.price;
          return total + price * item.quantity;
        }, 0)
      : 0;
  };

  // Hàm tính tổng tiền của các sản phẩm đã chọn trong giỏ hàng
  const calculateSelectedTotal = () => {
    return cart.items
      ? cart.items.reduce((total, item) => {
          if (localSelectedItems.includes(item.id)) {
            const price = item.product_variant
              ? item.product_variant.discount_price ||
                item.product_variant.price
              : item.product.discount_price || item.product.price;
            return total + price * item.quantity;
          }
          return total;
        }, 0)
      : 0;
  };

  // Hàm isItemSelected: Kiểm tra xem một sản phẩm có được chọn hay không
  const isItemSelected = (itemId) => {
    return localSelectedItems.includes(itemId);
  };

  // Hàm toggleItemSelection: Chọn hoặc bỏ chọn một sản phẩm
  const toggleItemSelection = (itemId) => {
    setLocalSelectedItems((prev) => {
      if (prev.includes(itemId)) {
        return prev.filter((id) => id !== itemId);
      } else {
        return [...prev, itemId];
      }
    });
  };

  // Hàm toggleSelectAll: Chọn hoặc bỏ chọn tất cả sản phẩm trong giỏ hàng
  const toggleSelectAll = () => {
    if (cart.items && cart.items.length > 0) {
      if (localSelectedItems.length === cart.items.length) {
        setLocalSelectedItems([]);
      } else {
        setLocalSelectedItems(cart.items.map((item) => item.id));
      }
    }
  };

  // Hàm tạo đơn hàng
  const handleCheckout = async () => {
    if (localSelectedItems.length === 0) {
      toast.error("Vui lòng chọn ít nhất một sản phẩm");
      return;
    }

    const selectedProducts = cart.items.filter((item) =>
      localSelectedItems.includes(item.id)
    );

    console.log("Các sản phẩm đã chọn:", selectedProducts);

    if (!selectedProducts || selectedProducts.length === 0) {
      toast.error("Không thể lấy thông tin sản phẩm đã chọn");
      return;
    }

    const total = calculateSelectedTotal();

    // Lưu thông tin sản phẩm đã chọn vào Redux store để sử dụng ở trang thanh toán
    dispatch(setSelectedItems(localSelectedItems));
    dispatch(setSelectedProducts(selectedProducts));

    navigate("/payment", {
      state: {
        selectedProducts: selectedProducts,
        total: total,
      },
    });
  };

  return (
    <div className="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 py-12 mt-20">
      <Toaster position="top-right" />
      <div className="max-w-6xl mx-auto px-4">
        <h1 className="text-3xl font-bold mb-8 bg-gradient-to-r from-gray-900 to-gray-700 bg-clip-text text-transparent">
          Giỏ hàng của bạn
        </h1>

        {!cart?.items || cart.items.length === 0 ? (
          <div className="text-center py-16 bg-white rounded-2xl shadow-lg backdrop-blur-xl bg-white/80">
            <div className="w-24 h-24 bg-gradient-to-br from-gray-100 to-gray-200 rounded-full flex items-center justify-center mx-auto mb-6 shadow-inner">
              <FaCartArrowDown className="w-12 h-12 text-gray-400" />
            </div>
            <p className="text-gray-500 text-lg mb-6">
              Giỏ hàng của bạn đang trống
            </p>
            <Link
              to="/products"
              className="inline-block bg-gradient-to-r from-gray-900 to-gray-700 text-white px-8 py-3 rounded-xl hover:scale-105 transition-all duration-300 shadow-lg hover:shadow-xl active:scale-95 backdrop-blur-xl"
            >
              Tiếp tục mua sắm
            </Link>
          </div>
        ) : (
          <div className="flex flex-col lg:flex-row gap-8">
            <div className="lg:w-2/3">
              <div className="bg-white/80 backdrop-blur-xl rounded-2xl shadow-lg overflow-hidden border border-gray-100">
                <div className="p-6 border-b border-gray-100">
                  <div className="flex justify-between items-center mb-4">
                    <label className="inline-flex items-center">
                      <input
                        type="checkbox"
                        className="w-5 h-5 rounded-lg border-gray-300 text-black focus:ring-black transition-all duration-300 hover:border-black"
                        checked={
                          cart.items.length > 0 &&
                          localSelectedItems.length === cart.items.length
                        }
                        onChange={toggleSelectAll}
                      />
                      <span className="ml-3 text-sm font-medium text-gray-500">
                        Chọn tất cả ({cart.items.length} sản phẩm)
                      </span>
                    </label>
                    <button
                      onClick={handleClearCart}
                      className="group relative px-6 py-2.5 text-red-500 rounded-xl transition-all duration-300 hover:text-white overflow-hidden"
                    >
                      <span className="absolute inset-0 w-full h-full bg-gradient-to-r from-red-500 to-red-600 opacity-0 group-hover:opacity-100 transition-all duration-300 rounded-xl transform scale-x-0 group-hover:scale-x-100 origin-left"></span>
                      <span className="relative flex items-center gap-2 transform group-hover:scale-105 transition-transform duration-300">
                        <FiTrash2
                          size={18}
                          className="transform group-hover:rotate-12 transition-transform duration-300"
                        />
                        Xóa tất cả
                      </span>
                    </button>
                  </div>

                  <div className="grid grid-cols-12 gap-6">
                    <div className="col-span-7">
                      <h2 className="text-sm font-medium text-gray-500">
                        Sản phẩm
                      </h2>
                    </div>
                    <div className="col-span-2 text-center">
                      <h2 className="text-sm font-medium text-gray-500">
                        Số lượng
                      </h2>
                    </div>
                    <div className="col-span-2 text-center">
                      <h2 className="text-sm font-medium text-gray-500">
                        Tổng tiền
                      </h2>
                    </div>
                    <div className="col-span-1 text-center">
                      <h2 className="text-sm font-medium text-gray-500">
                        Thao tác
                      </h2>
                    </div>
                  </div>
                </div>

                <div className="divide-y divide-gray-100">
                  <AnimatePresence>
                    {cart.items.map((item) => (
                      <motion.div
                        key={item.id}
                        initial={{ opacity: 0, y: 20 }}
                        animate={{ opacity: 1, y: 0 }}
                        exit={{ opacity: 0, y: -20 }}
                        transition={{ duration: 0.3, ease: "easeInOut" }}
                        className="p-6 hover:bg-gray-50/80 transition-all duration-300 group"
                      >
                        <div className="grid grid-cols-12 gap-6 items-center">
                          <div className="col-span-7 flex items-center gap-4">
                            <input
                              type="checkbox"
                              className="w-5 h-5 rounded-lg border-gray-300 text-black focus:ring-black transition-all duration-300 hover:border-black"
                              checked={isItemSelected(item.id)}
                              onChange={() => toggleItemSelection(item.id)}
                            />
                            <div className="relative w-20 h-20 rounded-xl overflow-hidden bg-gradient-to-br from-gray-100 to-gray-200 group/image">
                              <img
                                src={
                                  item.product.image_thumnail &&
                                  item.product.image_thumnail.startsWith("http")
                                    ? item.product.image_thumnail
                                    : `http://localhost:8000/storage/${item.product.image_thumnail}`
                                }
                                alt={item.product.name}
                                className="w-full h-full object-cover transform group-hover/image:scale-110 transition-all duration-500"
                                onError={(e) => {
                                  e.target.src =
                                    "https://via.placeholder.com/80x80?text=No+Image";
                                }}
                              />
                              {item.product.discount_price && (
                                <div className="absolute top-1 right-1 bg-gradient-to-r from-red-500 to-red-600 text-white text-xs px-2 py-1 rounded-lg shadow-lg">
                                  -
                                  {Math.round(
                                    (1 -
                                      item.product.discount_price /
                                        item.product.price) *
                                      100
                                  )}
                                  %
                                </div>
                              )}
                            </div>
                            <div>
                              <Link
                                to={`/product-detail/${item.product.id}`}
                                className="font-medium text-gray-800 group-hover:text-amber-500 transition-colors duration-300 hover:underline"
                              >
                                {item.product.name}
                              </Link>
                              <div className="mt-1 space-y-1">
                                {item.product_variant &&
                                item.product_variant.variant_details &&
                                Array.isArray(
                                  item.product_variant.variant_details
                                ) ? (
                                  <div className="flex flex-wrap gap-1">
                                    {item.product_variant.variant_details.map(
                                      (variant, index) => (
                                        <span
                                          key={index}
                                          className="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium bg-gradient-to-br from-gray-100 to-gray-200 text-gray-800 hover:from-gray-200 hover:to-gray-300 transition-all duration-300 shadow-sm"
                                        >
                                          {variant.name}: {variant.value}
                                        </span>
                                      )
                                    )}
                                  </div>
                                ) : (
                                  <span className="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium bg-gradient-to-br from-gray-100 to-gray-200 text-gray-800">
                                    Phiên bản tiêu chuẩn
                                  </span>
                                )}

                                {item.product_variant && (
                                  <p className="text-xs text-gray-500">
                                    Mã: {item.product_variant.sku}
                                  </p>
                                )}
                              </div>
                            </div>
                          </div>

                          <div className="col-span-2">
                            <div className="flex items-center justify-center">
                              <button
                                onClick={() =>
                                  handleUpdateQuantity(item, item.quantity - 1)
                                }
                                className="w-8 h-8 flex items-center justify-center border border-gray-300 rounded-l-lg hover:bg-gradient-to-r hover:from-gray-900 hover:to-gray-700 hover:text-white hover:border-transparent transition-all duration-300 active:scale-95 disabled:opacity-50"
                                disabled={
                                  isUpdatingQuantity || item.quantity <= 1
                                }
                              >
                                -
                              </button>
                              <div className="w-12 h-8 flex items-center justify-center border-t border-b border-gray-300 bg-white font-medium">
                                {isUpdatingQuantity ? (
                                  <div className="w-4 h-4 border-2 border-gray-300 border-t-amber-500 rounded-full animate-spin"></div>
                                ) : (
                                  item.quantity
                                )}
                              </div>
                              <button
                                onClick={() =>
                                  handleUpdateQuantity(item, item.quantity + 1)
                                }
                                className="w-8 h-8 flex items-center justify-center border border-gray-300 rounded-r-lg hover:bg-gradient-to-r hover:from-gray-900 hover:to-gray-700 hover:text-white hover:border-transparent transition-all duration-300 active:scale-95 disabled:opacity-50"
                                disabled={isUpdatingQuantity}
                              >
                                +
                              </button>
                            </div>
                            {stockError && (
                              <p className="text-red-500 text-xs mt-1 text-center">
                                {stockError}
                              </p>
                            )}
                          </div>

                          <div className="col-span-2 text-center">
                            <span className="font-medium text-gray-900">
                              {formatPrice(item.total_price)}
                            </span>
                            {item.product_variant &&
                              item.product_variant.discount_price && (
                                <p className="text-xs text-gray-500 line-through">
                                  {formatPrice(
                                    item.product_variant.price * item.quantity
                                  )}
                                </p>
                              )}
                            {!item.product_variant &&
                              item.product.discount_price && (
                                <p className="text-xs text-gray-500 line-through">
                                  {formatPrice(
                                    item.product.price * item.quantity
                                  )}
                                </p>
                              )}
                          </div>

                          <div className="col-span-1 flex justify-center">
                            <button
                              onClick={() => handleRemoveItem(item.id)}
                              className="group/delete relative p-2.5 rounded-xl overflow-hidden transition-all duration-300 hover:bg-red-50"
                            >
                              <span className="absolute inset-0 w-full h-full bg-gradient-to-r from-red-500/20 to-red-600/20 opacity-0 group-hover/delete:opacity-100 transition-all duration-300 rounded-xl transform scale-0 group-hover/delete:scale-100"></span>
                              <FiTrash2
                                size={18}
                                className="relative text-gray-400 group-hover/delete:text-red-500 transition-colors duration-300 transform group-hover/delete:rotate-12"
                              />
                            </button>
                          </div>
                        </div>
                      </motion.div>
                    ))}
                  </AnimatePresence>
                </div>
              </div>
            </div>

            <div className="lg:w-1/3">
              <div className="bg-white/80 backdrop-blur-xl rounded-2xl shadow-lg p-6 border border-gray-100 sticky top-24">
                <h2 className="text-lg font-medium bg-gradient-to-r from-gray-900 to-gray-700 bg-clip-text text-transparent mb-6">
                  Thông tin đơn hàng
                </h2>

                <div className="space-y-4">
                  {/* <div className="flex items-center">
                    <input
                      type="text"
                      value={discountCode}
                      onChange={(e) => setDiscountCode(e.target.value)}
                      placeholder="Mã giảm giá"
                      className="flex-1 px-4 py-2.5 border border-gray-300 rounded-l-xl focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent transition-all duration-300"
                      disabled={isVerifying}
                    />
                    <button
                      onClick={() =>
                        toast.error("Tính năng đang được phát triển")
                      }
                      disabled={isVerifying || !discountCode}
                      className="relative px-6 py-2.5 bg-gradient-to-r from-amber-500 to-amber-600 text-white rounded-r-xl overflow-hidden transform hover:scale-105 transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:scale-100"
                    >
                      <span className="absolute inset-0 w-full h-full bg-white opacity-0 hover:opacity-10 transition-opacity duration-300"></span>
                      <span className="relative flex items-center gap-2">
                        {isVerifying ? (
                          <svg
                            className="animate-spin h-4 w-4"
                            viewBox="0 0 24 24"
                          >
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
                    <p className="text-red-500 text-sm">{discountError}</p>
                  )} */}

                  <div className="border-t border-gray-100 pt-4 space-y-3">
                    <div className="flex justify-between text-gray-600">
                      <span>
                        Đã chọn ({localSelectedItems.length} sản phẩm)
                      </span>
                      <span>{formatPrice(calculateSelectedTotal())}</span>
                    </div>

                    {/* {cart.discount > 0 && (
                      <div className="flex justify-between text-green-600">
                        <span>
                          Giảm giá (
                          {Math.round((cart.discount / cart.subtotal) * 100)}
                          %)
                        </span>
                        <span>-{formatPrice(cart.discount)}</span>
                      </div>
                    )} */}

                    <div className="flex justify-between text-gray-600">
                      <span>Phí vận chuyển</span>
                      <span>Miễn phí</span>
                    </div>

                    <div className="border-t border-gray-100 pt-4">
                      <div className="flex justify-between text-lg font-medium">
                        <span className="text-gray-900">Tổng thanh toán</span>
                        <span className="bg-gradient-to-r from-amber-500 to-amber-600 bg-clip-text text-transparent">
                          {formatPrice(calculateSelectedTotal())}
                        </span>
                      </div>
                    </div>
                  </div>

                  <div className="mt-6 space-y-4">
                    <button
                      onClick={handleCheckout}
                      className={`relative block w-full text-center py-3.5 rounded-xl transform transition-all duration-300 ${
                        localSelectedItems.length > 0
                          ? "bg-gradient-to-r from-amber-500 to-amber-600 text-white hover:scale-105 active:scale-95 shadow-lg hover:shadow-xl"
                          : "bg-gray-200 text-gray-500 cursor-not-allowed"
                      }`}
                      disabled={localSelectedItems.length === 0}
                    >
                      <span className="absolute inset-0 w-full h-full bg-white opacity-0 hover:opacity-10 transition-opacity duration-300 rounded-xl"></span>
                      <span className="relative flex items-center justify-center gap-2">
                        <span>Thanh toán ngay</span>
                        <span className="bg-white/20 px-2 py-0.5 rounded-lg text-sm">
                          {localSelectedItems.length} sản phẩm
                        </span>
                      </span>
                    </button>

                    <div className="flex items-start gap-2 text-sm text-gray-500">
                      <span className="mt-0.5">⚬</span>
                      <p>
                        Bảo hành 12 tháng với lỗi từ nhà sản xuất.{" "}
                        <button className="text-amber-600 underline decoration-gray-300 hover:decoration-amber-500 transition-all duration-300">
                          Chi tiết
                        </button>
                      </p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        )}
      </div>
    </div>
  );
};

export default Cart;
