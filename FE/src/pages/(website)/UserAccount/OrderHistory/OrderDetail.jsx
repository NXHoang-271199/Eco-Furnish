import React, { useState, useEffect, useCallback, useRef, memo } from "react";
import { useParams, Link, useNavigate } from "react-router-dom";
import axios from "axios";
import axiosInstance from "../../../../utils/axiosConfig";
import Swal from 'sweetalert2';
import {
  FiArrowLeft,
  FiInfo,
  FiPhone,
  FiMail,
  FiMapPin,
  FiPackage,
  FiClock,
  FiTruck,
  FiCalendar,
  FiCheckCircle,
  FiXCircle,
  FiAlertCircle,
  FiShoppingBag,
  FiRefreshCw,
  FiHome,
  FiUser,
  FiX,
  FiLoader,
  FiStar,
  FiCamera,
  FiUpload,
  FiMessageSquare,
} from "react-icons/fi";
import { motion, AnimatePresence } from "framer-motion";
import { toast } from "react-hot-toast";
import { format } from "date-fns";
import { vi } from "date-fns/locale";
import { RiStarFill, RiStarLine } from "react-icons/ri";

const RefundRequestModal = memo(({
  showModal,
  setShowModal,
  loading,
  selectedReason,
  customReason,
  setCustomReason,
  showCustomInput,
  handleReasonSelect,
  submitRefundRequest,
}) => {
  const reasonOptions = [
    "Hàng lỗi, không hoạt động",
    "Hàng hết hạn sử dụng",
    "Khác với mô tả",
    "Hàng đã qua sử dụng",
    "Hàng giả, nhái",
    "Hàng nguyên vẹn nhưng không còn nhu cầu (sẽ trả nguyên seal, tem, hộp sản phẩm)",
    "Khác",
  ];

  if (!showModal) return null;

  console.log("Rendering RefundRequestModal");

  return (
    <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div className="bg-white rounded-lg shadow-xl w-full max-w-md overflow-hidden transform transition-all">
        <div className="bg-orange-500 text-white px-6 py-4 flex justify-between items-center">
          <h3 className="font-medium text-lg">Yêu cầu hoàn hàng</h3>
          <button
            onClick={() => setShowModal(false)}
            className="text-white hover:text-gray-200"
          >
            <FiX className="h-5 w-5" />
          </button>
        </div>
        <div className="p-6">
          <div className="mb-4">
            <label className="block text-gray-700 font-medium mb-2">
              Lý do*
            </label>
            <div className="space-y-2 max-h-60 overflow-y-auto border border-gray-300 rounded-md p-2">
              {reasonOptions.map((reason, index) => (
                <div
                  key={index}
                  className={`flex items-center p-2 rounded-md cursor-pointer ${selectedReason === reason
                    ? "bg-orange-100 border border-orange-500"
                    : "hover:bg-gray-100"
                    }`}
                  onClick={() => handleReasonSelect(reason)}
                >
                  <div className="h-4 w-4 rounded-full border border-gray-400 flex items-center justify-center mr-2">
                    {selectedReason === reason && (
                      <div className="h-2 w-2 rounded-full bg-orange-500"></div>
                    )}
                  </div>
                  <span className="text-sm">{reason}</span>
                </div>
              ))}
            </div>
          </div>

          {showCustomInput && (
            <div className="mb-4">
              <label className="block text-gray-700 mb-2">
                Nhập lý do khác:
              </label>
              <textarea
                className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                rows="3"
                placeholder="Vui lòng nhập lý do của bạn..."
                value={customReason}
                onChange={(e) => setCustomReason(e.target.value)}
              ></textarea>
            </div>
          )}

          <div className="flex justify-end space-x-3 mt-4">
            <button
              onClick={() => setShowModal(false)}
              className="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-100"
            >
              Hủy
            </button>
            <button
              onClick={submitRefundRequest}
              className="px-4 py-2 bg-orange-500 text-white rounded-md hover:bg-orange-600 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-opacity-50"
              disabled={loading}
            >
              {loading ? (
                <div className="flex items-center justify-center">
                  <FiLoader className="animate-spin mr-2" />
                  Đang xử lý...
                </div>
              ) : (
                "Gửi yêu cầu"
              )}
            </button>
          </div>
        </div>
      </div>
    </div>
  );
});

// Thêm component Modal đánh giá sản phẩm
const ReviewModal = memo(({
  showModal,
  setShowModal,
  loading,
  productId,
  productName,
  productImage,
  orderId,
  productVariant,
  onReviewSubmitSuccess,
}) => {
  const [rating, setRating] = useState(5);
  const [reviewText, setReviewText] = useState("");
  const [reviewImages, setReviewImages] = useState([]);
  const [isSubmitting, setIsSubmitting] = useState(false);

  // Xử lý chọn số sao
  const handleRatingChange = (newRating) => {
    setRating(newRating);
  };

  // Hàm render các sao tương tác
  const renderStars = () => {
    const stars = [];
    for (let i = 1; i <= 5; i++) {
      stars.push(
        <button
          key={i}
          type="button"
          onClick={() => handleRatingChange(i)}
          className={`text-2xl focus:outline-none ${i <= rating ? "text-amber-400" : "text-gray-300"
            }`}
        >
          <FiStar
            className={i <= rating ? "fill-amber-400" : ""}
          />
        </button>
      );
    }
    return stars;
  };

  // Xử lý chọn ảnh
  const handleImageUpload = (e) => {
    const files = Array.from(e.target.files);
    if (files.length + reviewImages.length > 5) {
      toast.error("Bạn chỉ được tải lên tối đa 5 ảnh");
      return;
    }
    setReviewImages([...reviewImages, ...files]);
  };

  // Xóa ảnh đã chọn
  const removeImage = (index) => {
    setReviewImages((prevImages) => prevImages.filter((_, i) => i !== index));
  };

  // Xử lý gửi đánh giá
  const handleSubmitReview = async (e) => {
    e.preventDefault();

    if (!reviewText.trim()) {
      toast.error("Vui lòng nhập nội dung đánh giá");
      return;
    }

    setIsSubmitting(true);

    try {
      const token = localStorage.getItem("authToken");
      if (!token) {
        toast.error("Vui lòng đăng nhập để đánh giá");
        setShowModal(false);
        return;
      }

      // Tạo form data để gửi cả ảnh và thông tin đánh giá
      const formData = new FormData();
      formData.append("product_id", productId);
      formData.append("order_id", orderId);
      formData.append("rating", rating);
      formData.append("review_text", reviewText);
      
      // Thêm product_variant_id nếu có biến thể
      if (productVariant && productVariant.id) {
        formData.append("product_variant_id", productVariant.id);
      }

      // Thêm các ảnh vào form data nếu có
      if (reviewImages.length > 0) {
        reviewImages.forEach((image) => {
          formData.append("images[]", image);
        });
      }

      const response = await axiosInstance.post("/reviews", formData, {
        headers: {
          "Content-Type": "multipart/form-data",
        },
      });

      if (response.data && response.data.success) {
        toast.success("Đánh giá của bạn đã được gửi thành công");
        setShowModal(false);
        setRating(5);
        setReviewText("");
        setReviewImages([]);

        // Gọi callback để cập nhật UI sau khi đánh giá thành công
        if (onReviewSubmitSuccess) {
          onReviewSubmitSuccess(productId);
        }
      }
    } catch (error) {
      console.error("Lỗi khi gửi đánh giá:", error);
      if (error.response?.data?.message) {
        toast.error(error.response.data.message);
      } else {
        toast.error("Có lỗi xảy ra khi gửi đánh giá");
      }
    } finally {
      setIsSubmitting(false);
    }
  };

  // Hiển thị chi tiết biến thể
  const renderVariantDetails = () => {
    if (!productVariant || !productVariant.variant_details) return null;

    return (
      <div className="mt-1 text-gray-500 text-sm">
        {productVariant.variant_details.map((detail, index) => (
          <span key={index}>
            {detail.name}: <span className="font-medium">{detail.value}</span>
            {index < productVariant.variant_details.length - 1 ? ', ' : ''}
          </span>
        ))}
      </div>
    );
  };

  if (!showModal) return null;

  return (
    <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div className="bg-white rounded-lg shadow-xl w-full max-w-lg overflow-hidden transform transition-all">
        <div className="bg-amber-500 text-white px-6 py-4 flex justify-between items-center">
          <h3 className="font-medium text-lg">Đánh giá sản phẩm</h3>
          <button
            onClick={() => setShowModal(false)}
            className="text-white hover:text-gray-200"
          >
            <FiX className="h-5 w-5" />
          </button>
        </div>
        <div className="p-6">
          <div className="flex items-center mb-6">
            <div className="w-16 h-16 flex-shrink-0 rounded-md overflow-hidden border border-gray-200">
              <img
                src={productImage ? `http://localhost:8000/storage/${productImage}` : "https://via.placeholder.com/80"}
                alt={productName}
                className="w-full h-full object-cover"
                onError={(e) => {
                  e.target.onerror = null;
                  e.target.src = "https://via.placeholder.com/80";
                }}
              />
            </div>
            <div className="ml-4">
              <h4 className="font-medium text-gray-800">{productName}</h4>
              {renderVariantDetails()}
            </div>
          </div>

          <form onSubmit={handleSubmitReview}>
            <div className="space-y-4">
              {/* Rating */}
              <div>
                <label className="block text-gray-700 mb-2 font-medium">
                  Đánh giá của bạn
                </label>
                <div className="flex items-center">
                  {renderStars()}
                  <span className="ml-2 text-amber-500 font-medium">
                    {rating}/5
                  </span>
                </div>
              </div>

              {/* Review Text */}
              <div>
                <label className="block text-gray-700 mb-2 font-medium">
                  Chia sẻ trải nghiệm của bạn
                </label>
                <textarea
                  value={reviewText}
                  onChange={(e) => setReviewText(e.target.value)}
                  placeholder="Chia sẻ trải nghiệm của bạn về sản phẩm này..."
                  className="w-full border border-gray-300 rounded-lg p-3 focus:outline-none focus:ring-2 focus:ring-amber-500 resize-none"
                  rows={4}
                ></textarea>
              </div>

              {/* Image Upload */}
              <div>
                <label className="block text-gray-700 mb-2 font-medium">
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
                    className="cursor-pointer inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors"
                  >
                    <FiCamera className="mr-2" />
                    Chọn ảnh
                  </label>
                  <span className="ml-2 text-gray-500 text-sm">
                    {reviewImages.length}/5 ảnh đã chọn
                  </span>
                </div>

                {/* Image Previews */}
                {reviewImages.length > 0 && (
                  <div className="flex flex-wrap gap-3 mt-3">
                    {reviewImages.map((file, index) => (
                      <div key={index} className="relative group">
                        <div className="w-20 h-20 rounded-lg overflow-hidden border border-gray-300">
                          <img
                            src={URL.createObjectURL(file)}
                            alt={`Preview ${index}`}
                            className="w-full h-full object-cover"
                          />
                        </div>
                        <button
                          type="button"
                          onClick={() => removeImage(index)}
                          className="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs hover:bg-red-600 shadow-md transition-colors"
                        >
                          &times;
                        </button>
                      </div>
                    ))}
                  </div>
                )}
              </div>

              <div className="flex justify-end space-x-3 mt-4">
                <button
                  type="button"
                  onClick={() => setShowModal(false)}
                  className="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-100"
                >
                  Hủy
                </button>
                <button
                  type="submit"
                  className="px-4 py-2 bg-amber-500 text-white rounded-md hover:bg-amber-600 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50"
                  disabled={isSubmitting}
                >
                  {isSubmitting ? (
                    <div className="flex items-center justify-center">
                      <FiLoader className="animate-spin mr-2" />
                      Đang gửi...
                    </div>
                  ) : (
                    "Gửi đánh giá"
                  )}
                </button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  );
});

