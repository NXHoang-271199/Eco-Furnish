import { useState, useEffect } from "react";
import { Link, useParams, useNavigate, useLocation } from "react-router-dom";
import axios from "axios";
import { useDispatch } from "react-redux";
import { addToCart } from "../../../store/cartSlice";
import AddToCartToast from "../../../components/AddToCartToast";

const ProductDetail = () => {
  const [product, setProduct] = useState(null);
  const [comments, setComments] = useState([]);
  const [loading, setLoading] = useState(true);
  const [commentInput, setCommentInput] = useState("");
  const [isLoggedIn, setIsLoggedIn] = useState(false);
  const [currentUser, setCurrentUser] = useState(null);
  const [selectedImage, setSelectedImage] = useState(null);
  const [selectedVariants, setSelectedVariants] = useState(null);
  const [quantity, setQuantity] = useState(1);
  const [error, setError] = useState("");
  const [currentPrice, setCurrentPrice] = useState(null);
  const [currentDiscount, setCurrentDiscount] = useState(null);
  const { id } = useParams();
  const navigate = useNavigate();
  const location = useLocation();
  const dispatch = useDispatch();
  const [showToast, setShowToast] = useState(false);
  const [toastProduct, setToastProduct] = useState(null);

  // State để hiển thị thông báo
  const [showFeedback, setShowFeedback] = useState(false);
  const [feedbackMessage, setFeedbackMessage] = useState("");
  const [feedbackType, setFeedbackType] = useState("success"); // "success" hoặc "error"

  console.log("ID from useParams:", id);

  // Hàm kiểm tra xác thực và lấy thông tin người dùng
  const checkAuthentication = async () => {
    // Thêm độ trễ nhỏ để đảm bảo token đã được lưu trữ
    await new Promise((resolve) => setTimeout(resolve, 100));

    const token = localStorage.getItem("authToken");
    console.log("authToken:", token);

    // Tạm thời lấy thông tin từ userData trong localStorage nếu có
    const userDataStr = localStorage.getItem("userData");
    if (userDataStr) {
      try {
        const userData = JSON.parse(userDataStr);
        console.log("Loaded user data from localStorage:", userData);

        // Cập nhật trạng thái người dùng từ localStorage
        setCurrentUser(userData);
        setIsLoggedIn(true);
      } catch (error) {
        console.error("Error parsing userData from localStorage:", error);
      }
    }

    // Vẫn gọi API để cập nhật thông tin mới nhất
    if (token) {
      const success = await fetchCurrentUser(token);
      if (!success) {
        console.log("Xác thực thất bại, xóa dữ liệu người dùng...");
        localStorage.removeItem("userData");
      }
    } else {
      setIsLoggedIn(false);
      setCurrentUser(null);
    }
  };

  useEffect(() => {
    const fetchProduct = async () => {
      try {
        const timestamp = new Date().getTime();
        const response = await axios.get(
          `http://127.0.0.1:8000/api/products/${id}`
        );
        console.log("API Response:", response.data);
        let productData = response.data.data;

        if (productData.variants && Array.isArray(productData.variants)) {
          console.log("Variants từ API:", productData.variants);

          setSelectedVariants({});
          setCurrentPrice(productData.price);
          setCurrentDiscount(productData.discount_price);

          // Xử lý các biến thể từ API
          let variantsWithValues = [];

          // Duyệt qua từng biến thể với hàm normalizeVariant
          for (const variant of productData.variants) {
            const result = normalizeVariant(variant);

            // Nếu normalizeVariant trả về mảng (trường hợp tách biến thể)
            if (Array.isArray(result)) {
              // Nối mảng kết quả vào mảng chính
              variantsWithValues.push(...result);
            } else {
              // Trường hợp thông thường, thêm một biến thể
              variantsWithValues.push(result);
            }
          }

          // Cập nhật thông tin hiển thị của variant_value cho mỗi biến thể
          const variantValueMap = {
            // Màu sắc
            1: {
              1: "Đỏ",
              2: "Xanh",
              3: "Vàng",
              4: "Đen",
              5: "Trắng",
              6: "Xám",
              7: "Hồng",
              8: "Cam",
              9: "Tím",
              10: "Nâu",
              11: "Xanh lá",
              12: "Xanh dương",
            },
            // Kích thước
            2: {
              1: "S",
              2: "M",
              3: "L",
              4: "XL",
              5: "XXL",
              6: "Nhỏ",
              7: "Vừa",
              8: "Lớn",
              9: "Trung bình",
              10: "Tùy chỉnh",
            },
            // Chất liệu
            3: {
              1: "Gỗ",
              2: "Nhựa",
              3: "Kim loại",
              4: "Vải",
              5: "Da",
              6: "Thủy tinh",
              7: "Nhôm",
              8: "Cao su",
              9: "Giấy",
              10: "Composite",
            },
          };

          // Cập nhật tên hiển thị của variant_value
          variantsWithValues.forEach((variant) => {
            const typeId = variant.variant_id.toString();
            const valueId = variant.variant_value_id.toString();

            if (variantValueMap[typeId] && variantValueMap[typeId][valueId]) {
              variant.variant_value.value = variantValueMap[typeId][valueId];
              variant.variant_value_name = variantValueMap[typeId][valueId];

              // Cập nhật cả trong variant_details_display nếu có
              if (
                variant.variant_details_display &&
                variant.variant_details_display.length > 0
              ) {
                variant.variant_details_display[0].value =
                  variantValueMap[typeId][valueId];
                variant.variant_details_display[0].full_description = `${variant.variant_name}: ${variantValueMap[typeId][valueId]}`;
              }
            }
          });

          // Xác định biến thể mặc định
          const variantTypes = [
            ...new Set(variantsWithValues.map((v) => v.variant_id)),
          ].filter((id) => id > 0);

          const defaultVariants = {};
          variantTypes.forEach((typeId) => {
            const availableVariants = variantsWithValues.filter(
              (v) =>
                v.variant_id === typeId &&
                (v.quantity > 0 || v.quantity === undefined)
            );

            if (availableVariants.length > 0) {
              defaultVariants[typeId] = availableVariants[0].variant_value_id;
            }
          });

          console.log("Biến thể mặc định:", defaultVariants);

          setProduct({ ...productData, variants: variantsWithValues });
          setSelectedVariants(defaultVariants);
        } else {
          setProduct(productData);
        }

        const commentsResponse = await axios.get(
          `http://127.0.0.1:8000/api/products/${id}/comments?_=${timestamp}`
        );
        setComments(commentsResponse.data.data || []);
      } catch (error) {
        console.error("Error fetching product data:", error);
      } finally {
        setLoading(false);
      }
    };
    fetchProduct();

    // Kiểm tra xác thực khi component mount
    checkAuthentication();

    // Kiểm tra nếu người dùng vừa đăng nhập và quay lại trang này
    const returnPath = localStorage.getItem("returnPath");
    if (returnPath && returnPath === location.pathname) {
      console.log("Người dùng vừa đăng nhập và quay lại trang:", returnPath);
      // Gọi làm mới dữ liệu người dùng sau khi đã đăng nhập
      setTimeout(() => {
        checkAuthentication();
        // Làm mới danh sách bình luận
        refreshProductComments();
        // Xóa returnPath để không kiểm tra lại
        localStorage.removeItem("returnPath");
      }, 500);
    }
  }, [id, location.pathname]);

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

  // Theo dõi thay đổi của location
  useEffect(() => {
    checkAuthentication();
  }, [location]);

  // Theo dõi thay đổi của localStorage để cập nhật trạng thái đăng nhập
  useEffect(() => {
    const handleStorageChange = (e) => {
      console.log("Storage changed:", e);
      if (e.key === "authToken" || e.key === "userData") {
        console.log("Token or userData changed, checking authentication");
        setTimeout(() => {
          checkAuthentication();
        }, 200); // Thêm độ trễ để đảm bảo localStorage đã được cập nhật
      }

      // Kiểm tra khi quay lại từ trang đăng nhập
      if (e.key === "returnPath" && e.newValue === location.pathname) {
        console.log("Quay lại từ trang đăng nhập:", e.newValue);
        setTimeout(() => {
          checkAuthentication();
          localStorage.removeItem("returnPath");
        }, 200);
      }
    };

    // Theo dõi sự kiện storage
    window.addEventListener("storage", handleStorageChange);

    // Sự kiện tùy chỉnh cho cùng cửa sổ/tab
    window.addEventListener("auth-change", checkAuthentication);

    // Kiểm tra khi component được mount
    setTimeout(checkAuthentication, 300);

    return () => {
      window.removeEventListener("storage", handleStorageChange);
      window.removeEventListener("auth-change", checkAuthentication);
    };
  }, []);

  // Lấy thông tin người dùng hiện tại
  const fetchCurrentUser = async (token) => {
    try {
      console.log("Fetching user with token:", token);
      const response = await axios.get("http://127.0.0.1:8000/api/users", {
        headers: { Authorization: `Bearer ${token}` },
      });
      console.log("User profile response:", response.data);
      if (response.data && response.data.data) {
        setCurrentUser(response.data.data);
        setIsLoggedIn(true);
        console.log("User logged in successfully:", response.data.data);
        return true;
      } else if (response.data) {
        setCurrentUser(response.data);
        setIsLoggedIn(true);
        console.log("User logged in successfully:", response.data.name);
        return true;
      } else {
        console.log("No user data in response");
        setIsLoggedIn(false);
        setCurrentUser(null);
        localStorage.removeItem("authToken");
        return false;
      }
    } catch (error) {
      console.error("Error fetching user:", error.response || error);
      if (error.response?.status === 401 || error.response?.status === 404) {
        console.log("Token không hợp lệ hoặc API không tồn tại, đăng xuất...");
        setIsLoggedIn(false);
        setCurrentUser(null);
        localStorage.removeItem("authToken");
      }
      return false;
    }
  };

  if (loading) {
    return <div className="text-center mt-32">Đang tải...</div>;
  }

  if (!product) {
    return <div className="text-center mt-32">Không tìm thấy sản phẩm</div>;
  }

  const formatPrice = (price) => {
    const numericPrice = price ? parseFloat(price) : null;
    return numericPrice && !isNaN(numericPrice)
      ? numericPrice.toLocaleString("vi-VN", { minimumFractionDigits: 0 }) + "đ"
      : "Giá không khả dụng";
  };

  const baseURL = "http://127.0.0.1:8000/";

  const mainImageUrl =
    selectedImage ||
    (product.image_thumnail
      ? product.image_thumnail.startsWith("http")
        ? product.image_thumnail
        : `${baseURL}storage/${product.image_thumnail}`
      : "https://via.placeholder.com/400x400?text=No+Image");

  console.log("Main Image URL:", mainImageUrl);

  if (product.gallery) {
    product.gallery.forEach((item, index) => {
      const galleryImageUrl = item.image_url
        ? item.image_url.startsWith("http")
          ? item.image_url
          : `${baseURL}storage/${item.image_url}`
        : "https://via.placeholder.com/100x100?text=No+Image";

      console.log(`Gallery Image ${index + 1} URL:`, galleryImageUrl);
    });
  }

  // Xử lý click hình ảnh trong gallery
  const handleGalleryImageClick = (imageUrl) => {
    setSelectedImage(imageUrl);
  };

  // Xử lý chọn biến thể
  const handleVariantSelect = (variantId, variantValueId, variant) => {
    const newSelectedVariants = {
      ...selectedVariants,
      [variantId]: variantValueId,
    };
    setSelectedVariants(newSelectedVariants);

    console.log(`Đã chọn biến thể:`, variant);
    console.log(`Lựa chọn biến thể hiện tại:`, newSelectedVariants);

    const selectedVariantTypes = Object.keys(newSelectedVariants).map(Number);
    const skuCandidates = new Set();

    selectedVariantTypes.forEach((typeId) => {
      const valueId = newSelectedVariants[typeId];
      const matches = product.variants.filter(
        (v) => v.variant_id === typeId && v.variant_value_id === valueId
      );

      matches.forEach((v) => skuCandidates.add(v.sku));
    });

    console.log("Các SKU ứng viên:", [...skuCandidates]);

    let bestSku = null;

    for (const sku of skuCandidates) {
      const isMatch = selectedVariantTypes.every((typeId) => {
        const valueId = newSelectedVariants[typeId];
        return product.variants.some(
          (v) =>
            v.sku === sku &&
            v.variant_id === typeId &&
            v.variant_value_id === valueId
        );
      });

      if (isMatch) {
        bestSku = sku;
        break;
      }
    }

    console.log("SKU phù hợp nhất:", bestSku);

    if (bestSku) {
      const bestVariant = product.variants.find((v) => v.sku === bestSku);
      if (bestVariant && bestVariant.price) {
        console.log("Biến thể phù hợp nhất:", bestVariant);
        setCurrentPrice(bestVariant.price);
        setCurrentDiscount(bestVariant.discount_price || bestVariant.price);
        console.log(`Cập nhật giá theo biến thể: ${bestVariant.price}`);
      }
    } else if (variant && variant.price) {
      setCurrentPrice(variant.price);
      setCurrentDiscount(variant.discount_price || variant.price);
      console.log(`Cập nhật giá theo biến thể được chọn: ${variant.price}`);
    }

    setError("");
  };

  const isVariantInStock = (variant) => {
    if (!variant) return false;
    return variant.quantity > 0;
  };

  const hasSelectedAllRequiredVariants = () => {
    if (!product.variants || product.variants.length === 0) return true;

    const availableVariantTypes = [
      ...new Set(product.variants.map((v) => v.variant_id)),
    ];

    return availableVariantTypes.every((type) => selectedVariants[type]);
  };

  // Thêm sản phẩm vào giỏ hàng
  const handleAddToCart = () => {
    // Kiểm tra đăng nhập
    if (!isLoggedIn) {
      navigate("/signin", { state: { returnUrl: location.pathname } });
      localStorage.setItem("returnPath", location.pathname);
      return;
    }

    if (!hasSelectedAllRequiredVariants()) {
      setError("Vui lòng chọn đầy đủ biến thể sản phẩm");
      return;
    }

    const selectedVariantValues = {};
    Object.entries(selectedVariants).forEach(([variantId, valueId]) => {
      const variant = product.variants.find(
        (v) =>
          v.variant_id === Number(variantId) && v.variant_value_id === valueId
      );
      if (variant) {
        selectedVariantValues[variant.variant_info.name] =
          variant.variant_value.value;
      }
    });

    // Thêm vào giỏ hàng
    dispatch(
      addToCart({
        product,
        quantity,
        variants: selectedVariantValues,
      })
    );

    // Hiển thị thông báo
    setToastProduct(product);
    setShowToast(true);
    setTimeout(() => setShowToast(false), 3000);

    console.log("isLoggedIn:", isLoggedIn);
    console.log("currentUser:", currentUser);
    console.log("token:", localStorage.getItem("token"));
  };

  // Mua ngay - vẫn yêu cầu đăng nhập
  const handleBuyNow = () => {
    if (!isLoggedIn) {
      navigate("/signin", { state: { returnUrl: location.pathname } });
      return;
    }

    if (!hasSelectedAllRequiredVariants()) {
      setError("Vui lòng chọn đầy đủ biến thể sản phẩm");
      return;
    }

    console.log("Mua ngay:", {
      product_id: product.id,
      variants: selectedVariants,
      quantity: quantity,
    });

    alert("Đang chuyển đến trang thanh toán...");
  };
  // Tăng số lượng
  const increaseQuantity = () => {
    setQuantity((prev) => prev + 1);
  };

  const decreaseQuantity = () => {
    if (quantity > 1) {
      setQuantity((prev) => prev - 1);
    }
  };

  // Lấy biến thể theo loại
  const getVariantsByType = (variantId) => {
    if (!product.variants || product.variants.length === 0) {
      console.log(`Không tìm thấy biến thể nào cho sản phẩm ${product.id}`);
      return [];
    }

    console.log(`Tìm biến thể cho loại ${variantId}`, product.variants);

    // Lọc biến thể theo nhiều trường hợp khác nhau
    const typeVariants = product.variants.filter((variant) => {
      // Trường hợp 1: variant_id trực tiếp
      if (variant.variant_id === variantId) {
        return true;
      }

      // Trường hợp 2: variant_info.id
      if (variant.variant_info && variant.variant_info.id === variantId) {
        return true;
      }

      // Trường hợp 3: variant_details hoặc details (API sử dụng tên trường khác nhau)
      if (
        (variant.variant_details && variant.variant_details[variantId]) ||
        (variant.details && variant.details[variantId])
      ) {
        return true;
      }

      // Trường hợp 4: variant_details_display
      if (
        variant.variant_details_display &&
        Array.isArray(variant.variant_details_display)
      ) {
        return variant.variant_details_display.some(
          (detail) => detail.variant_id === variantId
        );
      }

      return false;
    });

    console.log(
      `Tìm thấy ${typeVariants.length} biến thể cho loại ${variantId}`
    );

    // Lọc các biến thể trùng lặp
    const latestVariants = new Map();

    typeVariants.forEach((variant) => {
      // Xác định variant_value_id
      let valueId = null;

      // Trường hợp 1: Lấy từ variant_value_id trực tiếp
      if (variant.variant_value_id) {
        valueId = variant.variant_value_id;
      }
      // Trường hợp 2: Lấy từ variant_value.id
      else if (variant.variant_value && variant.variant_value.id) {
        valueId = variant.variant_value.id;
      }
      // Trường hợp 3: Lấy từ variant_details hoặc details
      else if (variant.variant_details && variant.variant_details[variantId]) {
        valueId = variant.variant_details[variantId];
      } else if (variant.details && variant.details[variantId]) {
        valueId = variant.details[variantId];
      }
      // Trường hợp 4: Lấy từ variant_details_display
      else if (
        variant.variant_details_display &&
        Array.isArray(variant.variant_details_display)
      ) {
        const detail = variant.variant_details_display.find(
          (d) => d.variant_id === variantId
        );
        if (detail) valueId = detail.value_id;
      }

      if (valueId) {
        const key = `${variantId}_${valueId}`;

        if (
          !latestVariants.has(key) ||
          (variant.updated_at &&
            latestVariants.get(key).updated_at &&
            new Date(variant.updated_at) >
              new Date(latestVariants.get(key).updated_at))
        ) {
          latestVariants.set(key, variant);
        }
      }
    });

    const uniqueVariants = Array.from(latestVariants.values());

    // Log chi tiết từng biến thể
    uniqueVariants.forEach((v, i) => {
      console.log(`Biến thể ${i + 1}:`, {
        id: v.id,
        variant_id: v.variant_id,
        variant_name: v.variant_name,
        variant_value_id: v.variant_value_id,
        variant_value_name: v.variant_value_name,
        variant_info: v.variant_info,
        variant_value: v.variant_value,
        details: v.details,
        variant_details: v.variant_details,
        details_display: v.variant_details_display,
      });
    });

    return uniqueVariants;
  };

  // Lấy tên loại biến thể
  const getVariantTypeName = (variantId) => {
    const variant = product.variants?.find((v) => v.variant_id === variantId);
    if (variant && variant.variant_info && variant.variant_info.name) {
      return variant.variant_info.name;
    }

    const variantTypes = {
      1: "Màu sắc",
      2: "Kích thước",
      3: "Chất liệu",
      4: "Kiểu dáng",
    };
    return variantTypes[variantId] || "Biến thể";
  };

  // Gửi bình luận
  const handleCommentSubmit = async () => {
    // Kiểm tra trạng thái đăng nhập trước khi gửi bình luận
    await checkAuthentication();

    if (!isLoggedIn || !currentUser) {
      navigate("/signin", { state: { returnUrl: location.pathname } });
      return;
    }

    if (!commentInput.trim()) {
      return;
    }

    try {
      const token = localStorage.getItem("token");
      if (!token) {
        setIsLoggedIn(false);
        setCurrentUser(null);
        navigate("/signin", { state: { returnUrl: location.pathname } });
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
        `http://127.0.0.1:8000/api/comments`,
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
        localStorage.removeItem("token");
        setIsLoggedIn(false);
        setCurrentUser(null);
        navigate("/signin", { state: { returnUrl: location.pathname } });
      }
    }
  };

  // Hàm chỉ refresh comments, không load lại toàn bộ sản phẩm
  const refreshProductComments = async () => {
    try {
      const timestamp = new Date().getTime();
      const commentsResponse = await axios.get(
        `http://127.0.0.1:8000/api/products/${id}/comments?_=${timestamp}`
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
        `http://127.0.0.1:8000/api/products/${id}?_=${timestamp}`
      );
      console.log("Refreshed API Response:", response.data);
      let productData = response.data.data;

      setSelectedVariants({});
      setCurrentPrice(productData.price);
      setCurrentDiscount(productData.discount_price);

      if (productData.variants && Array.isArray(productData.variants)) {
        const variantsWithValues = productData.variants.map((variant) => {
          return {
            ...variant,
            variant_value: {
              id: variant.variant_value_id || 0,
              value: variant.variant_value_name || "Không xác định",
            },
            variant_info: {
              id: variant.variant_id || 0,
              name: variant.variant_name || "Không xác định",
            },
          };
        });

        const defaultVariants = {};
        const variantTypes = [
          ...new Set(variantsWithValues.map((v) => v.variant_id)),
        ];

        variantTypes.forEach((typeId) => {
          const availableVariants = variantsWithValues.filter(
            (v) => v.variant_id === typeId && v.quantity > 0
          );

          if (availableVariants.length > 0) {
            defaultVariants[typeId] = availableVariants[0].variant_value_id;
          }
        });

        if (Object.keys(defaultVariants).length > 0) {
          const skuCandidates = new Set();

          Object.entries(defaultVariants).forEach(([typeId, valueId]) => {
            const matches = variantsWithValues.filter(
              (v) =>
                v.variant_id === Number(typeId) &&
                v.variant_value_id === valueId
            );

            matches.forEach((v) => skuCandidates.add(v.sku));
          });

          let bestSku = null;

          for (const sku of skuCandidates) {
            const isMatch = Object.entries(defaultVariants).every(
              ([typeId, valueId]) => {
                return variantsWithValues.some(
                  (v) =>
                    v.sku === sku &&
                    v.variant_id === Number(typeId) &&
                    v.variant_value_id === valueId
                );
              }
            );

            if (isMatch) {
              bestSku = sku;
              break;
            }
          }

          if (bestSku) {
            const bestVariant = variantsWithValues.find(
              (v) => v.sku === bestSku
            );
            if (bestVariant && bestVariant.price) {
              setCurrentPrice(bestVariant.price);
              setCurrentDiscount(
                bestVariant.discount_price || bestVariant.price
              );
              console.log(
                `Giá ban đầu được thiết lập theo biến thể: ${bestVariant.price}`
              );
            }
          }
        }

        setProduct({ ...productData, variants: variantsWithValues });
        setSelectedVariants(defaultVariants);
      } else {
        setProduct(productData);
      }

      const commentsResponse = await axios.get(
        `http://127.0.0.1:8000/api/products/${id}/comments?_=${timestamp}`
      );
      setComments(commentsResponse.data.data || []);
    } catch (error) {
      console.error("Error refreshing product data:", error);
    } finally {
      setLoading(false);
    }
  };

  // Tính tổng số lượng sản phẩm
  const getTotalQuantity = () => {
    if (!product.variants || product.variants.length === 0) {
      return product.quantity || 0;
    }

    // Tính tổng số lượng của các biến thể
    const variantQuantity = product.variants.reduce(
      (sum, variant) => sum + (variant.quantity || 0),
      0
    );

    // Nếu có cả số lượng chính và biến thể, lấy tổng
    return variantQuantity + (product.quantity || 0);
  };

  // Hiển thị số lượng theo biến thể
  const getVariantQuantity = (variantId, valueId) => {
    if (!product.variants) return 0;

    const variant = product.variants.find(
      (v) => v.variant_id === variantId && v.variant_value_id === valueId
    );

    return variant ? variant.quantity : 0;
  };

  // Hàm helper để tạo cấu trúc biến thể chuẩn
  const normalizeVariant = (variant, getVariantTypeNameFn) => {
    // Sử dụng getVariantTypeNameFn nếu được truyền vào, ngược lại sử dụng getVariantTypeNameById
    const getTypeName = getVariantTypeNameFn || getVariantTypeName;

    console.log(">> Đang normalize biến thể:", variant);

    // Thống nhất dữ liệu giữa details và variant_details
    if (variant.details && !variant.variant_details) {
      variant.variant_details = variant.details;
    }

    // Trường hợp đặc biệt: variant_details chứa nhiều biến thể
    if (
      variant.variant_details &&
      Object.keys(variant.variant_details).length > 1
    ) {
      console.log(">> Đây là biến thể tổng hợp:", variant.variant_details);

      // Tạo các biến thể riêng biệt từ mỗi cặp key-value trong variant_details
      const normalizedVariants = [];

      Object.entries(variant.variant_details).forEach(
        ([variantTypeId, variantValueId]) => {
          const newVariant = {
            ...variant,
            id: `${variant.id}-${variantTypeId}-${variantValueId}`,
            variant_id: Number(variantTypeId),
            variant_value_id: Number(variantValueId),
            variant_name: getTypeName(Number(variantTypeId)),
            variant_value_name: "Giá trị Biến thể", // Sẽ cập nhật sau
            variant_info: {
              id: Number(variantTypeId),
              name: getTypeName(Number(variantTypeId)),
            },
            variant_value: {
              id: Number(variantValueId),
              value: "Giá trị Biến thể", // Sẽ cập nhật sau
            },
            // Tạo variant_details_display mới chỉ chứa 1 cặp key-value
            variant_details_display: [
              {
                variant_id: Number(variantTypeId),
                variant_name: getTypeName(Number(variantTypeId)),
                value_id: Number(variantValueId),
                value: "", // Sẽ cập nhật sau
                full_description: "",
              },
            ],
          };

          normalizedVariants.push(newVariant);
        }
      );

      console.log(
        ">> Đã tách thành các biến thể riêng biệt:",
        normalizedVariants
      );

      // Trả về mảng các biến thể đã tách
      return normalizedVariants;
    }

    // Trường hợp thông thường: một biến thể thông thường
    // Khởi tạo các thông tin mặc định
    let variantId = 0;
    let variantValueId = 0;
    let variantName = "Không xác định";
    let variantValueName = "Không xác định";

    // Cách 1: Trực tiếp từ các trường cơ bản
    if (variant.variant_id) variantId = variant.variant_id;
    if (variant.variant_value_id) variantValueId = variant.variant_value_id;
    if (variant.variant_name) variantName = variant.variant_name;
    if (variant.variant_value_name)
      variantValueName = variant.variant_value_name;

    // Cách 2: Từ variant_details
    if (
      variant.variant_details &&
      Object.keys(variant.variant_details).length === 1
    ) {
      // Lấy cặp khóa-giá trị từ variant_details
      const [firstKey, firstValue] = Object.entries(variant.variant_details)[0];
      console.log(`Variant details - Key: ${firstKey}, Value: ${firstValue}`);

      // Cập nhật thông tin từ variant_details
      variantId = parseInt(firstKey) || variantId;
      variantValueId = parseInt(firstValue) || variantValueId;
      variantName = getTypeName(variantId);
    }

    // Cách 3: Từ variant_details_display
    if (
      variant.variant_details_display &&
      Array.isArray(variant.variant_details_display) &&
      variant.variant_details_display.length > 0
    ) {
      const firstDetail = variant.variant_details_display[0];
      console.log("First variant detail:", firstDetail);

      if (firstDetail.variant_id) variantId = firstDetail.variant_id;
      if (firstDetail.value_id) variantValueId = firstDetail.value_id;
      if (firstDetail.variant_name) variantName = firstDetail.variant_name;
      if (firstDetail.value) variantValueName = firstDetail.value;
    }

    // Đảm bảo các giá trị là dạng số
    variantId = Number(variantId);
    variantValueId = Number(variantValueId);

    // Nếu không có variant_details_display, tạo mới
    if (!variant.variant_details_display) {
      variant.variant_details_display = [
        {
          variant_id: variantId,
          variant_name: variantName,
          value_id: variantValueId,
          value: variantValueName,
          full_description: `${variantName}: ${variantValueName}`,
        },
      ];
    }

    // Kiểm tra và log các thông tin quan trọng
    console.log(
      `Biến thể sau khi chuẩn hóa - ID: ${variantId}, Name: ${variantName}, ValueID: ${variantValueId}, ValueName: ${variantValueName}`
    );

    // Trả về biến thể đã chuẩn hóa
    return {
      ...variant,
      variant_id: variantId,
      variant_value_id: variantValueId,
      variant_name: variantName,
      variant_value_name: variantValueName,
      variant_info: {
        id: variantId,
        name: variantName,
      },
      variant_value: {
        id: variantValueId,
        value: variantValueName,
      },
    };
  };

  return (
    <>
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
                          : `${baseURL}storage/${item.image_url}`
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
                      <svg
                        xmlns="http://www.w3.org/2000/svg"
                        className="h-6 w-6 mx-auto text-gray-400 mb-1"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                      >
                        <path
                          strokeLinecap="round"
                          strokeLinejoin="round"
                          strokeWidth={1.5}
                          d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
                        />
                      </svg>
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
                {currentDiscount &&
                  currentPrice &&
                  Number(currentDiscount) !== Number(currentPrice) && (
                    <span className="text-gray-500 line-through mr-2">
                      {formatPrice(currentPrice)}
                    </span>
                  )}
                <span className="text-2xl font-semibold text-green-600">
                  {currentDiscount
                    ? formatPrice(currentDiscount)
                    : currentPrice
                    ? formatPrice(currentPrice)
                    : formatPrice(product.price)}
                </span>
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
              <div className="mt-6 space-y-4">
                <h4 className="text-sm font-medium text-gray-600 uppercase tracking-wide mb-3">
                  Lựa chọn biến thể sản phẩm
                </h4>

                {[1, 2, 3, 4].map((variantTypeId) => {
                  const variants = getVariantsByType(variantTypeId);
                  if (variants.length === 0) return null;

                  return (
                    <div
                      className="mt-4 border-b border-gray-100 pb-4"
                      key={variantTypeId}
                    >
                      <p className="text-gray-700 font-medium mb-2">
                        {getVariantTypeName(variantTypeId)}:
                        <span className="text-red-500 ml-1">*</span>
                      </p>
                      <div className="flex flex-wrap gap-3 mt-2">
                        {variants.map((variant) => {
                          if (!variant) return null;

                          // Xác định giá trị của biến thể từ các nguồn khác nhau
                          let variantValueId = variant.variant_value_id;
                          let variantValueName = variant.variant_value_name;

                          // Nếu không có trực tiếp, thử lấy từ variant_value
                          if (!variantValueName && variant.variant_value) {
                            variantValueName = variant.variant_value.value;
                          }

                          // Nếu vẫn không có, thử lấy từ variant_details_display
                          if (
                            !variantValueName &&
                            variant.variant_details_display &&
                            variant.variant_details_display.length > 0
                          ) {
                            const detail = variant.variant_details_display.find(
                              (d) => d.variant_id === variantTypeId
                            );
                            if (detail) {
                              variantValueName = detail.value;
                            }
                          }

                          if (!variantValueId || !variantValueName) {
                            console.error(
                              "Không thể xác định variant_value cho biến thể:",
                              variant
                            );
                            return null;
                          }

                          const isSelected =
                            selectedVariants[variantTypeId] === variantValueId;
                          const inStock = isVariantInStock(variant);

                          if (variantTypeId === 1) {
                            // Màu sắc
                            let bgColor = "gray";
                            const colorValue =
                              typeof variantValueName === "string"
                                ? variantValueName.toLowerCase()
                                : "";

                            if (colorValue.includes("đỏ")) bgColor = "red";
                            else if (colorValue.includes("xanh"))
                              bgColor = "blue";
                            else if (colorValue.includes("đen"))
                              bgColor = "black";
                            else if (colorValue.includes("trắng"))
                              bgColor = "white";
                            else if (colorValue.includes("vàng"))
                              bgColor = "yellow";
                            else if (colorValue.includes("cam"))
                              bgColor = "orange";
                            else if (colorValue.includes("tím"))
                              bgColor = "purple";
                            else if (colorValue.includes("hồng"))
                              bgColor = "pink";
                            else if (colorValue.includes("nâu"))
                              bgColor = "brown";
                            else if (colorValue.includes("xám"))
                              bgColor = "gray";

                            console.log(
                              `Hiển thị biến thể màu: ${variantValueName} (${bgColor}), ID: ${variantValueId}`
                            );

                            return (
                              <div
                                key={
                                  variant.id ||
                                  `color-${variantTypeId}-${variantValueId}`
                                }
                                className={`w-10 h-10 rounded-full cursor-pointer border-2 shadow hover:shadow-md transition-all duration-300 flex items-center justify-center ${
                                  isSelected
                                    ? "border-green-500 ring-2 ring-green-200 ring-offset-2 scale-110"
                                    : "border-gray-200 hover:border-gray-300"
                                } ${
                                  !inStock
                                    ? "opacity-40 cursor-not-allowed"
                                    : ""
                                }`}
                                title={`${
                                  variantValueName || "Không xác định"
                                } ${!inStock ? "(Hết hàng)" : ""}`}
                                onClick={() => {
                                  if (inStock) {
                                    handleVariantSelect(
                                      variantTypeId,
                                      variantValueId,
                                      variant
                                    );
                                  }
                                }}
                              >
                                <span
                                  className="block w-8 h-8 rounded-full"
                                  style={{
                                    backgroundColor: bgColor,
                                    border:
                                      bgColor === "white"
                                        ? "1px solid #ddd"
                                        : "none",
                                  }}
                                ></span>
                              </div>
                            );
                          } else if (variantTypeId === 2) {
                            // Kích thước
                            const sizeLabel =
                              typeof variantValueName === "string"
                                ? variantValueName
                                : "Không xác định";

                            console.log(
                              `Hiển thị biến thể kích thước: ${sizeLabel}, ID: ${variantValueId}`
                            );

                            return (
                              <div
                                key={
                                  variant.id ||
                                  `size-${variantTypeId}-${variantValueId}`
                                }
                                className={`min-w-[50px] h-10 px-4 border-2 rounded-md cursor-pointer shadow-sm text-center flex items-center justify-center transition-all duration-300 ${
                                  isSelected
                                    ? "border-green-500 bg-green-50 text-green-600 font-semibold shadow-md"
                                    : "border-gray-200 bg-white hover:border-gray-300 hover:bg-gray-50"
                                } ${
                                  !inStock
                                    ? "opacity-40 cursor-not-allowed bg-gray-100"
                                    : ""
                                }`}
                                onClick={() => {
                                  if (inStock) {
                                    handleVariantSelect(
                                      variantTypeId,
                                      variantValueId,
                                      variant
                                    );
                                  }
                                }}
                                title={!inStock ? "Hết hàng" : ""}
                              >
                                {sizeLabel}
                              </div>
                            );
                          } else {
                            // Các loại biến thể khác
                            const otherLabel =
                              typeof variantValueName === "string"
                                ? variantValueName
                                : "Không xác định";

                            console.log(
                              `Hiển thị biến thể khác: ${otherLabel}, ID: ${variantValueId}`
                            );

                            return (
                              <div
                                key={
                                  variant.id ||
                                  `variant-${variantTypeId}-${variantValueId}`
                                }
                                className={`px-4 py-2 border-2 rounded-md cursor-pointer flex items-center transition-all ${
                                  isSelected
                                    ? "border-green-500 bg-green-50 text-green-600 font-medium"
                                    : "border-gray-200 hover:border-gray-300 hover:bg-gray-50"
                                } ${
                                  !inStock
                                    ? "opacity-40 cursor-not-allowed bg-gray-100"
                                    : ""
                                }`}
                                onClick={() => {
                                  if (inStock) {
                                    handleVariantSelect(
                                      variantTypeId,
                                      variantValueId,
                                      variant
                                    );
                                  }
                                }}
                                title={!inStock ? "Hết hàng" : ""}
                              >
                                {otherLabel}
                              </div>
                            );
                          }
                        })}
                      </div>
                    </div>
                  );
                })}
              </div>

              {/* Hiển thị thông báo lỗi khi chưa chọn đủ biến thể */}
              {error && (
                <div className="mt-2 bg-red-50 text-red-600 text-sm p-3 rounded-md border border-red-200">
                  <div className="flex items-center">
                    <svg
                      xmlns="http://www.w3.org/2000/svg"
                      className="h-5 w-5 mr-2"
                      viewBox="0 0 20 20"
                      fill="currentColor"
                    >
                      <path
                        fillRule="evenodd"
                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                        clipRule="evenodd"
                      />
                    </svg>
                    {error}
                  </div>
                </div>
              )}

              {product.variants && product.variants.length === 0 && (
                <div className="p-3 bg-gray-50 rounded border border-gray-200 text-gray-500">
                  Sản phẩm này không có biến thể.
                </div>
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
                  >
                    +
                  </button>
                </div>
              </div>

              {/* Add to Cart and Wishlist Buttons - Same Row with Black Background */}
              <div className="flex gap-4 mt-4">
                <button
                  className="w-full bg-gradient-to-r from-amber-500 to-amber-600 text-white py-3 px-6 rounded-full font-semibold shadow-md hover:shadow-lg transition-all duration-300 transform hover:scale-[1.01] flex items-center justify-center"
                  onClick={handleAddToCart}
                >
                  <svg
                    xmlns="http://www.w3.org/2000/svg"
                    className="h-5 w-5 mr-2"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                  >
                    <path
                      strokeLinecap="round"
                      strokeLinejoin="round"
                      strokeWidth={2}
                      d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"
                    />
                  </svg>
                  THÊM VÀO GIỎ HÀNG
                </button>
              </div>

              {/* Buy Now Button - Below */}
              <div className="mt-4">
                <button
                  className="w-full bg-gradient-to-r from-orange-500 to-red-500 text-white py-3 px-4 rounded-full font-bold shadow-lg transform transition duration-300 hover:scale-[1.02] hover:shadow-xl hover:from-orange-600 hover:to-red-600 focus:outline-none focus:ring-2 focus:ring-red-400"
                  onClick={handleBuyNow}
                >
                  MUA NGAY
                </button>
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
                  Tổng số lượng: {getTotalQuantity()}
                </p>
                {product.variants && product.variants.length > 0 && (
                  <div className="mt-2">
                    <p className="text-sm font-medium text-gray-700">
                      Số lượng theo biến thể:
                    </p>
                    {[1, 2, 3, 4].map((variantTypeId) => {
                      const variants = getVariantsByType(variantTypeId);
                      if (variants.length === 0) return null;

                      return (
                        <div key={variantTypeId} className="mt-1">
                          <p className="text-sm text-gray-600">
                            {getVariantTypeName(variantTypeId)}:
                          </p>
                          <div className="flex flex-wrap gap-2">
                            {variants.map((variant) => (
                              <span
                                key={variant.id}
                                className="text-sm text-gray-500"
                              >
                                {variant.variant_value.value}:{" "}
                                {getVariantQuantity(
                                  variant.variant_id,
                                  variant.variant_value_id
                                )}
                              </span>
                            ))}
                          </div>
                        </div>
                      );
                    })}
                  </div>
                )}
              </div>
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
              <svg
                xmlns="http://www.w3.org/2000/svg"
                className="h-6 w-6 mr-2 text-green-600"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
              >
                <path
                  strokeLinecap="round"
                  strokeLinejoin="round"
                  strokeWidth={2}
                  d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"
                />
              </svg>
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
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  className="h-10 w-10 mx-auto text-gray-400 mb-3"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                >
                  <path
                    strokeLinecap="round"
                    strokeLinejoin="round"
                    strokeWidth={1.5}
                    d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"
                  />
                </svg>
                <p className="text-gray-600 mb-4">
                  Bạn cần đăng nhập để bình luận về sản phẩm này
                </p>
                <Link
                  to="/signin"
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
                  <svg
                    xmlns="http://www.w3.org/2000/svg"
                    className="h-14 w-14 mx-auto text-gray-400 mb-3"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                  >
                    <path
                      strokeLinecap="round"
                      strokeLinejoin="round"
                      strokeWidth={1}
                      d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"
                    />
                  </svg>
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
