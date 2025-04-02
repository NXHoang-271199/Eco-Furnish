import { useState, useEffect, useRef, useCallback } from "react";
import { Link, useParams, useNavigate, useLocation } from "react-router-dom";
import axios from "axios";
import { useDispatch } from "react-redux";
import { addToCart, updateQuantity } from "../../../store/cartSlice";
import AddToCartToast from "../../../components/AddToCartToast";
import { Toaster, toast } from "react-hot-toast";

// Hàm phân tích thông tin biến thể từ SKU
const parseVariantFromSku = (sku) => {
  if (!sku || typeof sku !== "string") return null;
  const parts = sku.split("-");
  if (parts.length < 3) return null;
  const colorSizeCode = parts[1];
  const colorCode = colorSizeCode.substring(0, 2);
  const sizeCode = colorSizeCode.substring(2, 4);
  const colorMap = { NA: "Nâu gỗ", DE: "Đen", TR: "Trắng", XA: "Xám", VA: "Vàng" };
  const sizeMap = { VU: "Vừa", LO: "Lớn", NH: "Nhỏ", TB: "Trung bình" };
  return { color: colorMap[colorCode] || colorCode, size: sizeMap[sizeCode] || sizeCode, colorCode, sizeCode };
};

// Cache đơn giản cho API
const apiCache = {};