// Thêm component Modal hủy đơn hàng
const CancelOrderModal = memo(({
  showModal,
  setShowModal,
  loading,
  selectedReason,
  customReason,
  setCustomReason,
  showCustomInput,
  handleReasonSelect,
  submitCancelOrder,
}) => {
  const reasonOptions = [
    "Tôi muốn thay đổi địa chỉ giao hàng",
    "Tôi muốn nhập/thay đổi mã Voucher",
    "Tôi muốn thay đổi sản phẩm trong đơn hàng (size, màu sắc, số lượng,...)",
    "Thủ tục thanh toán quá rắc rối",
    "Tìm thấy chỗ mua khác (rẻ hơn, uy tín hơn, giao nhanh hơn,...)",
    "Tôi không có nhu cầu mua nữa",
    "Tôi không tìm thấy lý do hủy phù hợp",
    "Khác",
  ];

  if (!showModal) return null;

  return (
    <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div className="bg-white rounded-lg shadow-xl w-full max-w-md overflow-hidden transform transition-all">
        <div className="bg-orange-500 text-white px-6 py-4 flex justify-between items-center">
          <h3 className="font-medium text-lg">Chọn Lý Do Hủy</h3>
          <button
            onClick={() => setShowModal(false)}
            className="text-white hover:text-gray-200"
          >
            <FiX className="h-5 w-5" />
          </button>
        </div>
        <div className="p-6">
          <div className="mb-4">
            <div className="flex items-center text-amber-500 bg-amber-50 p-3 rounded-md mb-4">
              <FiInfo className="mr-2 flex-shrink-0" />
              <p className="text-sm">Vui lòng chọn lý do hủy. Với lý do này, bạn sẽ hủy tất cả sản phẩm trong đơn hàng và không thể thay đổi sau đó.</p>
            </div>
            <div className="space-y-2 max-h-60 overflow-y-auto border border-gray-300 rounded-md p-2">
              {reasonOptions.map((reason, index) => (
                <div
                  key={index}
                  className={`flex items-center p-2 rounded-md cursor-pointer ${selectedReason === reason
                    ? "bg-orange-100 border border-orange-500"
                    : "hover:bg-gray-100"
                    }`}
                  onClick={() => handleReasonSelect(reason)}
                >
                  <div className="h-4 w-4 rounded-full border border-gray-400 flex items-center justify-center mr-2">
                    {selectedReason === reason && (
                      <div className="h-2 w-2 rounded-full bg-orange-500"></div>
                    )}
                  </div>
                  <span className="text-sm">{reason}</span>
                </div>
              ))}
            </div>
          </div>

          {showCustomInput && (
            <div className="mb-4">
              <label className="block text-gray-700 mb-2">
                Nhập lý do khác:
              </label>
              <textarea
                className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                rows="3"
                placeholder="Vui lòng nhập lý do của bạn..."
                value={customReason}
                onChange={(e) => setCustomReason(e.target.value)}
              ></textarea>
            </div>
          )}

          <div className="flex justify-end space-x-3 mt-4">
            <button
              onClick={() => setShowModal(false)}
              className="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-100"
            >
              Hủy
            </button>
            <button
              onClick={submitCancelOrder}
              className="px-4 py-2 bg-orange-500 text-white rounded-md hover:bg-orange-600 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-opacity-50"
              disabled={loading || !selectedReason || (showCustomInput && !customReason)}
            >
              {loading ? (
                <div className="flex items-center justify-center">
                  <FiLoader className="animate-spin mr-2" />
                  Đang xử lý...
                </div>
              ) : (
                "Đồng ý"
              )}
            </button>
          </div>
        </div>
      </div>
    </div>
  );
});

// Component hiển thị đánh giá
const ReviewsList = ({ orderId }) => {
  const [reviews, setReviews] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const fetchReviews = async () => {
      try {
        setLoading(true);
        // Sử dụng axiosInstance thay vì axios để có sẵn xử lý token
        const response = await axiosInstance.get(`/orders/${orderId}/reviews`);

        if (response.data.status === "success") {
          setReviews(response.data.data);
        } else {
          console.error("API trả về lỗi:", response.data);
          toast.error(response.data.message || "Không thể tải đánh giá");
        }
      } catch (error) {
        console.error("Error fetching reviews:", error);
        // Hiển thị lỗi cho người dùng
        if (error.response?.status === 401) {
          toast.error("Vui lòng đăng nhập lại để xem đánh giá");
        } else {
          toast.error("Không thể tải đánh giá. Vui lòng thử lại sau.");
        }
      } finally {
        setLoading(false);
      }
    };

    fetchReviews();
  }, [orderId]);

  // Hàm hiển thị thông tin biến thể
  const renderVariantDetails = (variantDetails) => {
    if (!variantDetails || Object.keys(variantDetails).length === 0) return null;

    // Xử lý trường hợp variant_details là mảng object
    if (Array.isArray(variantDetails)) {
      return (
        <div className="text-sm text-gray-600 mt-1">
          {variantDetails.map((variant, index) => (
            <span key={index}>
              {variant.attribute_name || variant.name}: {variant.attribute_value || variant.value}
              {index < variantDetails.length - 1 ? ', ' : ''}
            </span>
          ))}
        </div>
      );
    }

    // Xử lý trường hợp variant_details là object
    return (
      <div className="text-sm text-gray-600 mt-1">
        {Object.entries(variantDetails).map(([key, value], index, arr) => (
          <span key={key}>
            {key}: {value}
            {index < arr.length - 1 ? ', ' : ''}
          </span>
        ))}
      </div>
    );
  };

  if (loading) {
    return <div className="text-center py-4">Đang tải đánh giá...</div>;
  }

  if (!reviews || reviews.length === 0) {
    return <div className="text-center py-4">Bạn chưa đánh giá sản phẩm nào cho đơn hàng này.</div>;
  }

  return (
    <div className="mt-6">
      <h3 className="text-lg font-semibold mb-4">Đánh giá của bạn</h3>
      <div className="space-y-4">
        {reviews.map((review) => (
          <div key={review.id} className="border rounded-lg p-4 bg-white shadow-sm">
            <div className="flex items-start">
              {review.product_image && (
                <img
                  src={`${import.meta.env.VITE_API_BASE_URL}/storage/${review.product_image}`}
                  alt={review.product_name}
                  className="w-16 h-16 object-cover rounded mr-4"
                  onError={(e) => {
                    e.target.src = "https://via.placeholder.com/64x64?text=No+Image";
                  }}
                />
              )}
              <div className="flex-1">
                <h4 className="font-medium">{review.product_name}</h4>
                {review.has_variant && renderVariantDetails(review.variant_details)}
                <div className="flex items-center mt-2">
                  {[1, 2, 3, 4, 5].map((star) => (
                    <span key={star}>
                      {star <= review.rating ? (
                        <RiStarFill className="text-yellow-400" />
                      ) : (
                        <RiStarLine className="text-gray-400" />
                      )}
                    </span>
                  ))}
                </div>
                <p className="mt-2">{review.review_text}</p>
                {review.images && review.images.length > 0 && (
                  <div className="mt-3 flex flex-wrap gap-2">
                    {review.images.map((image, index) => (
                      <img
                        key={index}
                        src={`${import.meta.env.VITE_API_BASE_URL}/storage/${image}`}
                        alt={`Review image ${index + 1}`}
                        className="w-16 h-16 object-cover rounded"
                        onError={(e) => {
                          e.target.src = "https://via.placeholder.com/64x64?text=No+Image";
                        }}
                      />
                    ))}
                  </div>
                )}
                <div className="text-sm text-gray-500 mt-2">
                  {new Date(review.created_at).toLocaleDateString('vi-VN')}
                </div>
              </div>
            </div>
          </div>
        ))}
      </div>
    </div>
  );
};

