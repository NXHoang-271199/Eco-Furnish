import { useState, useEffect } from "react";
import { Link, useParams, useNavigate, useLocation } from "react-router-dom";
import axios from "axios";
import { useDispatch } from "react-redux";
import { addToCart, updateQuantity } from "../../../store/cartSlice";
import AddToCartToast from "../../../components/AddToCartToast";
import { Toaster, toast } from "react-hot-toast";
import { CiImageOn } from "react-icons/ci";
import { MdOutlinePayments, MdOutlineInsertComment } from "react-icons/md";
import { RiShieldKeyholeLine } from "react-icons/ri";

// Hàm phân tích thông tin biến thể từ SKU
const parseVariantFromSku = (sku) => {
  // Ví dụ chuỗi SKU: BG-NAVU-111 (Nâu gỗ - Vừa)
  // BG-DELO-101 (Đen - Lớn)
  if (!sku || typeof sku !== "string") return null;

  // Mẫu: [tiền tố]-[mã màu][mã kích thước]-[mã số]
  const parts = sku.split("-");
  if (parts.length < 3) return null;

  const colorSizeCode = parts[1]; // NAVU, NALO, DEVU, DELO

  // 2 ký tự đầu là mã màu (NA: Nâu, DE: Đen)
  // 2 ký tự sau là mã kích thước (VU: Vừa, LO: Lớn)
  const colorCode = colorSizeCode.substring(0, 2);
  const sizeCode = colorSizeCode.substring(2, 4);

  // Ánh xạ mã màu
  const colorMap = {
    NA: "Nâu gỗ",
    DE: "Đen",
    TR: "Trắng",
    XA: "Xám",
    VA: "Vàng",
  };

  // Ánh xạ mã kích thước
  const sizeMap = {
    VU: "Vừa",
    LO: "Lớn",
    NH: "Nhỏ",
    TB: "Trung bình",
  };

  const color = colorMap[colorCode] || colorCode;
  const size = sizeMap[sizeCode] || sizeCode;

  return {
    color,
    size,
    colorCode,
    sizeCode,
  };
};