const ProductDetail = () => {
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
  const { id } = useParams();
  const navigate = useNavigate();
  const location = useLocation();
  const dispatch = useDispatch();
  const [showToast, setShowToast] = useState(false);
  const [toastProduct, setToastProduct] = useState(null);
  const [editingQuantity, setEditingQuantity] = useState("");
  const [isUpdatingQuantity, setIsUpdatingQuantity] = useState(false);
  const [stockError, setStockError] = useState("");
  const quantityInputRef = useRef(null);
  const isEditingRef = useRef(false);

  // State hiển thị thông báo
  const [showFeedback, setShowFeedback] = useState(false);
  const [feedbackMessage, setFeedbackMessage] = useState("");
  const [feedbackType, setFeedbackType] = useState("success");

  // State lưu thông tin biến thể
  const [variantTypes, setVariantTypes] = useState([]);
  const [variantValues, setVariantValues] = useState([]);
  const [orderTotal, setOrderTotal] = useState(0);

  // Hàm fetch data có cache và hỗ trợ abort
  const fetchWithCache = async (url, signal) => {
    const now = Date.now();
    if (apiCache[url] && now - apiCache[url].timestamp < 5 * 60 * 1000) {
      return apiCache[url].data;
    }
    const response = await axios.get(url, { signal });
    apiCache[url] = { data: response.data, timestamp: now };
    return response.data;
  };

  // Hàm refresh dữ liệu sản phẩm với AbortController
  const refreshProductData = useCallback(async () => {
    setLoading(true);
    const controller = new AbortController();
    try {
      const timestamp = new Date().getTime();
      const url = `http://localhost:8000/api/products/${id}?_=${timestamp}`;
      const response = await fetchWithCache(url, controller.signal);
      const productData = response.data;
      setSelectedVariants({});
      setCurrentPrice(productData.price);
      setCurrentDiscount(productData.discount_price);

      if (productData.variants && Array.isArray(productData.variants)) {
        const variantsWithValues = productData.variants.map((variant, index) => {
          let variantId = 0;
          if (variant.variant_id !== undefined)
            variantId = Number(variant.variant_id);
          else if (variant.type_id !== undefined)
            variantId = Number(variant.type_id);
          else if (variant.attribute_id !== undefined)
            variantId = Number(variant.attribute_id);
          else if (variant.variant_details && typeof variant.variant_details === "object") {
            const keys = Object.keys(variant.variant_details);
            if (keys.length > 0) {
              variantId = Number(keys[0]);
            }
          }

          let variantValueId = 0;
          if (variant.variant_value_id !== undefined)
            variantValueId = Number(variant.variant_value_id);
          else if (variant.value_id !== undefined)
            variantValueId = Number(variant.value_id);
          else if (variant.option_id !== undefined)
            variantValueId = Number(variant.option_id);
          else if (variant.variant_details && typeof variant.variant_details === "object" && variantId > 0) {
            const valueId = variant.variant_details[variantId];
            if (valueId) {
              variantValueId = Number(valueId);
            }
          }

          let variantName = "Không xác định";
          if (variant.variant_name) variantName = variant.variant_name;
          else if (variant.type_name) variantName = variant.type_name;
          else if (variant.attribute_name) variantName = variant.attribute_name;

          let variantValue = "Không xác định";
          if (variant.variant_value_name) variantValue = variant.variant_value_name;
          else if (variant.value_name) variantValue = variant.value_name;
          else if (variant.value) variantValue = variant.value;
          else if (variant.option_name) variantValue = variant.option_name;
          else if (variant.sku) {
            const parsedVariant = parseVariantFromSku(variant.sku);
            if (parsedVariant) {
              variantValue = variantId === 1 ? parsedVariant.color : variantId === 2 ? parsedVariant.size : variantValue;
            }
          }

          return {
            ...variant,
            id: variant.id || index + 1,
            variant_id: variantId,
            variant_name: variantName,
            variant_value_id: variantValueId,
            variant_value_name: variantValue,
            variant_value: { id: variantValueId, value: variantValue },
            variant_info: { id: variantId, name: variantName },
            quantity: Number(variant.quantity || 10),
            price: variant.price || productData.price,
            discount_price:
              variant.discount_price ||
              variant.price ||
              productData.discount_price ||
              productData.price,
            sku: variant.sku || `${productData.id}-variant-${index + 1}`,
          };
        });

        // Sắp xếp các biến thể theo loại
        variantsWithValues.sort((a, b) => a.variant_id - b.variant_id);

        // Thiết lập biến thể mặc định
        const defaultVariants = {};
        const variantTypesArr = [...new Set(variantsWithValues.map((v) => Number(v.variant_id)))]
          .filter((id) => id > 0);

        variantTypesArr.forEach((typeId) => {
          if (typeId === 0) return;
          const availableVariants = variantsWithValues.filter(
            (v) => Number(v.variant_id) === typeId && Number(v.quantity) > 0
          );
          if (availableVariants.length > 0) {
            defaultVariants[typeId] = Number(availableVariants[0].variant_value_id);
          }
        });

        // Thiết lập giá dựa trên biến thể mặc định
        if (Object.keys(defaultVariants).length > 0) {
          const findMatchingVariant = () => {
            const combinedVariants = variantsWithValues.filter((v) => v.variant_id === 3);
            if (combinedVariants.length > 0) {
              const colorId = defaultVariants[1];
              const sizeId = defaultVariants[2];
              const matchingVariant = combinedVariants.find(
                (v) => v.color_id === colorId && v.size_id === sizeId
              );
              if (matchingVariant) return matchingVariant;
            }
            const skuCandidates = new Set();
            Object.entries(defaultVariants).forEach(([typeId, valueId]) => {
              const matches = variantsWithValues.filter(
                (v) => v.variant_id === Number(typeId) && v.variant_value_id === valueId
              );
              matches.forEach((v) => skuCandidates.add(v.sku));
            });
            for (const sku of skuCandidates) {
              const matchingVariants = variantsWithValues.filter((v) => v.sku === sku);
              if (matchingVariants.length > 0) return matchingVariants[0];
            }
            return null;
          };
          const bestVariant = findMatchingVariant();
          if (bestVariant && bestVariant.price) {
            setCurrentPrice(bestVariant.price);
            setCurrentDiscount(bestVariant.discount_price || bestVariant.price);
          }
        }

        setProduct({ ...productData, variants: variantsWithValues });
        setSelectedVariants(defaultVariants);
      } else {
        setProduct(productData);
      }

      // Lấy comments
      const commentsUrl = `http://localhost:8000/api/products/${id}/comments?_=${timestamp}`;
      const commentsResponse = await fetchWithCache(commentsUrl);
      setComments(commentsResponse.data || []);
    } catch (err) {
      console.error("Error refreshing product data:", err);
    } finally {
      setLoading(false);
    }
    return () => controller.abort();
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

  useEffect(() => {
    const handleVisibilityChange = () => {
      if (document.visibilityState === "visible") {
        refreshProductData();
        checkAuthentication();
      }
    };
    document.addEventListener("visibilitychange", handleVisibilityChange);
    return () => document.removeEventListener("visibilitychange", handleVisibilityChange);
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
// hàm này dùng để lấy các biến thể theo loại
  
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
// hàm này dùng để lấy các loại biến thể từ product.variants
  // nếu product và product.variants không tồn tại thì trả về mảng rỗng
  // khai bao mảng types
  // duyệt qua các biến thể trong product.variants
  // khai bao mảng values
  // nếu variant.variant_details tồn tại thì duyệt qua các loại biến thể
  // khai bao mảng typeId
  // nếu typeId không tồn tại thì gán cho biến types
  // trả về mảng types
  const extractVariantTypes = (variants) => {
    const types = new Set();
    if (variants && variants.length > 0) {
      variants.forEach((variant) => {
        if (variant.variant_details) {
          Object.keys(variant.variant_details).forEach((typeId) => types.add(Number(typeId)));
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
// hàm này dùng để lấy các giá trị biến thể theo loại
  // nếu product và product.variants không tồn tại thì trả về mảng rỗng
  // khai bao mảng values
  // duyệt qua các biến thể trong product.variants
  // nếu variant.variant_details tồn tại và có typeId thì lấy giá trị id của biến thể
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
          values.add({ id: valueId, value: matchingVariant.variant_value.value });
        }
      }
    });
    return Array.from(values);
  };
// hàm này dùng để xử lý variant_details nếu variant_details không tồn tại thì trả về null
  // nếu variant_details là chuỗi thì chuyển đổi thành đối tượng
  // nếu không thì trả về variant_details
  // sau đó lấy colorId và sizeId từ variant_details
  // nếu colorId và sizeId không tồn tại thì trả về null
  const processVariantDetails = (variant) => {
    if (!variant.variant_details) return null;
    try {
      const details = typeof variant.variant_details === "string"
        ? JSON.parse(variant.variant_details)
        : variant.variant_details;
      const colorId = details["1"];
      const sizeId = details["2"];
      const variantName = variant.variant_value_name || variant.variant_value?.value;
      return { colorId: colorId ? String(colorId) : null, sizeId: sizeId ? String(sizeId) : null, variantName };
    } catch (error) {
      console.error("Lỗi xử lý variant_details:", error);
      return null;
    }
  };
// hàm này lấy các biến thể theo loại sau đó nếu 
  const getVariantsByType = (variantTypeId) => {
    //nếu product và product.variants tồn tại thì gán cho biến variants để lấy các biến thể sau đó sử lý biến thể theo loại 
    if (!product || !product.variants) return [];
    const variants = product.variants.filter((variant) => {
      // kiểm tra xem biến thể có thuộc loại không khi lý biến
      //  thể theo loại nếu detail không tìm thấy thì 
      // return false còn không thì return variantType Id === 1 ? details.colorId !== undefined : details.sizeId !== undefined;
      const details = processVariantDetails(variant);
      if (!details) return false;
      return variantTypeId === 1 ? details.colorId !== undefined : details.sizeId !== undefined;
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
    return uniqueVariants;
  };
// dùng để lấy các biến thể theo loại
  useEffect(() => {
    // nếu product và product.variants tồn tại
    if (product && product.variants) {
      // thì lấy các biến thể theo loại gán cho biến colorVariants và sizeVariants
      const colorVariants = getVariantsByType(1);
      const sizeVariants = getVariantsByType(2);
      console.log(
        "Biến thể màu sắc:",
        colorVariants.map((v) => ({ variant_details: v.variant_details, sku: v.sku, parsed: parseVariantFromSku(v.sku) }))
      );
      console.log(
        "Biến thể kích thước:",
        sizeVariants.map((v) => ({ variant_details: v.variant_details, sku: v.sku, parsed: parseVariantFromSku(v.sku) }))
      );
    }
  }, [product]);

  useEffect(() => {
    const accessToken = localStorage.getItem("access_token");
    const authToken = localStorage.getItem("authToken");
    if (accessToken && !authToken) {
      localStorage.setItem("authToken", accessToken);
      console.log("Đã đồng bộ từ access_token sang authToken");
    } else if (!accessToken && authToken) {
      localStorage.setItem("access_token", authToken);
      console.log("Đã đồng bộ từ authToken sang access_token");
    }
  }, []);

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

  const baseURL = "http://localhost:8000/";
  const mainImageUrl =
    selectedImage ||
    (product.image_thumnail
      ? product.image_thumnail.startsWith("http")
        ? product.image_thumnail
        : `${baseURL}storage/${product.image_thumnail}`
      : "https://via.placeholder.com/400x400?text=No+Image");

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
// hàm xử lý khi nhấn vào ảnh trong bộ sưu tập
  const handleGalleryImageClick = (imageUrl) => {
    setSelectedImage(imageUrl);
  };
// hàm xử lý khi nhấn vào biến thể
  const handleVariantSelect = (variantType, value) => {
    // hàm này được gọi khi người dùng chọn một biến thể
    setSelectedVariants((prev) => ({ ...prev, [variantType]: value }));
    // nếu biến thể đã được chọn thì không làm gì cả
    if (product.variants) {
      const selectedVariant = product.variants.find((variant) => {
        const variantDetails = variant.variant_details;
        const colorMatch =
          variantDetails[0]?.value === (variantType === 1 ? value : selectedVariants[1]);
        const sizeMatch =
          variantDetails[1]?.value === (variantType === 2 ? value : selectedVariants[2]);
        return colorMatch && sizeMatch;
      });
      // nếu biến thể đã được chọn thì cập nhật giá và giảm giá
      if (selectedVariant) {
        setCurrentPrice(selectedVariant.price);
        setCurrentDiscount(selectedVariant.discount_price);
      }
    }
  };

  const isVariantInStock = (variant) => variant && variant.quantity > 0;
  const hasSelectedAllRequiredVariants = () => {
    if (!product.variants || product.variants.length === 0) return true;
    const availableVariantTypes = [...new Set(product.variants.map((v) => v.variant_id))];
    return availableVariantTypes.every((type) => selectedVariants[type]);
  };
// hàm thêm sản phẩm vào giỏ hàng
  const handleAddToCart = async () => {
    // nếu chưa chọn biến thể màu sắc hoặc kích thước
    if (!selectedVariants[1] || !selectedVariants[2]) {
      toast.error("Vui lòng chọn đầy đủ màu sắc và kích thước", {
        duration: 2000,
        style: { background: "#fff", color: "#e11d48", border: "1px solid #fecdd3" },
      });
      return;
    }
    const selectedVariant = product.variants.find((variant) => {
      const variantDetails = variant.variant_details;
      return (
        variantDetails[0]?.value === selectedVariants[1] &&
        variantDetails[1]?.value === selectedVariants[2]
      );
    });
    //nếu selectedVariant không tồn tại
    if (!selectedVariant) {
      toast.error("Không tìm thấy biến thể phù hợp", {
        duration: 2000,
        style: { background: "#fff", color: "#e11d48", border: "1px solid #fecdd3" },
      });
      return;
    }
    if (selectedVariant.quantity < quantity) {
      toast.error(`Chỉ còn ${selectedVariant.quantity} sản phẩm trong kho`, {
        duration: 3000,
        style: { background: "#fff", color: "#e11d48", border: "1px solid #fecdd3" },
      });
      return;
    }
    try {
      const token = localStorage.getItem("authToken");
      if (!token) {
        navigate("/signin", { state: { returnUrl: location.pathname } });
        return;
      }
      const finalPrice = selectedVariant.discount_price || selectedVariant.price;
      const cartItem = {
        product_id: product.id,
        product: { ...product, price: finalPrice },
        quantity: quantity,
        price: finalPrice * quantity,
        variant_details: selectedVariant.variant_details,
      };
      console.log("Gửi dữ liệu đến API:", {
        product_id: product.id,
        product_variant_id: selectedVariant.id,
        quantity: quantity,
      });
      const response = await axios.post(
        "http://localhost:8000/api/cart/add",
        { product_id: product.id, product_variant_id: selectedVariant.id, quantity: quantity },
        {
          headers: {
            Authorization: `Bearer ${token}`,
            "Content-Type": "application/json",
            Accept: "application/json",
          },
        }
      );
      if (response.data.success || response.data.message === "Thêm vào giỏ hàng thành công") {
        dispatch(addToCart(cartItem));
        toast.success(response.data.message || "Đã thêm vào giỏ hàng", {
          duration: 2000,
          style: { background: "#fff", color: "#059669", border: "1px solid #a7f3d0" },
        });
      } else {
        toast.error(response.data.message || "Không thể thêm vào giỏ hàng", {
          duration: 2000,
          style: { background: "#fff", color: "#e11d48", border: "1px solid #fecdd3" },
        });
      }
    } catch (error) {
      console.error("Lỗi khi thêm vào giỏ hàng:", error.response || error);
      if (error.response?.status === 401) {
        localStorage.removeItem("authToken");
        navigate("/signin", { state: { returnUrl: location.pathname } });
        return;
      }
      toast.error(error.response?.data?.message || "Có lỗi xảy ra khi thêm vào giỏ hàng", {
        duration: 2000,
        style: { background: "#fff", color: "#e11d48", border: "1px solid #fecdd3" },
      });
    }
  };

  const handleBuyNow = () => {
    // nếu chua chon mau sac va kich thuoc thi khong cho mua
    if (!selectedVariants[1] || !selectedVariants[2]) {
      toast.error("Vui lòng chọn đầy đủ màu sắc và kích thước", {
        duration: 2000,
        style: { background: "#fff", color: "#e11d48", border: "1px solid #fecdd3" },
      });
      return;
    }
    handleAddToCart();
    navigate("/payment");
  };

  const handleQuantityInputChange = (value) => setEditingQuantity(value);
  const handleQuantityInputFocus = () => { isEditingRef.current = true; };
  const handleQuantityInputBlur = (inputValue) => {
    isEditingRef.current = false;
    setEditingQuantity("");
    const newValue = parseInt(inputValue, 10);
    if (!isNaN(newValue) && newValue > 0) {
      handleQuantityChange(newValue);
    } else {
      setQuantity(1);
      setStockError("");
    }
  };
  const handleKeyDown = (e) => { if (e.key === "Enter") quantityInputRef.current.blur(); };

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
        setStockError(`Chỉ còn ${selectedVariant.quantity} sản phẩm trong kho`);
      }
    } catch (error) {
      console.error("Lỗi xử lý:", error);
      setStockError("Có lỗi xảy ra khi cập nhật số lượng");
    } finally {
      setIsUpdatingQuantity(false);
    }
  };
// hàm cập nhật số lượng sản phẩm trong giỏ hàng
  const updateCartItemIfExists = async (productId, variant, newQuantity) => {
    const token = localStorage.getItem("authToken");
    if (!token) return;
    try {
      const cartResponse = await axios.get("http://localhost:8000/api/cart", {
        headers: { Authorization: `Bearer ${token}` },
      });
      if (!cartResponse.data.items || cartResponse.data.items.length === 0) return;
      const cartItem = cartResponse.data.items.find(
        (item) =>
          item.product_id === productId &&
          JSON.stringify(item.variant_details) === JSON.stringify(variant.variant_details)
      );
      if (!cartItem) return;
      await axios.put(
        `http://localhost:8000/api/cart/update/${cartItem.id}`,
        { quantity: newQuantity },
        { headers: { Authorization: `Bearer ${token}` } }
      );
      dispatch(updateQuantity({ productId, quantity: newQuantity, variant_details: variant.variant_details }));
      toast.success("Đã cập nhật số lượng trong giỏ hàng", {
        duration: 2000,
        style: { background: "#fff", color: "#059669", border: "1px solid #a7f3d0" },
      });
    } catch (error) {
      console.error("Lỗi khi cập nhật giỏ hàng:", error);
      if (error.response) {
        if (error.response.status === 401) {
          localStorage.removeItem("authToken");
          toast.error("Phiên đăng nhập hết hạn. Vui lòng đăng nhập lại", {
            duration: 2000,
            style: { background: "#fff", color: "#e11d48", border: "1px solid #fecdd3" },
          });
          navigate("/signin", { state: { returnUrl: location.pathname } });
          return;
        } else if (error.response.status === 500) {
          const errorMessage = error.response?.data?.message || "";
          if (errorMessage.includes("property") && errorMessage.includes("null")) {
            toast.error("Sản phẩm này không còn tồn tại trên hệ thống", {
              duration: 3000,
              style: { background: "#fff", color: "#e11d48", border: "1px solid #fecdd3" },
            });
          } else {
            toast.error(`Lỗi server: ${errorMessage}. Vui lòng thử lại sau.`, {
              duration: 3000,
              style: { background: "#fff", color: "#e11d48", border: "1px solid #fecdd3" },
            });
          }
        } else {
          toast.error(error.response?.data?.message || "Có lỗi xảy ra khi cập nhật giỏ hàng", {
            duration: 2000,
            style: { background: "#fff", color: "#e11d48", border: "1px solid #fecdd3" },
          });
        }
      } else {
        toast.error("Lỗi kết nối đến server", {
          duration: 2000,
          style: { background: "#fff", color: "#e11d48", border: "1px solid #fecdd3" },
        });
      }
    }
  };

  const increaseQuantity = () => handleQuantityChange(quantity + 1);
  const decreaseQuantity = () => { if (quantity > 1) handleQuantityChange(quantity - 1); };
// hàm lấy tên loại biến thể
  const getVariantTypeName = (variantId) => {
    const typeFromState = variantTypes.find((t) => t.id === variantId);
    if (typeFromState) return typeFromState.name;
    const variant = product.variants?.find((v) => v.variant_id === variantId);
    if (variant && variant.variant_info?.name) return variant.variant_info.name;
    const variantTypeNames = { 1: "Màu sắc", 2: "Kích thước"};
    return variantTypeNames[variantId] || "Biến thể";
  };
// hàm lấy tên giá trị biến thể
  const getVariantValueName = (variantId, valueId) => {
    const valueFromState = variantValues.find((v) => v.id === valueId && v.variant_id === variantId);
    if (valueFromState) return valueFromState.value;
    const variant = product.variants?.find(
      (v) => v.variant_id === variantId && v.variant_value_id === valueId
    );
    if (variant) {
      if (variant.variant_value?.value) return variant.variant_value.value;
      if (variant.variant_value_name) return variant.variant_value_name;
    }
    return "Không xác định";
  };

  const handleCommentSubmit = async () => {
    await checkAuthentication();
    if (!isLoggedIn || !currentUser) {
      navigate("/signin", { state: { returnUrl: location.pathname } });
      return;
    }
    if (!commentInput.trim()) return;
    try {
      const token = localStorage.getItem("authToken");
      if (!token) {
        setIsLoggedIn(false);
        setCurrentUser(null);
        navigate("/signin", { state: { returnUrl: location.pathname } });
        return;
      }
      let userData = null;
      try {
        userData = JSON.parse(localStorage.getItem("userData") || "{}");
        console.log("Gửi bình luận với userData:", userData);
      } catch (e) {
        console.error("Lỗi parse userData:", e);
      }
      const response = await axios.post(
        `http://localhost:8000/api/comments`,
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
      console.error("Error submitting comment:", error.response || error);
      setFeedbackMessage("Không thể gửi bình luận: " + (error.response?.data?.message || "Lỗi kết nối"));
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
// hàm render biến thể của sản phẩm 
  const renderVariants = () => {
    // nếu không có biến thể trả về null
    if (!product.variants) return null;
    const uniqueColors = [...new Set(product.variants.map((v) => v.variant_details[0]?.value))];
    const uniqueSizes = [...new Set(product.variants.map((v) => v.variant_details[1]?.value))];
    //trả về các biến thể màu sắc và kích thước
    return (
      <div className="space-y-6">
        <div className="variant-section">
          <div className="flex items-center gap-2 mb-3">
            <span className="text-gray-700 font-medium">Màu sắc:</span>
            {selectedVariants[1] && <span className="text-green-600 text-sm">Đã chọn: {selectedVariants[1]}</span>}
          </div>
          <div className="flex flex-wrap gap-2">
            {uniqueColors.map((color, index) => (
              <button
                key={index}
                onClick={() => handleVariantSelect(1, color)}
                className={`
                  px-4 py-2 rounded-lg font-medium text-sm transition-all duration-200 transform hover:scale-105
                  ${selectedVariants[1] === color ? "bg-green-50 text-green-700 border-2 border-green-500 shadow-sm" : "bg-white text-gray-700 border border-gray-200 hover:border-gray-300 hover:bg-gray-50"}
                `}
              >
                <div className="flex items-center gap-2">
                  {selectedVariants[1] === color && (
                    <svg xmlns="http://www.w3.org/2000/svg" className="h-4 w-4 text-green-500" viewBox="0 0 20 20" fill="currentColor">
                      <path fillRule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clipRule="evenodd" />
                    </svg>
                  )}
                  {color}
                </div>
              </button>
            ))}
          </div>
        </div>
        <div className="variant-section">
          <div className="flex items-center gap-2 mb-3">
            <span className="text-gray-700 font-medium">Kích thước:</span>
            {selectedVariants[2] && <span className="text-green-600 text-sm">Đã chọn: {selectedVariants[2]}</span>}
          </div>
          <div className="flex flex-wrap gap-2">
            {uniqueSizes.map((size, index) => (
              <button
                key={index}
                onClick={() => handleVariantSelect(2, size)}
                className={`
                  min-w-[80px] px-4 py-2 rounded-lg font-medium text-sm transition-all duration-200 transform hover:scale-105
                  ${selectedVariants[2] === size ? "bg-green-50 text-green-700 border-2 border-green-500 shadow-sm" : "bg-white text-gray-700 border border-gray-200 hover:border-gray-300 hover:bg-gray-50"}
                `}
              >
                <div className="flex items-center justify-center gap-2">
                  {selectedVariants[2] === size && (
                    <svg xmlns="http://www.w3.org/2000/svg" className="h-4 w-4 text-green-500" viewBox="0 0 20 20" fill="currentColor">
                      <path fillRule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clipRule="evenodd" />
                    </svg>
                  )}
                  {size}
                </div>
              </button>
            ))}
          </div>
        </div>
        <div className="border-b border-gray-200 my-6"></div>
      </div>
    );
  };
//trả về các option cơ bản của sản phẩm bao gồm màu sắc, kích thước biến thể
  const getVariantOptions = () => {
    if (!product || !product.variants) return [];
    return extractVariantTypes(product.variants);
    
  };
  console.log("Các biến thể:", product.variants);

  return (
    <>
      <Toaster position="top-right" />
      <main className="max-w-6xl mx-auto mb-20 mt-32">
        <div className="container mx-auto p-6">
          <div className="flex flex-col lg:flex-row">
            <div className="lg:w-1/2">
              <div className="relative h-[650px] bg-gray-50 rounded-xl shadow-md flex justify-center items-center p-2 overflow-hidden border border-gray-100">
                <div className="absolute inset-0 bg-gradient-to-t from-gray-100 to-transparent opacity-20"></div>
                <img
                  src={mainImageUrl}
                  alt={product.name || "Sản phẩm"}
                  loading="lazy"
                  className="max-h-[620px] max-w-[95%] object-contain z-10 transition-all duration-500 hover:scale-105"
                  onError={(e) => {
                    console.log("Main Image Load Error:", e);
                    e.target.src = "https://via.placeholder.com/600x600?text=Image+Error";
                  }}
                />
                {product.discount_price && Number(product.discount_price) !== Number(product.price) && (
                  <span className="absolute top-4 left-4 bg-red-500 text-white text-sm font-bold px-3 py-1.5 rounded-full z-20 shadow-lg transform -rotate-2">
                    GIẢM GIÁ
                  </span>
                )}
              </div>
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
                            selectedImage === galleryImageUrl ? "border-green-500 shadow-md" : "border-gray-200 hover:border-gray-300"
                          }`}
                          onClick={() => handleGalleryImageClick(galleryImageUrl)}
                        >
                          <div className="aspect-w-1 aspect-h-1">
                            <img
                              src={galleryImageUrl}
                              alt={`Ảnh ${index + 1}`}
                              loading="lazy"
                              className="w-full h-full object-cover"
                              onError={(e) => {
                                console.log(`Gallery Image ${index + 1} Load Error:`, e);
                                e.target.src = "https://via.placeholder.com/100x100?text=Error";
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
                      <svg xmlns="http://www.w3.org/2000/svg" className="h-6 w-6 mx-auto text-gray-400 mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2?"/>
                      </svg>
                      <span className="text-sm text-gray-500">Không có ảnh bổ sung</span>
                    </div>
                  )}
                </div>
              </div>
            </div>
            <div className="lg:w-1/2 lg:pl-10 mt-6 lg:mt-0">
              <h1 className="text-2xl md:text-3xl font-extrabold text-gray-800 leading-tight mb-4">
                {product.name || "Tên sản phẩm"}
              </h1>
              <div className="flex items-center mt-6">
                {currentDiscount && currentPrice && Number(currentDiscount) !== Number(currentPrice) && (
                  <span className="text-gray-500 line-through mr-2">{formatPrice(currentPrice)}</span>
                )}
                <span className="text-2xl font-semibold text-green-600">
                  {currentDiscount ? formatPrice(currentDiscount) : currentPrice ? formatPrice(currentPrice) : formatPrice(product.price)}
                </span>
              </div>
              {isSampleProduct && (
                <p className="mt-4 text-red-500 font-medium">Đây là sản phẩm mẫu không bán</p>
              )}
              <p className="mt-4 text-gray-600">{product.short_description || "Không có mô tả"}</p>
              <div className="mt-6 space-y-4">{renderVariants()}</div>
              {error && <div className="mt-2 text-red-500 text-sm">{error}</div>}
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
                    ref={quantityInputRef}
                    type="text"
                    value={isEditingRef.current ? editingQuantity : quantity}
                    onChange={(e) => handleQuantityInputChange(e.target.value)}
                    onFocus={handleQuantityInputFocus}
                    onBlur={(e) => handleQuantityInputBlur(e.target.value)}
                    onKeyDown={handleKeyDown}
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
              {stockError && <p className="text-red-500 text-xs mt-2">{stockError}</p>}
              <div className="flex gap-2 mt-4">
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
                    <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    MUA NGAY
                  </button>
                </div>
              </div>
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
                    ? product.variants.reduce((sum, v) => sum + (v.quantity || 0), 0)
                    : product.quantity || 0}
                </p>
              </div>
            </div>
          </div>
        </div>
        <div className="mt-12 px-6">
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
              <svg xmlns="http://www.w3.org/2000/svg" className="h-6 w-6 mr-2 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
              </svg>
              Bình luận khách hàng
            </h3>
            {isLoggedIn && currentUser ? (
              <div className="mb-10 bg-white rounded-lg shadow-sm overflow-hidden">
                <form className="w-full divide-y divide-gray-100" onSubmit={(e) => { e.preventDefault(); handleCommentSubmit(); }}>
                  {showFeedback && (
                    <div className={`p-4 rounded-lg mb-4 ${feedbackType === "success" ? "bg-green-50 text-green-700 border-l-4 border-green-500" : "bg-red-50 text-red-700 border-l-4 border-red-500"}`}>
                      <div className="flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" className={`h-5 w-5 mr-2 ${feedbackType === "success" ? "text-green-500" : "text-red-500"}`} viewBox="0 0 20 20" fill="currentColor">
                          {feedbackType === "success" ? (
                            <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clipRule="evenodd" />
                          ) : (
                            <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clipRule="evenodd" />
                          )}
                        </svg>
                        {feedbackMessage}
                      </div>
                    </div>
                  )}
                  <div className="px-6 pt-5 pb-4">
                    <div className="flex items-center mb-3">
                      {currentUser?.avatar ? (
                        <img src={currentUser.avatar} alt="Avatar" className="w-10 h-10 rounded-full object-cover mr-3 border border-gray-200" />
                      ) : (
                        <div className="w-10 h-10 rounded-full bg-green-100 text-green-600 flex items-center justify-center font-semibold mr-3">
                          {currentUser?.name?.charAt(0) || "U"}
                        </div>
                      )}
                      <div>
                        <p className="font-medium text-gray-700">{currentUser?.name || "Người dùng"}</p>
                        <p className="text-xs text-gray-500">{currentUser?.email}</p>
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
                      <span className="mr-1">💬</span> Bình luận sẽ được hiển thị công khai
                    </div>
                    <button type="submit" disabled={!commentInput.trim()} className={`px-5 py-2 bg-green-600 text-white rounded-md text-sm font-medium transition ${!commentInput.trim() ? "opacity-50 cursor-not-allowed" : "hover:bg-green-700"}`}>
                      Gửi bình luận
                    </button>
                  </div>
                </form>
              </div>
            ) : (
              <div className="text-center p-8 bg-gray-50 rounded-lg mb-8 border border-gray-100 shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" className="h-10 w-10 mx-auto text-gray-400 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v4h8z" />
                </svg>
                <p className="text-gray-600 mb-4">Bạn cần đăng nhập để bình luận về sản phẩm này</p>
                <Link
                  to="/signin"
                  state={{ returnUrl: location.pathname }}
                  className="inline-block px-5 py-2 bg-green-600 text-white rounded-md font-medium hover:bg-green-700 transition"
                  onClick={() => localStorage.setItem("returnPath", location.pathname)}
                >
                  Đăng nhập ngay
                </Link>
              </div>
            )}
            <div className="space-y-6">
              {comments.length > 0 ? (
                <>
                  <h4 className="font-medium text-gray-500 mb-2">{comments.length} bình luận</h4>
                  {comments.map((comment) => (
                    <div key={comment.id} className="bg-white rounded-lg shadow-sm overflow-hidden border border-gray-100">
                      <div className="p-5">
                        <div className="flex items-start">
                          {comment.user?.avatar ? (
                            <img src={comment.user?.avatar} alt="User avatar" className="w-10 h-10 rounded-full object-cover mr-4 border border-gray-200" />
                          ) : (
                            <div className="w-10 h-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-semibold mr-4">
                              {comment.user?.name?.charAt(0) || "U"}
                            </div>
                          )}
                          <div className="flex-1">
                            <div className="flex justify-between items-center mb-1">
                              <h4 className="font-semibold text-gray-800">{comment.user?.name || "Người dùng ẩn danh"}</h4>
                              <span className="text-xs text-gray-500">
                                {new Date(comment.created_at).toLocaleDateString("vi-VN", {
                                  year: "numeric", month: "short", day: "numeric", hour: "2-digit", minute: "2-digit",
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
                  <svg xmlns="http://www.w3.org/2000/svg" className="h-14 w-14 mx-auto text-gray-400 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1} d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
                  </svg>
                  <p className="text-gray-500 text-lg mb-2">Chưa có bình luận nào</p>
                  <p className="text-gray-400">Hãy là người đầu tiên bình luận về sản phẩm này</p>
                </div>
              )}
            </div>
          </div>
        </div>
        <AddToCartToast isVisible={showToast} product={toastProduct} onClose={() => setShowToast(false)} />
      </main>
    </>
  );
};

export default ProductDetail;