const OrderDetail = () => {
  const { id } = useParams();
  const navigate = useNavigate();
  const [order, setOrder] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [autoConfirmTimerSet, setAutoConfirmTimerSet] = useState(false);
  const autoConfirmTimerIdRef = useRef(null); // Ref để lưu ID của timer
  const [autoCancelTimerSet, setAutoCancelTimerSet] = useState(false);
  const autoCancelTimerIdRef = useRef(null);
  const [showRefundModal, setShowRefundModal] = useState(false);
  const [refundReason, setRefundReason] = useState("");
  const [selectedReasonOption, setSelectedReasonOption] = useState(null);
  const [customReason, setCustomReason] = useState("");
  const [showCustomReasonInput, setShowCustomReasonInput] = useState(false);

  // Thêm state để quản lý modal đánh giá
  const [showReviewModal, setShowReviewModal] = useState(false);
  const [selectedProduct, setSelectedProduct] = useState(null);
  const [reviewedProducts, setReviewedProducts] = useState([]);
  const [showReviewsSection, setShowReviewsSection] = useState(false);

  // Thêm state cho modal hủy đơn hàng
  const [showCancelModal, setShowCancelModal] = useState(false);
  const [selectedCancelReason, setSelectedCancelReason] = useState(null);
  const [cancelCustomReason, setCancelCustomReason] = useState("");
  const [showCancelCustomInput, setShowCancelCustomInput] = useState(false);

  // --- Logic Polling ---
  const fetchOrderDetailCallback = useCallback(
    async (isPolling = false) => {
      // Không hiển thị loading toàn trang khi polling
      // if (!isPolling) setLoading(true);

      try {
        const token = localStorage.getItem("authToken");
        const response = await axiosInstance.get(`/orders/${id}`, {
          headers: {
            Authorization: `Bearer ${token}`,
          },
          withCredentials: true,
        });

        if (response.data.status === "success") {
          setOrder(response.data.data);
          if (!isPolling) setError(null); // Chỉ xóa lỗi chính khi tải lần đầu thành công
        } else {
          // Xử lý trường hợp API trả về status không phải success
          console.warn(
            "API không trả về success khi lấy chi tiết đơn hàng:",
            response.data
          );
          if (!isPolling) setError("Không thể tải thông tin đơn hàng.");
        }
      } catch (error) {
        console.error(
          "Lỗi khi lấy chi tiết đơn hàng (polling: " + isPolling + "):",
          error
        );
        // Chỉ hiển thị lỗi toàn trang khi tải lần đầu thất bại
        if (!isPolling) {
          setError("Không thể tải thông tin đơn hàng. Vui lòng thử lại sau.");
        }
      } finally {
        // Không tắt loading toàn trang khi polling
        // if (!isPolling) setLoading(false);
      }
    },
    [id]
  );

  // useEffect cho lần tải đầu tiên
  useEffect(() => {
    setLoading(true); // Bật loading khi tải lần đầu
    fetchOrderDetailCallback(false).finally(() => setLoading(false)); // Tải lần đầu và tắt loading sau khi xong
  }, [fetchOrderDetailCallback]);

  // useEffect cho việc polling
  useEffect(() => {
    // Điều kiện dừng polling
    const finalOrderStates = ["Đã Nhận", "Hủy Đơn"];
    const finalRefundStates = [
      "completed",
      "rejected",
      "đã hoàn tiền",
      "từ chối",
    ]; // lowercase
    const latestRefundStatus =
      order?.refund_request?.[0]?.status?.toLowerCase();
    const isOrderInFinalState =
      finalOrderStates.includes(order?.order_status) ||
      (latestRefundStatus && finalRefundStates.includes(latestRefundStatus));

    if (!order || isOrderInFinalState) {
      console.log("Polling stopped: Order data missing or in final state.");
      return; // Dừng nếu không có order hoặc đã ở trạng thái cuối
    }

    console.log("Starting polling for order details...");
    const intervalId = setInterval(() => {
      console.log("Polling...");
      fetchOrderDetailCallback(true); // Gọi với isPolling = true
    }, 5000); // 5 giây

    // Hàm dọn dẹp khi component unmount hoặc dependencies thay đổi
    return () => {
      console.log("Clearing polling interval.");
      clearInterval(intervalId);
    };
    // Chạy lại effect này nếu order thay đổi (để kiểm tra lại điều kiện dừng)
  }, [order, fetchOrderDetailCallback]);

  // --- Tự động xác nhận sau 30 phút --- START ---
  // Hàm thực hiện gọi API xác nhận
  const performOrderConfirmation = useCallback(
    async (isAutoConfirm = false) => {
      // setLoading(true); // Cân nhắc hiển thị loading

      try {
        const token = localStorage.getItem("authToken");
        // Lấy trạng thái mới nhất trước khi xác nhận (đề phòng race condition)
        const latestOrderResponse = await axiosInstance.get(`/orders/${id}`, {
          headers: { Authorization: `Bearer ${token}` },
        });

        if (
          latestOrderResponse.data.status !== "success" ||
          latestOrderResponse.data.data.order_status !== "Đã Giao"
        ) {
          console.log(
            "Auto-confirmation skipped, status is no longer 'Đã Giao' or failed to fetch latest status."
          );
          // setLoading(false);
          return; // Không xác nhận nếu trạng thái không còn là Đã Giao
        }

        // Chỉ hỏi nếu là xác nhận thủ công
        if (
          !isAutoConfirm &&
          !(await Swal.fire({
            title: 'Xác nhận đã nhận hàng',
            text: 'Bạn đã nhận được hàng và muốn xác nhận?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Xác nhận',
            cancelButtonText: 'Hủy'
          })).isConfirmed
        ) {
          // setLoading(false);
          return;
        }

        const response = await axiosInstance.post(
          `/orders/${id}/confirm`,
          {},
          {
            headers: {
              Authorization: `Bearer ${token}`,
            },
          }
        );

        if (response.data.status === "success") {
          if (!isAutoConfirm) {
            // Thay thế alert bằng toast tùy chỉnh
            toast.custom(
              (t) => (
                <div className="bg-white shadow-lg rounded-lg overflow-hidden pointer-events-auto border-l-4 border-green-600">
                  <div className="p-4">
                    <div className="flex items-start">
                      <div className="flex-shrink-0 pt-0.5">
                        <div className="h-10 w-10 rounded-full bg-green-100 text-green-600 flex items-center justify-center">
                          <FiCheckCircle className="h-6 w-6" />
                        </div>
                      </div>
                      <div className="ml-3 flex-1">
                        <div className="flex justify-between items-start">
                          <p className="text-sm font-medium text-gray-900">
                            Xác nhận thành công!
                          </p>
                          <button
                            onClick={() => toast.dismiss(t.id)}
                            className="bg-white rounded-md inline-flex text-gray-400 hover:text-gray-500 focus:outline-none"
                          >
                            <span className="sr-only">Đóng</span>
                            <FiX className="h-5 w-5" />
                          </button>
                        </div>
                        <p className="mt-1 text-sm text-gray-500">
                          Đơn hàng #{order.order_code} đã được xác nhận đã nhận hàng.
                        </p>
                      </div>
                    </div>
                  </div>
                </div>
              ),
              {
                duration: 5000
              }
            );

            // Gửi thông báo realtime đến BE thông qua Socket Server
            try {
              const socketServerUrl = import.meta.env.VITE_SOCKET_SERVER_URL || "http://localhost:3002";
              const userData = JSON.parse(localStorage.getItem("userData") || "{}");

              // Chuẩn bị dữ liệu thông báo
              const notificationData = {
                event: 'order_confirmation',
                data: {
                  order_id: order.id,
                  order_code: order.order_code,
                  user_id: userData.id,
                  user_name: order.user_name,
                  total_price: order.total_price,
                  created_at: new Date().toISOString(),
                  message: `Đơn hàng #${order.order_code} đã được xác nhận đã nhận hàng.`
                }
              };

              // Gửi thông báo đến Socket Server
              axios.post(`${socketServerUrl}/broadcast-admin`, {
                event: 'order_confirmation_notification',
                data: notificationData
              });

              console.log('Đã gửi thông báo xác nhận đơn hàng đến admin');

              // Phát sự kiện để các component khác có thể biết về việc xác nhận đơn hàng
              const confirmEvent = new CustomEvent('order-confirmed', {
                detail: {
                  orderId: id,
                  orderCode: order.order_code,
                  userId: order.user_id,
                  userName: order.user_name,
                  totalPrice: order.total_price
                }
              });
              window.dispatchEvent(confirmEvent);

            } catch (e) {
              console.error('Không thể gửi thông báo realtime:', e);
              // Không ảnh hưởng đến luồng chính nếu gửi thông báo lỗi
            }
          } else {
            console.log("Đơn hàng tự động xác nhận sau 30 phút.");
            // Có thể thêm thông báo nhẹ nhàng hơn alert
          }
          // Cập nhật lại thông tin đơn hàng cục bộ
          setOrder(prevOrder => ({
            ...prevOrder,
            ...response.data.data,
            // Đảm bảo giữ nguyên payment_method nếu API không trả về
            payment_method: response.data.data.payment_method || prevOrder.payment_method
          }));
        } else {
          // Xử lý lỗi từ API confirm
          if (!isAutoConfirm) {
            toast.error(response.data.message || "Không thể xác nhận đơn hàng.");
          } else {
            console.error("Lỗi tự động xác nhận:", response.data.message);
          }
        }
      } catch (error) {
        console.error(
          "Lỗi khi xác nhận đơn hàng:",
          error.response?.data || error.message
        );
        if (!isAutoConfirm) {
          toast.error("Không thể xác nhận đơn hàng. Vui lòng thử lại sau.");
        } // Lỗi tự động thì log
      } finally {
        // setLoading(false);
      }
    },
    [id, order]
  ); // Thêm order vào dependencies

  // --- Tự động xác nhận sau 24 giờ --- START ---
  // useEffect để thiết lập timer tự động xác nhận
  useEffect(() => {
    // Hàm cleanup để xóa timer cũ
    const clearExistingTimer = () => {
      if (autoConfirmTimerIdRef.current) {
        clearTimeout(autoConfirmTimerIdRef.current);
        autoConfirmTimerIdRef.current = null;
        console.log("Cleared previous auto-confirm timer.");
      }
    };

    if (order && order.order_status === "Đã Giao" && !autoConfirmTimerSet) {
      // console.log("Order is 'Đã Giao'. Checking for auto-confirmation.");
      // Giả định updated_at là thởi điểm chuyển sang "Đã Giao"
      const deliveredTime = new Date(order.updated_at).getTime();
      const currentTime = new Date().getTime();
      const timeSinceDelivered = currentTime - deliveredTime;
      const autoConfirmDelayMs = 24 * 60 * 60 * 1000; // 24 giờ

      if (timeSinceDelivered >= autoConfirmDelayMs) {
        console.log(
          "Order delivered more than 30 mins ago. Triggering auto-confirm."
        );
        // Dùng setTimeout 0 để đẩy ra khỏi luồng render hiện tại
        clearExistingTimer(); // Xóa timer cũ nếu có
        // Không cần set timer, gọi trực tiếp (hoặc qua setTimeout 0)
        performOrderConfirmation(true); // Gọi xác nhận tự động
        setAutoConfirmTimerSet(true); // Đánh dấu đã xử lý
      } else {
        const remainingDelay = autoConfirmDelayMs - timeSinceDelivered;
        console.log(
          `Setting auto-confirm timer for ${Math.round(
            remainingDelay / 1000
          )} seconds.`
        );
        clearExistingTimer(); // Xóa timer cũ trước khi set timer mới
        autoConfirmTimerIdRef.current = setTimeout(() => {
          performOrderConfirmation(true); // Gọi xác nhận tự động
        }, remainingDelay);
        setAutoConfirmTimerSet(true); // Đánh dấu đã set timer
      }
    } else if (order && order.order_status !== "Đã Giao") {
      // Nếu trạng thái không còn là Đã Giao, xóa timer và reset cờ
      clearExistingTimer();
      if (autoConfirmTimerSet) {
        setAutoConfirmTimerSet(false);
      }
    }

    // Hàm cleanup chính của useEffect
    return () => {
      clearExistingTimer();
    };
    // Thêm performOrderConfirmation vào dependencies
  }, [order, autoConfirmTimerSet, performOrderConfirmation]);
  // --- Tự động xác nhận sau 30 phút --- END ---

  // --- Tự động hủy đơn hàng chưa thanh toán online sau 30 phút --- START ---
  // Hàm xử lý hủy tự động
  const handleAutoCancelOrder = useCallback(async () => {
    console.log("Attempting auto-cancel for order:", id);
    try {
      const token = localStorage.getItem("authToken");
      // Kiểm tra trạng thái mới nhất trước khi hủy
      const latestOrderResponse = await axiosInstance.get(`/orders/${id}`, {
        headers: { Authorization: `Bearer ${token}` },
      });

      if (latestOrderResponse.data.status === "success") {
        const latestOrder = latestOrderResponse.data.data;
        const isOnlinePayment =
          latestOrder.payment_method.name === "MoMo" ||
          latestOrder.payment_method.name === "VNPAY";
        const isUnpaidOrPending =
          latestOrder.payment_status === 0 || latestOrder.payment_status === 2;
        const isCancelableStatus =
          latestOrder.order_status !== "Hủy Đơn" &&
          latestOrder.order_status !== "Đã Nhận";

        if (isOnlinePayment && isUnpaidOrPending && isCancelableStatus) {
          console.log("Conditions met for auto-cancel. Proceeding...");
          const response = await axiosInstance.post(
            `/orders/${id}/cancel`,
            {}, // Backend không yêu cầu reason
            {
              headers: { Authorization: `Bearer ${token}` },
            }
          );

          if (response.data.status === "success") {
            console.log(
              "Đơn hàng đã được tự động hủy do quá 30 phút chưa thanh toán online"
            );
            // Cập nhật trạng thái local
            setOrder((prevOrder) => ({
              ...prevOrder,
              order_status: "Hủy Đơn",
            }));
            setAutoCancelTimerSet(true); // Đánh dấu đã hủy để không set timer lại
          } else {
            console.error(
              "Lỗi API khi tự động hủy đơn hàng:",
              response.data.message
            );
          }
        } else {
          console.log(
            "Auto-cancel skipped: Order conditions no longer met after checking latest status.",
            latestOrder
          );
        }
      } else {
        console.log(
          "Auto-cancel skipped: Failed to fetch latest order status."
        );
      }
    } catch (error) {
      console.error(
        "Lỗi khi tự động hủy đơn hàng:",
        error.response?.data || error.message
      );
    }
  }, [id, setOrder]); // Thêm setOrder vào dependencies

  // useEffect cho tự động hủy
  useEffect(() => {
    // Hàm cleanup để xóa timer
    const clearCancelTimer = () => {
      if (autoCancelTimerIdRef.current) {
        clearTimeout(autoCancelTimerIdRef.current);
        autoCancelTimerIdRef.current = null;
        console.log("Cleared auto-cancel timer.");
      }
    };

    // Điều kiện kiểm tra để tự động hủy
    const shouldCheckAutoCancel =
      order &&
      (order.payment_method?.name === "MoMo" ||
        order.payment_method?.name === "VNPAY") && // Thanh toán online
      (order.payment_status === 0 || order.payment_status === 2) && // Chưa thanh toán hoặc đang chờ
      order.order_status !== "Hủy Đơn" && // Chưa bị hủy
      order.order_status !== "Đã Nhận"; // Chưa nhận

    if (shouldCheckAutoCancel) {
      const orderCreatedTime = new Date(order.created_at).getTime();
      const currentTime = new Date().getTime();
      const timeSinceCreated = currentTime - orderCreatedTime;
      const autoCancelDelayMs = 24 * 60 * 60 * 1000; // 24 giờ

      if (timeSinceCreated >= autoCancelDelayMs) {
        console.log(
          "Order unpaid online for over 30 minutes. Triggering auto-cancel immediately."
        );
        clearCancelTimer(); // Xóa timer cũ nếu có
        if (!autoCancelTimerSet) {
          // Chỉ hủy nếu chưa bị hủy hoặc đang trong quá trình set timer
          handleAutoCancelOrder(); // Gọi hủy ngay
        }
      } else if (!autoCancelTimerSet) {
        // Nếu chưa đủ 30 phút và chưa set timer/chưa bị hủy
        const remainingDelay = autoCancelDelayMs - timeSinceCreated;
        console.log(
          `Setting auto-cancel timer for ${Math.round(
            remainingDelay / 1000
          )} seconds.`
        );
        clearCancelTimer(); // Xóa timer cũ trước khi set mới
        autoCancelTimerIdRef.current = setTimeout(() => {
          console.log(
            "Auto-cancel timer expired. Calling handleAutoCancelOrder."
          );
          handleAutoCancelOrder();
        }, remainingDelay);
        // Không set autoCancelTimerSet = true ở đây vì chỉ set khi timer thực sự chạy và hủy
        // Nếu không, reload trang trước khi timer chạy sẽ không set lại timer
      }
    } else {
      // Nếu điều kiện không còn đúng (đã thanh toán, đã hủy, đã nhận, hoặc là COD), xóa timer
      clearCancelTimer();
      // Reset cờ nếu cần (ví dụ nếu trạng thái thay đổi trước khi timer chạy)
      if (autoCancelTimerSet) {
        setAutoCancelTimerSet(false);
      }
    }

    // Hàm cleanup chính của useEffect
    return () => {
      clearCancelTimer();
    };
    // Thêm handleAutoCancelOrder và autoCancelTimerSet vào dependencies
  }, [order, autoCancelTimerSet, handleAutoCancelOrder]);
  // --- Tự động hủy đơn hàng chưa thanh toán online sau 30 phút --- END ---

  // Chuyển đổi mã trạng thái thanh toán thành text và màu sắc
  const getPaymentStatusInfo = (statusCode) => {
    switch (statusCode) {
      case 0:
        return {
          text: "Chưa thanh toán",
          color: "text-yellow-600",
          bgColor: "bg-yellow-100",
          icon: <FiClock className="w-5 h-5" />,
        };
      case 1:
        return {
          text: "Đã thanh toán",
          color: "text-green-600",
          bgColor: "bg-green-100",
          icon: <FiCheckCircle className="w-5 h-5" />,
        };
      case 2:
        return {
          text: "Đang chờ thanh toán",
          color: "text-blue-600",
          bgColor: "bg-blue-100",
          icon: <FiRefreshCw className="w-5 h-5" />,
        };
      default:
        return {
          text: "Không xác định",
          color: "text-gray-600",
          bgColor: "bg-gray-100",
          icon: <FiAlertCircle className="w-5 h-5" />,
        };
    }
  };

  // Lấy thông tin về trạng thái đơn hàng
  const getOrderStatusInfo = (status) => {
    switch (status) {
      case "Đang Xử Lý":
        return {
          color: "text-blue-600",
          bgColor: "bg-blue-100",
          icon: <FiRefreshCw className="w-5 h-5" />,
        };
      case "Chưa Xác Nhận":
        return {
          color: "text-yellow-600",
          bgColor: "bg-yellow-100",
          icon: <FiClock className="w-5 h-5" />,
        };
      case "Đã Xác Nhận":
        return {
          color: "text-purple-600",
          bgColor: "bg-purple-100",
          icon: <FiCheckCircle className="w-5 h-5" />,
        };
      case "Đang Chuẩn Bị Hàng":
        return {
          color: "text-blue-600",
          bgColor: "bg-blue-100",
          icon: <FiPackage className="w-5 h-5" />,
        };
      case "Đang Giao":
        return {
          color: "text-amber-600",
          bgColor: "bg-amber-100",
          icon: <FiTruck className="w-5 h-5" />,
        };
      case "Đã Giao":
        return {
          color: "text-green-600",
          bgColor: "bg-green-100",
          icon: <FiTruck className="w-5 h-5" />,
        };
      case "Đã Nhận":
        return {
          color: "text-green-600",
          bgColor: "bg-green-100",
          icon: <FiCheckCircle className="w-5 h-5" />,
        };
      case "Hủy Đơn":
        return {
          color: "text-red-600",
          bgColor: "bg-red-100",
          icon: <FiXCircle className="w-5 h-5" />,
        };
      case "Hoàn Hàng":
        return {
          color: "text-orange-600",
          bgColor: "bg-orange-100",
          icon: <FiPackage className="w-5 h-5" />,
        };
      default:
        return {
          color: "text-gray-600",
          bgColor: "bg-gray-100",
          icon: <FiInfo className="w-5 h-5" />,
        };
    }
  };

  // Hiển thị các thuộc tính của biến thể sản phẩm
  const renderVariantDetails = (variant) => {
    if (
      !variant ||
      !variant.variant_details ||
      variant.variant_details.length === 0
    ) {
      return null;
    }

    return variant.variant_details.map((detail, index) => (
      <div
        key={index}
        className="text-sm text-gray-600 inline-flex items-center mr-3"
      >
        <span className="font-medium mr-1">{detail.name}:</span> {detail.value}
      </div>
    ));
  };

  // Xử lý chọn lý do hủy đơn hàng
  const handleCancelReasonSelect = (reason) => {
    setSelectedCancelReason(reason);
    
    // Hiển thị ô input nếu chọn "Khác"
    if (reason === "Khác") {
      setShowCancelCustomInput(true);
    } else {
      setShowCancelCustomInput(false);
      setCancelCustomReason("");
    }
  };

  // Xử lý hủy đơn hàng
  const handleCancelOrder = () => {
    setShowCancelModal(true);
  };

  // Xử lý gửi yêu cầu hủy đơn hàng
  const submitCancelOrder = async () => {
    if (!selectedCancelReason) {
      toast.error("Vui lòng chọn lý do hủy đơn hàng");
      return;
    }

    // Kiểm tra nếu chọn "Khác" thì phải nhập lý do
    if (selectedCancelReason === "Khác" && !cancelCustomReason) {
      toast.error("Vui lòng nhập lý do hủy đơn hàng");
      return;
    }

    try {
      setLoading(true);
      const token = localStorage.getItem("authToken");
      
      // Xác định lý do gửi lên server
      const reasonToSubmit = selectedCancelReason === "Khác" 
        ? cancelCustomReason 
        : selectedCancelReason;
      
      // Tạo FormData để gửi dữ liệu
      const formData = new FormData();
      formData.append('reason', reasonToSubmit); 
      
      const response = await axiosInstance.post(
        `/orders/${id}/cancel`,
        formData,
        {
          headers: {
            Authorization: `Bearer ${token}`,
            'Content-Type': 'multipart/form-data',
          },
        }
      );

      if (response.data.status === "success") {
        // Đóng modal
        setShowCancelModal(false);
        
        // Thay thế alert bằng toast.custom
        toast.custom(
          (t) => (
            <div className="bg-white shadow-lg rounded-lg overflow-hidden pointer-events-auto border-l-4 border-red-600">
              <div className="p-4">
                <div className="flex items-start">
                  <div className="flex-shrink-0 pt-0.5">
                    <div className="h-10 w-10 rounded-full bg-red-100 text-red-600 flex items-center justify-center">
                      <FiXCircle className="h-6 w-6" />
                    </div>
                  </div>
                  <div className="ml-3 flex-1">
                    <p className="text-sm font-medium text-gray-900">
                      Hủy đơn hàng thành công!
                    </p>
                    <p className="mt-1 text-sm text-gray-500">
                      Đơn hàng #{order.order_code} đã được hủy.
                    </p>
                    {order.payment_status === 1 && (
                      <p className="mt-1 text-sm text-gray-500">
                        Số tiền sẽ được hoàn về ví của bạn.
                      </p>
                    )}
                  </div>
                </div>
              </div>
            </div>
          ),
          {
            duration: 5000
          }
        );

        // Cập nhật trạng thái đơn hàng
        setOrder((prevOrder) => ({
          ...prevOrder,
          order_status: "Hủy Đơn",
          reason: reasonToSubmit
        }));
      } else {
        toast.error(response.data.message || "Không thể hủy đơn hàng.");
      }
    } catch (error) {
      console.error("Lỗi khi hủy đơn hàng:", error.response?.data || error.message);
      toast.error(error.response?.data?.message || "Không thể hủy đơn hàng. Vui lòng thử lại sau.");
    } finally {
      setLoading(false);
    }
  };

  // Xử lý xác nhận đã nhận hàng (thủ công)
  const handleConfirmOrder = async () => {
    await performOrderConfirmation(false); // false = xác nhận thủ công
  };

  // Xử lý yêu cầu hoàn hàng
  const handleRequestRefund = useCallback(async () => {
    setShowRefundModal(true);
    setSelectedReasonOption(null);
    setCustomReason("");
    setShowCustomReasonInput(false);
  }, []);

  const handleReasonSelect = useCallback((reason) => {
    if (reason === "Khác") {
      setShowCustomReasonInput(true);
      setSelectedReasonOption(reason);
      setRefundReason("");
    } else {
      setShowCustomReasonInput(false);
      setSelectedReasonOption(reason);
      setRefundReason(reason);
    }
  }, []);

  const submitRefundRequest = useCallback(async () => {
    let finalReason = refundReason;
    if (selectedReasonOption === "Khác") {
      if (!customReason.trim()) {
        toast.error("Vui lòng nhập lý do hoàn hàng");
        return;
      }
      finalReason = customReason;
    } else if (!finalReason) {
      toast.error("Vui lòng chọn lý do hoàn hàng");
      return;
    }

    try {
      setLoading(true);
      const token = localStorage.getItem("authToken");
      const response = await axiosInstance.post(
        `/orders/${id}/request-refund`,
        { reason: finalReason },
        {
          headers: {
            Authorization: `Bearer ${token}`,
          },
        }
      );

      if (response.data.status === "success") {
        setShowRefundModal(false);
        toast.custom(
          (t) => (
            <div className="bg-white shadow-lg rounded-lg overflow-hidden pointer-events-auto border-l-4 border-green-600">
              <div className="p-4">
                <div className="flex items-start">
                  <div className="flex-shrink-0 pt-0.5">
                    <div className="h-10 w-10 rounded-full bg-green-100 text-green-600 flex items-center justify-center">
                      <FiCheckCircle className="h-6 w-6" />
                    </div>
                  </div>
                  <div className="ml-3 flex-1">
                    <div className="flex justify-between items-start">
                      <p className="text-sm font-medium text-gray-900">
                        Thành công!
                      </p>
                      <button
                        onClick={() => toast.dismiss(t.id)}
                        className="bg-white rounded-md inline-flex text-gray-400 hover:text-gray-500 focus:outline-none"
                      >
                        <span className="sr-only">Đóng</span>
                        <FiX className="h-5 w-5" />
                      </button>
                    </div>
                    <p className="mt-1 text-sm text-gray-500">
                      Yêu cầu hoàn hàng của bạn đã được gửi. Vui lòng chờ xét duyệt.
                    </p>
                  </div>
                </div>
              </div>
            </div>
          ),
          { duration: 5000 }
        );
        setOrder(prevOrder => ({
          ...prevOrder,
          refund_request: [
            {
              status: "pending",
              reason: finalReason,
              created_at: new Date().toISOString(),
            },
          ],
        }));
        setRefundReason("");
        setSelectedReasonOption(null);
        setCustomReason("");
        setShowCustomReasonInput(false);
      }
    } catch (error) {
      console.error("Lỗi khi yêu cầu hoàn hàng:", error);
      if (error.response?.data?.message) {
        toast.error(error.response.data.message);
      } else {
        toast.error("Có lỗi xảy ra khi gửi yêu cầu hoàn hàng");
      }
    } finally {
      setLoading(false);
    }
  }, [id, refundReason, selectedReasonOption, customReason, setOrder]);

  // Xử lý thanh toán lại
  const handleRetryPayment = async () => {
    try {
      setLoading(true);
      const token = localStorage.getItem("authToken");
      const response = await axiosInstance.post(
        `/payment-method/retry/${id}`,
        {},
        {
          headers: {
            Authorization: `Bearer ${token}`,
          },
        }
      );

      console.log("Response từ API thanh toán lại:", response);

      // Kiểm tra phản hồi và chuyển hướng
      if (response.data.status === "success" && response.data.payUrl) {
        // Nếu backend trả về payUrl
        window.location.href = response.data.payUrl;
      } else if (response.data.message === "success" && response.data.data) {
        // Nếu backend trả về data (trường hợp VNPAY)
        window.location.href = response.data.data;
      } else if (response.data.payUrl) {
        // Nếu backend chỉ trả về payUrl (trường hợp MoMo có thể)
        window.location.href = response.data.payUrl;
      } else {
        // Hiển thị thông báo lỗi cụ thể hơn nếu có
        alert(
          response.data.message ||
          "Không thể lấy link thanh toán lại. Vui lòng kiểm tra console."
        );
        console.error(
          "API response không chứa URL thanh toán hợp lệ:",
          response.data
        );
      }
    } catch (error) {
      console.error("Lỗi khi thanh toán lại:", error);
      alert("Không thể thanh toán lại. Vui lòng thử lại sau.");
    } finally {
      setLoading(false);
    }
  };

  // Hiển thị tiến trình hoàn tiền
  const renderRefundProgress = (refundStatus) => {
    const refundSteps = [
      { id: 1, name: "Gửi yêu cầu", icon: <FiRefreshCw /> },
      { id: 2, name: "Được chấp nhận", icon: <FiCheckCircle /> },
      { id: 3, name: "Đã hoàn tiền", icon: <FiCheckCircle /> },
    ];

    let currentStep = 1; // Mặc định là bước 1

    // Xác định bước hiện tại dựa trên trạng thái hoàn hàng
    const refundStatusLower = refundStatus?.toLowerCase(); // Chuyển sang chữ thường để không phân biệt hoa/thường

    // Xử lý trường hợp bị từ chối riêng
    if (refundStatusLower === "rejected" || refundStatusLower === "từ chối") {
      return (
        <div className="my-6 bg-red-100 text-red-700 p-4 rounded-lg flex items-center justify-center">
          <FiXCircle className="w-5 h-5 mr-2" />
          <span>Yêu cầu hoàn hàng của bạn đã bị từ chối.</span>
          {/* Optional: Thêm lý do từ chối nếu có */}
          {/* {order.refund_request[0]?.reject_reason && <p className="mt-2 text-sm">Lý do: {order.refund_request[0].reject_reason}</p>} */}
        </div>
      );
    }

    // Xác định bước cho các trạng thái khác
    switch (refundStatusLower) {
      case "pending":
        currentStep = 1;
        break;
      case "đã duyệt":
        // Nếu đã duyệt và đã hoàn tiền (payment_status = 1), chuyển bước cuối
        currentStep = (order.payment_status === 1) ? 3 : 2;
        break;
      case "completed":
      case "refunded":
      case "đã hoàn tiền":
        currentStep = 3;
        break;
      default:
        currentStep = 1; // Mặc định nếu trạng thái không xác định
    }

    return (
      <div className="my-6">
        <div className="relative">
          <div className="overflow-hidden h-2 mb-4 text-xs flex rounded bg-gray-200">
            <motion.div
              initial={{ width: 0 }}
              animate={{
                width: `${Math.min(
                  100,
                  (currentStep / refundSteps.length) * 100
                )}%`,
              }}
              transition={{ duration: 0.8, ease: "easeOut" }}
              className="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-orange-500"
            ></motion.div>
          </div>
          <div className="flex justify-between">
            {refundSteps.map((step, index) => (
              <div
                key={step.id}
                className={`flex flex-col items-center ${currentStep >= step.id ? "text-orange-600" : "text-gray-400"
                  }`}
              >
                <div
                  className={`
                  rounded-full h-8 w-8 flex items-center justify-center border-2 mb-1
                  ${currentStep >= step.id
                      ? "border-orange-500 bg-orange-100"
                      : "border-gray-300"
                    }
                `}
                >
                  {step.icon}
                </div>
                <span className="text-xs font-medium">{step.name}</span>
              </div>
            ))}
          </div>
        </div>
      </div>
    );
  };

  // Hiển thị tiến trình đơn hàng
  const renderOrderProgress = (status) => {
    // Nếu có yêu cầu hoàn tiền (là mảng và không rỗng), hiển thị thanh tiến trình hoàn tiền
    if (
      Array.isArray(order.refund_request) &&
      order.refund_request.length > 0
    ) {
      // Lấy trạng thái từ yêu cầu hoàn tiền đầu tiên (giả định chỉ có 1 yêu cầu active)
      const currentRefundStatus = order.refund_request[0]?.status || "pending";
      return renderRefundProgress(currentRefundStatus);
    }

    const steps = [
      { id: 1, name: "Chưa Xác Nhận", icon: <FiClock /> },
      { id: 2, name: "Đã Xác Nhận", icon: <FiCheckCircle /> },
      { id: 3, name: "Đang Chuẩn Bị Hàng", icon: <FiPackage /> },
      { id: 4, name: "Đang Giao", icon: <FiTruck /> },
      { id: 5, name: "Đã Giao", icon: <FiTruck /> },
      { id: 6, name: "Đã Nhận", icon: <FiCheckCircle /> },
    ];

    let currentStep = 0;

    switch (status) {
      case "Chưa Xác Nhận":
        currentStep = 1;
        break;
      case "Đã Xác Nhận":
        currentStep = 2;
        break;
      case "Đang Chuẩn Bị Hàng":
        currentStep = 3;
        break;
      case "Đang Giao":
        currentStep = 4;
        break;
      case "Đã Giao":
        currentStep = 5;
        break;
      case "Đã Nhận":
        currentStep = 6;
        break;
      case "Đang Xử Lý":
        currentStep = 1;
        break;
      case "Hủy Đơn":
      case "Hoàn Hàng":
        currentStep = -1;
        break;
      default:
        currentStep = 0;
    }

    return (
      <div className="my-6">
        {currentStep === -1 ? (
          <div className="bg-red-100 text-red-700 p-4 rounded-lg flex items-center justify-center">
            <FiAlertCircle className="w-5 h-5 mr-2" />
            <span>
              {status === "Hủy Đơn"
                ? "Đơn hàng đã bị hủy."
                : "Đơn hàng đang trong quá trình hoàn hàng hoặc đã hoàn."}
            </span>
          </div>
        ) : (
          <div className="relative">
            <div className="overflow-hidden h-2 mb-4 text-xs flex rounded bg-gray-200">
              <motion.div
                initial={{ width: 0 }}
                animate={{
                  width: `${Math.min(
                    100,
                    (currentStep / steps.length) * 100
                  )}%`,
                }}
                transition={{ duration: 0.8, ease: "easeOut" }}
                className="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-amber-500"
              ></motion.div>
            </div>
            <div className="flex justify-between">
              {steps.map((step, index) => (
                <div
                  key={step.id}
                  className={`flex flex-col items-center ${currentStep >= step.id ? "text-amber-600" : "text-gray-400"
                    }`}
                >
                  <div
                    className={`
                    rounded-full h-8 w-8 flex items-center justify-center border-2 mb-1
                    ${currentStep >= step.id
                        ? "border-amber-500 bg-amber-100"
                        : "border-gray-300"
                      }
                  `}
                  >
                    {step.icon}
                  </div>
                  <span className="text-xs font-medium">{step.name}</span>
                </div>
              ))}
            </div>
          </div>
        )}
      </div>
    );
  };

  // Format giá tiền thành dạng xxx.xxx đ
  const formatCurrency = (amount) => {
    if (amount == null) return ""; // Handle null or undefined
    let amountStr = amount.toString();
    // Remove .00 if it exists
    if (amountStr.endsWith(".00")) {
      amountStr = amountStr.slice(0, -3);
    }
    return amountStr.replace(/\B(?=(\d{3})+(?!\d))/g, ".") + " đ";
  };

  // Thêm useEffect để lấy danh sách sản phẩm đã đánh giá
  useEffect(() => {
    if (order && order.order_status === "Đã Nhận") {
      fetchReviewedProducts();
    }
  }, [order]);

  // Hàm lấy danh sách sản phẩm đã đánh giá
  const fetchReviewedProducts = async () => {
    try {
      const token = localStorage.getItem("authToken");
      if (!token) return;

      // Lấy danh sách đánh giá của đơn hàng này
      const reviewsResponse = await axiosInstance.get(`/orders/${id}/reviews`);
      
      if (reviewsResponse.data.status === 'success' && reviewsResponse.data.data) {
        // Tạo danh sách sản phẩm đã đánh giá từ response
        const reviewedIds = reviewsResponse.data.data.map(review => review.product_id);
        setReviewedProducts(reviewedIds);
      } else {
        // Phương pháp dự phòng: kiểm tra từng sản phẩm
        const promises = order.order_items.map(item =>
          axiosInstance.get(`/products/${item.product_id}/can-review`).catch(error => {
            // Nếu API trả về lỗi 'đã đánh giá rồi', thêm vào danh sách đã đánh giá
            if (error.response?.data?.message === 'Bạn đã đánh giá sản phẩm này rồi.') {
              return { data: { alreadyReviewed: true, productId: item.product_id } };
            }
            return { data: { success: true } }; // Default is can review
          })
        );

        const results = await Promise.all(promises);
        const reviewed = results
          .filter(response => response.data?.alreadyReviewed)
          .map(response => response.data.productId);

        setReviewedProducts(reviewed);
      }
    } catch (error) {
      console.error("Lỗi khi lấy thông tin đánh giá:", error);
    }
  };

  // Hàm xử lý hiển thị modal đánh giá
  const handleOpenReviewModal = (item) => {
    setSelectedProduct({
      ...item,
      // Thêm thông tin biến thể nếu có
      product_variant: item.product_variant_id ? {
        id: item.product_variant_id, // Lấy id từ product_variant_id
        variant_details: item.product_variant?.variant_details || []
      } : null
    });
    setShowReviewModal(true);
  };

  // Hàm xử lý sau khi đánh giá thành công
  const handleReviewSuccess = (productId) => {
    setReviewedProducts(prev => [...prev, productId]);
  };

  if (loading) {
    return (
      <div className="flex flex-col items-center justify-center min-h-screen p-4">
        <motion.div
          initial={{ rotate: 0 }}
          animate={{ rotate: 360 }}
          transition={{ duration: 1, repeat: Infinity, ease: "linear" }}
          className="rounded-full h-12 w-12 border-t-2 border-b-2 border-amber-500 mb-4"
        ></motion.div>
        <p className="text-gray-600 animate-pulse">
          Đang tải thông tin đơn hàng...
        </p>
      </div>
    );
  }

  if (error || !order) {
    return (
      <motion.div
        initial={{ opacity: 0, y: 20 }}
        animate={{ opacity: 1, y: 0 }}
        transition={{ duration: 0.5 }}
        className="bg-gray-50 min-h-screen py-8 px-4"
      >
        <div className="max-w-4xl mx-auto bg-white rounded-lg shadow-sm p-8 text-center">
          <FiAlertCircle className="h-16 w-16 text-red-500 mx-auto mb-4" />
          <p className="text-red-500 text-lg mb-6">
            {error || "Không tìm thấy đơn hàng"}
          </p>
          <Link
            to="/account/list_order"
            className="inline-flex items-center px-5 py-2.5 bg-amber-500 hover:bg-amber-600 text-white rounded-lg transition duration-200"
          >
            <FiArrowLeft className="mr-2" />
            Quay lại danh sách đơn hàng
          </Link>
        </div>
      </motion.div>
    );
  }

  // Lấy thông tin trạng thái thanh toán và đơn hàng
  const paymentStatusInfo = getPaymentStatusInfo(order.payment_status);

  // Xác định trạng thái hiển thị hiệu lực
  let effectiveOrderStatus = order.order_status;
  let isRefundActive = false;
  let isRefundRejected = false;

  if (Array.isArray(order.refund_request) && order.refund_request.length > 0) {
    const latestRefundStatus = order.refund_request[0]?.status?.toLowerCase();
    if (latestRefundStatus === "rejected" || latestRefundStatus === "từ chối") {
      isRefundRejected = true;
    } else if (latestRefundStatus) {
      // Các trạng thái khác (pending, accepted, completed)
      isRefundActive = true;
    }
  }

  if (isRefundActive && order.order_status !== "Hủy Đơn") {
    effectiveOrderStatus = "Hoàn Hàng";
  } // Nếu isRefundRejected = true, effectiveOrderStatus sẽ giữ nguyên giá trị gốc (ví dụ: "Đã Giao")

  const orderStatusInfo = getOrderStatusInfo(effectiveOrderStatus);

  return (
    <motion.div
      initial={{ opacity: 0 }}
      animate={{ opacity: 1 }}
      transition={{ duration: 0.5 }}
      className="bg-gray-50 min-h-screen py-8 px-4"
    >
      <div className="max-w-5xl mx-auto">
        <div className="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between">
          <Link
            to="/account/list_order"
            className="inline-flex items-center text-amber-500 hover:text-amber-600 transition-colors mb-4 sm:mb-0"
          >
            <FiArrowLeft className="mr-2" />
            <span>Quay lại danh sách đơn hàng</span>
          </Link>

          <div className="flex items-center space-x-2">
            <FiHome className="text-gray-500" />
            <span className="text-gray-500">/</span>
            <Link to="/account" className="text-gray-500 hover:text-amber-500">
              Tài khoản
            </Link>
            <span className="text-gray-500">/</span>
            <Link
              to="/account/list_order"
              className="text-gray-500 hover:text-amber-500"
            >
              Đơn hàng
            </Link>
            <span className="text-gray-500">/</span>
            <span className="text-gray-700">Chi tiết</span>
          </div>
        </div>

        {/* Header Card */}
        <motion.div
          initial={{ y: 20, opacity: 0 }}
          animate={{ y: 0, opacity: 1 }}
          transition={{ delay: 0.1 }}
          className="bg-white rounded-lg shadow-sm p-6 mb-6"
        >
          <div className="flex flex-col lg:flex-row justify-between items-start lg:items-center mb-4">
            <div>
              <h1 className="text-2xl font-bold text-gray-800 mb-2">
                Đơn hàng #{order.order_code}
              </h1>
              <div className="flex items-center text-gray-600">
                <FiCalendar className="mr-2" />
                <span>
                  Ngày đặt:{" "}
                  {new Date(order.created_at).toLocaleDateString("vi-VN", {
                    day: "2-digit",
                    month: "2-digit",
                    year: "numeric",
                    hour: "2-digit",
                    minute: "2-digit",
                  })}
                </span>
              </div>
            </div>
            <div
              className={`mt-4 lg:mt-0 px-4 py-2 rounded-full ${orderStatusInfo.bgColor} ${orderStatusInfo.color} flex items-center`}
            >
              {orderStatusInfo.icon}
              <span className="ml-2 font-medium">{effectiveOrderStatus}</span>
            </div>
          </div>

          {/* Progress bar */}
          {renderOrderProgress(order.order_status)}

          {/* Action buttons */}
          <div className="flex flex-wrap gap-3 mt-6 justify-end">
            {order.order_status === "Chưa Xác Nhận" && (
              <button
                onClick={handleCancelOrder}
                className="px-4 py-2 bg-red-100 text-red-600 hover:bg-red-200 rounded-lg transition-colors flex items-center"
              >
                <FiXCircle className="mr-2" />
                Hủy đơn hàng
              </button>
            )}
            {order?.order_status === "Đã Giao" &&
              !isRefundActive &&
              !isRefundRejected && (
                <button
                  onClick={handleConfirmOrder}
                  className="px-4 py-2 bg-green-100 text-green-600 hover:bg-green-200 rounded-lg transition-colors flex items-center"
                >
                  <FiCheckCircle className="mr-2" />
                  Xác nhận đã nhận hàng
                </button>
              )}
            {order?.order_status === "Đã Giao" &&
              !isRefundActive &&
              !isRefundRejected && (
                <button
                  onClick={handleRequestRefund}
                  className="px-4 py-2 bg-orange-100 text-orange-600 hover:bg-orange-200 rounded-lg transition-colors flex items-center"
                >
                  <FiRefreshCw className="mr-2" />
                  Yêu cầu hoàn hàng
                </button>
              )}
          </div>
        </motion.div>

        <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
          {/* Left column */}
          <div className="lg:col-span-2 space-y-6">
            {/* Order Information */}
            <motion.div
              initial={{ y: 20, opacity: 0 }}
              animate={{ y: 0, opacity: 1 }}
              transition={{ delay: 0.2 }}
              className="bg-white rounded-lg shadow-sm overflow-hidden"
            >
              <div className="border-b border-gray-100 px-6 py-4">
                <h2 className="text-xl font-semibold text-gray-800 flex items-center">
                  <FiInfo className="mr-2 text-amber-500" />
                  Thông tin đơn hàng
                </h2>
              </div>

              <div className="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <div className="mb-4">
                    <h3 className="text-sm font-medium text-gray-500 mb-2">
                      PHƯƠNG THỨC THANH TOÁN
                    </h3>
                    <p className="flex items-center">
                      <span
                        className={`inline-block w-3 h-3 rounded-full mr-2 ${paymentStatusInfo.bgColor}`}
                      ></span>
                      <span>
                        {order.payment_method?.name || 'Không xác định'} - {paymentStatusInfo.text}
                      </span>
                    </p>
                  </div>

                  <div>
                    <h3 className="text-sm font-medium text-gray-500 mb-2">
                      TRẠNG THÁI ĐƠN HÀNG
                    </h3>
                    <p>{order.order_status}</p>
                  </div>
                </div>

                <div>
                  <h3 className="text-sm font-medium text-gray-500 mb-2">
                    THÔNG TIN GIAO HÀNG
                  </h3>
                  <div className="space-y-3">
                    <div className="flex">
                      <FiUser className="text-gray-400 mt-1 mr-3 flex-shrink-0" />
                      <p className="font-medium">{order.user_name}</p>
                    </div>
                    <div className="flex">
                      <FiPhone className="text-gray-400 mt-1 mr-3 flex-shrink-0" />
                      <p>{order.user_phone}</p>
                    </div>
                    <div className="flex">
                      <FiMail className="text-gray-400 mt-1 mr-3 flex-shrink-0" />
                      <p>{order.user_email}</p>
                    </div>
                    <div className="flex">
                      <FiMapPin className="text-gray-400 mt-1 mr-3 flex-shrink-0" />
                      <p>{order.user_address}</p>
                    </div>
                  </div>
                </div>
              </div>
            </motion.div>

            {/* Order Items */}
            <motion.div
              initial={{ y: 20, opacity: 0 }}
              animate={{ y: 0, opacity: 1 }}
              transition={{ delay: 0.3 }}
              className="bg-white rounded-lg shadow-sm overflow-hidden"
            >
              <div className="border-b border-gray-100 px-6 py-4">
                <h2 className="text-xl font-semibold text-gray-800 flex items-center">
                  <FiShoppingBag className="mr-2 text-amber-500" />
                  Sản phẩm đã đặt
                </h2>
              </div>

              <div className="divide-y divide-gray-100">
                {order.order_items.map((item) => (
                  <div key={item.id} className="p-6 flex flex-col sm:flex-row">
                    <div className="sm:w-20 sm:h-20 h-32 w-full mb-4 sm:mb-0 sm:mr-4 flex-shrink-0 bg-gray-100 rounded-md overflow-hidden">
                      <img
                        src={
                          item.image_url
                            ? `http://localhost:8000/storage/${item.image_url}`
                            : "https://via.placeholder.com/80"
                        }
                        alt={item.product_name}
                        className="w-full h-full object-cover object-center"
                        onError={(e) => {
                          e.target.onerror = null;
                          e.target.src = "https://via.placeholder.com/80";
                        }}
                      />
                    </div>
                    <div className="flex-grow">
                      <div className="flex flex-col sm:flex-row sm:justify-between">
                        <div>
                          <Link
                            to={`/product-detail/${item.product_id}`}
                            className="text-lg font-medium text-gray-800 hover:text-amber-500 transition-colors"
                          >
                            {item.product_name}
                          </Link>
                          {item.product_variant && (
                            <div className="mt-2 flex flex-wrap">
                              {renderVariantDetails(item.product_variant)}
                            </div>
                          )}
                          <div className="mt-1 text-gray-600">
                            Số lượng: {item.quantity} ×{" "}
                            {formatCurrency(item.price)}
                          </div>
                        </div>
                        <div className="mt-2 sm:mt-0 text-lg font-semibold text-amber-600">
                          {formatCurrency(item.quantity * item.price)}
                        </div>
                      </div>

                      {/* Thêm phần đánh giá sản phẩm */}
                      {order.order_status === "Đã Nhận" && (
                        <div className="mt-3">
                          {reviewedProducts.includes(item.product_id) ? (
                            <div className="text-green-600 text-sm flex items-center">
                              <FiCheckCircle className="mr-1" />
                              Đã đánh giá
                            </div>
                          ) : (
                            <button
                              onClick={() => handleOpenReviewModal(item)}
                              className="inline-flex items-center text-sm px-3 py-1.5 bg-amber-100 text-amber-600 hover:bg-amber-200 rounded transition-colors"
                            >
                              <FiStar className="mr-1" />
                              Đánh giá sản phẩm
                            </button>
                          )}
                        </div>
                      )}
                    </div>
                  </div>
                ))}
              </div>
            </motion.div>

            {/* Đánh giá của tôi - Chỉ hiển thị khi đơn hàng đã nhận */}
            {order?.order_status === "Đã Nhận" && (
              <motion.div
                initial={{ y: 20, opacity: 0 }}
                animate={{ y: 0, opacity: 1 }}
                transition={{ delay: 0.4 }}
                className="bg-white rounded-lg shadow-sm overflow-hidden"
              >
                <div className="border-b border-gray-100 px-6 py-4 flex justify-between items-center">
                  <h2 className="text-xl font-semibold text-gray-800 flex items-center">
                    <FiMessageSquare className="mr-2 text-amber-500" />
                    Đánh giá của tôi
                  </h2>
                  <button
                    onClick={() => setShowReviewsSection(!showReviewsSection)}
                    className="text-amber-500 hover:text-amber-600 transition-colors"
                  >
                    {showReviewsSection ? "Ẩn đánh giá" : "Xem đánh giá"}
                  </button>
                </div>

                {showReviewsSection && (
                  <div className="p-6">
                    <ReviewsList orderId={id} />
                  </div>
                )}
              </motion.div>
            )}
          </div>

          {/* Right column - Order Summary */}
          <motion.div
            initial={{ y: 20, opacity: 0 }}
            animate={{ y: 0, opacity: 1 }}
            transition={{ delay: 0.4 }}
            className="bg-white rounded-lg shadow-sm overflow-hidden h-fit"
          >
            <div className="border-b border-gray-100 px-6 py-4">
              <h2 className="text-xl font-semibold text-gray-800">Tổng cộng</h2>
            </div>

            <div className="p-6">
              <div className="space-y-3 text-gray-600">
                <div className="flex justify-between">
                  <span>Tạm tính:</span>
                  <span>{formatCurrency(order.total_price)}</span>
                </div>
                <div className="flex justify-between">
                  <span>Phí vận chuyển:</span>
                  <span>{formatCurrency(0)}</span>
                </div>
                {order.voucher && (
                  <div className="flex justify-between text-green-600">
                    <span>Giảm giá (voucher):</span>
                    <span>-{formatCurrency(order.discount_amount || 0)}</span>
                  </div>
                )}
              </div>

              <div className="mt-4 pt-4 border-t border-gray-100">
                <div className="flex justify-between items-center">
                  <span className="text-lg font-medium">Tổng cộng:</span>
                  <span className="text-xl font-bold text-amber-600">
                    {formatCurrency(
                      order.total_price - (order.discount_amount || 0)
                    )}
                  </span>
                </div>
              </div>

              {order.payment_status !== 1 &&
                (order.payment_method?.name === "MoMo" ||
                  order.payment_method?.name === "VNPAY") && (
                  <div className="mt-6">
                    <button
                      className="w-full py-3 bg-amber-500 hover:bg-amber-600 text-white rounded-lg transition-colors flex items-center justify-center"
                      onClick={handleRetryPayment}
                    >
                      <FiRefreshCw className="mr-2" />
                      Thanh toán lại
                    </button>
                  </div>
                )}
            </div>
          </motion.div>
        </div>
      </div>

      {/* Modal yêu cầu hoàn hàng */}
      <RefundRequestModal
        showModal={showRefundModal}
        setShowModal={setShowRefundModal}
        loading={loading}
        selectedReason={selectedReasonOption}
        customReason={customReason}
        setCustomReason={setCustomReason}
        showCustomInput={showCustomReasonInput}
        handleReasonSelect={handleReasonSelect}
        submitRefundRequest={submitRefundRequest}
      />

      {/* Modal hủy đơn hàng */}
      <CancelOrderModal
        showModal={showCancelModal}
        setShowModal={setShowCancelModal}
        loading={loading}
        selectedReason={selectedCancelReason}
        customReason={cancelCustomReason}
        setCustomReason={setCancelCustomReason}
        showCustomInput={showCancelCustomInput}
        handleReasonSelect={handleCancelReasonSelect}
        submitCancelOrder={submitCancelOrder}
      />

      {/* Modal đánh giá sản phẩm */}
      {selectedProduct && (
        <ReviewModal
          showModal={showReviewModal}
          setShowModal={setShowReviewModal}
          loading={loading}
          productId={selectedProduct.product_id}
          productName={selectedProduct.product_name}
          productImage={selectedProduct.image_url}
          orderId={id}
          productVariant={selectedProduct.product_variant}
          onReviewSubmitSuccess={handleReviewSuccess}
        />
      )}
    </motion.div>
  );
};

export default OrderDetail;