const ProductDetail = () => {
  // State quản lý thông tin sản phẩm, bình luận, trạng thái tải, và người dùng
  const [product, setProduct] = useState(null);
  const [comments, setComments] = useState([]);
  const [loading, setLoading] = useState(true);
  const [commentInput, setCommentInput] = useState("");
  const [isLoggedIn, setIsLoggedIn] = useState(false);
  const [currentUser, setCurrentUser] = useState(null);
  const [selectedImage, setSelectedImage] = useState(null);
  const [selectedVariants, setSelectedVariants] = useState({});
  const [quantity, setQuantity] = useState(1);
  const [error, setError] = useState("");
  const [currentPrice, setCurrentPrice] = useState(null);
  const [currentDiscount, setCurrentDiscount] = useState(null);

  // Hook lấy ID sản phẩm từ URL
  const { id } = useParams();
  const navigate = useNavigate();
  const location = useLocation();
  const dispatch = useDispatch();

  // State hiển thị thông báo và phản hồi từ hệ thống
  const [showToast, setShowToast] = useState(false);
  const [toastProduct, setToastProduct] = useState(null);
  const [showFeedback, setShowFeedback] = useState(false);
  const [feedbackMessage, setFeedbackMessage] = useState("");
  const [feedbackType, setFeedbackType] = useState("success"); // success hoặc error

  // State quản lý loại biến thể và giá trị của sản phẩm
  const [variantTypes, setVariantTypes] = useState([]);
  const [variantValues, setVariantValues] = useState([]);

  // State quản lý tổng tiền và trạng thái cập nhật số lượng sản phẩm
  const [orderTotal, setOrderTotal] = useState(0);
  const [isUpdatingQuantity, setIsUpdatingQuantity] = useState(false);
  const [stockError, setStockError] = useState("");

  // Kiểm tra đăng nhập và lấy thông tin người dùng từ localStorage hoặc API
  const checkAuthentication = async () => {
    await new Promise((resolve) => setTimeout(resolve, 100)); // Độ trễ để đảm bảo token được lưu

    const token = localStorage.getItem("authToken");
    // console.log("Current token:", token);

    const userDataStr = localStorage.getItem("userData");
    if (userDataStr) {
      try {
        const userData = JSON.parse(userDataStr);
        // console.log("Loaded user data from localStorage:", userData);
        setCurrentUser(userData);
        setIsLoggedIn(true);
      } catch (error) {
        console.error("Error parsing userData from localStorage:", error);
      }
    }

    if (token) {
      const success = await fetchCurrentUser(token);
      if (!success) {
        localStorage.removeItem("userData");
      }
    } else {
      setIsLoggedIn(false);
      setCurrentUser(null);
    }
  };
  ///////////////////////////////////////////////////////////////////////////

  // useEffect để tải dữ liệu sản phẩm và bình luận khi component mount hoặc khi ID sản phẩm thay đổi
  useEffect(() => {
    const fetchProduct = async () => {
      try {
        const productResponse = await axios.get(
          `http://localhost:8000/api/products/${id}`
        );
        console.log("Product Data:", productResponse.data.data);

        const productData = productResponse.data.data;

        // Thiết lập giá mặc định từ biến thể đầu tiên
        if (productData.variants && productData.variants.length > 0) {
          const firstVariant = productData.variants[0];
          setCurrentPrice(firstVariant.price);
          setCurrentDiscount(firstVariant.discount_price);

          // Thiết lập biến thể mặc định từ variant_details của biến thể đầu tiên
          if (firstVariant.variant_details) {
            const defaultVariants = {};
            firstVariant.variant_details.forEach((detail, index) => {
              // index + 1 vì chúng ta đang sử dụng 1 cho màu sắc và 2 cho kích thước
              defaultVariants[index + 1] = detail.value;
            });
            setSelectedVariants(defaultVariants);
          }
        }

        setProduct(productResponse.data.data);
        setLoading(false);

        // Lấy danh sách bình luận của sản phẩm
        const commentsResponse = await axios.get(
          `http://localhost:8000/api/products/${id}/comments`
        );
        setComments(commentsResponse.data.data || []);
      } catch (error) {
        console.error("Error fetching product:", error);
        setLoading(false);
      }
    };

    fetchProduct();
  }, [id]);

  // Kiểm tra xác thực khi thay đổi tab hoặc quay lại trang
  useEffect(() => {
    const handleVisibilityChange = () => {
      if (document.visibilityState === "visible") {
        refreshProductData();
        checkAuthentication();
      }
    };

    document.addEventListener("visibilitychange", handleVisibilityChange);
    return () => {
      document.removeEventListener("visibilitychange", handleVisibilityChange);
    };
  }, [id]);

  // Kiểm tra đăng nhập khi thay đổi trang
  useEffect(() => {
    checkAuthentication();
  }, [location]);

  // Lắng nghe thay đổi của localStorage để cập nhật trạng thái đăng nhập
  useEffect(() => {
    const handleStorageChange = (e) => {
      console.log("Storage changed:", e);
      if (e.key === "authToken" || e.key === "userData") {
        setTimeout(() => {
          checkAuthentication();
        }, 200);
      }

      if (e.key === "returnPath" && e.newValue === location.pathname) {
        setTimeout(() => {
          checkAuthentication();
          localStorage.removeItem("returnPath");
        }, 200);
      }
    };

    window.addEventListener("storage", handleStorageChange);
    window.addEventListener("auth-change", checkAuthentication);

    setTimeout(checkAuthentication, 300);

    return () => {
      window.removeEventListener("storage", handleStorageChange);
      window.removeEventListener("auth-change", checkAuthentication);
    };
  }, []);
  // //////////////////////////////////////////////////////////////////////////////

  // Hàm lấy thông tin người dùng từ API
  const fetchCurrentUser = async (token) => {
    try {
      // console.log("Fetching user with token:", token);
      const response = await axios.get("http://127.0.0.1:8000/api/users", {
        headers: { Authorization: `Bearer ${token}` },
      });

      if (response.data?.data) {
        setCurrentUser(response.data.data);
        setIsLoggedIn(true);
        return true;
      } else {
        setIsLoggedIn(false);
        setCurrentUser(null);
        localStorage.removeItem("authToken");
        return false;
      }
    } catch (error) {
      console.error("Error fetching user:", error.response || error);
      if (error.response?.status === 401 || error.response?.status === 404) {
        setIsLoggedIn(false);
        setCurrentUser(null);
        localStorage.removeItem("authToken");
      }
      return false;
    }
  };

  // Kiểm tra nếu đang tải dữ liệu sản phẩm
  if (loading) {
    return <div className="text-center mt-32">Đang tải...</div>;
  }

  // Hiển thị thông báo khi không tìm thấy sản phẩm
  if (!product) {
    return <div className="text-center mt-32">Không tìm thấy sản phẩm</div>;
  }

  // console.log("Product state:", product);

  // hien thi thong tin san pham (gia)
  const formatPrice = (price) => {
    const numericPrice = price ? parseFloat(price) : null;
    return numericPrice && !isNaN(numericPrice)
      ? numericPrice.toLocaleString("vi-VN", { minimumFractionDigits: 0 }) + "đ"
      : "Giá không khả dụng";
  };
  ///////////////////////////////////////////////////////////////
  const mainImageUrl =
    selectedImage ||
    (product.image_thumnail
      ? product.image_thumnail.startsWith("http")
        ? product.image_thumnail
        : `http://localhost:8000/storage/${product.image_thumnail}`
      : "https://via.placeholder.com/400x400?text=No+Image");

  console.log("Main Image URL:", mainImageUrl);

  if (product.gallery) {
    product.gallery.forEach((item, index) => {
      const galleryImageUrl = item.image_url
        ? item.image_url.startsWith("http")
          ? item.image_url
          : `http://localhost:8000/storage/${item.image_url}`
        : "https://via.placeholder.com/100x100?text=No+Image";

      console.log(`Gallery Image ${index + 1} URL:`, galleryImageUrl);
    });
  }

  const handleGalleryImageClick = (imageUrl) => {
    setSelectedImage(imageUrl);
  };
  ///////////////////////////////////////////////////////////////

  // Sửa lại hàm handleVariantSelect để cập nhật giá chính xác
  const handleVariantSelect = (variantType, value) => {
    setSelectedVariants((prev) => ({
      ...prev,
      [variantType]: value,
    }));

    // Tìm biến thể phù hợp với lựa chọn hiện tại
    if (product.variants) {
      const selectedVariant = product.variants.find((variant) => {
        const variantDetails = variant.variant_details;
        const colorMatch =
          variantDetails[0]?.value ===
          (variantType === 1 ? value : selectedVariants[1]);
        const sizeMatch =
          variantDetails[1]?.value ===
          (variantType === 2 ? value : selectedVariants[2]);
        return colorMatch && sizeMatch;
      });

      if (selectedVariant) {
        setCurrentPrice(selectedVariant.price);
        setCurrentDiscount(selectedVariant.discount_price);
        console.log("Giá được cập nhật:", {
          price: selectedVariant.price,
          discount: selectedVariant.discount_price,
        });
      }
    }
  };

  // Hàm thêm sản phẩm vào giỏ hàng
  const handleAddToCart = async () => {
    try {
      // Kiểm tra xem đã chọn đủ biến thể chưa
      if (!selectedVariants[1] || !selectedVariants[2]) {
        toast.error("Vui lòng chọn đầy đủ màu sắc và kích thước", {
          duration: 2000,
          style: {
            background: "#fff",
            color: "#e11d48",
            border: "1px solid #fecdd3",
          },
        });
        return;
      }

      // Tìm biến thể phù hợp
      const selectedVariant = product.variants.find((variant) => {
        const variantDetails = variant.variant_details;
        return (
          variantDetails[0]?.value === selectedVariants[1] &&
          variantDetails[1]?.value === selectedVariants[2]
        );
      });

      if (!selectedVariant) {
        toast.error("Không tìm thấy biến thể phù hợp", {
          duration: 2000,
          style: {
            background: "#fff",
            color: "#e11d48",
            border: "1px solid #fecdd3",
          },
        });
        return;
      }

      // Tính toán giá cuối cùng
      const finalPrice =
        selectedVariant.discount_price || selectedVariant.price;

      // Tạo đối tượng cart item với thông tin đầy đủ
      const cartItem = {
        product_id: product.id,
        product: {
          ...product,
          price: finalPrice,
        },
        quantity: quantity,
        price: finalPrice * quantity,
        variant_details: selectedVariants.variant_details,
      };

      // Kiểm tra token trong localStorage (DM CAY VL)
      const token = localStorage.getItem("authToken");
      console.log("Current token:", token); // Log để debug

      if (!token) {
        console.log("No token found, redirecting to signin");
        navigate("/sign-in", { state: { returnUrl: location.pathname } });
        return;
      }

      // Hiển thị thông báo đang xử lý
      const toastId = toast.loading("Đang thêm vào giỏ hàng...");

      // Gửi request đến API thêm sản phẩm vào giỏ hàng
      const response = await axios.post(
        "http://localhost:8000/api/cart/add",
        {
          product_id: product.id,
          product_variant_id: selectedVariant.id,
          quantity: quantity,
        },
        {
          headers: {
            Authorization: `Bearer ${token}`,
            Accept: "application/json",
            "Content-Type": "application/json",
          },
        }
      );

      console.log("API Response:", response.data);

      // Cập nhật toast thành công
      toast.success("Đã thêm vào giỏ hàng", {
        id: toastId,
        duration: 2000,
        style: {
          background: "#fff",
          color: "#059669",
          border: "1px solid #a7f3d0",
        },
      });
    } catch (error) {
      console.error("Error adding to cart:", error);

      //Log thông tin chi tiết về lỗi
      if (error.response) {
        console.log("Error response status:", error.response.status);
        console.log("Error response data:", error.response.data);
      } else if (error.request) {
        console.log("Error request:", error.request);
      } else {
        console.log("Error message:", error.message);
      }

      if (error.response?.status === 401) {
        console.log("Token expired or invalid, redirecting to signin");
        localStorage.clear();
        toast.error("Phiên đăng nhập hết hạn, vui lòng đăng nhập lại", {
          duration: 3000,
          style: {
            background: "#fff",
            color: "#e11d48",
            border: "1px solid #fecdd3",
          },
        });
        navigate("/sign-in", { state: { returnUrl: location.pathname } });
        return;
      }

      // Hiển thị thông báo lỗi chi tiết
      const errorMessage =
        error.response?.data?.message ||
        (error.response?.status === 500
          ? "Lỗi máy chủ, vui lòng thử lại sau"
          : error.message || "Có lỗi xảy ra khi thêm vào giỏ hàng");

      toast.error(errorMessage, {
        duration: 3000,
        style: {
          background: "#fff",
          color: "#e11d48",
          border: "1px solid #fecdd3",
        },
      });
    }

    // Tính toán giá cuối cùng
    // const finalPrice =
    //   selectedVariants.discount_price || selectedVariants.price;

    // Tạo đối tượng cart item với thông tin đầy đủ
    // const cartItem = {
    //   product_id: product.id,
    //   product: {
    //     ...product,
    //     price: finalPrice,
    //   },
    //   quantity: quantity,
    //   price: finalPrice * quantity,
    //   variant_details: selectedVariants.variant_details,
    // };

    // try {
    //   // Kiểm tra token trong localStorage
    //   const token = localStorage.getItem("authToken"); // Thay đổi từ "token" thành "authToken"
    //   if (!token) {
    //     navigate("/sign-in", { state: { returnUrl: location.pathname } });
    //     return;
    //   }

    //   const response = await axios.post(
    //     "http://localhost:8000/api/cart/add",
    //     {
    //       product_id: product.id,
    //       product_variant_id: selectedVariants.id,
    //       quantity: quantity,
    //     },
    //     {
    //       headers: {
    //         Authorization: `Bearer ${token}`,
    //       },
    //     }
    //   );

    //   if (response.data.message === "Thêm vào giỏ hàng thành công") {
    //     // Thêm vào Redux store
    //     dispatch(addToCart(cartItem));
    //     toast.success("Đã thêm vào giỏ hàng", {
    //       duration: 2000,
    //       style: {
    //         background: "#fff",
    //         color: "#059669",
    //         border: "1px solid #a7f3d0",
    //       },
    //     });
    //   }
    // } catch (error) {
    //   if (error.response?.status === 401) {
    //     // Token hết hạn hoặc không hợp lệ
    //     localStorage.removeItem("authToken");
    //     navigate("/sign-in", { state: { returnUrl: location.pathname } });
    //     return;
    //   }
    //   toast.error(
    //     error.response?.data?.message || "Có lỗi xảy ra khi thêm vào giỏ hàng",
    //     {
    //       duration: 2000,
    //       style: {
    //         background: "#fff",
    //         color: "#e11d48",
    //         border: "1px solid #fecdd3",
    //       },
    //     }
    //   );
    // }
  };

  // Hàm tạo đơn hàng mua ngay
  const handleBuyNow = () => {
    // Kiểm tra xem sản phẩm có biến thể không và đã chọn biến thể chưa
    const hasVariants = product.variants && product.variants.length > 0;

    if (hasVariants && (!selectedVariants[1] || !selectedVariants[2])) {
      toast.error("Vui lòng chọn đầy đủ màu sắc và kích thước", {
        duration: 2000,
        style: {
          background: "#fff",
          color: "#e11d48",
          border: "1px solid #fecdd3",
        },
      });
      return;
    }

    handleAddToCart();
    navigate("/payment");
  };

  const handleQuantityChange = (newQuantity) => {
    if (newQuantity < 1) return;

    setIsUpdatingQuantity(true);
    setStockError("");

    try {
      // Tìm biến thể phù hợp
      if (!product || !product.variants) {
        setStockError("Không tìm thấy thông tin sản phẩm");
        return;
      }

      const selectedVariant = product.variants.find((variant) => {
        if (!variant.variant_details) return false;
        const variantDetails = variant.variant_details;
        return (
          variantDetails[0]?.value === selectedVariants[1] &&
          variantDetails[1]?.value === selectedVariants[2]
        );
      });

      if (!selectedVariant) {
        setStockError("Không tìm thấy biến thể phù hợp");
        return;
      }

      // Kiểm tra tồn kho cục bộ
      if (selectedVariant.quantity >= newQuantity) {
        setQuantity(newQuantity);
      } else {
        setStockError(`Chỉ còn ${selectedVariant.quantity} sản phẩm trong kho`);
      }
    } catch (error) {
      console.error("Lỗi xử lý:", error);
      setStockError("Có lỗi xảy ra khi cập nhật số lượng");
    } finally {
      setIsUpdatingQuantity(false);
    }
  };

  // Sửa lại hàm increaseQuantity và decreaseQuantity để sử dụng handleQuantityChange mới
  const increaseQuantity = () => {
    handleQuantityChange(quantity + 1);
  };

  const decreaseQuantity = () => {
    if (quantity > 1) {
      handleQuantityChange(quantity - 1);
    }
  };

  const handleCommentSubmit = async () => {
    // Kiểm tra trạng thái đăng nhập trước khi gửi bình luận
    await checkAuthentication();

    if (!isLoggedIn || !currentUser) {
      navigate("/sign-in", { state: { returnUrl: location.pathname } });
      return;
    }

    if (!commentInput.trim()) {
      return;
    }

    try {
      // Kiểm tra token trong localStorage
      const token = localStorage.getItem("authToken"); // Thay đổi từ "token" thành "authToken"
      if (!token) {
        setIsLoggedIn(false);
        setCurrentUser(null);
        navigate("/sign-in", { state: { returnUrl: location.pathname } });
        return;
      }

      // Lấy userData từ localStorage
      let userData = null;
      try {
        userData = JSON.parse(localStorage.getItem("userData") || "{}");
        console.log("Gửi bình luận với userData:", userData);
      } catch (e) {
        console.error("Lỗi parse userData:", e);
      }

      console.log("Gửi bình luận với token:", token);

      // API endpoint cho comments từ routes/api.php
      const response = await axios.post(
        `http://localhost:8000/api/comments`,
        {
          product_id: id,
          content: commentInput,
        },
        {
          headers: { Authorization: `Bearer ${token}` },
        }
      );

      console.log("Phản hồi từ API bình luận:", response.data);

      if (response.data && response.data.data) {
        // Thêm comment mới vào danh sách
        const newComment = {
          ...response.data.data,
          user: currentUser,
        };
        setComments([...comments, newComment]);
        setCommentInput("");

        // Hiển thị thông báo thành công
        setFeedbackMessage("Bình luận đã được gửi thành công!");
        setFeedbackType("success");
        setShowFeedback(true);

        // Ẩn thông báo sau 3 giây
        setTimeout(() => {
          setShowFeedback(false);
        }, 3000);

        // Refresh comments sau khi thêm
        setTimeout(() => {
          refreshProductComments();
        }, 500);
      }
    } catch (error) {
      console.error("Error submitting comment:", error.response || error);
      // Hiển thị thông báo lỗi
      setFeedbackMessage(
        "Không thể gửi bình luận: " +
          (error.response?.data?.message || "Lỗi kết nối")
      );
      setFeedbackType("error");
      setShowFeedback(true);

      // Ẩn thông báo sau 3 giây
      setTimeout(() => {
        setShowFeedback(false);
      }, 3000);

      if (error.response?.status === 401) {
        localStorage.removeItem("authToken"); // Sửa lại từ "token" thành "authToken"
        setIsLoggedIn(false);
        setCurrentUser(null);
        navigate("/sign-in", { state: { returnUrl: location.pathname } });
      }
    }
  };

  // Hàm chỉ refresh comments, không load lại toàn bộ sản phẩm
  const refreshProductComments = async () => {
    try {
      const timestamp = new Date().getTime();
      const commentsResponse = await axios.get(
        `http://localhost:8000/api/products/${id}/comments?_=${timestamp}`
      );
      setComments(commentsResponse.data.data || []);
    } catch (error) {
      console.error("Lỗi khi làm mới comments:", error);
    }
  };

  const isSampleProduct =
    product.is_sample === 1 ||
    product.is_sample === true ||
    product.status === "sample";

  const refreshProductData = async () => {
    setLoading(true);
    try {
      const timestamp = new Date().getTime();
      const response = await axios.get(
        `http://localhost:8000/api/products/${id}?_=${timestamp}`
      );
      console.log("Refreshed API Response:", response.data);
      let productData = response.data.data;

      setSelectedVariants({});
      setCurrentPrice(productData.price);
      setCurrentDiscount(productData.discount_price);

      if (productData.variants && Array.isArray(productData.variants)) {
        const variantsWithValues = productData.variants.map(
          (variant, index) => {
            console.log(
              `Biến thể thô thứ ${index + 1}:`,
              JSON.stringify(variant, null, 2)
            );

            // Kiểm tra variant_id
            let variantId = 0;
            if (variant.variant_id !== undefined)
              variantId = Number(variant.variant_id);
            else if (variant.type_id !== undefined)
              variantId = Number(variant.type_id);
            else if (variant.attribute_id !== undefined)
              variantId = Number(variant.attribute_id);
            else if (
              variant.variant_details &&
              typeof variant.variant_details === "object"
            ) {
              const keys = Object.keys(variant.variant_details);
              if (keys.length > 0) {
                console.log(
                  "Tìm thấy variant_details:",
                  variant.variant_details
                );
                // Sử dụng key đầu tiên trong variant_details làm variant_id
                variantId = Number(keys[0]);
                console.log(
                  "Đã trích xuất variant_id từ variant_details:",
                  variantId
                );
              }
            }

            // Kiểm tra variant_value_id
            let variantValueId = 0;
            if (variant.variant_value_id !== undefined)
              variantValueId = Number(variant.variant_value_id);
            else if (variant.value_id !== undefined)
              variantValueId = Number(variant.value_id);
            else if (variant.option_id !== undefined)
              variantValueId = Number(variant.option_id);
            else if (
              variant.variant_details &&
              typeof variant.variant_details === "object" &&
              variantId > 0
            ) {
              const valueId = variant.variant_details[variantId];
              if (valueId) {
                variantValueId = Number(valueId);
                console.log(
                  "Đã trích xuất variant_value_id từ variant_details:",
                  variantValueId
                );
              }
            }

            // Kiểm tra tên biến thể
            let variantName = "Không xác định";
            if (variant.variant_name) variantName = variant.variant_name;
            else if (variant.type_name) variantName = variant.type_name;
            else if (variant.attribute_name)
              variantName = variant.attribute_name;

            // Kiểm tra giá trị biến thể
            let variantValue = "Không xác định";
            if (variant.variant_value_name)
              variantValue = variant.variant_value_name;
            else if (variant.value_name) variantValue = variant.value_name;
            else if (variant.value) variantValue = variant.value;
            else if (variant.option_name) variantValue = variant.option_name;
            else if (variant.sku) {
              // Cố gắng trích xuất từ SKU
              const parsedVariant = parseVariantFromSku(variant.sku);
              if (parsedVariant) {
                if (variantId === 1) {
                  // Màu sắc
                  variantValue = parsedVariant.color;
                } else if (variantId === 2) {
                  // Kích thước
                  variantValue = parsedVariant.size;
                }
              }
            }

            // Tạo cấu trúc chuẩn của biến thể
            const processedVariant = {
              ...variant,
              id: variant.id || index + 1,
              variant_id: variantId,
              variant_name: variantName,
              variant_value_id: variantValueId,
              variant_value_name: variantValue,
              variant_value: {
                id: variantValueId,
                value: variantValue,
              },
              variant_info: {
                id: variantId,
                name: variantName,
              },
              quantity: Number(variant.quantity || 10),
              price: variant.price || productData.price,
              discount_price:
                variant.discount_price ||
                variant.price ||
                productData.discount_price ||
                productData.price,
              sku: variant.sku || `${productData.id}-variant-${index + 1}`,
            };

            console.log("Biến thể đã xử lý:", processedVariant);
            return processedVariant;
          }
        );

        // Thêm debugging
        console.log(
          "Tất cả biến thể sau khi xử lý:",
          JSON.stringify(variantsWithValues, null, 2)
        );

        // Kiểm tra lại một số biến thể
        if (variantsWithValues.length > 0) {
          console.log(
            "Biến thể đầu tiên sau xử lý - variant_id:",
            variantsWithValues[0].variant_id
          );
          console.log(
            "Biến thể đầu tiên sau xử lý - variant_info:",
            variantsWithValues[0].variant_info
          );
          console.log(
            "Biến thể đầu tiên sau xử lý - variant_value:",
            variantsWithValues[0].variant_value
          );
        }

        // Sắp xếp các biến thể theo loại
        variantsWithValues.sort((a, b) => a.variant_id - b.variant_id);

        // Thiết lập biến thể mặc định được chọn
        const defaultVariants = {};
        const variantTypes = [
          ...new Set(variantsWithValues.map((v) => Number(v.variant_id))),
        ].filter((id) => id > 0);

        console.log("Các loại biến thể (sau khi lọc):", variantTypes);

        // Chọn biến thể đầu tiên cho mỗi loại
        variantTypes.forEach((typeId) => {
          if (typeId === 0) return; // Bỏ qua biến thể có id = 0

          const availableVariants = variantsWithValues.filter(
            (v) => Number(v.variant_id) === typeId && Number(v.quantity) > 0
          );

          if (availableVariants.length > 0) {
            defaultVariants[typeId] = Number(
              availableVariants[0].variant_value_id
            );
            console.log(
              `Đã chọn biến thể mặc định cho loại ${typeId}:`,
              defaultVariants[typeId]
            );
          }
        });

        // Thiết lập giá dựa trên biến thể mặc định
        if (Object.keys(defaultVariants).length > 0) {
          // Tìm biến thể phù hợp với sự kết hợp của những loại đã chọn
          const findMatchingVariant = () => {
            // Nếu có biến thể loại 3 (kết hợp), tìm biến thể phù hợp
            const combinedVariants = variantsWithValues.filter(
              (v) => v.variant_id === 3
            );
            if (combinedVariants.length > 0) {
              const colorId = defaultVariants[1]; // ID giá trị màu sắc đã chọn
              const sizeId = defaultVariants[2]; // ID giá trị kích thước đã chọn

              const matchingVariant = combinedVariants.find(
                (v) => v.color_id === colorId && v.size_id === sizeId
              );

              if (matchingVariant) {
                return matchingVariant;
              }
            }

            // Nếu không tìm thấy biến thể kết hợp, sử dụng logic cũ
            const skuCandidates = new Set();

            Object.entries(defaultVariants).forEach(([typeId, valueId]) => {
              const matches = variantsWithValues.filter(
                (v) =>
                  v.variant_id === Number(typeId) &&
                  v.variant_value_id === valueId
              );

              matches.forEach((v) => skuCandidates.add(v.sku));
            });

            for (const sku of skuCandidates) {
              const matchingVariants = variantsWithValues.filter(
                (v) => v.sku === sku
              );
              if (matchingVariants.length > 0) {
                return matchingVariants[0];
              }
            }

            return null;
          };

          const bestVariant = findMatchingVariant();

          if (bestVariant && bestVariant.price) {
            setCurrentPrice(bestVariant.price);
            setCurrentDiscount(bestVariant.discount_price || bestVariant.price);
            console.log(
              `Giá ban đầu được thiết lập theo biến thể: ${bestVariant.price}`
            );
          }
        }

        setProduct({ ...productData, variants: variantsWithValues });
        setSelectedVariants(defaultVariants);
        console.log("Biến thể đã chọn mặc định:", defaultVariants);
      } else {
        console.error("Không thể tìm thấy biến thể sản phẩm");
        setProduct(productData);
      }

      const commentsResponse = await axios.get(
        `http://localhost:8000/api/products/${id}/comments?_=${timestamp}`
      );
      setComments(commentsResponse.data.data || []);
    } catch (error) {
      console.error("Error refreshing product data:", error);
    } finally {
      setLoading(false);
    }
  };

  // Render variants section
  const renderVariants = () => {
    if (!product.variants) return null;

    // Lấy danh sách unique các giá trị biến thể
    const uniqueColors = [
      ...new Set(product.variants.map((v) => v.variant_details[0]?.value)),
    ];
    const uniqueSizes = [
      ...new Set(product.variants.map((v) => v.variant_details[1]?.value)),
    ];

    return (
      <div className="space-y-6">
        {/* Color variants */}
        <div className="variant-section">
          <div className="flex items-center gap-2 mb-3">
            <span className="text-gray-700 font-medium">Màu sắc:</span>
            {selectedVariants[1] && (
              <span className="text-green-600 text-sm">
                Đã chọn: {selectedVariants[1]}
              </span>
            )}
          </div>
          <div className="flex flex-wrap gap-2">
            {uniqueColors.map((color, index) => (
              <button
                key={index}
                onClick={() => handleVariantSelect(1, color)}
                className={`
                  px-4 py-2 rounded-lg font-medium text-sm
                  transition-all duration-200 transform hover:scale-105
                  ${
                    selectedVariants[1] === color
                      ? "bg-green-50 text-green-700 border-2 border-green-500 shadow-sm"
                      : "bg-white text-gray-700 border border-gray-200 hover:border-gray-300 hover:bg-gray-50"
                  }
                `}
              >
                <div className="flex items-center gap-2">
                  {selectedVariants[1] === color && (
                    <svg
                      xmlns="http://www.w3.org/2000/svg"
                      className="h-4 w-4 text-green-500"
                      viewBox="0 0 20 20"
                      fill="currentColor"
                    >
                      <path
                        fillRule="evenodd"
                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                        clipRule="evenodd"
                      />
                    </svg>
                  )}
                  {color}
                </div>
              </button>
            ))}
          </div>
        </div>

        {/* Size variants */}
        <div className="variant-section">
          <div className="flex items-center gap-2 mb-3">
            <span className="text-gray-700 font-medium">Kích thước:</span>
            {selectedVariants[2] && (
              <span className="text-green-600 text-sm">
                Đã chọn: {selectedVariants[2]}
              </span>
            )}
          </div>
          <div className="flex flex-wrap gap-2">
            {uniqueSizes.map((size, index) => (
              <button
                key={index}
                onClick={() => handleVariantSelect(2, size)}
                className={`
                  min-w-[80px] px-4 py-2 rounded-lg font-medium text-sm
                  transition-all duration-200 transform hover:scale-105
                  ${
                    selectedVariants[2] === size
                      ? "bg-green-50 text-green-700 border-2 border-green-500 shadow-sm"
                      : "bg-white text-gray-700 border border-gray-200 hover:border-gray-300 hover:bg-gray-50"
                  }
                `}
              >
                <div className="flex items-center justify-center gap-2">
                  {selectedVariants[2] === size && (
                    <svg
                      xmlns="http://www.w3.org/2000/svg"
                      className="h-4 w-4 text-green-500"
                      viewBox="0 0 20 20"
                      fill="currentColor"
                    >
                      <path
                        fillRule="evenodd"
                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                        clipRule="evenodd"
                      />
                    </svg>
                  )}
                  {size}
                </div>
              </button>
            ))}
          </div>
        </div>

        {/* Divider line */}
        <div className="border-b border-gray-200 my-6"></div>
      </div>
    );
  };

  return (
    <>
      <Toaster position="top-right" />
      <main className="max-w-6xl mx-auto mb-20 mt-32">
        {/* Thiết kế mới cho chi tiết sản phẩm */}
        <div className="container mx-auto p-6">
          <div className="flex flex-col lg:flex-row">
            {/* Image Section */}
            <div className="lg:w-1/2">
              <div className="relative h-[650px] bg-gray-50 rounded-xl shadow-md flex justify-center items-center p-2 overflow-hidden border border-gray-100">
                {/* Gradient overlay effect */}
                <div className="absolute inset-0 bg-gradient-to-t from-gray-100 to-transparent opacity-20"></div>
                <img
                  src={mainImageUrl}
                  alt={product.name || "Sản phẩm"}
                  className="max-h-[620px] max-w-[95%] object-contain z-10 transition-all duration-500 hover:scale-105"
                  onError={(e) => {
                    console.log("Main Image Load Error:", e);
                    e.target.src =
                      "https://via.placeholder.com/600x600?text=Image+Error";
                  }}
                />

                {product.discount_price &&
                  Number(product.discount_price) !== Number(product.price) && (
                    <span className="absolute top-4 left-4 bg-red-500 text-white text-sm font-bold px-3 py-1.5 rounded-full z-20 shadow-lg transform -rotate-2">
                      GIẢM GIÁ
                    </span>
                  )}
              </div>

              {/* Gallery images */}
              <div className="mt-6">
                <h3 className="text-sm font-medium text-gray-600 mb-2 uppercase tracking-wide">
                  Thư viện ảnh
                </h3>
                <div className="grid grid-cols-5 gap-2">
                  {product.gallery && product.gallery.length > 0 ? (
                    product.gallery.map((item, index) => {
                      const galleryImageUrl = item.image_url
                        ? item.image_url.startsWith("http")
                          ? item.image_url
                          : `http://localhost:8000/storage/${item.image_url}`
                        : "https://via.placeholder.com/100x100?text=No+Image";

                      return (
                        <div
                          key={index}
                          className={`relative border-2 rounded-lg overflow-hidden cursor-pointer transition-all duration-300 ${
                            selectedImage === galleryImageUrl
                              ? "border-green-500 shadow-md"
                              : "border-gray-200 hover:border-gray-300"
                          }`}
                          onClick={() =>
                            handleGalleryImageClick(galleryImageUrl)
                          }
                        >
                          <div className="aspect-w-1 aspect-h-1">
                            <img
                              src={galleryImageUrl}
                              alt={`Ảnh ${index + 1}`}
                              className="w-full h-full object-cover"
                              onError={(e) => {
                                console.log(
                                  `Gallery Image ${index + 1} Load Error:`,
                                  e
                                );
                                e.target.src =
                                  "https://via.placeholder.com/100x100?text=Error";
                              }}
                            />
                          </div>
                          {selectedImage === galleryImageUrl && (
                            <div className="absolute inset-0 bg-green-500 bg-opacity-10 flex items-center justify-center">
                              <div className="w-4 h-4 rounded-full bg-green-500 flex items-center justify-center">
                                <span className="text-white text-[8px]">✓</span>
                              </div>
                            </div>
                          )}
                        </div>
                      );
                    })
                  ) : (
                    <div className="col-span-5 text-center py-4 bg-gray-50 rounded-lg border border-dashed border-gray-200">
                      <CiImageOn className="h-6 w-6 mx-auto text-gray-400 mb-1" />
                      <span className="text-sm text-gray-500">
                        Không có ảnh bổ sung
                      </span>
                    </div>
                  )}
                </div>
              </div>
            </div>

            {/* Details Section */}
            <div className="lg:w-1/2 lg:pl-10 mt-6 lg:mt-0">
              <h1 className="text-2xl md:text-3xl font-extrabold text-gray-800 leading-tight mb-4">
                {product.name || "Tên sản phẩm"}
              </h1>

              <div className="flex items-center mt-6">
                {currentDiscount && currentPrice && (
                  <>
                    <span className="text-gray-500 line-through mr-2">
                      {formatPrice(currentPrice)}
                    </span>
                    <span className="text-2xl font-semibold text-green-600">
                      {formatPrice(currentDiscount)}
                    </span>
                  </>
                )}
              </div>

              {isSampleProduct && (
                <p className="mt-4 text-red-500 font-medium">
                  Đây là sản phẩm mẫu không bán
                </p>
              )}

              <p className="mt-4 text-gray-600">
                {product.short_description || "Không có mô tả"}
              </p>

              {/* Variants Section */}
              <div className="mt-6 space-y-4">{renderVariants()}</div>

              {error && (
                <div className="mt-2 text-red-500 text-sm">{error}</div>
              )}

              {/* Quantity Section */}
              <div className="flex items-center mt-6">
                <div className="flex border border-gray-300 rounded-full overflow-hidden">
                  <button
                    className="bg-gray-200 text-gray-700 px-3 py-1 hover:bg-gray-300 transition"
                    onClick={decreaseQuantity}
                    disabled={quantity <= 1}
                  >
                    -
                  </button>
                  <input
                    type="text"
                    value={quantity}
                    readOnly
                    className="w-12 text-center border-x border-gray-200 bg-white"
                  />
                  <button
                    className="bg-gray-200 text-gray-700 px-3 py-1 hover:bg-gray-300 transition"
                    onClick={increaseQuantity}
                    disabled={isUpdatingQuantity}
                  >
                    +
                  </button>
                </div>
              </div>

              <div className="flex gap-2  mt-4">
                {/* Add to Cart and Wishlist Buttons - Same Row with Black Background */}

                <div className="flex gap-4 mt-4">
                  <button
                    className="w-full bg-gradient-to-r from-blue-500 to-blue-800 text-white py-3 px-6 rounded-full font-semibold shadow-md hover:shadow-lg transition-all duration-300 transform hover:scale-[1.01] flex items-center justify-center"
                    onClick={handleAddToCart}
                  >
                    THÊM VÀO GIỎ HÀNG
                  </button>
                </div>
                <div className="flex gap-4 mt-4">
                  <button
                    className="w-full bg-gradient-to-r from-blue-500 to-blue-800 text-white py-3 px-6 rounded-full font-semibold shadow-md hover:shadow-lg transition-all duration-300 transform hover:scale-[1.01] flex items-center justify-center"
                    onClick={handleBuyNow}
                  >
                    <MdOutlinePayments className="h-5 w-5 mr-2" />
                    MUA NGAY
                  </button>
                </div>
              </div>
              {/* Additional Information */}
              <div className="mt-6">
                <p className="text-gray-600">
                  Danh mục:{" "}
                  {product.category
                    ? typeof product.category === "string"
                      ? product.category
                      : product.category.name || "Không xác định"
                    : "Không xác định"}
                </p>
                <p className="text-gray-600">
                  Số lượng:{" "}
                  {product.variants && Array.isArray(product.variants)
                    ? product.variants.reduce(
                        (sum, v) => sum + (v.quantity || 0),
                        0
                      )
                    : product.quantity || 0}
                </p>
              </div>

              {stockError && (
                <p className="text-red-500 text-xs mt-1 text-center">
                  {stockError}
                </p>
              )}
            </div>
          </div>
        </div>

        {/* Comment Section (Keep Existing) */}
        <div className="mt-12 px-6">
          {/* Tab Navigation */}
          <ul className="flex border-b border-gray-200 mb-8 gap-x-8">
            <li className="mr-0">
              <button className="py-3 px-1 border-b-2 border-green-600 font-semibold text-green-600 relative">
                Bình luận
                {comments.length > 0 && (
                  <span className="absolute -top-2 -right-2 bg-green-600 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">
                    {comments.length}
                  </span>
                )}
              </button>
            </li>
          </ul>

          <div className="mt-8">
            <h3 className="font-semibold text-xl mb-6 text-gray-800 flex items-center">
              <MdOutlineInsertComment className="h-6 w-6 mr-2 text-green-600" />
              Bình luận khách hàng
            </h3>

            {isLoggedIn && currentUser ? (
              <div className="mb-10 bg-white rounded-lg shadow-sm overflow-hidden">
                <form
                  className="w-full divide-y divide-gray-100"
                  onSubmit={(e) => {
                    e.preventDefault();
                    handleCommentSubmit();
                  }}
                >
                  {showFeedback && (
                    <div
                      className={`p-4 rounded-lg mb-4 ${
                        feedbackType === "success"
                          ? "bg-green-50 text-green-700 border-l-4 border-green-500"
                          : "bg-red-50 text-red-700 border-l-4 border-red-500"
                      }`}
                    >
                      <div className="flex items-center">
                        <svg
                          xmlns="http://www.w3.org/2000/svg"
                          className={`h-5 w-5 mr-2 ${
                            feedbackType === "success"
                              ? "text-green-500"
                              : "text-red-500"
                          }`}
                          viewBox="0 0 20 20"
                          fill="currentColor"
                        >
                          {feedbackType === "success" ? (
                            <path
                              fillRule="evenodd"
                              d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                              clipRule="evenodd"
                            />
                          ) : (
                            <path
                              fillRule="evenodd"
                              d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                              clipRule="evenodd"
                            />
                          )}
                        </svg>
                        {feedbackMessage}
                      </div>
                    </div>
                  )}

                  <div className="px-6 pt-5 pb-4">
                    <div className="flex items-center mb-3">
                      {currentUser?.avatar ? (
                        <img
                          src={currentUser.avatar}
                          alt="Avatar"
                          className="w-10 h-10 rounded-full object-cover mr-3 border border-gray-200"
                        />
                      ) : (
                        <div className="w-10 h-10 rounded-full bg-green-100 text-green-600 flex items-center justify-center font-semibold mr-3">
                          {currentUser?.name?.charAt(0) || "U"}
                        </div>
                      )}
                      <div>
                        <p className="font-medium text-gray-700">
                          {currentUser?.name || "Người dùng"}
                        </p>
                        <p className="text-xs text-gray-500">
                          {currentUser?.email}
                        </p>
                      </div>
                    </div>

                    <textarea
                      className="w-full rounded-lg border border-gray-200 bg-gray-50 p-3 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition"
                      rows="3"
                      placeholder="Chia sẻ cảm nhận của bạn về sản phẩm này..."
                      value={commentInput}
                      onChange={(e) => setCommentInput(e.target.value)}
                      onFocus={() => checkAuthentication()}
                    ></textarea>
                  </div>

                  <div className="flex items-center justify-between px-6 py-3 bg-gray-50">
                    <div className="text-xs text-gray-500">
                      <span className="mr-1">💬</span> Bình luận sẽ được hiển
                      thị công khai
                    </div>
                    <button
                      type="submit"
                      disabled={!commentInput.trim()}
                      className={`px-5 py-2 bg-green-600 text-white rounded-md text-sm font-medium transition ${
                        !commentInput.trim()
                          ? "opacity-50 cursor-not-allowed"
                          : "hover:bg-green-700"
                      }`}
                    >
                      Gửi bình luận
                    </button>
                  </div>
                </form>
              </div>
            ) : (
              <div className="text-center p-8 bg-gray-50 rounded-lg mb-8 border border-gray-100 shadow-sm">
                <RiShieldKeyholeLine className="h-10 w-10 mx-auto text-gray-400 mb-3" />
                <p className="text-gray-600 mb-4">
                  Bạn cần đăng nhập để bình luận về sản phẩm này
                </p>
                <Link
                  to="/sign-in"
                  state={{ returnUrl: location.pathname }}
                  className="inline-block px-5 py-2 bg-green-600 text-white rounded-md font-medium hover:bg-green-700 transition"
                  onClick={() => {
                    localStorage.setItem("returnPath", location.pathname);
                  }}
                >
                  Đăng nhập ngay
                </Link>
              </div>
            )}

            {/* Comment List */}
            <div className="space-y-6">
              {comments.length > 0 ? (
                <>
                  <h4 className="font-medium text-gray-500 mb-2">
                    {comments.length} bình luận
                  </h4>

                  {comments.map((comment) => (
                    <div
                      key={comment.id}
                      className="bg-white rounded-lg shadow-sm overflow-hidden border border-gray-100"
                    >
                      <div className="p-5">
                        <div className="flex items-start">
                          {comment.user?.avatar ? (
                            <img
                              src={comment.user?.avatar}
                              alt="User avatar"
                              className="w-10 h-10 rounded-full object-cover mr-4 border border-gray-200"
                            />
                          ) : (
                            <div className="w-10 h-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-semibold mr-4">
                              {comment.user?.name?.charAt(0) || "U"}
                            </div>
                          )}

                          <div className="flex-1">
                            <div className="flex justify-between items-center mb-1">
                              <h4 className="font-semibold text-gray-800">
                                {comment.user?.name || "Người dùng ẩn danh"}
                              </h4>
                              <span className="text-xs text-gray-500">
                                {new Date(
                                  comment.created_at
                                ).toLocaleDateString("vi-VN", {
                                  year: "numeric",
                                  month: "short",
                                  day: "numeric",
                                  hour: "2-digit",
                                  minute: "2-digit",
                                })}
                              </span>
                            </div>
                            <p className="text-gray-700">{comment.content}</p>
                          </div>
                        </div>
                      </div>
                    </div>
                  ))}
                </>
              ) : (
                <div className="text-center py-10 bg-gray-50 rounded-lg border border-dashed border-gray-300">
                  <MdOutlineInsertComment className="h-14 w-14 mx-auto text-gray-400 mb-3" />
                  <p className="text-gray-500 text-lg mb-2">
                    Chưa có bình luận nào
                  </p>
                  <p className="text-gray-400">
                    Hãy là người đầu tiên bình luận về sản phẩm này
                  </p>
                </div>
              )}
            </div>
          </div>
        </div>
      </main>

      <AddToCartToast
        isVisible={showToast}
        product={toastProduct}
        onClose={() => setShowToast(false)}
      />
    </>
  );
};

export default ProductDetail;
