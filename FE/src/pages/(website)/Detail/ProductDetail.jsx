import React, { useEffect, useRef, useCallback, useState } from "react";
import { useParams, useNavigate } from "react-router-dom";
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

  useEffect(() => {
    axios
      .get(`http://localhost:8000/api/products/${id}`)
      .then((response) => {
        const productData = response.data.data;
        setProduct(productData);
        setActiveImage(productData.image_thumnail);
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

        // Nếu có biến thể, thiết lập biến thể đầu tiên là mặc định
        if (productData.variants && productData.variants.length > 0) {
          setSelectedVariantId(productData.variants[0].id);

          // Khởi tạo thuộc tính biến thể nếu có
          if (
            productData.variants[0].variant_details &&
            productData.variants[0].variant_details.length > 0
          ) {
            const initialAttributes = {};
            productData.variants[0].variant_details.forEach((attr) => {
              initialAttributes[attr.name] = attr.value;
            });
            setSelectedVariantAttributes(initialAttributes);
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

        setLoading(false);
      })
      .catch((error) => {
        console.error("Lỗi khi lấy dữ liệu sản phẩm:", error);
        setError(error);
        setLoading(false);
      });
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
    }

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

  const increaseQuantity = () => {
    const stockQuantity = getStockQuantity();
    if (quantity < stockQuantity) {
      setQuantity(quantity + 1);
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
// hàm thêm sản phẩm vào giỏ hàng
  const handleAddToCart = async () => {
    const token = localStorage.getItem("authToken");

    if (!token) {
      toast.error("Vui lòng đăng nhập để thêm sản phẩm vào giỏ hàng");
      navigate("/sign-in");
    // nếu chưa chọn biến thể màu sắc hoặc kích thước
    if (!selectedVariants[1] || !selectedVariants[2]) {
      toast.error("Vui lòng chọn đầy đủ màu sắc và kích thước", {
        duration: 2000,
        style: { background: "#fff", color: "#e11d48", border: "1px solid #fecdd3" },
      });
      return;
    }

    if (getStockQuantity() <= 0) {
      toast.error("Sản phẩm đã hết hàng");
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

    setAddingToCart(true);

    try {
      const cartData = {
        product_id: product.id,
        product_variant_id: selectedVariantId,
        quantity: quantity,
      };

      const response = await axios.post(
        "http://localhost:8000/api/cart/add",
        cartData,
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

      if (response.status === 201) {
        toast.custom(
          (t) => (
            <div
              className={`${
                t.visible ? "animate-enter" : "animate-leave"
              } max-w-md w-full bg-white shadow-lg rounded-lg pointer-events-auto flex ring-1 ring-black ring-opacity-5`}
            >
              <div className="flex-1 w-0 p-4">
                <div className="flex items-start">
                  <div className="flex-shrink-0 pt-0.5">
                    <IoCheckmarkCircle className="h-10 w-10 text-green-500" />
                  </div>
                  <div className="ml-3 flex-1">
                    <p className="text-sm font-medium text-gray-900">
                      Đã thêm sản phẩm vào giỏ hàng!
                    </p>
                    <p className="mt-1 text-sm text-gray-500">
                      {product.name}{" "}
                      {selectedVariantId
                        ? `(${Object.entries(selectedVariantAttributes)
                            .map(([key, value]) => `${key}: ${value}`)
                            .join(", ")})`
                        : ""}
                    </p>
                  </div>
                </div>
              </div>
              <div className="flex border-l border-gray-200">
                <button
                  onClick={() => {
                    toast.dismiss(t.id);
                    navigate("/cart");
                  }}
                  className="w-full border border-transparent rounded-none rounded-r-lg p-4 flex items-center justify-center text-sm font-medium text-amber-600 hover:text-amber-500 focus:outline-none"
                >
                  Xem giỏ hàng
                </button>
              </div>
            </div>
          ),
          { duration: 3000 }
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
      }
    } catch (error) {
      console.error("Lỗi khi thêm vào giỏ hàng:", error);
      if (
        error.response &&
        error.response.data &&
        error.response.data.message
      ) {
        toast.error(error.response.data.message);
      });
      if (!selectedVariant) {
        setStockError("Không tìm thấy biến thể phù hợp");
        return;
      }
      if (selectedVariant.quantity >= newQuantity) {
        setQuantity(newQuantity);
        updateCartItemIfExists(product.id, selectedVariant, newQuantity);
      } else {
        toast.error("Có lỗi xảy ra khi thêm vào giỏ hàng");
      }
    } finally {
      setAddingToCart(false);
    }
  };

  const handleBuyNow = async () => {
    const token = localStorage.getItem("authToken");

    if (!token) {
      toast.error("Vui lòng đăng nhập để mua sản phẩm");
      navigate("/sign-in");
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

    if (getStockQuantity() <= 0) {
      toast.error("Sản phẩm đã hết hàng");
      return;
    }

    setAddingToBuy(true);

    try {
      // Thêm vào giỏ hàng trước
      const cartData = {
        product_id: product.id,
        product_variant_id: selectedVariantId,
        quantity: quantity,
      };

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
        "http://localhost:8000/api/cart/add",
        cartData,
        {
          headers: {
            Authorization: `Bearer ${token}`,
          },
        }
      );

      if (response.status === 201) {
        // Chuyển tới trang thanh toán với các thông tin sản phẩm vừa thêm
        navigate("/payment", {
          state: {
            selectedProducts: [response.data.cartItem],
            total: response.data.cartItem.total_price,
            buyNow: true,
          },
        });
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
      console.error("Lỗi khi mua ngay:", error);
      if (
        error.response &&
        error.response.data &&
        error.response.data.message
      ) {
        toast.error(error.response.data.message);
      } else {
        toast.error("Có lỗi xảy ra khi xử lý mua ngay");
      }
    } finally {
      setAddingToBuy(false);
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

            {/* Gallery */}
            {product.gallery && product.gallery.length > 0 && (
              <div className="flex space-x-2 overflow-x-auto pb-2">
                <div
                  onClick={() => setActiveImage(product.image_thumnail)}
                  className={`flex-shrink-0 w-20 h-20 rounded-lg overflow-hidden cursor-pointer border-2 ${
                    activeImage === product.image_thumnail
                      ? "border-amber-500"
                      : "border-transparent"
                  }`}
                >
                  <img
                    src={
                      product.image_thumnail &&
                      product.image_thumnail.startsWith("http")
                        ? product.image_thumnail
                        : `http://localhost:8000/storage/${product.image_thumnail}`
                    }
                    alt="thumbnail"
                    className="w-full h-full object-cover object-center"
                    onError={(e) => {
                      e.target.src =
                        "https://via.placeholder.com/80x80?text=No+Image";
                    }}
                  />
                </div>

                {product.gallery.map((image, index) => (
                  <div
                    key={index}
                    onClick={() => setActiveImage(image.image_url)}
                    className={`flex-shrink-0 w-20 h-20 rounded-lg overflow-hidden cursor-pointer border-2 ${
                      activeImage === image.image_url
                        ? "border-amber-500"
                        : "border-transparent"
                    }`}
                  >
                    <img
                      src={
                        image.image_url && image.image_url.startsWith("http")
                          ? image.image_url
                          : `http://localhost:8000/storage/${image.image_url}`
                      }
                      alt={`gallery-${index}`}
                      className="w-full h-full object-cover object-center"
                      onError={(e) => {
                        e.target.src =
                          "https://via.placeholder.com/80x80?text=No+Image";
                      }}
                    />
                  </div>
                ))}
              </div>
            )}
          </div>

          {/* Phần thông tin sản phẩm */}
          <div className="space-y-6">
            <div>
              <h1 className="text-3xl font-bold text-gray-800">
                {product.name}
              </h1>
              <p className="text-gray-600 mt-2">
                Mã sản phẩm: {product.product_code}
              </p>

              {/* Đánh giá */}
              <div className="flex items-center mt-4">
                <div className="flex">
                  {[1, 2, 3, 4, 5].map((star) => (
                    <IoStar
                      key={star}
                      className={`w-5 h-5 ${
                        star <= 4 ? "text-amber-400" : "text-gray-300"
                      }`}
                    />
                  ))}
                </div>
                <span className="text-gray-500 ml-2">(4.0)</span>
                <span className="mx-2 text-gray-300">|</span>
                <span className="text-gray-500">Đã bán: 120</span>
                <span className="mx-2 text-gray-300">|</span>
                <span className="text-gray-500">
                  Danh mục: {product.category.name}
                </span>
              </div>
            </div>

            {/* Giá */}
            <div className="py-4 border-t border-b border-gray-100">
              <div className="flex items-baseline">
                {getCurrentPrice() ? (
                  <>
                    <span className="text-2xl font-semibold text-amber-500">
                      {formatPrice(getCurrentPrice())}
                    </span>

                    {getOriginalPrice() && (
                      <>
                        <span className="ml-3 text-lg text-gray-400 line-through">
                          {formatPrice(getOriginalPrice())}
                        </span>
                        <span className="ml-3 bg-amber-100 text-amber-700 px-2 py-1 rounded-md text-sm font-medium">
                          {Math.round(
                            (1 - getCurrentPrice() / getOriginalPrice()) * 100
                          )}
                          % giảm
                        </span>
                      </>
                    )}
                  </>
                ) : (
                  <span className="text-xl font-medium text-gray-700">
                    Liên hệ để biết giá
                  </span>
                )}
              </div>

              <p className="mt-2 text-gray-600">
                Trạng thái:
                <span
                  className={`ml-2 font-medium ${
                    getStockQuantity() > 0 ? "text-green-600" : "text-red-500"
                  }`}
                >
                  {getStockQuantity() > 0 ? "Còn hàng" : "Hết hàng"}
                </span>
                {getStockQuantity() > 0 && (
                  <span className="ml-2 text-gray-500">
                    ({getStockQuantity()} sản phẩm)
                  </span>
                )}
              </p>
            </div>

            {/* Biến thể sản phẩm */}
            {product.variants && product.variants.length > 0 && (
              <div className="space-y-4">
                <h3 className="font-semibold text-gray-800">Phiên bản</h3>

                {/* Group bởi các loại thuộc tính khác nhau */}
                {(() => {
                  // Tạo danh sách các nhóm thuộc tính độc nhất
                  const attributeGroups = {};

                  // Thu thập tất cả các thuộc tính từ biến thể
                  product.variants.forEach((variant) => {
                    if (
                      variant.variant_details &&
                      variant.variant_details.length > 0
                    ) {
                      variant.variant_details.forEach((attr) => {
                        if (!attributeGroups[attr.name]) {
                          attributeGroups[attr.name] = new Set();
                        }
                        attributeGroups[attr.name].add(attr.value);
                      });
                    }
                  });

                  // Render UI cho từng nhóm thuộc tính
                  return Object.entries(attributeGroups).map(
                    ([attrName, values]) => (
                      <div key={attrName} className="space-y-2">
                        <div className="flex items-center">
                          <span className="text-gray-600 w-24">
                            {attrName}:
                          </span>
                          <div className="flex flex-wrap gap-2">
                            {Array.from(values).map((value, idx) => {
                              // Tìm biến thể phù hợp với thuộc tính đã chọn
                              const matchedVariant = product.variants.find(
                                (variant) => {
                                  if (!variant.variant_details) return false;

                                  const hasAttribute =
                                    variant.variant_details.some(
                                      (attr) =>
                                        attr.name === attrName &&
                                        attr.value === value
                                    );

                                  // Kiểm tra các thuộc tính đã chọn khác
                                  const matchesOtherSelectedAttributes =
                                    Object.entries(selectedVariantAttributes)
                                      .filter(([name]) => name !== attrName) // Bỏ qua thuộc tính hiện tại
                                      .every(([name, val]) => {
                                        return variant.variant_details.some(
                                          (attr) =>
                                            attr.name === name &&
                                            attr.value === val
                                        );
                                      });

                                  return (
                                    hasAttribute &&
                                    matchesOtherSelectedAttributes
                                  );
                                }
                              );

                              const isSelected =
                                selectedVariantAttributes[attrName] === value;

                              return (
                                <button
                                  key={idx}
                                  className={`px-4 py-2 rounded-lg border ${
                                    isSelected
                                      ? "border-amber-500 bg-amber-50 text-amber-700"
                                      : "border-gray-300 hover:border-amber-200"
                                  } ${
                                    !matchedVariant
                                      ? "opacity-50 cursor-not-allowed"
                                      : "cursor-pointer"
                                  }`}
                                  onClick={() => {
                                    if (matchedVariant) {
                                      handleVariantChange(matchedVariant.id);
                                    }
                                  }}
                                  disabled={!matchedVariant}
                                >
                                  {value}
                                </button>
                              );
                            })}
                          </div>
                        </div>
                      </div>
                    )
                  );
                })()}

                {/* Hiển thị danh sách các biến thể sản phẩm */}
                <div className="mt-4 bg-gray-50 p-3 rounded-lg">
                  <h4 className="font-medium text-gray-700 mb-2">
                    Thông tin chi tiết biến thể:
                  </h4>
                  <div className="space-y-2">
                    {product.variants.map((variant) => (
                      <div
                        key={variant.id}
                        className={`p-3 rounded-lg border ${
                          selectedVariantId === variant.id
                            ? "border-amber-500 bg-amber-50"
                            : "border-gray-200"
                        } cursor-pointer`}
                        onClick={() => handleVariantChange(variant.id)}
                      >
                        <div className="flex justify-between">
                          <div>
                            <p className="font-medium text-gray-800">
                              SKU: {variant.sku}
                            </p>
                            {variant.variant_details &&
                              variant.variant_details.length > 0 && (
                                <div className="mt-1 flex flex-wrap gap-1">
                                  {variant.variant_details.map((attr, i) => (
                                    <span
                                      key={i}
                                      className="inline-flex items-center px-2 py-1 rounded-md text-xs bg-gray-100 text-gray-800"
                                    >
                                      {attr.name}: {attr.value}
                                    </span>
                                  ))}
                                </div>
                              )}
                          </div>
                          <div className="text-right">
                            <p className="font-semibold text-amber-500">
                              {formatPrice(
                                variant.discount_price || variant.price
                              )}
                            </p>
                            {variant.discount_price && (
                              <p className="text-xs text-gray-500 line-through">
                                {formatPrice(variant.price)}
                              </p>
                            )}
                          </div>
                        </div>
                        <div className="mt-1 text-sm text-gray-600">
                          Còn lại: {variant.quantity} sản phẩm
                        </div>
                      </div>
                    ))}
                  </div>
                </div>
              </div>
            )}

            {/* Số lượng */}
            <div className="flex items-center space-x-6">
              <span className="text-gray-600">Số lượng:</span>
              <div className="flex items-center">
                <button
                  onClick={decreaseQuantity}
                  className="w-10 h-10 flex items-center justify-center border border-gray-300 rounded-l-lg hover:bg-gray-100 transition-colors disabled:opacity-50"
                  disabled={quantity <= 1}
                >
                  -
                </button>
                <input
                  type="number"
                  value={quantity}
                  onChange={(e) => {
                    const value = parseInt(e.target.value);
                    if (
                      !isNaN(value) &&
                      value >= 1 &&
                      value <= getStockQuantity()
                    ) {
                      setQuantity(value);
                    }
                  }}
                  className="w-14 h-10 border-t border-b border-gray-300 text-center focus:outline-none"
                />
                <button
                  onClick={increaseQuantity}
                  className="w-10 h-10 flex items-center justify-center border border-gray-300 rounded-r-lg hover:bg-gray-100 transition-colors disabled:opacity-50"
                  disabled={quantity >= getStockQuantity()}
                >
                  +
                </button>
              </div>
            </div>

            {/* Nút mua và thêm vào giỏ hàng */}
            <div className="flex space-x-4 pt-4">
              <button
                onClick={handleAddToCart}
                disabled={getStockQuantity() <= 0 || addingToCart}
                className="flex-1 bg-amber-500 hover:bg-amber-600 text-white py-3 px-6 rounded-lg font-medium transition-colors flex items-center justify-center space-x-2 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                {addingToCart ? (
                  <div className="h-5 w-5 border-2 border-white border-t-transparent rounded-full animate-spin mr-2"></div>
                ) : (
                  <IoCartOutline className="text-xl" />
                )}
                <span>Thêm vào giỏ</span>
              </button>
              <button
                onClick={handleBuyNow}
                disabled={getStockQuantity() <= 0 || addingToBuy}
                className="flex-1 bg-gray-800 hover:bg-gray-900 text-white py-3 px-6 rounded-lg font-medium transition-colors flex items-center justify-center space-x-2 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                {addingToBuy ? (
                  <div className="h-5 w-5 border-2 border-white border-t-transparent rounded-full animate-spin mr-2"></div>
                ) : (
                  <span>Mua ngay</span>
                )}
                {!addingToBuy && <IoArrowForward className="text-lg" />}
              </button>
            </div>

            {/* Các quyền lợi */}
            <div className="flex flex-wrap gap-4 pt-6 border-t border-gray-100">
              <div className="flex items-center space-x-2">
                <FaTruck className="text-amber-500" />
                <span className="text-sm text-gray-700">
                  Giao hàng miễn phí
                </span>
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
              <div className="flex items-center space-x-2">
                <FaExchangeAlt className="text-amber-500" />
                <span className="text-sm text-gray-700">
                  Đổi trả trong 7 ngày
                </span>
              </div>
              <div className="flex items-center space-x-2">
                <FaShieldAlt className="text-amber-500" />
                <span className="text-sm text-gray-700">Bảo hành 12 tháng</span>
              </div>
            </div>
          </div>
        </div>

        {/* Mô tả sản phẩm */}
        <div className="p-6 border-t border-gray-100">
          <h2 className="text-xl font-semibold text-gray-800 mb-4">
            Mô tả sản phẩm
          </h2>
          {product.description ? (
            <div
              dangerouslySetInnerHTML={{ __html: product.description }}
              className="prose max-w-none text-gray-700"
            />
          ) : (
            <p className="text-gray-500 italic">Sản phẩm chưa có mô tả.</p>
          )}
        </div>
      </motion.div>
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
