import { useState, useEffect } from "react";
import { Link, useParams, useNavigate, useLocation } from "react-router-dom";
import axios from "axios";
import { useDispatch } from "react-redux";
import { addToCart } from "../../../store/cartSlice";
import AddToCartToast from "../../../components/AddToCartToast";

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

  // State để hiển thị thông báo
  const [showFeedback, setShowFeedback] = useState(false);
  const [feedbackMessage, setFeedbackMessage] = useState("");
  const [feedbackType, setFeedbackType] = useState("success"); // "success" hoặc "error"

  // State để lưu thông tin các loại biến thể và giá trị
  const [variantTypes, setVariantTypes] = useState([]);
  const [variantValues, setVariantValues] = useState([]);

  // Thêm state để quản lý tổng tiền trong Order Summary
  const [orderTotal, setOrderTotal] = useState(0);

  console.log("ID from useParams:", id);

  // Hàm kiểm tra xác thực và lấy thông tin người dùng
  const checkAuthentication = async () => {
    // Thêm độ trễ nhỏ để đảm bảo token đã được lưu trữ
    await new Promise((resolve) => setTimeout(resolve, 100));

    const token = localStorage.getItem("token");
    console.log("Current token:", token);

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
          `http://localhost:8000/api/products/${id}?_=${timestamp}`
        );
        console.log("API Response:", response.data);
        let productData = response.data.data;

        // Log chi tiết cấu trúc dữ liệu của sản phẩm để debug
        console.log("Chi tiết sản phẩm:", JSON.stringify(productData, null, 2));

        // Thiết lập giá mặc định từ sản phẩm
        setCurrentPrice(productData.price);
        setCurrentDiscount(productData.discount_price);

        // Kiểm tra xem productData có thuộc tính variants không
        if (
          !productData.variants ||
          !Array.isArray(productData.variants) ||
          productData.variants.length === 0
        ) {
          console.log(
            "Không có dữ liệu biến thể trong phản hồi API hoặc biến thể rỗng"
          );

          const variants = [];

          // Tạo biến thể màu sắc
          const colors = [...new Set(variantTable.map((item) => item.color))];
          colors.forEach((color, index) => {
            variants.push({
              id: index + 1,
              variant_id: 1,
              variant_name: "",
              variant_value_id: index + 1,
              variant_value_name: color,
              quantity: 10,
              price: productData.price,
              sku: `COLOR-${index + 1}`,
            });
          });

          // Tạo biến thể kích thước
          const sizes = [...new Set(variantTable.map((item) => item.size))];
          sizes.forEach((size, index) => {
            variants.push({
              id: index + 1,
              variant_id: 2,
              variant_name: "Kích thước",
              variant_value_id: index + 1,
              variant_value_name: size,
              quantity: 10,
              price: productData.price,
              sku: `SIZE-${index + 1}`,
            });
          });

          console.log("Biến thể:", sizes);

          // Tạo các biến thể kết hợp (không hiển thị nhưng dùng để tính giá)
          variantTable.forEach((item, index) => {
            const colorId = colors.indexOf(item.color) + 1;
            const sizeId = sizes.indexOf(item.size) + 1;

            variants.push({
              id: colors.length + sizes.length + index + 1,
              variant_id: 3, // Biến thể kết hợp (không hiển thị)
              variant_name: "Kết hợp",
              variant_value_id: index + 1,
              variant_value_name: `${item.color} - ${item.size}`,
              color_id: colorId,
              size_id: sizeId,
              quantity: item.quantity,
              price: item.price,
              discount_price: item.discount_price,
              sku: item.sku,
            });
          });

          productData.variants = variants;
          console.log("Đã tạo biến thể từ bảng SKU:", variants);
        }

        // Tiếp tục xử lý nếu có biến thể
        if (productData.variants && Array.isArray(productData.variants)) {
          console.log("Số lượng biến thể:", productData.variants.length);

          // Chuẩn hóa dữ liệu biến thể
          const variantsWithValues = productData.variants.map(
            (variant, index) => {
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
              else {
                // Xác định tên biến thể dựa trên variant_id
                if (variantId === 1) variantName = "Màu sắc";
                else if (variantId === 2) variantName = "Kích thước";
              }

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

              return processedVariant;
            }
          );

          // Thêm debugging
          console.log("Tất cả biến thể sau khi xử lý:", variantsWithValues);

          // Sắp xếp các biến thể theo loại và loại bỏ những biến thể đặc biệt (như loại 3)
          const displayVariants = variantsWithValues.filter(
            (v) => v.variant_id < 3
          );
          displayVariants.sort((a, b) => a.variant_id - b.variant_id);

          // Thiết lập biến thể mặc định được chọn
          const defaultVariants = {};
          const variantTypes = [
            ...new Set(displayVariants.map((v) => Number(v.variant_id))),
          ].filter((id) => id > 0);

          console.log("Các loại biến thể để hiển thị:", variantTypes);

          // Chọn biến thể đầu tiên cho mỗi loại
          variantTypes.forEach((typeId) => {
            if (typeId === 0) return; // Bỏ qua biến thể có id = 0

            const availableVariants = displayVariants.filter(
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
                  console.log(
                    "Tìm thấy biến thể kết hợp phù hợp:",
                    matchingVariant
                  );
                  return matchingVariant;
                }
              }

              // Nếu không tìm thấy, lấy biến thể đầu tiên
              if (displayVariants.length > 0) {
                return displayVariants[0];
              }

              return null;
            };

            const bestVariant = findMatchingVariant();

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

          setProduct({ ...productData, variants: displayVariants });
          setSelectedVariants(defaultVariants);
          console.log("Biến thể đã chọn mặc định:", defaultVariants);
        }

        // Lấy bình luận
        const commentsResponse = await axios.get(
          `http://localhost:8000/api/products/${id}/comments?_=${timestamp}`
        );
        setComments(commentsResponse.data.data || []);

        setLoading(false);
      } catch (error) {
        console.error("Error fetching product:", error);
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
      if (e.key === "token" || e.key === "userData") {
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

  const fetchCurrentUser = async (token) => {
    try {
      console.log("Fetching user with token:", token);
      const response = await axios.get("http://localhost:8000/api/user", {
        headers: { Authorization: `Bearer ${token}` },
      });
      console.log("User profile response:", response.data);
      if (response.data && response.data.data) {
        setCurrentUser(response.data.data);
        setIsLoggedIn(true);
        console.log("User logged in successfully:", response.data.data.name);
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
        localStorage.removeItem("token");
        return false;
      }
    } catch (error) {
      console.error("Error fetching user:", error.response || error);
      if (error.response?.status === 401 || error.response?.status === 404) {
        console.log("Token không hợp lệ hoặc API không tồn tại, đăng xuất...");
        setIsLoggedIn(false);
        setCurrentUser(null);
        localStorage.removeItem("token");
      }
      return false;
    }
  };

  useEffect(() => {
    // Log biến thể kích thước khi product thay đổi
    if (product && product.variants) {
      const sizeVariants = product.variants.filter((v) => v.variant_id === 2);
      console.log("============= BIẾN THỂ KÍCH THƯỚC =============");
      console.log("Số lượng biến thể kích thước:", sizeVariants.length);
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
      console.log("==============================================");
    }
  }, [product]);

  // Thêm hàm xử lý variant_details
  const extractVariantTypes = (variants) => {
    const types = new Set();

    if (variants && variants.length > 0) {
      variants.forEach((variant) => {
        if (variant.variant_details) {
          // Lấy tất cả các loại biến thể từ variant_details
          Object.keys(variant.variant_details).forEach((typeId) => {
            types.add(Number(typeId));
          });
        }
      });
    }

    return Array.from(types).sort();
  };

  // Trong useEffect khi nhận được dữ liệu
  useEffect(() => {
    if (product && product.variants) {
      // Lấy các loại biến thể
      const types = extractVariantTypes(product.variants);
      console.log("Các loại biến thể: ", types); // Sẽ bao gồm cả 1 (màu sắc) và 2 (kích thước)

      // Xử lý hiển thị cho từng loại
      types.forEach((typeId) => {
        // Tìm các giá trị biến thể cho từng loại
        const typeValues = getVariantValuesByType(typeId);
        console.log(`Giá trị biến thể cho loại ${typeId}:`, typeValues);
      });
    }
  }, [product]);

  // Hàm lấy giá trị biến thể theo loại
  const getVariantValuesByType = (typeId) => {
    if (!product || !product.variants) return [];

    const values = new Set();
    product.variants.forEach((variant) => {
      if (variant.variant_details && variant.variant_details[typeId]) {
        const valueId = variant.variant_details[typeId];
        // Tìm thông tin giá trị
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

  // Sửa lại hàm processVariantDetails
  const processVariantDetails = (variant) => {
    if (!variant.variant_details) return null;

    try {
      // Đảm bảo variant_details là object
      const details =
        typeof variant.variant_details === "string"
          ? JSON.parse(variant.variant_details)
          : variant.variant_details;

      // Lấy variant_value_id cho màu sắc và kích thước
      const colorId = details["1"]; // variant type 1 là màu sắc
      const sizeId = details["2"]; // variant type 2 là kích thước

      // Tìm tên của biến thể
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

  // Sửa lại hàm getVariantsByType
  const getVariantsByType = (variantTypeId) => {
    if (!product || !product.variants) return [];

    // Lọc các biến thể có chứa variantTypeId trong variant_details
    const variants = product.variants.filter((variant) => {
      const details = processVariantDetails(variant);
      if (!details) return false;

      // Kiểm tra xem biến thể có chứa loại variant cần tìm không
      return variantTypeId === 1
        ? details.colorId !== undefined
        : details.sizeId !== undefined;
    });

    // Loại bỏ các biến thể trùng lặp
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

  useEffect(() => {
    if (product && product.variants) {
      console.log("Tất cả biến thể:", product.variants);

      // Log chi tiết từng loại biến thể
      const colorVariants = getVariantsByType(1);
      const sizeVariants = getVariantsByType(2);

      console.log(
        "Biến thể màu sắc:",
        colorVariants.map((v) => ({
          variant_details: v.variant_details,
          sku: v.sku,
          parsed: parseVariantFromSku(v.sku),
        }))
      );

      console.log(
        "Biến thể kích thước:",
        sizeVariants.map((v) => ({
          variant_details: v.variant_details,
          sku: v.sku,
          parsed: parseVariantFromSku(v.sku),
        }))
      );
    }
  }, [product]);

  if (loading) {
    return <div className="text-center mt-32">Đang tải...</div>;
  }

  if (!product) {
    return <div className="text-center mt-32">Không tìm thấy sản phẩm</div>;
  }

  console.log("Product state:", product);

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

  const handleGalleryImageClick = (imageUrl) => {
    setSelectedImage(imageUrl);
  };

  // Sửa lại hàm handleVariantSelect để cập nhật giá chính xác
  const handleVariantSelect = (variantId, variantValueId, variant) => {
    const numericVariantId = Number(variantId);
    const numericVariantValueId = Number(variantValueId);

    const newSelectedVariants = {
      ...selectedVariants,
      [numericVariantId]: numericVariantValueId,
    };
    setSelectedVariants(newSelectedVariants);

    // Cập nhật giá khi đã chọn đủ cả màu sắc và kích thước
    if (newSelectedVariants[1] && newSelectedVariants[2]) {
      const matchingVariant = product.variants.find((v) => {
        if (!v.variant_details) return false;
        const details =
          typeof v.variant_details === "string"
            ? JSON.parse(v.variant_details)
            : v.variant_details;
        return (
          details["1"] === String(newSelectedVariants[1]) &&
          details["2"] === String(newSelectedVariants[2])
        );
      });

      if (matchingVariant) {
        const variantPrice = matchingVariant.price || product.price;
        const variantDiscountPrice =
          matchingVariant.discount_price || variantPrice;

        setCurrentPrice(variantPrice);
        setCurrentDiscount(variantDiscountPrice);

        // Cập nhật tổng tiền cho Order Summary
        const finalPrice = variantDiscountPrice || variantPrice;
        const total = finalPrice * quantity;
        setOrderTotal(total);
      }
    }

    setError("");
  };

  const isVariantInStock = (variant) => {
    if (!variant) return false;
    return variant.quantity > 0;
  };

  const hasSelectedAllRequiredVariants = () => {
    // Kiểm tra trực tiếp xem đã chọn cả màu sắc (1) và kích thước (2) chưa
    return (
      selectedVariants[1] !== undefined && selectedVariants[2] !== undefined
    );
  };

  // Xác định có tồn tại biến thể màu sắc và kích thước không
  const hasVariantTypes = () => {
    const hasColors =
      product.variants && product.variants.some((v) => v.variant_id === 1);
    const hasSizes =
      product.variants && product.variants.some((v) => v.variant_id === 2);
    return { hasColors, hasSizes };
  };

  const handleAddToCart = () => {
    // Kiểm tra đã chọn biến thể chưa
    const { hasColors, hasSizes } = hasVariantTypes();

    if (
      (hasColors && !selectedVariants[1]) ||
      (hasSizes && !selectedVariants[2])
    ) {
      // ... phần xử lý lỗi giữ nguyên ...
      return;
    }

    // Tìm biến thể phù hợp dựa trên màu sắc và kích thước đã chọn
    const selectedColorId = selectedVariants[1]; // ID của màu sắc đã chọn
    const selectedSizeId = selectedVariants[2]; // ID của kích thước đã chọn

    // Tìm thông tin tên màu sắc và kích thước từ variants
    const colorVariant = product.variants.find(
      (v) => v.variant_id === 1 && v.variant_value_id === selectedColorId
    );
    const sizeVariant = product.variants.find(
      (v) => v.variant_id === 2 && v.variant_value_id === selectedSizeId
    );

    // Lấy tên của màu sắc và kích thước
    const colorName = colorVariant?.variant_value_name || "Không xác định";
    const sizeName = sizeVariant?.variant_value_name || "Không xác định";

    // Log để debug
    console.log("Màu sắc đã chọn:", colorName, "ID:", selectedColorId);
    console.log("Kích thước đã chọn:", sizeName, "ID:", selectedSizeId);

    // Tìm biến thể kết hợp để lấy giá chính xác (giữ nguyên code cũ)
    const combinedVariant = product.variants.find((variant) => {
      if (!variant.variant_details) return false;
      const details =
        typeof variant.variant_details === "string"
          ? JSON.parse(variant.variant_details)
          : variant.variant_details;
      return (
        details["1"] === String(selectedColorId) &&
        details["2"] === String(selectedSizeId)
      );
    });

    // Tính toán giá cuối cùng
    const finalPrice =
      combinedVariant?.discount_price ||
      combinedVariant?.price ||
      currentDiscount ||
      currentPrice;
    const total = finalPrice * quantity;

    // Tạo đối tượng để thêm vào giỏ hàng
    const cartItem = {
      product: {
        ...product,
        price: finalPrice,
        total: total,
      },
      quantity: quantity,
      variants: {
        color: colorName, // Sử dụng tên đã tìm được
        size: sizeName, // Sử dụng tên đã tìm được
      },
      variant_details: {
        color_id: selectedColorId,
        size_id: selectedSizeId,
      },
    };

    console.log("Thêm vào giỏ hàng:", cartItem);
    dispatch(addToCart(cartItem));

    setToastProduct({
      ...product,
      selectedVariants: cartItem.variants,
      quantity: quantity,
      total: total,
    });
    setShowToast(true);
    setTimeout(() => setShowToast(false), 3000);
  };

  const handleBuyNow = () => {
    const { hasColors, hasSizes } = hasVariantTypes();

    // Bắt buộc phải chọn biến thể nếu tồn tại
    if (
      (hasColors && !selectedVariants[1]) ||
      (hasSizes && !selectedVariants[2])
    ) {
      let errorMessage = "Vui lòng chọn ";

      if (hasColors && hasSizes) {
        if (!selectedVariants[1] && !selectedVariants[2]) {
          errorMessage += "màu sắc và kích thước";
        } else if (!selectedVariants[1]) {
          errorMessage += "màu sắc";
        } else if (!selectedVariants[2]) {
          errorMessage += "kích thước";
        }
      } else if (hasColors && !selectedVariants[1]) {
        errorMessage += "màu sắc";
      } else if (hasSizes && !selectedVariants[2]) {
        errorMessage += "kích thước";
      }

      setError(errorMessage);
      return;
    }

    console.log("Mua ngay:", {
      product_id: product.id,
      variants: selectedVariants,
      quantity: quantity,
    });

    alert("Đang chuyển đến trang thanh toán...");
  };

  const increaseQuantity = () => {
    setQuantity((prev) => prev + 1);
  };

  const decreaseQuantity = () => {
    if (quantity > 1) {
      setQuantity((prev) => prev - 1);
    }
  };

  // Hàm lấy tên loại biến thể
  const getVariantTypeName = (variantId) => {
    // Kiểm tra trong state trước
    const typeFromState = variantTypes.find((t) => t.id === variantId);
    if (typeFromState) return typeFromState.name;

    // Kiểm tra trong các biến thể của sản phẩm
    const variant = product.variants?.find((v) => v.variant_id === variantId);
    if (variant && variant.variant_info && variant.variant_info.name) {
      return variant.variant_info.name;
    }

    // Fallback tới giá trị mặc định
    const variantTypeNames = {
      1: "Màu sắc",
      2: "Kích thước",
      3: "Chất liệu",
      4: "Kiểu dáng",
    };
    return variantTypeNames[variantId] || "Biến thể";
  };

  // Hàm lấy tên giá trị biến thể
  const getVariantValueName = (variantId, valueId) => {
    // Tìm trong các giá trị biến thể đã lấy từ API
    const valueFromState = variantValues.find(
      (v) => v.id === valueId && v.variant_id === variantId
    );
    if (valueFromState) return valueFromState.value;

    // Tìm trong các biến thể của sản phẩm
    const variant = product.variants?.find(
      (v) => v.variant_id === variantId && v.variant_value_id === valueId
    );

    if (variant) {
      if (variant.variant_value && variant.variant_value.value) {
        return variant.variant_value.value;
      }
      if (variant.variant_value_name) {
        return variant.variant_value_name;
      }
    }

    return "Không xác định";
  };

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
                          d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"
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
                {[1, 2].map((variantTypeId) => {
                  const variants = getVariantsByType(variantTypeId);
                  if (variants.length === 0) return null;

                  return (
                    <div className="mt-4" key={variantTypeId}>
                      <p className="text-gray-700 font-medium">
                        {variantTypeId === 1 ? "Màu sắc" : "Kích thước"}:
                      </p>
                      <div className="flex flex-wrap gap-3 mt-2">
                        {variants.map((variant) => {
                          const details = processVariantDetails(variant);
                          if (!details) return null;

                          const valueId =
                            variantTypeId === 1
                              ? details.colorId
                              : details.sizeId;
                          const isSelected =
                            selectedVariants[variantTypeId] === valueId;

                          // Lấy tên biến thể từ SKU hoặc variant_value_name
                          const parsedVariant = parseVariantFromSku(
                            variant.sku
                          );
                          const variantName =
                            variantTypeId === 1
                              ? parsedVariant?.color || "Màu không xác định"
                              : parsedVariant?.size ||
                                "Kích thước không xác định";

                          if (variantTypeId === 1) {
                            // Render màu sắc
                            return (
                              <div
                                key={`${variantTypeId}-${valueId}`}
                                className={`w-8 h-8 rounded-full cursor-pointer border shadow transition-all duration-300 ${
                                  isSelected
                                    ? "ring-2 ring-offset-2 ring-green-500 scale-110"
                                    : "hover:scale-105"
                                }`}
                                onClick={() =>
                                  handleVariantSelect(
                                    variantTypeId,
                                    valueId,
                                    variant
                                  )
                                }
                                title={variantName}
                              >
                                {/* Hiển thị màu sắc */}
                              </div>
                            );
                          } else {
                            // Render kích thước
                            return (
                              <div
                                key={`${variantTypeId}-${valueId}`}
                                className={`px-4 py-2 border rounded-md cursor-pointer ${
                                  isSelected
                                    ? "bg-green-600 text-white"
                                    : "hover:bg-gray-100"
                                }`}
                                onClick={() =>
                                  handleVariantSelect(
                                    variantTypeId,
                                    valueId,
                                    variant
                                  )
                                }
                              >
                                {variantName}
                              </div>
                            );
                          }
                        })}
                      </div>
                    </div>
                  );
                })}
              </div>

              {error && (
                <div className="mt-4 flex items-center p-3 bg-red-50 text-red-600 rounded-md border border-red-200">
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

              <div className="flex gap-2  mt-4">
                {/* Add to Cart and Wishlist Buttons - Same Row with Black Background */}

                <div className="flex gap-4 mt-4">
                  <button
                    className="w-full bg-gradient-to-r from-amber-500 to-amber-600 text-white py-3 px-6 rounded-full font-semibold shadow-md hover:shadow-lg transition-all duration-300 transform hover:scale-[1.01] flex items-center justify-center"
                    onClick={handleAddToCart}
                  >
                    THÊM VÀO GIỎ HÀNG
                  </button>
                </div>
                <div className="flex gap-4 mt-4">
                  <button
                    className="w-full bg-gradient-to-r from-amber-500 to-amber-600 text-white py-3 px-6 rounded-full font-semibold shadow-md hover:shadow-lg transition-all duration-300 transform hover:scale-[1.01] flex items-center justify-center"
                    onClick={handleBuyNow}
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
                    MUA NGAY
                  </button>
                </div>

                {/* Buy Now Button - Below */}
                {/* <div className="mt-4">
                <button
                  className="w-full bg-gradient-to-r from-orange-500 to-red-500 text-white py-3 px-4 rounded-full font-bold shadow-lg transform transition duration-300 hover:scale-[1.02] hover:shadow-xl hover:from-orange-600 hover:to-red-600 focus:outline-none focus:ring-2 focus:ring-red-400"
                  onClick={handleBuyNow}
                >
                  MUA NGAY
                </button>
                </div> */}
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
            <li className="mr-0">
              <button className="py-3 px-1 text-gray-500 hover:text-gray-700 transition duration-200">
                Thông tin thêm
              </button>
            </li>
            <li className="mr-0">
              <button className="py-3 px-1 text-gray-500 hover:text-gray-700 transition duration-200">
                Mô tả
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
