import React, { useEffect, useRef, useCallback, useState } from "react";
import { useParams, useNavigate, useLocation } from "react-router-dom";
import axios from "axios";
import { motion } from "framer-motion";
import {
  IoStar,
  IoCartOutline,
  IoArrowForward,
  IoCheckmarkCircle,
} from "react-icons/io5";
import { FaTruck, FaExchangeAlt, FaShieldAlt } from "react-icons/fa";
import { toast, Toaster } from "react-hot-toast";

// Cache đơn giản cho API
const apiCache = {};

const ProductDetail = () => {
  const { id } = useParams();
  const navigate = useNavigate();
  const location = useLocation();
  const [product, setProduct] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [activeImage, setActiveImage] = useState(null);
  const [selectedVariantId, setSelectedVariantId] = useState(null);
  const [selectedVariantAttributes, setSelectedVariantAttributes] = useState(
    {}
  );
  const [quantity, setQuantity] = useState(1);
  const [addingToCart, setAddingToCart] = useState(false);
  const [addingToBuy, setAddingToBuy] = useState(false);
  const [currentPrice, setCurrentPrice] = useState(null);
  const [currentDiscount, setCurrentDiscount] = useState(null);
  const [selectedVariants, setSelectedVariants] = useState({});
  const [comments, setComments] = useState([]);
  const [commentInput, setCommentInput] = useState("");
  const [showFeedback, setShowFeedback] = useState(false);
  const [feedbackMessage, setFeedbackMessage] = useState("");
  const [feedbackType, setFeedbackType] = useState("success");
  const [isLoggedIn, setIsLoggedIn] = useState(false);
  const [currentUser, setCurrentUser] = useState(null);
  const [stockError, setStockError] = useState("");
  const [isUpdatingQuantity, setIsUpdatingQuantity] = useState(false);
  const [editingQuantity, setEditingQuantity] = useState("");
  const isEditingRef = useRef(false);
  const quantityInputRef = useRef(null);

  useEffect(() => {
    const controller = new AbortController();
    const fetchData = async () => {
      try {
        const response = await axios.get(
          `http://localhost:8000/api/products/${id}`,
          {
            signal: controller.signal,
          }
        );
        const productData = response.data.data;
        setActiveImage(productData.image_thumnail);

        if (productData.variants && Array.isArray(productData.variants)) {
          const variantsWithValues = productData.variants.map((variant) => {
            // Xử lý variant
            return {
              ...variant,
              // Thêm các thuộc tính cần thiết
            };
          });

          setProduct({ ...productData, variants: variantsWithValues });

          // Thiết lập biến thể mặc định nếu có
          if (variantsWithValues.length > 0) {
            const defaultVariants = {};
            setSelectedVariants(defaultVariants);
          }
        } else {
          setProduct(productData);
        }

        // Lấy comments
        const timestamp = new Date().getTime();
        const commentsUrl = `http://localhost:8000/api/products/${id}/comments?_=${timestamp}`;
        const commentsResponse = await fetchWithCache(
          commentsUrl,
          controller.signal
        );
        setComments(commentsResponse.data || []);
      } catch (error) {
        console.error("Lỗi khi lấy dữ liệu sản phẩm:", error);
        setError(error);
      } finally {
        setLoading(false);
      }
    };

    fetchData();

    return () => {
      controller.abort();
    };
  }, [id]);

  // Hàm kiểm tra xác thực và lấy thông tin người dùng
  const checkAuthentication = useCallback(async () => {
    await new Promise((resolve) => setTimeout(resolve, 100));
    const token = localStorage.getItem("authToken");
    if (!token) {
      setIsLoggedIn(false);
      setCurrentUser(null);
      return;
    }
    const userDataStr = localStorage.getItem("userData");
    if (userDataStr) {
      try {
        const userData = JSON.parse(userDataStr);
        setCurrentUser(userData);
        setIsLoggedIn(true);
      } catch (error) {
        console.error("Error parsing userData:", error);
      }
    }
    try {
      const success = await fetchCurrentUser(token);
      if (!success) localStorage.removeItem("userData");
    } catch (error) {
      console.error("Error during auth check:", error);
    }
  }, []);

  const formatPrice = (price) => {
    return new Intl.NumberFormat("vi-VN", {
      style: "currency",
      currency: "VND",
    }).format(price);
  };

  const handleVariantChange = (variantId) => {
    setSelectedVariantId(variantId);
    const selectedVariant = product.variants.find((v) => v.id === variantId);

    if (selectedVariant && selectedVariant.variant_details) {
      const attributes = {};
      selectedVariant.variant_details.forEach((attr) => {
        attributes[attr.name] = attr.value;
      });
      setSelectedVariantAttributes(attributes);
    }

    // Reset số lượng
    setQuantity(1);
  };

  const getCurrentPrice = () => {
    if (!product) return null;

    if (product.variants && product.variants.length > 0) {
      const selectedVariant = product.variants.find(
        (v) => v.id === selectedVariantId
      );
      if (selectedVariant) {
        return selectedVariant.discount_price || selectedVariant.price;
      }
    }

    return product.discount_price || product.price;
  };

  const getOriginalPrice = () => {
    if (!product) return null;

    if (product.variants && product.variants.length > 0) {
      const selectedVariant = product.variants.find(
        (v) => v.id === selectedVariantId
      );
      if (selectedVariant && selectedVariant.discount_price) {
        return selectedVariant.price;
      }
    }
    return product.price;
  };

  useEffect(() => {
    const handleVisibilityChange = () => {
      if (document.visibilityState === "visible") {
        refreshProductData();
        checkAuthentication();
      }
    };
    document.addEventListener("visibilitychange", handleVisibilityChange);
    return () =>
      document.removeEventListener("visibilitychange", handleVisibilityChange);
  }, [id, refreshProductData, checkAuthentication]);

  useEffect(() => {
    checkAuthentication();
  }, [location, checkAuthentication]);

  useEffect(() => {
    const handleStorageChange = (e) => {
      if (e.key === "authToken" || e.key === "userData") {
        setTimeout(checkAuthentication, 200);
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
  }, [checkAuthentication, location]);

  const fetchCurrentUser = async (token) => {
    if (!token) return false;
    try {
      const response = await axios.get("http://localhost:8000/api/user", {
        headers: { Authorization: `Bearer ${token}` },
      });
      const userInfo = response.data.data || response.data;
      if (userInfo) {
        setCurrentUser(userInfo);
        setIsLoggedIn(true);
        localStorage.setItem("userData", JSON.stringify(userInfo));
        return true;
      } else {
        setIsLoggedIn(false);
        setCurrentUser(null);
        localStorage.removeItem("authToken");
        localStorage.removeItem("userData");
        return false;
      }
    } catch (error) {
      console.error("Error fetching user:", error.response || error);
      if (error.response?.status === 401 || error.response?.status === 404) {
        setIsLoggedIn(false);
        setCurrentUser(null);
        localStorage.removeItem("authToken");
        localStorage.removeItem("userData");
      }
      return false;
    }
  };

  useEffect(() => {
    if (product && product.variants) {
      const sizeVariants = product.variants.filter((v) => v.variant_id === 2);
      sizeVariants.forEach((variant, index) => {
        console.log(`Kích thước ${index + 1}:`, {
          id: variant.id,
          variant_id: variant.variant_id,
          variant_name: variant.variant_name,
          variant_value_id: variant.variant_value_id,
          variant_value_name: variant.variant_value_name,
          variant_details: variant.variant_details,
          sku: variant.sku,
          price: variant.price,
          quantity: variant.quantity,
        });
      });
    }
  }, [product]);

  const extractVariantTypes = (variants) => {
    const types = new Set();
    if (variants && variants.length > 0) {
      variants.forEach((variant) => {
        if (variant.variant_details) {
          Object.keys(variant.variant_details).forEach((typeId) =>
            types.add(Number(typeId))
          );
        }
      });
    }
    return Array.from(types).sort();
  };

  useEffect(() => {
    if (product && product.variants) {
      const types = extractVariantTypes(product.variants);
      types.forEach((typeId) => {
        const typeValues = getVariantValuesByType(typeId);
      });
    }
  }, [product]);

  const getVariantValuesByType = (typeId) => {
    if (!product || !product.variants) return [];
    const values = new Set();
    product.variants.forEach((variant) => {
      if (variant.variant_details && variant.variant_details[typeId]) {
        const valueId = variant.variant_details[typeId];
        const matchingVariant = product.variants.find(
          (v) =>
            v.variant_info &&
            v.variant_info.id === typeId &&
            v.variant_value &&
            v.variant_value.id === valueId
        );
        if (matchingVariant) {
          values.add({
            id: valueId,
            value: matchingVariant.variant_value.value,
          });
        }
      }
    });
    return Array.from(values);
  };

  const processVariantDetails = (variant) => {
    if (!variant.variant_details) return null;
    try {
      const details =
        typeof variant.variant_details === "string"
          ? JSON.parse(variant.variant_details)
          : variant.variant_details;
      const colorId = details["1"];
      const sizeId = details["2"];
      const variantName =
        variant.variant_value_name || variant.variant_value?.value;
      return {
        colorId: colorId ? String(colorId) : null,
        sizeId: sizeId ? String(sizeId) : null,
        variantName,
      };
    } catch (error) {
      console.error("Lỗi xử lý variant_details:", error);
      return null;
    }
  };

  const getVariantsByType = (variantTypeId) => {
    if (!product || !product.variants) return [];
    const variants = product.variants.filter((variant) => {
      const details = processVariantDetails(variant);
      if (!details) return false;
      return variantTypeId === 1
        ? details.colorId !== undefined
        : details.sizeId !== undefined;
    });
    const uniqueVariants = [];
    const seen = new Set();
    variants.forEach((variant) => {
      const details = processVariantDetails(variant);
      if (!details) return;
      const valueId = variantTypeId === 1 ? details.colorId : details.sizeId;
      if (!seen.has(valueId)) {
        seen.add(valueId);
        uniqueVariants.push(variant);
      }
    });

    return product.discount_price ? product.price : null;
  };

  const getStockQuantity = () => {
    if (!product) return 0;

    if (product.variants && product.variants.length > 0) {
      const selectedVariant = product.variants.find(
        (v) => v.id === selectedVariantId
      );
      if (selectedVariant) {
        return selectedVariant.quantity;
      }
      return 0;
    }

    return product.quantity || 0;
  };

  const handleQuantityChange = (newQuantity) => {
    if (newQuantity < 1) return;
    setIsUpdatingQuantity(true);
    setStockError("");

    try {
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

      if (selectedVariant.quantity >= newQuantity) {
        setQuantity(newQuantity);
        updateCartItemIfExists(product.id, selectedVariant, newQuantity);
      } else {
        setStockError("Số lượng vượt quá tồn kho");
      }
    } catch (error) {
      console.error("Lỗi khi cập nhật số lượng:", error);
      toast.error("Có lỗi xảy ra khi cập nhật số lượng");
    } finally {
      setIsUpdatingQuantity(false);
    }
  };

  const handleCommentSubmit = async () => {
    if (!commentInput.trim()) return;

    try {
      const token = localStorage.getItem("authToken");
      if (!token) {
        setIsLoggedIn(false);
        setCurrentUser(null);
        navigate("/signin", { state: { returnUrl: location.pathname } });
        return;
      }

      const response = await axios.post(
        "http://localhost:8000/api/comments",
        { product_id: id, content: commentInput },
        { headers: { Authorization: `Bearer ${token}` } }
      );

      if (response.data && response.data.data) {
        const newComment = { ...response.data.data, user: currentUser };
        setComments([...comments, newComment]);
        setCommentInput("");
        setFeedbackMessage("Bình luận đã được gửi thành công!");
        setFeedbackType("success");
        setShowFeedback(true);
        setTimeout(() => setShowFeedback(false), 3000);
        setTimeout(() => refreshProductComments(), 500);
      }
    } catch (error) {
      console.error("Lỗi khi gửi bình luận:", error);
      setFeedbackMessage(
        "Không thể gửi bình luận: " +
          (error.response?.data?.message || "Lỗi kết nối")
      );
      setFeedbackType("error");
      setShowFeedback(true);
      setTimeout(() => setShowFeedback(false), 3000);

      if (error.response?.status === 401) {
        localStorage.removeItem("authToken");
        setIsLoggedIn(false);
        setCurrentUser(null);
        navigate("/signin", { state: { returnUrl: location.pathname } });
      }
    }
  };

  if (loading)
    return (
      <div className="p-4 max-w-7xl mx-auto h-screen flex items-center justify-center">
        <div className="animate-spin rounded-full h-16 w-16 border-t-2 border-b-2 border-amber-500"></div>
      </div>
    );

  if (error)
    return (
      <div className="p-4 max-w-7xl mx-auto text-center py-20">
        <h2 className="text-2xl font-bold text-red-500 mb-4">
          Error loading product details
        </h2>
        <p className="text-gray-600">Vui lòng thử lại sau</p>
      </div>
    );

  if (!product)
    return (
      <div className="p-4 max-w-7xl mx-auto text-center py-20">
        <h2 className="text-2xl font-bold text-gray-800 mb-4">
          Product not found
        </h2>
        <p className="text-gray-600">Sản phẩm không tồn tại hoặc đã bị xóa</p>
      </div>
    );

  return (
    <div className="max-w-7xl mx-auto px-4 py-8 mt-20">
      <Toaster position="top-right" />
      <motion.div
        className="bg-white rounded-2xl shadow-lg overflow-hidden"
        initial={{ opacity: 0, y: 20 }}
        animate={{ opacity: 1, y: 0 }}
        transition={{ duration: 0.5 }}
      >
        <div className="grid grid-cols-1 md:grid-cols-2 gap-8 p-6">
          {/* Phần hiển thị hình ảnh */}
          <div className="space-y-4">
            <div className="aspect-square rounded-xl overflow-hidden bg-gray-100 shadow-md">
              <img
                src={
                  activeImage && activeImage.startsWith("http")
                    ? activeImage
                    : `http://localhost:8000/storage/${activeImage}`
                }
                alt={product.name}
                className="w-full h-full object-cover object-center"
                onError={(e) => {
                  e.target.src =
                    "https://via.placeholder.com/600x600?text=No+Image";
                }}
              />
            </div>
          </div>
        </div>
      </motion.div>
    </div>
  );
};

export default ProductDetail;
