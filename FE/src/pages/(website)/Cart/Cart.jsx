import { useState, useEffect } from "react";
import { FiTrash2 } from "react-icons/fi";
import { FaCartArrowDown } from "react-icons/fa";
import { Link, useNavigate, useLocation } from "react-router-dom";
import { motion, AnimatePresence } from "framer-motion";
import axios from "axios";
import { toast, Toaster } from "react-hot-toast";
import { useDispatch } from "react-redux";
import {
  setSelectedItems,
  setSelectedProducts,
} from "../../../store/cartSlice";
import { toast } from "react-hot-toast";

const Cart = () => {
  const navigate = useNavigate(); // Hook để điều hướng giữa các trang trong React Router
  const [discountCode, setDiscountCode] = useState(""); // State lưu mã giảm giá người dùng nhập
  const [discountError, setDiscountError] = useState(""); // State lưu lỗi khi áp dụng mã giảm giá
  const [isVerifying, setIsVerifying] = useState(false); // State kiểm tra xem mã giảm giá đang được xác minh hay không
  const [localSelectedItems, setLocalSelectedItems] = useState([]); // State lưu danh sách các sản phẩm được chọn trong giỏ hàng
  const [isUpdatingQuantity, setIsUpdatingQuantity] = useState(false); // State kiểm tra xem số lượng đang được cập nhật hay không (hiện bị comment)
  const [stockError, setStockError] = useState(""); // State lưu lỗi liên quan đến tồn kho
  const location = useLocation();
  const dispatch = useDispatch();
  const cart = useSelector((state) => state.cart);
  const [discountCode, setDiscountCode] = useState("");
  const [discountError, setDiscountError] = useState("");
  const [isVerifying, setIsVerifying] = useState(false);
  const [localSelectedItems, setLocalSelectedItems] = useState([]);
  const [isUpdatingQuantity, setIsUpdatingQuantity] = useState(false);
  const [stockErrors, setStockErrors] = useState({});
  const [editingQuantities, setEditingQuantities] = useState({});
  const [validationError, setValidationError] = useState(false);
  const [toastMessage, setToastMessage] = useState("");
  const [showToast, setShowToast] = useState(false);
  const [updateTimeouts, setUpdateTimeouts] = useState({});
  const [pendingUpdates, setPendingUpdates] = useState({});
  const [isServerBusy, setIsServerBusy] = useState(false);


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

  // Hàm gọi API với xử lý refresh token và retry khi gặp lỗi server
  const callApiWithRefresh = async (apiCall, maxRetries = 2) => {
    let retries = 0;

    const executeWithRetry = async () => {
      try {
        const token = localStorage.getItem("authToken");
        if (!token) {
          throw new Error("Không có token xác thực");
        }

        return await apiCall(token);
      } catch (error) {
        // Xử lý lỗi 401 - Unauthorized
        if (error.response && error.response.status === 401) {
          console.log("Token không hợp lệ hoặc đã hết hạn, thử refresh token");

          // Thử refresh token
          const newToken = await refreshToken();
          if (newToken) {
            console.log("Đã refresh token thành công, gọi lại API");

            try {
              return await apiCall(newToken);
            } catch (retryError) {
              throw retryError;
            }
          } else {
            console.log("Không thể refresh token, đăng xuất");
            localStorage.removeItem("authToken");
            localStorage.removeItem("access_token");
            localStorage.removeItem("userData");
            localStorage.removeItem("refreshToken");

            throw new Error("Phiên đăng nhập hết hạn, vui lòng đăng nhập lại");
          }
        }

        // Xử lý lỗi 429 - Too Many Attempts
        if (error.response && error.response.status === 429) {
          // Đánh dấu server đang bận
          setIsServerBusy(true);

          // Tự động đặt lại sau 5 giây
          setTimeout(() => setIsServerBusy(false), 5000);

          if (retries < maxRetries) {
            retries++;
            console.log(
              `Quá nhiều yêu cầu (429), thử lại lần ${retries}/${maxRetries}`
            );

            // Chờ lâu hơn trước khi thử lại (1.5 giây * số lần thử)
            const delayTime = 1500 * retries;
            console.log(`Đợi ${delayTime}ms trước khi thử lại...`);
            await new Promise((resolve) => setTimeout(resolve, delayTime));
            return executeWithRetry();
          } else {
            console.log("Đã hết số lần thử lại cho lỗi 429");
            // Hiển thị thông báo người dùng
            toast.error(
              "Hệ thống đang bận, vui lòng đợi một lát trước khi thay đổi số lượng",
              { duration: 3000 }
            );
          }
        }

        // Xử lý lỗi 500 - Server Error với cơ chế retry
        if (error.response && error.response.status === 500) {
          if (retries < maxRetries) {
            retries++;
            console.log(`Lỗi server 500, thử lại lần ${retries}/${maxRetries}`);

            // Chờ 1 giây trước khi thử lại
            await new Promise((resolve) => setTimeout(resolve, 1000));
            return executeWithRetry();
          }
        }

        // Các lỗi khác hoặc đã hết số lần retry
        throw error;
      }
    };

    return executeWithRetry();
  };

  // Hàm xử lý thay đổi số lượng sản phẩm trong giỏ hàng
  const handleUpdateQuantity = async (item, newQuantity) => {
    // Đảm bảo số lượng là số nguyên và lớn hơn 0
    newQuantity = Math.max(1, parseInt(newQuantity, 10) || 1);

    if (newQuantity < 1) return;

    // Nếu đang cập nhật, không cho phép thao tác tiếp
    if (isUpdatingQuantity) {
      console.log("Đang cập nhật, bỏ qua yêu cầu mới");
      return;
    }

    // Nếu server đang bận, hiển thị thông báo
    if (isServerBusy) {
      toast.error("Hệ thống đang bận, vui lòng thử lại sau vài giây", {
        duration: 2000,
      });
      return;
    }

    // Xóa lỗi cũ của sản phẩm này
    const itemKey = `${productId}-${JSON.stringify(variant_details)}`;
    setStockErrors((prev) => {
      const newErrors = { ...prev };
      delete newErrors[itemKey];
      return newErrors;
    });

    // Bắt đầu cập nhật - đánh dấu trạng thái đang cập nhật
    setIsUpdatingQuantity(true);

    // Cập nhật UI trước để phản hồi nhanh
    dispatch(
      updateQuantity({ productId, quantity: newQuantity, variant_details })
    );
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
      // Gọi API cập nhật số lượng
      await updateQuantityWithServer(productId, variant_details, newQuantity);
    } catch (error) {
      console.error("Lỗi khi cập nhật số lượng:", error);

      // Nếu có lỗi, khôi phục số lượng ban đầu
      const cartItem = cart.items.find(
        (item) =>
          item.product.id === productId &&
          JSON.stringify(item.variant_details) ===
            JSON.stringify(variant_details)
      );

      if (cartItem) {
        // Khôi phục lại số lượng cũ nếu có lỗi
        dispatch(
          updateQuantity({
            productId,
            quantity: cartItem.quantity,
            variant_details,
          })
        );
      }

      // Hiển thị thông báo lỗi
      toast.error("Không thể cập nhật số lượng. Vui lòng thử lại sau.", {
        duration: 3000,
      });
    } finally {
      // Kết thúc cập nhật - bỏ đánh dấu trạng thái đang cập nhật
      setIsUpdatingQuantity(false);
    }
  };

  // Hàm cập nhật số lượng với server
  const updateQuantityWithServer = async (
    productId,
    variant_details,
    newQuantity
  ) => {
    const itemKey = `${productId}-${JSON.stringify(variant_details)}`;

    try {
      // Đảm bảo số lượng là số nguyên hợp lệ
      const quantityAsInt = parseInt(newQuantity, 10);
      if (isNaN(quantityAsInt) || quantityAsInt < 1) {
        console.error("Số lượng không hợp lệ:", newQuantity);
        setStockErrors((prev) => ({
          ...prev,
          [itemKey]: "Số lượng không hợp lệ, vui lòng nhập lại",
        }));
        throw new Error("Số lượng không hợp lệ");
      }

      newQuantity = Math.max(1, quantityAsInt);
      console.log("Số lượng gửi đi:", newQuantity, "kiểu:", typeof newQuantity);

      // Tìm item trong giỏ hàng
      const cartItem = cart.items.find(
        (item) =>
          item.product.id === productId &&
          JSON.stringify(item.variant_details) ===
            JSON.stringify(variant_details)
      );

      if (!cartItem) {
        setStockErrors((prev) => ({
          ...prev,
          [itemKey]: "Không tìm thấy sản phẩm trong giỏ hàng",
        }));
        throw new Error("Không tìm thấy sản phẩm");
      }

      // Chắc chắn user đã đăng nhập
      const token = localStorage.getItem("authToken");
      if (!token) {
        console.log("Người dùng chưa đăng nhập, chỉ cập nhật giỏ hàng cục bộ");
        return;
      }

      console.log("Token để xác thực:", token.substring(0, 15) + "...");

      // Sử dụng API mới để cập nhật số lượng trực tiếp
      const updateCartItemDirectly = async (currentToken) => {
        return axios.post(
          "http://localhost:8000/api/cart-items/update-quantity",
          {
            product_id: productId,
            quantity: newQuantity,
            variant_details: variant_details,
          },
          {
            headers: {
              Authorization: `Bearer ${currentToken}`,
              "Content-Type": "application/json",
              Accept: "application/json",
            },
          }
        );
      };

      // Thêm thời gian chờ tối thiểu để người dùng thấy đang cập nhật
      const startTime = Date.now();

      const response = await callApiWithRefresh(updateCartItemDirectly);

      // Đảm bảo hiển thị loading ít nhất 300ms để người dùng nhận biết
      const elapsedTime = Date.now() - startTime;
      if (elapsedTime < 300) {
        await new Promise((resolve) => setTimeout(resolve, 300 - elapsedTime));
      }

      console.log("Cập nhật số lượng trực tiếp thành công:", response.data);

      // Cập nhật lại UI với dữ liệu từ server để đảm bảo đồng bộ
      if (
        response.data.success &&
        response.data.data &&
        response.data.data.cart_item
      ) {
        const serverQuantity = response.data.data.cart_item.quantity;
        console.log("Số lượng từ server:", serverQuantity);

        // Luôn cập nhật số lượng từ server để đảm bảo chính xác
        dispatch(
          updateQuantity({
            productId,
            quantity: serverQuantity,
            variant_details,
          })
        );

        // Chỉ hiển thị thông báo điều chỉnh nếu số lượng từ server khác với yêu cầu
        if (serverQuantity !== newQuantity) {
          setStockErrors((prev) => ({
            ...prev,
            [itemKey]: `Số lượng đã được điều chỉnh thành ${serverQuantity} (tối đa có thể)`,
          }));

          // Thông báo khi số lượng bị điều chỉnh
          toast.info(`Số lượng đã được điều chỉnh thành ${serverQuantity}`, {
            duration: 3000,
          });
        }
      }

      // Kiểm tra phản hồi từ API nếu có lỗi về tồn kho
      if (response.data.message && response.data.message.includes("tối đa")) {
        const message = response.data.message || "Không thể cập nhật số lượng";
        setStockErrors((prev) => ({
          ...prev,
          [itemKey]: message,
        }));

        // Lấy số lượng tối đa có thể từ thông báo lỗi
        const maxQuantityMatch = response.data.message.match(/\d+/);
        if (maxQuantityMatch) {
          const maxQuantity = parseInt(maxQuantityMatch[0]);
          // Cập nhật lại số lượng trong giỏ hàng với số lượng tối đa
          dispatch(
            updateQuantity({
              productId,
              quantity: maxQuantity,
              variant_details,
            })
          );
        }
      }

      return response;
    } catch (apiError) {
      console.error("Lỗi khi cập nhật database:", apiError);

      // Xử lý các loại lỗi cụ thể
      if (apiError.response) {
        console.error("Mã lỗi:", apiError.response.status);
        console.error("Dữ liệu lỗi:", apiError.response.data);

        if (apiError.response.status === 401) {
          setStockErrors((prev) => ({
            ...prev,
            [itemKey]: "Phiên đăng nhập hết hạn. Vui lòng đăng nhập lại",
          }));
          // Xóa token không hợp lệ
          localStorage.removeItem("authToken");
          localStorage.removeItem("access_token");
          localStorage.removeItem("userData");
          localStorage.removeItem("refreshToken");

          // Chuyển hướng người dùng đến trang đăng nhập
          navigate("/signin", { state: { returnUrl: location.pathname } });
        } else if (apiError.response.status === 404) {
          setStockErrors((prev) => ({
            ...prev,
            [itemKey]: "Không tìm thấy sản phẩm trong giỏ hàng",
          }));
        } else if (apiError.response.status === 500) {
          // Xử lý lỗi server với hàm chuyên biệt
          handleServerError(apiError, productId, variant_details);
        } else if (apiError.response.status === 429) {
          setStockErrors((prev) => ({
            ...prev,
            [itemKey]: "Hệ thống đang bận, vui lòng thử lại sau",
          }));
          setIsServerBusy(true);
          setTimeout(() => setIsServerBusy(false), 5000);
        } else {
          setStockErrors((prev) => ({
            ...prev,
            [itemKey]: "Lỗi khi cập nhật số lượng",
          }));
        }
      } else {
        setStockErrors((prev) => ({
          ...prev,
          [itemKey]: "Lỗi kết nối đến server",
        }));
      }

      // Ném lỗi để xử lý bên ngoài
      throw apiError;
    }
  };

  const handleRemoveItem = async (productId, variant_details) => {
    try {
      const token = localStorage.getItem("authToken");
      if (token) {
        console.log("Đang xóa sản phẩm với productId:", productId);
        console.log("variant_details gửi đi:", variant_details);

        // Chuẩn bị variant_details để gửi đến API
        const preparedVariantDetails = Array.isArray(variant_details)
          ? variant_details
          : variant_details
          ? [variant_details]
          : null;

        // Gửi API xóa sản phẩm với retry và refresh token
        const removeCartItem = async (currentToken) => {
          return axios.delete(
            `http://localhost:8000/api/cart/remove/${productId}`,
            {
              headers: {
                Authorization: `Bearer ${currentToken}`,
                "Content-Type": "application/json",
              },
              data: {
                variant_details: preparedVariantDetails,
              },
            }
          );
        };

        try {
          await callApiWithRefresh(removeCartItem);
          console.log("Xóa sản phẩm thành công");
        } catch (error) {
          if (error.response && error.response.status === 500) {
            // Lỗi 500 khi xóa sản phẩm - có thể sản phẩm đã không còn tồn tại
            console.error("Lỗi 500 khi xóa sản phẩm:", error.response.data);
            // Vẫn tiếp tục xóa sản phẩm trong Redux, vì sản phẩm không hợp lệ
          } else {
            console.error("Không thể xóa sản phẩm:", error);
            throw error;
          }
        }
      }

      // Sau khi xóa thành công từ backend hoặc nếu không có token, cập nhật state Redux
      dispatch(removeFromCart({ productId, variant_details }));
      setLocalSelectedItems(
        localSelectedItems.filter(
          (item) =>
            !(
              item.productId === productId &&
              JSON.stringify(item.variant_details) ===
                JSON.stringify(variant_details)
            )
        )
      );
    } catch (error) {
      console.error("Lỗi khi xóa sản phẩm:", error);
      console.error("Chi tiết lỗi:", error.response?.data || error.message);

      if (error.response?.status === 401) {
        // Thử refresh token
        try {
          const newToken = await refreshToken();
          if (newToken) {
            // Nếu refresh thành công, thử xóa lại
            handleRemoveItem(productId, variant_details);
            return;
          }
        } catch (refreshError) {
          console.error("Lỗi khi refresh token:", refreshError);
        }
        // Nếu refresh thất bại, đăng xuất
        localStorage.removeItem("authToken");
        localStorage.removeItem("access_token");
        localStorage.removeItem("userData");
        localStorage.removeItem("refreshToken");
        navigate("/signin", { state: { returnUrl: location.pathname } });
      } else if (error.response?.status === 500) {
        // Lỗi 500 - Có thể sản phẩm không tồn tại
        const errorMessage = error.response?.data?.message || "";
        if (
          errorMessage.includes("property") &&
          errorMessage.includes("null")
        ) {
          alert(
            "Sản phẩm này không còn tồn tại trên hệ thống. Đã xóa khỏi giỏ hàng của bạn."
          );
        } else {
          alert(`Lỗi server: ${errorMessage}. Vui lòng thử lại sau.`);
        }
      } else {
        alert("Có lỗi xảy ra khi xóa sản phẩm khỏi giỏ hàng");
      }
    }
  };

  const handleClearCart = async () => {
    if (window.confirm("Bạn có chắc muốn xóa toàn bộ giỏ hàng?")) {
      try {
        const token = localStorage.getItem("authToken");
        if (token) {
          // Gọi API xóa toàn bộ giỏ hàng với retry và refresh token
          const clearCartApi = async (currentToken) => {
            return axios.delete("http://localhost:8000/api/cart/clear", {
              headers: {
                Authorization: `Bearer ${currentToken}`,
              },
            });
          };

          try {
            await callApiWithRefresh(clearCartApi);
            console.log("Xóa giỏ hàng thành công");
          } catch (error) {
            console.error("Không thể xóa giỏ hàng:", error);
            throw error;
          }
        }

        // Sau khi xóa thành công từ backend hoặc nếu không có token, cập nhật state Redux
        dispatch(clearCart());
        setLocalSelectedItems([]);
      } catch (error) {
        console.error("Lỗi khi xóa giỏ hàng:", error);

        if (error.response?.status === 401) {
          // Thử refresh token
          try {
            const newToken = await refreshToken();
            if (newToken) {
              // Nếu refresh thành công, thử xóa lại
              handleClearCart();
              return;
            }
          } catch (refreshError) {
            console.error("Lỗi khi refresh token:", refreshError);
          }
          // Nếu refresh thất bại, đăng xuất
          localStorage.removeItem("authToken");
          localStorage.removeItem("access_token");
          localStorage.removeItem("userData");
          localStorage.removeItem("refreshToken");
          navigate("/signin", { state: { returnUrl: location.pathname } });
        }

        alert("Có lỗi xảy ra khi xóa toàn bộ giỏ hàng");
      }
    }
  };

  const handleApplyDiscount = async () => {
    if (!discountCode) return;

    setIsVerifying(true);
    setDiscountError("");

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

  const handleInputQuantityChange = (productId, variant_details, value) => {
    // Lưu giá trị đang nhập vào state cục bộ
    const itemKey = `${productId}-${JSON.stringify(variant_details)}`;
    setEditingQuantities({
      ...editingQuantities,
      [itemKey]: value,
    });
  };

  const handleInputQuantityBlur = (productId, variant_details, inputValue) => {
    const itemKey = `${productId}-${JSON.stringify(variant_details)}`;

    // Lấy giá trị từ state cục bộ hoặc từ input
    const value = inputValue || editingQuantities[itemKey] || "";

    // Xóa giá trị khỏi state cục bộ
    const newEditingQuantities = { ...editingQuantities };
    delete newEditingQuantities[itemKey];
    setEditingQuantities(newEditingQuantities);

    // Kiểm tra giá trị có phải là số hợp lệ không
    const newValue = parseInt(value, 10);
    if (!isNaN(newValue) && newValue > 0) {
      // Cập nhật trực tiếp số lượng và gọi API ngay lập tức
      console.log("Cập nhật số lượng khi blur:", newValue);

      // Cập nhật UI ngay lập tức
      dispatch(
        updateQuantity({ productId, quantity: newValue, variant_details })
      );

      // Gọi API để cập nhật số lượng mới
      updateQuantityWithServer(productId, variant_details, newValue);
    } else {
      // Nếu giá trị không hợp lệ, đặt lại về 1
      console.log("Giá trị không hợp lệ, đặt về 1");
      dispatch(updateQuantity({ productId, quantity: 1, variant_details }));
      updateQuantityWithServer(productId, variant_details, 1);
    }
  };

  // Thêm hàm refreshToken
  const refreshToken = async () => {
    try {
      const refreshTokenValue = localStorage.getItem("refreshToken");
      if (!refreshTokenValue) {
        return null;
      }

      const response = await axios.post(
        "http://127.0.0.1:8000/api/users/refresh-token",
        {
          refresh_token: refreshTokenValue,
        },
        {
          headers: {
            "Content-Type": "application/json",
            Accept: "application/json",
          },
        }
      );

      if (response.data && response.data.status === "success") {
        // Lưu token mới
        localStorage.setItem("authToken", response.data.data.access_token);
        localStorage.setItem("access_token", response.data.data.access_token);

        // Kích hoạt sự kiện để thông báo token đã được làm mới
        window.dispatchEvent(new Event("storage"));
        window.dispatchEvent(new CustomEvent("auth-change"));

        return response.data.data.access_token;
      }
      return null;
    } catch (error) {
      console.error("Lỗi khi refresh token:", error);
      return null;
    }
  };

  // Thêm useEffect để kiểm tra token khi component được tải
  useEffect(() => {
    const checkAuthentication = async () => {
      const token = localStorage.getItem("authToken");
      if (!token) {
        // Nếu không có token, chuyển hướng đến trang đăng nhập
        navigate("/signin", { state: { returnUrl: location.pathname } });
        return;
      }

      try {
        // Kiểm tra token có hợp lệ không
        const response = await axios.get("http://localhost:8000/api/cart", {
          headers: {
            Authorization: `Bearer ${token}`,
            Accept: "application/json",
          },
        });

        // Nếu thành công, token vẫn còn hợp lệ
        console.log("Token hợp lệ, tiếp tục sử dụng");
      } catch (error) {
        if (error.response && error.response.status === 401) {
          console.log("Token hết hạn, thử refresh token");

          // Thử refresh token
          const newToken = await refreshToken();
          if (!newToken) {
            // Nếu không thể refresh token, đăng xuất và chuyển hướng
            localStorage.removeItem("authToken");
            localStorage.removeItem("access_token");
            localStorage.removeItem("userData");
            localStorage.removeItem("refreshToken");

            navigate("/signin", { state: { returnUrl: location.pathname } });
          } else {
            console.log("Đã refresh token thành công");
          }
        }
      }
    };

    checkAuthentication();
  }, []);

  // Hàm hiển thị toast
  const showToastMessage = (message) => {
    setToastMessage(message);
    setShowToast(true);
    setTimeout(() => {
      setShowToast(false);
    }, 5000);
  };

  // Kiểm tra tất cả sản phẩm trong giỏ hàng khi component mount
  useEffect(() => {
    const validateCartItems = async () => {
      if (!cart.items || cart.items.length === 0) return;

      const token = localStorage.getItem("authToken");
      if (!token) return;

      try {
        // Sử dụng callApiWithRefresh thay vì gọi axios trực tiếp
        const getCartData = async (currentToken) => {
          return axios.get("http://localhost:8000/api/cart", {
            headers: {
              Authorization: `Bearer ${currentToken}`,
              Accept: "application/json",
            },
          });
        };

        // Gọi API với xử lý refresh token
        let response;
        try {
          response = await callApiWithRefresh(getCartData);
        } catch (apiError) {
          console.error("Lỗi khi lấy dữ liệu giỏ hàng:", apiError);
          // Nếu lỗi liên quan đến xác thực, đã được xử lý trong callApiWithRefresh
          if (apiError.response?.status === 500) {
            console.error(
              "Lỗi server 500 khi lấy giỏ hàng:",
              apiError.response?.data
            );
            setValidationError(true);
            showToastMessage(
              "Không thể kiểm tra giỏ hàng do lỗi server. Vui lòng làm mới trang sau."
            );
            // Thử lại sau 30 giây
            setTimeout(() => {
              setValidationError(false);
              validateCartItems();
            }, 30000);
          }
          // Nếu không lấy được dữ liệu giỏ hàng, vẫn tiếp tục với dữ liệu local
          response = { data: { items: [] } };
        }

        // Lấy danh sách sản phẩm cần kiểm tra từ store (Redux)
        const productsToCheck = cart.items.map((item) => ({
          productId: item.product.id,
          variant_details: item.variant_details,
          name: item.product.name,
        }));

        if (productsToCheck.length === 0) return;

        // Kiểm tra mỗi sản phẩm trong giỏ hàng xem có tồn tại không
        const invalidItems = [];

        // Sử dụng Promise.allSettled thay vì Promise.all để không dừng khi có lỗi
        const checkResults = await Promise.allSettled(
          productsToCheck.map(async (item) => {
            try {
              // Kiểm tra sản phẩm có tồn tại không
              await axios.get(
                `http://localhost:8000/api/products/${item.productId}`,
                {
                  headers: {
                    Accept: "application/json",
                  },
                  timeout: 5000, // Thêm timeout để tránh chờ quá lâu
                }
              );
              return { valid: true, item };
            } catch (error) {
              console.log(
                `Kiểm tra sản phẩm ${item.productId}:`,
                error.message
              );

              if (
                error.response &&
                (error.response.status === 404 || error.response.status === 500)
              ) {
                // Nếu sản phẩm không tồn tại, thêm vào danh sách
                return { valid: false, item };
              }
              // Nếu lỗi khác (như timeout), coi như sản phẩm vẫn hợp lệ
              return { valid: true, item };
            }
          })
        );

        // Xử lý kết quả kiểm tra
        checkResults.forEach((result) => {
          if (result.status === "fulfilled" && result.value.valid === false) {
            invalidItems.push(result.value.item);
          }
        });

        // Xóa các sản phẩm không tồn tại
        if (invalidItems.length > 0) {
          const invalidNames = invalidItems
            .map((item) => item.name || `Sản phẩm #${item.productId}`)
            .join(", ");
          showToastMessage(
            `Đã xóa ${invalidItems.length} sản phẩm không tồn tại: ${invalidNames}`
          );

          // Xóa sản phẩm khỏi Redux store và server
          for (const item of invalidItems) {
            // Xóa khỏi Redux store
            dispatch(
              removeFromCart({
                productId: item.productId,
                variant_details: item.variant_details,
              })
            );

            // Xóa khỏi danh sách đã chọn
            setLocalSelectedItems((prev) =>
              prev.filter(
                (selectedItem) =>
                  !(
                    selectedItem.productId === item.productId &&
                    JSON.stringify(selectedItem.variant_details) ===
                      JSON.stringify(item.variant_details)
                  )
              )
            );

            // Xóa khỏi server - sử dụng try/catch riêng để không ảnh hưởng đến UI nếu có lỗi
            try {
              const preparedVariantDetails = Array.isArray(item.variant_details)
                ? item.variant_details
                : item.variant_details
                ? [item.variant_details]
                : null;

              // Định nghĩa hàm xóa sản phẩm
              const removeCartItem = async (currentToken) => {
                return axios.delete(
                  `http://localhost:8000/api/cart/remove/${item.productId}`,
                  {
                    headers: {
                      Authorization: `Bearer ${currentToken}`,
                      "Content-Type": "application/json",
                    },
                    data: {
                      variant_details: preparedVariantDetails,
                    },
                  }
                );
              };

              // Gọi API với refresh token nếu cần
              callApiWithRefresh(removeCartItem).catch((removeError) => {
                console.error(
                  "Không thể xóa sản phẩm trên server:",
                  removeError
                );
              });
            } catch (removeError) {
              console.error(
                "Lỗi khi chuẩn bị xóa sản phẩm trên server:",
                removeError
              );
            }
          }
        }
      } catch (error) {
        console.error("Lỗi khi kiểm tra giỏ hàng:", error);
        setValidationError(true);
        showToastMessage(
          "Không thể kiểm tra giỏ hàng. Vui lòng làm mới trang."
        );
      }
    };

    validateCartItems();
  }, [cart.items.length]);

  // Xử lý lỗi 500 (sản phẩm không tồn tại)
  const handleServerError = async (error, productId, variant_details) => {
    console.error("Lỗi server:", error);

    const errorMessage =
      error.response?.data?.message || "Lỗi server không xác định";
    console.error("Chi tiết lỗi:", errorMessage);

    // Kiểm tra nếu lỗi liên quan đến sản phẩm không tồn tại
    if (errorMessage.includes("property") && errorMessage.includes("null")) {
      // Sản phẩm không còn tồn tại trong database
      alert(
        "Sản phẩm này không còn tồn tại trên hệ thống. Sẽ được xóa khỏi giỏ hàng của bạn."
      );

      // Xóa sản phẩm khỏi giỏ hàng cục bộ
      dispatch(removeFromCart({ productId, variant_details }));

      // Xóa khỏi danh sách đã chọn
      setLocalSelectedItems(
        localSelectedItems.filter(
          (item) =>
            !(
              item.productId === productId &&
              JSON.stringify(item.variant_details) ===
                JSON.stringify(variant_details)
            )
        )
      );

      try {
        // Gọi API để xóa sản phẩm khỏi giỏ hàng trên server
        const token = localStorage.getItem("authToken");
        if (token) {
          // Chuẩn bị variant_details để gửi đến API
          const preparedVariantDetails = Array.isArray(variant_details)
            ? variant_details
            : variant_details
            ? [variant_details]
            : null;

          await axios.delete(
            `http://localhost:8000/api/cart/remove/${productId}`,
            {
              headers: {
                Authorization: `Bearer ${token}`,
                "Content-Type": "application/json",
              },
              data: {
                variant_details: preparedVariantDetails,
              },
            }
          );
          console.log("Đã xóa sản phẩm không tồn tại khỏi giỏ hàng");
        }
      } catch (removeError) {
        console.error("Không thể xóa sản phẩm trên server:", removeError);
      }
    } else {
      // Lỗi server khác
      alert(`Lỗi server: ${errorMessage}. Vui lòng thử lại sau.`);
    }
  };

  // Thêm useEffect để xử lý unmount component, xóa các timeout còn lại
  useEffect(() => {
    return () => {
      // Xóa tất cả các timeout khi component unmount
      Object.values(updateTimeouts).forEach((timeoutId) => {
        clearTimeout(timeoutId);
      });
    };
  }, [updateTimeouts]);

  // Thêm useEffect để đồng bộ hóa giỏ hàng khi component mount
  useEffect(() => {
    const syncCartWithServer = async () => {
      try {
        const token = localStorage.getItem("authToken");
        if (!token) return;

        // Sử dụng callApiWithRefresh thay vì gọi axios trực tiếp
        const getCartData = async (currentToken) => {
          return axios.get("http://localhost:8000/api/cart", {
            headers: {
              Authorization: `Bearer ${currentToken}`,
              Accept: "application/json",
            },
          });
        };

        const response = await callApiWithRefresh(getCartData);

        if (response.data && response.data.items) {
          // So sánh số lượng trong Redux store với số lượng từ server
          const serverItems = response.data.items;
          const storeItems = cart.items;

          // Kiểm tra mỗi sản phẩm trong giỏ hàng
          for (const serverItem of serverItems) {
            const storeItem = storeItems.find(
              (item) =>
                item.product.id === serverItem.product.id &&
                JSON.stringify(item.variant_details) ===
                  JSON.stringify(serverItem.variant_details)
            );

            // Nếu có sản phẩm trong cả server và store, nhưng số lượng khác nhau
            if (storeItem && storeItem.quantity !== serverItem.quantity) {
              console.log(`Đồng bộ số lượng của sản phẩm ${serverItem.product.name}: 
                Server: ${serverItem.quantity}, Local: ${storeItem.quantity}`);

              // Cập nhật số lượng trong Redux store theo số lượng từ server
              dispatch(
                updateQuantity({
                  productId: serverItem.product.id,
                  quantity: serverItem.quantity,
                  variant_details: serverItem.variant_details,
                })
              );
            }
          }
        }
      } catch (error) {
        console.error("Lỗi khi đồng bộ giỏ hàng:", error);
      }
    };

    // Gọi hàm đồng bộ khi component mount
    syncCartWithServer();
  }, []);

  return (
    <div className="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 py-12 mt-20">
      <Toaster position="top-right" />
      <div className="max-w-6xl mx-auto px-4 mt-12">
        <h1 className="text-3xl font-bold mb-8 bg-gradient-to-r from-gray-900 to-gray-700 bg-clip-text text-transparent">
          Giỏ hàng của bạn
        </h1>

        {/* Toast notification */}
        {showToast && (
          <div className="fixed top-5 right-5 z-50 max-w-sm">
            <div
              className={`${
                validationError
                  ? "bg-red-100 border-red-500"
                  : "bg-green-100 border-green-500"
              } border-l-4 p-4 rounded-lg shadow-lg`}
            >
              <div className="flex items-start">
                <div
                  className={`${
                    validationError ? "text-red-500" : "text-green-500"
                  } p-2`}
                >
                  <svg
                    xmlns="http://www.w3.org/2000/svg"
                    className="h-6 w-6"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                  >
                    {validationError ? (
                      <path
                        strokeLinecap="round"
                        strokeLinejoin="round"
                        strokeWidth={2}
                        d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                      />
                    ) : (
                      <path
                        strokeLinecap="round"
                        strokeLinejoin="round"
                        strokeWidth={2}
                        d="M5 13l4 4L19 7"
                      />
                    )}
                  </svg>
                </div>
                <div className="ml-3">
                  <p
                    className={`text-sm ${
                      validationError ? "text-red-700" : "text-green-700"
                    }`}
                  >
                    {toastMessage}
                  </p>
                </div>
                <div className="ml-auto pl-3">
                  <div className="-mx-1.5 -my-1.5">
                    <button
                      onClick={() => setShowToast(false)}
                      className={`inline-flex rounded-md p-1.5 ${
                        validationError
                          ? "text-red-500 hover:bg-red-100"
                          : "text-green-500 hover:bg-green-100"
                      } focus:outline-none`}
                    >
                      <span className="sr-only">Dismiss</span>
                      <svg
                        className="h-5 w-5"
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 20 20"
                        fill="currentColor"
                      >
                        <path
                          fillRule="evenodd"
                          d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                          clipRule="evenodd"
                        />
                      </svg>
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        )}

        {/* Thông báo khi server bận */}
        {isServerBusy && (
          <div className="bg-orange-100 border-l-4 border-orange-500 text-orange-700 p-4 mb-4 rounded shadow-sm">
            <div className="flex items-center">
              <svg
                className="h-6 w-6 text-orange-500 mr-3"
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
              >
                <path
                  strokeLinecap="round"
                  strokeLinejoin="round"
                  strokeWidth="2"
                  d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
                />
              </svg>
              <p className="font-medium">
                Hệ thống đang bận, vui lòng đợi vài giây trước khi thay đổi số
                lượng sản phẩm
              </p>
            </div>
          </div>
        )}

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
                                disabled={isUpdatingQuantity || isServerBusy}
                                className={`w-8 h-8 flex items-center justify-center border border-gray-300 rounded-l-lg transition-all duration-300 active:scale-95 ${
                                  isUpdatingQuantity || isServerBusy
                                    ? "bg-gray-100 text-gray-400 cursor-not-allowed"
                                    : "hover:bg-gradient-to-r hover:from-gray-900 hover:to-gray-700 hover:text-white hover:border-transparent"
                                }`}
                              >
                                -
                              </button>
                              <input
                                type="text"
                                value={
                                  editingQuantities[
                                    `${item.product.id}-${JSON.stringify(
                                      item.variant_details
                                    )}`
                                  ] !== undefined
                                    ? editingQuantities[
                                        `${item.product.id}-${JSON.stringify(
                                          item.variant_details
                                        )}`
                                      ]
                                    : item.quantity
                                }
                                onChange={(e) => {
                                  handleInputQuantityChange(
                                    item.product.id,
                                    item.variant_details,
                                    e.target.value
                                  );
                                }}
                                onBlur={(e) => {
                                  handleInputQuantityBlur(
                                    item.product.id,
                                    item.variant_details,
                                    e.target.value
                                  );
                                }}
                                onKeyDown={(e) => {
                                  if (e.key === "Enter") {
                                    e.preventDefault();
                                    e.target.blur();
                                    handleInputQuantityBlur(
                                      item.product.id,
                                      item.variant_details,
                                      e.target.value
                                    );
                                  }
                                }}
                                disabled={isUpdatingQuantity || isServerBusy}
                                className={`w-12 h-8 flex items-center justify-center border-t border-b border-gray-300 font-medium text-center ${
                                  isUpdatingQuantity
                                    ? "bg-gray-50 animate-pulse"
                                    : "bg-white"
                                } ${isServerBusy ? "bg-orange-50" : ""}`}
                              />
                              <button
                                onClick={() =>
                                  handleUpdateQuantity(item, item.quantity + 1)
                                }
                                disabled={isUpdatingQuantity || isServerBusy}
                                className={`w-8 h-8 flex items-center justify-center border border-gray-300 rounded-r-lg transition-all duration-300 active:scale-95 ${
                                  isUpdatingQuantity || isServerBusy
                                    ? "bg-gray-100 text-gray-400 cursor-not-allowed"
                                    : "hover:bg-gradient-to-r hover:from-gray-900 hover:to-gray-700 hover:text-white hover:border-transparent"
                                }`}
                                className="w-8 h-8 flex items-center justify-center border border-gray-300 rounded-r-lg hover:bg-gradient-to-r hover:from-gray-900 hover:to-gray-700 hover:text-white hover:border-transparent transition-all duration-300 active:scale-95 disabled:opacity-50"
                                disabled={isUpdatingQuantity}
                              >
                                +
                              </button>
                            </div>
                            {stockErrors[
                              `${item.product.id}-${JSON.stringify(
                                item.variant_details
                              )}`
                            ] && (
                              <p className="text-red-500 text-xs mt-1 text-center">
                                {
                                  stockErrors[
                                    `${item.product.id}-${JSON.stringify(
                                      item.variant_details
                                    )}`
                                  ]
                                }
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
                  <div className="border-t border-gray-100 pt-4 space-y-3">
                    <div className="flex justify-between text-gray-600">
                      <span>
                        Đã chọn ({localSelectedItems.length} sản phẩm)
                      </span>
                      <span>{formatPrice(calculateSelectedTotal())}</span>
                    </div>

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
