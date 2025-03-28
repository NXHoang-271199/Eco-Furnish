import { useState, useEffect } from "react";
import { Link, useParams, useNavigate, useLocation } from "react-router-dom";
import axios from "axios";

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

        if (productData.variants && Array.isArray(productData.variants)) {
          console.log("Variants từ API:", productData.variants);

          setSelectedVariants({});
          setCurrentPrice(productData.price);
          setCurrentDiscount(productData.discount_price);

          productData.variants.forEach((variant) => {
            console.log("Biến thể:", variant);
          });

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

  const handleAddToCart = () => {
    if (!hasSelectedAllRequiredVariants()) {
      setError("Vui lòng chọn đầy đủ biến thể sản phẩm");
      return;
    }

    console.log("Thêm vào giỏ hàng:", {
      product_id: product.id,
      variants: selectedVariants,
      quantity: quantity,
    });

    alert("Đã thêm sản phẩm vào giỏ hàng!");
  };

  const handleAddToWishlist = () => {
    if (!hasSelectedAllRequiredVariants()) {
      setError("Vui lòng chọn đầy đủ biến thể sản phẩm");
      return;
    }

    console.log("Thêm vào danh sách yêu thích:", {
      product_id: product.id,
      variants: selectedVariants,
    });

    alert("Đã thêm sản phẩm vào danh sách yêu thích!");
  };

  const handleBuyNow = () => {
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

  const increaseQuantity = () => {
    setQuantity((prev) => prev + 1);
  };

  const decreaseQuantity = () => {
    if (quantity > 1) {
      setQuantity((prev) => prev - 1);
    }
  };

  const getVariantsByType = (variantId) => {
    const typeVariants =
      product.variants?.filter((variant) => variant.variant_id === variantId) ||
      [];

    const latestVariants = new Map();

    typeVariants.forEach((variant) => {
      const key = `${variant.variant_id}_${variant.variant_value_id}`;

      if (
        !latestVariants.has(key) ||
        (variant.updated_at &&
          latestVariants.get(key).updated_at &&
          new Date(variant.updated_at) >
            new Date(latestVariants.get(key).updated_at))
      ) {
        latestVariants.set(key, variant);
      }
    });

    const uniqueVariants = Array.from(latestVariants.values());

    uniqueVariants.sort((a, b) => a.variant_value_id - b.variant_value_id);

    console.log(
      `Biến thể loại ${variantId} (đã lọc trùng lặp):`,
      uniqueVariants
    );

    return uniqueVariants;
  };

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

        // Refresh comments sau khi thêm
        setTimeout(() => {
          refreshProductComments();
        }, 500);
      }
    } catch (error) {
      console.error("Error submitting comment:", error.response || error);
      // Hiển thị thông báo lỗi cho người dùng
      alert(
        "Không thể gửi bình luận: " +
          (error.response?.data?.message || "Lỗi kết nối")
      );

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

  const isVariantAvailable = () => {
    // Hiện tại luôn trả về true để đơn giản hóa logic,
    // có thể mở rộng trong tương lai khi cần kiểm tra tính khả dụng phức tạp hơn
    return true;
  };

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
    <main className="max-w-6xl mx-auto mb-20 mt-32">
      {/* <div className="flex justify-between items-center mb-8">
        <Link
          to="/"
          className="text-blue-500 hover:underline flex items-center"
        >
          <svg
            xmlns="http://www.w3.org/2000/svg"
            className="h-5 w-5 mr-1"
            viewBox="0 0 20 20"
            fill="currentColor"
          >
            <path
              fillRule="evenodd"
              d="M9.707 14.707a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 1.414L7.414 9H15a1 1 0 110 2H7.414l2.293 2.293a1 1 0 010 1.414z"
              clipRule="evenodd"
            />
          </svg>
          Quay lại trang chủ
        </Link>
        <div className="relative">
          <input
            type="text"
            placeholder="Tìm kiếm sản phẩm..."
            className="px-4 py-2 border rounded-full w-64"
          />
          <button className="absolute right-2 top-2 text-gray-500">
            <svg
              xmlns="http://www.w3.org/2000/svg"
              className="h-5 w-5"
              viewBox="0 0 20 20"
              fill="currentColor"
            >
              <path
                fillRule="evenodd"
                d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                clipRule="evenodd"
              />
            </svg>
          </button>
        </div>
      </div> */}
      <div className="grid grid-cols-2 gap-8 my-16">
        <div className="grid grid-cols-1">
          <div className="col-span-5">
            <img
              src={mainImageUrl}
              alt={product.name || "Sản phẩm"}
              className="w-full rounded-md h-[400px] object-contain"
              onError={(e) => {
                console.log("Main Image Load Error:", e);
                e.target.src =
                  "https://via.placeholder.com/400x400?text=Image+Error";
              }}
            />
          </div>
          <div className="flex justify-center space-x-5 mt-4">
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
                    className="mt-4 cursor-pointer"
                    onClick={() => handleGalleryImageClick(galleryImageUrl)}
                  >
                    <img
                      src={galleryImageUrl}
                      alt={`Gallery ${index + 1}`}
                      className={`w-20 h-20 object-cover rounded-md ${
                        selectedImage === galleryImageUrl
                          ? "border-2 border-blue-500"
                          : ""
                      }`}
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
                );
              })
            ) : (
              <div className="mt-4 text-gray-500">Không có ảnh bổ sung</div>
            )}
          </div>
        </div>
        <div>
          <h5 className="text-[20px] font-semibold text-3xl">
            {product.name || "Tên sản phẩm"}
          </h5>

          <h3 className="text-[40px] font-bold mt-2 text-[#EF4444]">
            {currentDiscount
              ? formatPrice(currentDiscount)
              : currentPrice
              ? formatPrice(currentPrice)
              : formatPrice(product.price)}
          </h3>
          {currentDiscount &&
            currentPrice &&
            Number(currentDiscount) !== Number(currentPrice) && (
              <p className="text-gray-500 line-through">
                {formatPrice(currentPrice)}
              </p>
            )}

          {isSampleProduct && (
            <p className="mt-3 text-[16px] font-medium">
              Đây là sản phẩm mẫu không bán
            </p>
          )}

          <p className="mt-3 text-[16px] font-medium">
            {product.short_description || "Không có mô tả"}
          </p>

          {[1, 2, 3, 4].map((variantTypeId) => {
            const variants = getVariantsByType(variantTypeId);
            if (variants.length === 0) return null;

            return (
              <div className="mt-4" key={variantTypeId}>
                <p className="text-[#A3A3A3]">
                  {getVariantTypeName(variantTypeId)}
                </p>
                <div className="flex flex-wrap gap-4 mt-1">
                  {variants.map((variant) => {
                    if (!variant) {
                      console.log(`Bỏ qua variant không hợp lệ:`, variant);
                      return null;
                    }

                    const isSelected =
                      selectedVariants[variantTypeId] ===
                      variant.variant_value_id;
                    const inStock = isVariantInStock(variant);

                    const isAvailable = isVariantAvailable();

                    if (variantTypeId === 1) {
                      let bgColor = "gray";
                      const variantValue =
                        variant.variant_value?.value ||
                        variant.variant_value_name ||
                        "";
                      const colorValue =
                        typeof variantValue === "string"
                          ? variantValue.toLowerCase()
                          : "";

                      console.log(`Màu sắc: ${colorValue}`, variant);

                      if (colorValue.includes("đỏ")) bgColor = "red";
                      else if (colorValue.includes("xanh")) bgColor = "blue";
                      else if (colorValue.includes("đen")) bgColor = "black";
                      else if (colorValue.includes("trắng")) bgColor = "white";
                      else if (colorValue.includes("vàng")) bgColor = "yellow";
                      else if (colorValue.includes("cam")) bgColor = "orange";
                      else if (colorValue.includes("tím")) bgColor = "purple";
                      else if (colorValue.includes("hồng")) bgColor = "pink";
                      else if (colorValue.includes("nâu")) bgColor = "brown";
                      else if (colorValue.includes("xám")) bgColor = "gray";

                      return (
                        <div
                          key={variant.id || `color-${Math.random()}`}
                          className={`w-[30px] h-[30px] rounded-[50%] cursor-pointer ${
                            isSelected
                              ? "ring-2 ring-offset-2 ring-blue-500"
                              : "border"
                          } ${!inStock ? "opacity-50 cursor-not-allowed" : ""}`}
                          title={`${variantValue || "Không xác định"} ${
                            !inStock ? "(Hết hàng)" : ""
                          }`}
                          onClick={() => {
                            if (inStock) {
                              handleVariantSelect(
                                variantTypeId,
                                variant.variant_value_id,
                                variant
                              );
                            }
                          }}
                          style={{
                            backgroundColor: bgColor,
                            border:
                              bgColor === "white" ? "1px solid #ddd" : "none",
                          }}
                        />
                      );
                    } else if (variantTypeId === 2) {
                      const sizeValue =
                        variant.variant_value?.value ||
                        variant.variant_value_name ||
                        "";
                      const sizeLabel =
                        typeof sizeValue === "string"
                          ? sizeValue
                          : "Không xác định";

                      console.log(`Kích thước: ${sizeLabel}`, variant);

                      return (
                        <div
                          key={variant.id || `size-${Math.random()}`}
                          className={`px-6 py-2 border rounded-md cursor-pointer ${
                            isSelected
                              ? "bg-blue-500 text-white"
                              : "hover:bg-gray-100"
                          } ${
                            !inStock
                              ? "opacity-50 cursor-not-allowed bg-gray-200 hover:bg-gray-200"
                              : ""
                          }`}
                          onClick={() => {
                            if (inStock) {
                              handleVariantSelect(
                                variantTypeId,
                                variant.variant_value_id,
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
                      const otherValue =
                        variant.variant_value?.value ||
                        variant.variant_value_name ||
                        "";
                      const otherLabel =
                        typeof otherValue === "string"
                          ? otherValue
                          : "Không xác định";

                      return (
                        <div
                          key={variant.id || `variant-${Math.random()}`}
                          className={`px-3 py-1 border rounded-md cursor-pointer ${
                            isSelected
                              ? "bg-blue-500 text-white"
                              : "hover:bg-gray-100"
                          } ${
                            !inStock
                              ? "opacity-50 cursor-not-allowed bg-gray-200 hover:bg-gray-200"
                              : ""
                          }`}
                          onClick={() => {
                            if (inStock) {
                              handleVariantSelect(
                                variantTypeId,
                                variant.variant_value_id,
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

          {error && <div className="mt-2 text-red-500 text-sm">{error}</div>}

          <div className="mt-8 flex pb-8">
            <div className="grid grid-cols-3 w-[123px] h-[44px] border border-[#A3A3A3] rounded-[5px]">
              <button
                className="justify-center flex items-center"
                onClick={increaseQuantity}
              >
                +
              </button>
              <p className="flex justify-center items-center">{quantity}</p>
              <button
                className="justify-center flex items-center"
                onClick={decreaseQuantity}
              >
                -
              </button>
            </div>
            <div>
              <button
                className="justify-center flex items-center border border-[#CA8A04] rounded-[5px] w-[215px] h-[44px] ml-3 text-[#CA8A04] hover:bg-yellow-50"
                onClick={handleAddToCart}
              >
                Add To Cart
              </button>
            </div>
            <div>
              <button
                className="justify-center flex items-center border border-[#262626] rounded-[5px] w-[215px] h-[44px] ml-3 text-[#262626] hover:bg-gray-50"
                onClick={handleAddToWishlist}
              >
                Add to wish list
              </button>
            </div>
          </div>
          <div className="w-full mx-auto">
            <button
              className="w-full border rounded-md mx-auto py-2 font-semibold hover:bg-yellow-200"
              onClick={handleBuyNow}
            >
              Buy now
            </button>
          </div>
          <div className="mt-3">
            <div className="text-[#A3A3A3] text-[16px] mb-3">
              Danh mục:{" "}
              {product.category
                ? typeof product.category === "string"
                  ? product.category
                  : product.category.name || "Không xác định"
                : "Không xác định"}
            </div>
            <div className="text-[#A3A3A3] text-[16px] mb-3">
              Số lượng :{" "}
              {product.variants && Array.isArray(product.variants)
                ? product.variants.reduce(
                    (sum, v) => sum + (v.quantity || 0),
                    0
                  )
                : product.quantity || 0}
            </div>
          </div>
        </div>
      </div>

      <div className="mt-4">
        <div className="mt-4">
          <div className="underline mb-4">
            <h3 className="font-semibold text-xl mb-4">Bình luận</h3>
          </div>

          {isLoggedIn && currentUser ? (
            <div className="flex justify-between items-center border p-4 rounded-md mb-6">
              <div className="flex items-center gap-4 w-full">
                {/* <img
                  src={currentUser?.avatar || "https://via.placeholder.com/40"}
                  alt="User avatar"
                  className="w-10 h-10 rounded-full object-cover"
                /> */}
                <input
                  type="text"
                  className="flex-1 p-2 border rounded-md outline-none"
                  placeholder="Nhập đánh giá của bạn ở đây"
                  value={commentInput}
                  onChange={(e) => setCommentInput(e.target.value)}
                  onFocus={() => checkAuthentication()}
                />
                <button
                  className="px-6 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600"
                  onClick={handleCommentSubmit}
                >
                  Gửi
                </button>
              </div>
            </div>
          ) : (
            <div className="text-center p-4 bg-gray-50 rounded-md mb-6">
              <p>
                Vui lòng{" "}
                <Link
                  to="/signin"
                  state={{ returnUrl: location.pathname }}
                  className="text-blue-500 hover:underline"
                  onClick={() => {
                    // Lưu đường dẫn hiện tại để quay lại sau khi đăng nhập
                    localStorage.setItem("returnPath", location.pathname);
                  }}
                >
                  đăng nhập
                </Link>{" "}
                để bình luận
              </p>
            </div>
          )}

          <div className="space-y-4">
            {comments.length > 0 ? (
              comments.map((comment) => (
                <div
                  key={comment.id}
                  className="flex gap-4 p-4 border rounded-md"
                >
                  <img
                    src={
                      comment.user?.avatar || "https://via.placeholder.com/40"
                    }
                    alt="User avatar"
                    className="w-10 h-10 rounded-full object-cover"
                  />
                  <div>
                    <div className="flex items-center gap-2">
                      <h4 className="font-medium">
                        {comment.user?.name || "Người dùng ẩn danh"}
                      </h4>
                      <span className="text-gray-500 text-sm">
                        {new Date(comment.created_at).toLocaleDateString(
                          "vi-VN"
                        )}
                      </span>
                    </div>
                    <p className="mt-1 text-gray-700">{comment.content}</p>
                  </div>
                </div>
              ))
            ) : (
              <p className="text-center text-gray-500">
                Chưa có bình luận nào.
              </p>
            )}
          </div>
        </div>
      </div>
    </main>
  );
};

export default ProductDetail;
