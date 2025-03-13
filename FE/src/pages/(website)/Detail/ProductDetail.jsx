import { useState, useEffect } from "react";
import { Link, useParams } from "react-router-dom";
import axios from "axios";

const ProductDetail = () => {
  const [product, setProduct] = useState(null);
  const [comments, setComments] = useState([]);
  const [loading, setLoading] = useState(true);
  const [commentInput, setCommentInput] = useState(""); // State cho input bình luận
  const [selectedImage, setSelectedImage] = useState(null); // State cho ảnh được chọn
  const [selectedVariants, setSelectedVariants] = useState({}); // State cho biến thể được chọn
  const [quantity, setQuantity] = useState(1); // State cho số lượng sản phẩm
  const [error, setError] = useState(""); // State cho thông báo lỗi
  const [currentPrice, setCurrentPrice] = useState(null); // State cho giá hiện tại dựa trên biến thể
  const [currentDiscount, setCurrentDiscount] = useState(null); // State cho giá khuyến mãi hiện tại
  const { id } = useParams();

  console.log("ID from useParams:", id); // Debug ID

  useEffect(() => {
    const fetchProduct = async () => {
      try {
        // Lấy thông tin sản phẩm
        const response = await axios.get(
          `http://localhost:8000/api/products/${id}`
        );
        console.log("API Response:", response.data); // Debug response
        let productData = response.data.data;

        // Thiết lập giá mặc định
        setCurrentPrice(productData.price);
        setCurrentDiscount(productData.discount_price);

        // Xử lý dữ liệu biến thể trực tiếp từ response sản phẩm
        // QUAN TRỌNG: Không gọi API riêng cho từng biến thể nữa
        if (productData.variants && Array.isArray(productData.variants)) {
          console.log("Variants từ API:", productData.variants);

          // Map để xem cấu trúc dữ liệu biến thể
          productData.variants.forEach((variant) => {
            console.log("Biến thể:", variant);
          });

          // Chuẩn bị dữ liệu biến thể
          const variantsWithValues = productData.variants.map((variant) => {
            // Đảm bảo variant có đủ thông tin cần thiết
            return {
              ...variant,
              variant_value: {
                id: variant.variant_value_id || 0,
                value: variant.value || variant.name || "Không xác định",
              },
            };
          });

          // Tự động chọn biến thể đầu tiên của mỗi loại nếu có
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

              // Nếu biến thể đầu tiên có giá, cập nhật giá hiển thị
              if (availableVariants[0].price) {
                setCurrentPrice(availableVariants[0].price);
                setCurrentDiscount(
                  availableVariants[0].discount_price ||
                    availableVariants[0].price
                );
              }
            }
          });

          setProduct({ ...productData, variants: variantsWithValues });
          setSelectedVariants(defaultVariants);
        } else {
          setProduct(productData);
        }

        // Lấy danh sách bình luận
        const commentsResponse = await axios.get(
          `http://localhost:8000/api/products/${id}/comments`
        );
        setComments(commentsResponse.data.data || []);

        setLoading(false);
      } catch (error) {
        console.error("Error fetching product:", error);
        setLoading(false);
      }
    };
    fetchProduct();
  }, [id]);

  if (loading) {
    return <div className="text-center mt-32">Đang tải...</div>;
  }

  if (!product) {
    return <div className="text-center mt-32">Không tìm thấy sản phẩm</div>;
  }

  console.log("Product state:", product); // Debug product state

  // Hàm định dạng giá tiền
  const formatPrice = (price) => {
    const numericPrice = price ? parseFloat(price) : null;
    return numericPrice && !isNaN(numericPrice)
      ? numericPrice.toLocaleString("vi-VN", { minimumFractionDigits: 0 }) + "đ"
      : "Giá không khả dụng";
  };

  const baseURL = "http://localhost:8000/";

  // Sửa cách xử lý URL ảnh chính
  const mainImageUrl =
    selectedImage ||
    (product.image_thumnail
      ? product.image_thumnail.startsWith("http")
        ? product.image_thumnail
        : `${baseURL}storage/${product.image_thumnail}`
      : "https://via.placeholder.com/400x400?text=No+Image");

  console.log("Main Image URL:", mainImageUrl);

  // Debug URL ảnh thư viện
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

  // Hàm xử lý khi click vào ảnh trong thư viện
  const handleGalleryImageClick = (imageUrl) => {
    setSelectedImage(imageUrl);
  };

  // Hàm xử lý khi chọn biến thể
  const handleVariantSelect = (variantId, variantValueId, variant) => {
    const newSelectedVariants = {
      ...selectedVariants,
      [variantId]: variantValueId,
    };
    setSelectedVariants(newSelectedVariants);

    console.log(`Đã chọn biến thể:`, variant);

    // Cập nhật giá dựa trên biến thể đã chọn
    if (variant && variant.price) {
      // Nếu biến thể có giá riêng, cập nhật giá sản phẩm
      setCurrentPrice(variant.price);
      setCurrentDiscount(variant.discount_price || variant.price);
      console.log(`Đã cập nhật giá theo biến thể: ${variant.price}`);
    } else {
      // Tìm biến thể phù hợp nhất với các lựa chọn hiện tại
      const selectedVariantCombination =
        findSelectedVariantCombination(newSelectedVariants);
      if (selectedVariantCombination && selectedVariantCombination.price) {
        setCurrentPrice(selectedVariantCombination.price);
        setCurrentDiscount(
          selectedVariantCombination.discount_price ||
            selectedVariantCombination.price
        );
        console.log(
          `Cập nhật giá theo biến thể kết hợp: ${selectedVariantCombination.price}`
        );
      }
    }

    setError(""); // Xóa thông báo lỗi khi người dùng chọn biến thể
  };

  // Hàm tìm biến thể phù hợp với lựa chọn của người dùng
  const findSelectedVariantCombination = (selectedVars) => {
    if (!product.variants || product.variants.length === 0) return null;

    // Tìm biến thể phù hợp nhất với các lựa chọn hiện tại
    const selectedVariantTypes = Object.keys(selectedVars).map(Number);

    // Đầu tiên tìm biến thể khớp với tất cả các lựa chọn
    const exactMatch = product.variants.find((variant) => {
      return selectedVariantTypes.every(
        (typeId) =>
          variant.variant_id === typeId &&
          variant.variant_value_id === selectedVars[typeId]
      );
    });

    if (exactMatch) return exactMatch;

    // Nếu không tìm thấy, trả về biến thể đầu tiên có số lượng > 0
    return product.variants.find((v) => v.quantity > 0) || null;
  };

  // Kiểm tra xem biến thể có còn hàng không
  const isVariantInStock = (variant) => {
    if (!variant) return false;
    return variant.quantity > 0;
  };

  // Kiểm tra xem đã chọn đủ biến thể chưa
  const hasSelectedAllRequiredVariants = () => {
    if (!product.variants || product.variants.length === 0) return true;

    // Lấy danh sách các loại biến thể có sẵn
    const availableVariantTypes = [
      ...new Set(product.variants.map((v) => v.variant_id)),
    ];

    // Kiểm tra xem đã chọn đủ các loại biến thể chưa
    return availableVariantTypes.every((type) => selectedVariants[type]);
  };

  // Hàm xử lý khi thêm vào giỏ hàng
  const handleAddToCart = () => {
    if (!hasSelectedAllRequiredVariants()) {
      setError("Vui lòng chọn đầy đủ biến thể sản phẩm");
      return;
    }

    // Thêm vào giỏ hàng (có thể thay bằng API call thực tế)
    console.log("Thêm vào giỏ hàng:", {
      product_id: product.id,
      variants: selectedVariants,
      quantity: quantity,
    });

    // Hiển thị thông báo thành công
    alert("Đã thêm sản phẩm vào giỏ hàng!");
  };

  // Hàm xử lý khi thêm vào danh sách yêu thích
  const handleAddToWishlist = () => {
    if (!hasSelectedAllRequiredVariants()) {
      setError("Vui lòng chọn đầy đủ biến thể sản phẩm");
      return;
    }

    // Thêm vào danh sách yêu thích (có thể thay bằng API call thực tế)
    console.log("Thêm vào danh sách yêu thích:", {
      product_id: product.id,
      variants: selectedVariants,
    });

    // Hiển thị thông báo thành công
    alert("Đã thêm sản phẩm vào danh sách yêu thích!");
  };

  // Hàm xử lý khi mua ngay
  const handleBuyNow = () => {
    if (!hasSelectedAllRequiredVariants()) {
      setError("Vui lòng chọn đầy đủ biến thể sản phẩm");
      return;
    }

    // Xử lý mua ngay (có thể thay bằng chuyển hướng đến trang thanh toán)
    console.log("Mua ngay:", {
      product_id: product.id,
      variants: selectedVariants,
      quantity: quantity,
    });

    // Chuyển hướng đến trang thanh toán (giả lập)
    alert("Đang chuyển đến trang thanh toán...");
  };

  // Hàm tăng số lượng
  const increaseQuantity = () => {
    setQuantity((prev) => prev + 1);
  };

  // Hàm giảm số lượng
  const decreaseQuantity = () => {
    if (quantity > 1) {
      setQuantity((prev) => prev - 1);
    }
  };

  // Lấy danh sách biến thể theo loại
  const getVariantsByType = (variantId) => {
    // Lọc ra các biến thể của loại cụ thể
    const typeVariants =
      product.variants?.filter((variant) => variant.variant_id === variantId) ||
      [];

    console.log(`Biến thể loại ${variantId}:`, typeVariants);

    return typeVariants;
  };

  // Lấy tên của loại biến thể
  const getVariantTypeName = (variantId) => {
    const variantTypes = {
      1: "Màu sắc",
      2: "Kích thước",
      3: "Chất liệu",
      4: "Kiểu dáng",
    };
    return variantTypes[variantId] || "Biến thể";
  };

  // Hàm gửi bình luận
  const handleCommentSubmit = async () => {
    if (!commentInput.trim()) return;
    try {
      const response = await axios.post(
        `http://localhost:8000/api/products/${id}/comments`,
        {
          product_id: id,
          user_id: 1, // Giả định user_id (cần thay bằng ID người dùng đã đăng nhập)
          content: commentInput,
        }
      );
      setComments([...comments, response.data.data]);
      setCommentInput("");
    } catch (error) {
      console.error("Error submitting comment:", error);
    }
  };

  // Xác định sản phẩm mẫu từ dữ liệu BE
  const isSampleProduct =
    product.is_sample === 1 ||
    product.is_sample === true ||
    product.status === "sample";

  return (
    <main className="max-w-6xl mx-auto mb-20 mt-32">
      {/* Product_info */}
      <div className="grid grid-cols-2 gap-8 my-16">
        {/* IMAGE */}
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
          <div className="flex justify-start space-x-5 mt-4">
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
        {/* Info */}
        <div>
          {/* name */}
          <h5 className="text-[20px] font-semibold text-3xl">
            {product.name || "Tên sản phẩm"}
          </h5>
          {/* price */}
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

          {/* Hiển thị thông báo nếu là sản phẩm mẫu */}
          {isSampleProduct && (
            <p className="mt-3 text-[16px] font-medium">
              Đây là sản phẩm mẫu không bán
            </p>
          )}

          {/* short description */}
          <p className="mt-3 text-[16px] font-medium">
            {product.short_description || "Không có mô tả"}
          </p>

          {/* Hiển thị các biến thể */}
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
                    // Kiểm tra variant hợp lệ
                    if (!variant) {
                      console.log(`Bỏ qua variant không hợp lệ:`, variant);
                      return null;
                    }

                    const isSelected =
                      selectedVariants[variantTypeId] ===
                      variant.variant_value_id;
                    const inStock = isVariantInStock(variant);

                    // Xử lý hiển thị màu sắc đặc biệt
                    if (variantTypeId === 1) {
                      // Màu sắc
                      let bgColor = "gray";
                      // Lấy giá trị màu sắc từ nhiều nguồn có thể có
                      const variantValue =
                        variant.value || variant.variant_value?.value || "";
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
                      // Kích thước - Hiển thị đặc biệt
                      // Lấy giá trị từ nhiều nguồn có thể có
                      const sizeValue =
                        variant.value || variant.variant_value?.value || "";
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
                      // Các loại biến thể khác
                      const otherValue =
                        variant.value || variant.variant_value?.value || "";
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

          {/* Hiển thị thông báo lỗi nếu có */}
          {error && <div className="mt-2 text-red-500 text-sm">{error}</div>}

          {/* Button quantity/buy/compare */}
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
          {/* inf_end */}
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
              Hàng tồn kho:{" "}
              {product.variants && Array.isArray(product.variants)
                ? product.variants.reduce(
                    (sum, v) => sum + (v.quantity || 0),
                    0
                  )
                : 0}
            </div>
            {/* <div className="text-[#A3A3A3] text-[16px] mb-3">
              Thẻ:{" "}
              {product.tags && Array.isArray(product.tags)
                ? product.tags.join(", ")
                : "Sofa, Chair, Home, Shop"}
            </div> */}
          </div>
        </div>
      </div>

      {/* product_description / comment */}
      <div className="mt-4">
        <ul className="flex gap-x-16 mb-4">
          <li className="text-[20px] font-semibold text-[#000000]">
            <Link to="/">Comment</Link>
          </li>

          <li className="text-[20px] font-semibold text-[#A3A3A3]">
            <Link to="/">Additional Information</Link>
          </li>

          <li className="text-[20px] font-semibold text-[#A3A3A3]">
            <Link to="/">Description</Link>
          </li>
        </ul>
        {/* <div className="mt-4">
          <h3 className="font-semibold text-xl mb-2">Mô tả sản phẩm</h3>
          <div
            className="text-gray-600"
            dangerouslySetInnerHTML={{
              __html: product.description || "Không có mô tả chi tiết",
            }}
          />
        </div> */}

        {/* Form bình luận */}
        <div className="flex justify-between items-center border p-2 rounded-md mt-4">
          <input
            type="text"
            className="w-3/4 p-2 outline-none"
            placeholder="Nhập đánh giá của bạn ở đây"
            value={commentInput}
            onChange={(e) => setCommentInput(e.target.value)}
          />
          <button
            className="ml-4 border p-2 rounded-md bg-blue-500 text-white"
            onClick={handleCommentSubmit}
          >
            Gửi đánh giá
          </button>
        </div>
        {/* Danh sách bình luận */}
        <div className="mt-4">
          <h3 className="font-semibold text-xl mb-2">Bình luận</h3>
          {comments.length > 0 ? (
            comments.map((comment) => (
              <div key={comment.id} className="border-b py-2">
                <p>{comment.content}</p>
                <small className="text-gray-500">
                  {`Người dùng ID: ${comment.user_id}`}{" "}
                  {/* Có thể thay bằng tên nếu có API user */}
                </small>
              </div>
            ))
          ) : (
            <p>Chưa có bình luận nào.</p>
          )}
        </div>
      </div>
    </main>
  );
};

export default ProductDetail;
