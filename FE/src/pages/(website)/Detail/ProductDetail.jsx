import React, { useEffect, useState } from "react";
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
          }
        }

        setLoading(false);
      })
      .catch((error) => {
        console.error("Lỗi khi lấy dữ liệu sản phẩm:", error);
        setError(error);
        setLoading(false);
      });
  }, [id]);

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
    }
  };

  const decreaseQuantity = () => {
    if (quantity > 1) {
      setQuantity(quantity - 1);
    }
  };

  const handleAddToCart = async () => {
    const token = localStorage.getItem("authToken");

    if (!token) {
      toast.error("Vui lòng đăng nhập để thêm sản phẩm vào giỏ hàng");
      navigate("/sign-in");
      return;
    }

    if (getStockQuantity() <= 0) {
      toast.error("Sản phẩm đã hết hàng");
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
        {
          headers: {
            Authorization: `Bearer ${token}`,
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
                {/* <div className="mt-4 bg-gray-50 p-3 rounded-lg">
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
                </div> */}
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
  );
};

export default ProductDetail;
