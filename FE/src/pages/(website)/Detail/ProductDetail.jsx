import React, { useEffect, useState } from "react";
import { useParams, useNavigate } from "react-router-dom";
import axios from "axios";
import { motion } from "framer-motion";
import {
  IoStar,
  IoCartOutline,
  IoArrowForward,
  IoCheckmarkCircle,
  IoSend,
  IoStarOutline,
  IoChatbubbleOutline,
  IoTimeOutline,
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

  // State cho phần bình luận và đánh giá
  const [comments, setComments] = useState([]);
  const [reviews, setReviews] = useState([]);
  const [commentInput, setCommentInput] = useState("");
  const [isSubmittingComment, setIsSubmittingComment] = useState(false);
  const [currentUser, setCurrentUser] = useState(null);
  const [showFeedback, setShowFeedback] = useState(false);
  const [feedbackMessage, setFeedbackMessage] = useState("");
  const [feedbackType, setFeedbackType] = useState("success");
  const [activeTab, setActiveTab] = useState("reviews"); // 'reviews' hoặc 'comments'

  // State cho đánh giá sản phẩm
  const [selectedRating, setSelectedRating] = useState(5);
  const [isSubmittingReview, setIsSubmittingReview] = useState(false);
  const [canReview, setCanReview] = useState(false);
  const [canComment, setCanComment] = useState(false);
  const [purchaseMessage, setPurchaseMessage] = useState("");
  const [reviewText, setReviewText] = useState("");
  const [reviewImages, setReviewImages] = useState([]);

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

    // Lấy thông tin người dùng từ localStorage
    const userData = localStorage.getItem("userData");
    if (userData) {
      try {
        setCurrentUser(JSON.parse(userData));
      } catch (error) {
        console.error("Lỗi khi parse userData:", error);
      }
    }

    // Lấy bình luận và đánh giá khi component mount
    fetchCommentsAndReviews();

    // Kiểm tra trạng thái đánh giá
    checkReviewStatus();
  }, [id]);

  // Hàm kiểm tra trạng thái đánh giá đơn giản
  const checkReviewStatus = () => {
    const token = localStorage.getItem("authToken");
    if (!token) {
      setCanReview(false);
      setCanComment(false);
      return;
    }

    // Lấy danh sách reviews để kiểm tra xem đã đánh giá chưa
    axios
      .get(`http://localhost:8000/api/products/${id}/reviews`, {
        headers: {
          Authorization: `Bearer ${token}`,
        },
      })
      .then((response) => {
        if (response.data && response.data.data) {
          const reviewsList = response.data.data;
          const userData = JSON.parse(localStorage.getItem("userData"));
          const userId = userData ? userData.id : null;

          // Kiểm tra xem user đã đánh giá chưa
          const hasReviewed = reviewsList.some(
            (review) => review.user.id === userId
          );

          // Tạm thời set canReview là true, thực tế sẽ do API kiểm tra khi gửi đánh giá
          setCanReview(!hasReviewed);
          setCanComment(true);
        }
      })
      .catch((error) => {
        console.error("Lỗi khi lấy thông tin đánh giá:", error);
      });
  };

  // Hàm lấy bình luận và đánh giá
  const fetchCommentsAndReviews = () => {
    // Lấy bình luận
    axios
      .get(`http://localhost:8000/api/products/${id}/comments`)
      .then((response) => {
        if (response.data && response.data.data) {
          setComments(response.data.data);
        }
      })
      .catch((error) => {
        console.error("Lỗi khi lấy danh sách bình luận:", error);
      });

    // Lấy đánh giá
    axios
      .get(`http://localhost:8000/api/products/${id}/reviews`)
      .then((response) => {
        if (response.data && response.data.data) {
          setReviews(response.data.data);
        }
      })
      .catch((error) => {
        console.error("Lỗi khi lấy danh sách đánh giá:", error);
      });
  };

  // Hàm gửi bình luận
  const handleSubmitComment = async (e) => {
    e.preventDefault();

    if (!commentInput.trim()) {
      toast.error("Vui lòng nhập nội dung bình luận");
      return;
    }

    // Kiểm tra đăng nhập
    const token = localStorage.getItem("authToken");
    if (!token) {
      toast.error("Vui lòng đăng nhập để bình luận");
      navigate("/sign-in");
      return;
    }

    // Kiểm tra nếu người dùng chưa mua sản phẩm
    if (!canComment) {
      toast.error(
        purchaseMessage || "Bạn cần mua sản phẩm này trước khi bình luận"
      );
      return;
    }

    setIsSubmittingComment(true);

    try {
      const response = await axios.post(
        "http://localhost:8000/api/comments",
        {
          product_id: id,
          content: commentInput,
        },
        {
          headers: {
            Authorization: `Bearer ${token}`,
          },
        }
      );

      if (response.data && response.data.data) {
        // Reset input và hiển thị thông báo thành công
        setCommentInput("");
        toast.success("Bình luận đã được gửi thành công");

        // Refresh lại danh sách bình luận
        axios
          .get(`http://localhost:8000/api/products/${id}/comments`)
          .then((response) => {
            if (response.data && response.data.data) {
              setComments(response.data.data);
            }
          })
          .catch((error) => {
            console.error("Lỗi khi làm mới danh sách bình luận:", error);
          });
      }
    } catch (error) {
      console.error("Lỗi khi gửi bình luận:", error);
      if (
        error.response &&
        error.response.data &&
        error.response.data.message
      ) {
        toast.error(error.response.data.message);
      } else {
        toast.error("Có lỗi xảy ra khi gửi bình luận");
      }
    } finally {
      setIsSubmittingComment(false);
    }
  };

  // Hàm format thời gian
  const formatDateTime = (dateTimeStr) => {
    const date = new Date(dateTimeStr);
    return new Intl.DateTimeFormat("vi-VN", {
      year: "numeric",
      month: "2-digit",
      day: "2-digit",
      hour: "2-digit",
      minute: "2-digit",
    }).format(date);
  };

  // Hiển thị số sao đánh giá
  const renderStars = (rating) => {
    return (
      <div className="flex">
        {[1, 2, 3, 4, 5].map((star) => (
          <span key={star}>
            {star <= rating ? (
              <IoStar className="text-amber-400 w-4 h-4" />
            ) : (
              <IoStarOutline className="text-amber-400 w-4 h-4" />
            )}
          </span>
        ))}
      </div>
    );
  };

  // Hiển thị số sao đánh giá có thể tương tác
  const renderInteractiveStars = () => {
    return (
      <div className="flex">
        {[1, 2, 3, 4, 5].map((star) => (
          <span
            key={star}
            className="cursor-pointer"
            onClick={() => setSelectedRating(star)}
            onMouseEnter={() => setSelectedRating(star)}
          >
            {star <= selectedRating ? (
              <IoStar className="text-amber-400 w-6 h-6 transition-all hover:scale-110" />
            ) : (
              <IoStarOutline className="text-amber-400 w-6 h-6 transition-all hover:scale-110" />
            )}
          </span>
        ))}
      </div>
    );
  };

  // Tính trung bình đánh giá
  const calculateAverageRating = () => {
    if (!reviews || reviews.length === 0) return 0;

    const totalRating = reviews.reduce((sum, review) => sum + review.rating, 0);
    return (totalRating / reviews.length).toFixed(1);
  };

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

  // Hàm gửi đánh giá
  const handleSubmitReview = async (e) => {
    e.preventDefault();

    if (!reviewText.trim()) {
      toast.error("Vui lòng nhập nội dung đánh giá");
      return;
    }

    const token = localStorage.getItem("authToken");
    if (!token) {
      toast.error("Vui lòng đăng nhập để đánh giá");
      navigate("/sign-in");
      return;
    }

    setIsSubmittingReview(true);

    try {
      // Tạo form data để gửi cả ảnh và thông tin đánh giá
      const formData = new FormData();
      formData.append("product_id", id);
      formData.append("rating", selectedRating);
      formData.append("review_text", reviewText);

      // Thêm các ảnh vào form data nếu có
      if (reviewImages.length > 0) {
        reviewImages.forEach((image) => {
          formData.append("images[]", image);
        });
      }

      const response = await axios.post(
        "http://localhost:8000/api/reviews",
        formData,
        {
          headers: {
            Authorization: `Bearer ${token}`,
            "Content-Type": "multipart/form-data",
          },
        }
      );

      if (response.data && response.data.success) {
        // Reset input và hiển thị thông báo thành công
        setReviewText("");
        setReviewImages([]);
        toast.success("Đánh giá của bạn đã được gửi thành công");

        // Refresh lại danh sách đánh giá
        fetchCommentsAndReviews();

        // Đã đánh giá xong, không thể đánh giá lại
        setCanReview(false);
      }
    } catch (error) {
      console.error("Lỗi khi gửi đánh giá:", error);
      if (
        error.response &&
        error.response.data &&
        error.response.data.message
      ) {
        toast.error(error.response.data.message);
      } else {
        toast.error("Có lỗi xảy ra khi gửi đánh giá");
      }
    } finally {
      setIsSubmittingReview(false);
    }
  };

  // Xử lý chọn ảnh cho đánh giá
  const handleImageUpload = (e) => {
    const files = Array.from(e.target.files);
    if (files.length + reviewImages.length > 5) {
      toast.error("Bạn chỉ được tải lên tối đa 5 ảnh");
      return;
    }
    setReviewImages([...reviewImages, ...files]);
  };

  // Xóa ảnh khỏi danh sách đã chọn
  const removeImage = (index) => {
    setReviewImages(reviewImages.filter((_, i) => i !== index));
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

        {/* Phần đánh giá và bình luận */}
        <div className="p-6 border-t border-gray-100">
          <div className="mb-6">
            <div className="flex border-b">
              <button
                className={`px-6 py-3 font-medium text-sm ${
                  activeTab === "reviews"
                    ? "text-amber-500 border-b-2 border-amber-500"
                    : "text-gray-500 hover:text-gray-700"
                }`}
                onClick={() => setActiveTab("reviews")}
              >
                Đánh giá sản phẩm ({reviews.length})
              </button>
              <button
                className={`px-6 py-3 font-medium text-sm ${
                  activeTab === "comments"
                    ? "text-amber-500 border-b-2 border-amber-500"
                    : "text-gray-500 hover:text-gray-700"
                }`}
                onClick={() => setActiveTab("comments")}
              >
                Bình luận ({comments.length})
              </button>
            </div>
          </div>

          {activeTab === "reviews" && (
            <div className="space-y-6">
              {/* Tổng quan đánh giá */}
              <div className="bg-gray-50 p-4 rounded-lg">
                <div className="flex flex-col md:flex-row gap-6 items-center">
                  <div className="text-center">
                    <div className="text-3xl font-bold text-amber-500">
                      {calculateAverageRating()}
                      <span className="text-lg text-gray-500">/5</span>
                    </div>
                    <div className="flex justify-center mt-2">
                      {renderStars(Math.round(calculateAverageRating()))}
                    </div>
                    <div className="text-sm text-gray-500 mt-1">
                      ({reviews.length} đánh giá)
                    </div>
                  </div>

                  <div className="flex-1 grid grid-cols-1 gap-2">
                    {[5, 4, 3, 2, 1].map((star) => {
                      const count = reviews.filter(
                        (review) => review.rating === star
                      ).length;
                      const percentage =
                        reviews.length > 0
                          ? Math.round((count / reviews.length) * 100)
                          : 0;

                      return (
                        <div
                          key={star}
                          className="flex items-center text-sm gap-2"
                        >
                          <div className="w-8 text-gray-600">{star} sao</div>
                          <div className="flex-1 bg-gray-200 rounded-full h-2.5">
                            <div
                              className="bg-amber-400 h-2.5 rounded-full"
                              style={{ width: `${percentage}%` }}
                            ></div>
                          </div>
                          <div className="w-10 text-gray-500 text-right">
                            {percentage}%
                          </div>
                        </div>
                      );
                    })}
                  </div>
                </div>
              </div>

              {/* Hiển thị thông báo khi không thể đánh giá */}
              {!canReview && (
                <div className="bg-amber-50 border border-amber-200 rounded-lg p-4 text-amber-700">
                  <p className="font-medium text-center">
                    {currentUser
                      ? purchaseMessage || "Cần mua hàng để đánh giá"
                      : "Vui lòng đăng nhập để đánh giá sản phẩm"}
                  </p>
                </div>
              )}

              {/* Form đánh giá sản phẩm */}
              {canReview && (
                <div className="bg-white border border-gray-200 rounded-lg p-6 mb-6 shadow-sm">
                  <h3 className="text-lg font-medium text-gray-800 mb-4">
                    Đánh giá sản phẩm
                  </h3>
                  <form onSubmit={handleSubmitReview}>
                    <div className="space-y-4">
                      <div>
                        <label className="block text-gray-700 mb-2">
                          Chọn số sao
                        </label>
                        <div className="flex items-center">
                          {renderInteractiveStars()}
                          <span className="ml-2 text-amber-500 font-medium">
                            {selectedRating}/5
                          </span>
                        </div>
                      </div>

                      <div>
                        <label
                          htmlFor="reviewText"
                          className="block text-gray-700 mb-2"
                        >
                          Nội dung đánh giá
                        </label>
                        <textarea
                          id="reviewText"
                          value={reviewText}
                          onChange={(e) => setReviewText(e.target.value)}
                          placeholder="Chia sẻ trải nghiệm của bạn về sản phẩm này..."
                          className="w-full border border-gray-300 rounded-lg p-3 focus:outline-none focus:ring-2 focus:ring-amber-500"
                          rows={4}
                        ></textarea>
                      </div>

                      {/* Phần tải ảnh lên */}
                      <div>
                        <label className="block text-gray-700 mb-2">
                          Hình ảnh (Tối đa 5 ảnh)
                        </label>
                        <div className="mb-2">
                          <input
                            type="file"
                            accept="image/*"
                            multiple
                            onChange={handleImageUpload}
                            className="hidden"
                            id="review-images"
                          />
                          <label
                            htmlFor="review-images"
                            className="cursor-pointer inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50"
                          >
                            Chọn ảnh
                          </label>
                          <span className="ml-2 text-gray-500 text-sm">
                            {reviewImages.length}/5 ảnh đã chọn
                          </span>
                        </div>

                        {/* Hiển thị ảnh đã chọn */}
                        {reviewImages.length > 0 && (
                          <div className="flex flex-wrap gap-2 mt-2">
                            {reviewImages.map((file, index) => (
                              <div key={index} className="relative group">
                                <div className="w-16 h-16 rounded overflow-hidden border border-gray-300">
                                  <img
                                    src={URL.createObjectURL(file)}
                                    alt={`Preview ${index}`}
                                    className="w-full h-full object-cover"
                                  />
                                </div>
                                <button
                                  type="button"
                                  onClick={() => removeImage(index)}
                                  className="absolute -top-1 -right-1 bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs hover:bg-red-600"
                                >
                                  &times;
                                </button>
                              </div>
                            ))}
                          </div>
                        )}
                      </div>

                      <div className="text-right">
                        <button
                          type="submit"
                          disabled={isSubmittingReview || !reviewText.trim()}
                          className="px-5 py-2 bg-amber-500 text-white rounded-lg font-medium hover:bg-amber-600 transition disabled:opacity-50 disabled:cursor-not-allowed flex items-center ml-auto"
                        >
                          {isSubmittingReview ? (
                            <div className="h-5 w-5 border-2 border-white border-t-transparent rounded-full animate-spin mr-2"></div>
                          ) : null}
                          Gửi đánh giá
                        </button>
                      </div>
                    </div>
                  </form>
                </div>
              )}

              {/* Danh sách đánh giá */}
              {reviews.length > 0 ? (
                <div className="space-y-4">
                  {reviews.map((review) => (
                    <div
                      key={review.id}
                      className="border border-gray-100 rounded-lg p-4"
                    >
                      <div className="flex items-start gap-3">
                        <div className="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center text-gray-700 font-medium">
                          {review.user.name.charAt(0).toUpperCase()}
                        </div>
                        <div className="flex-1">
                          <div className="flex flex-wrap items-baseline gap-2">
                            <h3 className="text-gray-800 font-medium">
                              {review.user.name}
                            </h3>
                            <div className="flex items-center">
                              {renderStars(review.rating)}
                            </div>
                          </div>

                          <p className="text-gray-600 mt-2">
                            {review.review_text}
                          </p>

                          {/* Hình ảnh đánh giá */}
                          {review.images && review.images.length > 0 && (
                            <div className="mt-3 flex flex-wrap gap-2">
                              {review.images.map((img, index) => (
                                <div
                                  key={index}
                                  className="w-20 h-20 rounded overflow-hidden"
                                >
                                  <img
                                    src={`http://localhost:8000/storage/${img}`}
                                    alt={`review-${index}`}
                                    className="w-full h-full object-cover"
                                    onError={(e) => {
                                      e.target.src =
                                        "https://via.placeholder.com/80?text=Error";
                                    }}
                                  />
                                </div>
                              ))}
                            </div>
                          )}

                          <div className="flex items-center text-xs text-gray-400 mt-2">
                            <IoTimeOutline className="mr-1" />
                            {formatDateTime(review.created_at)}
                          </div>
                        </div>
                      </div>
                    </div>
                  ))}
                </div>
              ) : (
                <div className="text-center py-10">
                  <IoChatbubbleOutline className="mx-auto h-12 w-12 text-gray-300" />
                  <p className="mt-2 text-gray-500">
                    Sản phẩm này chưa có đánh giá nào.
                  </p>
                </div>
              )}
            </div>
          )}

          {activeTab === "comments" && (
            <div className="space-y-6">
              {/* Form bình luận */}
              {canComment ? (
                <div className="bg-gray-50 p-4 rounded-lg">
                  <h3 className="text-lg font-medium text-gray-800 mb-3">
                    Để lại bình luận
                  </h3>
                  <form onSubmit={handleSubmitComment}>
                    <div className="flex gap-3">
                      <div className="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center text-gray-700 font-medium shrink-0">
                        {currentUser
                          ? currentUser.name.charAt(0).toUpperCase()
                          : "?"}
                      </div>
                      <div className="flex-1 relative">
                        <textarea
                          value={commentInput}
                          onChange={(e) => setCommentInput(e.target.value)}
                          placeholder="Nhập bình luận của bạn..."
                          className="w-full border border-gray-300 rounded-lg p-3 pr-12 focus:outline-none focus:ring-2 focus:ring-amber-500"
                          rows={3}
                        ></textarea>
                        <button
                          type="submit"
                          disabled={isSubmittingComment || !commentInput.trim()}
                          className="absolute right-3 bottom-3 text-amber-500 hover:text-amber-600 disabled:opacity-50"
                        >
                          {isSubmittingComment ? (
                            <div className="h-5 w-5 border-2 border-amber-500 border-t-transparent rounded-full animate-spin"></div>
                          ) : (
                            <IoSend className="text-xl" />
                          )}
                        </button>
                      </div>
                    </div>
                  </form>
                </div>
              ) : (
                <div className="bg-amber-50 border border-amber-200 rounded-lg p-4 text-amber-700">
                  <p className="font-medium">
                    {purchaseMessage ||
                      "Bạn cần mua và nhận sản phẩm này để có thể bình luận."}
                  </p>
                </div>
              )}

              {/* Danh sách bình luận */}
              {comments.length > 0 ? (
                <div className="space-y-4">
                  {comments.map((comment) => (
                    <div
                      key={comment.id}
                      className="border border-gray-100 rounded-lg p-4"
                    >
                      <div className="flex items-start gap-3">
                        <div className="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center text-gray-700 font-medium">
                          {comment.user.name.charAt(0).toUpperCase()}
                        </div>
                        <div className="flex-1">
                          <div className="flex flex-wrap items-baseline gap-2">
                            <h3 className="text-gray-800 font-medium">
                              {comment.user.name}
                            </h3>
                          </div>

                          <p className="text-gray-600 mt-2">
                            {comment.content}
                          </p>

                          <div className="flex items-center text-xs text-gray-400 mt-2">
                            <IoTimeOutline className="mr-1" />
                            {formatDateTime(comment.created_at)}
                          </div>
                        </div>
                      </div>
                    </div>
                  ))}
                </div>
              ) : (
                <div className="text-center py-10">
                  <IoChatbubbleOutline className="mx-auto h-12 w-12 text-gray-300" />
                  <p className="mt-2 text-gray-500">
                    Chưa có bình luận nào cho sản phẩm này.
                  </p>
                </div>
              )}
            </div>
          )}
        </div>
      </motion.div>
    </div>
  );
};

export default ProductDetail;
