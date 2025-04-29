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
import axiosInstance from "../../../utils/axiosConfig";
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
  const [activeTab, setActiveTab] = useState("description"); // 'description', 'reviews' hoặc 'comments'

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
        console.log("API Response:", response.data);

        if (response.data.status !== "success") {
          throw new Error(
            response.data.message || "Lỗi khi lấy thông tin sản phẩm"
          );
        }

        const productData = response.data.data;
        if (!productData) {
          throw new Error("Không tìm thấy thông tin sản phẩm");
        }

        console.log("Product Data:", productData);
        setProduct(productData);
        setActiveImage(productData.image_thumnail);

        // Lưu thông tin sản phẩm đã xem vào localStorage
        try {
          const userActivities = JSON.parse(
            localStorage.getItem("userActivities")
          ) || {
            viewedProducts: [],
            searchedKeywords: [],
            cartProducts: [],
          };

          // Thêm sản phẩm vào viewedProducts
          const viewedProduct = {
            id: productData.id,
            name: productData.name,
            category: productData.category?.name || "",
            timestamp: new Date().toISOString(),
          };

          // Kiểm tra xem sản phẩm đã được xem chưa
          const existingIndex = userActivities.viewedProducts.findIndex(
            (item) => item.id === productData.id
          );
          if (existingIndex !== -1) {
            // Cập nhật timestamp nếu sản phẩm đã tồn tại
            userActivities.viewedProducts[existingIndex].timestamp =
              viewedProduct.timestamp;
          } else {
            // Thêm mới nếu chưa tồn tại
            userActivities.viewedProducts.push(viewedProduct);
          }

          localStorage.setItem(
            "userActivities",
            JSON.stringify(userActivities)
          );
          window.dispatchEvent(new CustomEvent("userActivitiesUpdated"));
        } catch (error) {
          console.error("Lỗi khi lưu hoạt động người dùng:", error);
        }

        // Kiểm tra và xử lý biến thể
        if (
          productData.variants &&
          Array.isArray(productData.variants) &&
          productData.variants.length > 0
        ) {
          console.log("Product has variants:", productData.variants);
          setSelectedVariantId(productData.variants[0].id);

          // Khởi tạo thuộc tính biến thể nếu có
          if (
            productData.variants[0].variant_details &&
            Array.isArray(productData.variants[0].variant_details)
          ) {
            console.log(
              "First variant details:",
              productData.variants[0].variant_details
            );
            const initialAttributes = {};
            productData.variants[0].variant_details.forEach((attr) => {
              if (attr && attr.name && attr.value) {
                initialAttributes[attr.name] = attr.value;
              }
            });
            console.log("Initial attributes:", initialAttributes);
            setSelectedVariantAttributes(initialAttributes);
          }
        } else {
          console.log("Product has no variants");
        }

        setLoading(false);
      })
      .catch((error) => {
        console.error("Lỗi khi lấy dữ liệu sản phẩm:", error);
        if (error.response) {
          // Lỗi từ server
          setError(
            error.response.data.message ||
            "Có lỗi xảy ra khi tải thông tin sản phẩm"
          );
        } else if (error.request) {
          // Lỗi không có phản hồi từ server
          setError("Không thể kết nối đến server. Vui lòng thử lại sau.");
        } else {
          // Lỗi khác
          setError("Có lỗi xảy ra. Vui lòng thử lại sau.");
        }
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

  // Kiểm tra và hiển thị dữ liệu comment đến console để debug
  useEffect(() => {
    if (comments.length > 0) {
      console.log("Comments data:", comments);
      // Kiểm tra cấu trúc avatar
      comments.forEach((comment, index) => {
        console.log(`Comment ${index} user avatar:`, comment.user?.avatar);
      });
    }
  }, [comments]);

  // Kiểm tra và hiển thị dữ liệu currentUser để debug
  useEffect(() => {
    if (currentUser) {
      console.log("Current user data:", currentUser);
      console.log("Current user avatar:", currentUser.avatar);
      console.log("Processed avatar URL:", processAvatar(currentUser.avatar));
    }
  }, [currentUser]);

  // Hàm kiểm tra trạng thái đánh giá đơn giản
  const checkReviewStatus = () => {
    // Đặt giá trị mặc định
    setCanReview(false); // Mặc định ẩn phần đánh giá
    setCanComment(true); // Luôn cho phép bình luận
    setPurchaseMessage("");

    // Kiểm tra xem người dùng có token không
    const token = localStorage.getItem("authToken");
    if (!token) {
      return; // Nếu chưa đăng nhập thì không cần gọi API
    }

    // Gọi API kiểm tra quyền đánh giá
    axiosInstance.get(`/products/${id}/can-review`, {
      headers: {
        Authorization: `Bearer ${token}`
      }
    })
      .then(response => {
        if (response.data && response.data.success) {
          setCanReview(true); // Nếu API trả về thành công, cho phép đánh giá
        } else if (response.data && response.data.message) {
          setPurchaseMessage(response.data.message);
        }
      })
      .catch(error => {
        console.error("Lỗi khi kiểm tra quyền đánh giá:", error);
        // Nếu có thông báo lỗi từ server, hiển thị
        if (error.response && error.response.data && error.response.data.message) {
          setPurchaseMessage(error.response.data.message);
        } else {
          setPurchaseMessage("Bạn cần mua và nhận sản phẩm này trước khi đánh giá");
        }
      });

    // Lấy danh sách đánh giá để hiển thị
    axios
      .get(`http://localhost:8000/api/products/${id}/reviews`)
      .then((response) => {
        if (response.data && response.data.data) {
          setReviews(response.data.data);
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

  // Function để xử lý avatar link
  const processAvatar = (avatarUrl) => {
    if (!avatarUrl) return '/images/avatarEmpty/avatarUser.png';

    console.log('Processing avatar URL:', avatarUrl);

    // Nếu là URL trực tiếp từ Facebook hoặc Google, sử dụng trực tiếp
    if (avatarUrl.includes('facebook.com') || avatarUrl.includes('google')) {
      console.log('Detected external avatar:', avatarUrl);
      return avatarUrl;
    }

    // Nếu đã là URL đầy đủ (http/https), trả về trực tiếp
    if (avatarUrl.startsWith('http')) {
      console.log('Using direct URL:', avatarUrl);
      return avatarUrl;
    }

    // Nếu là đường dẫn storage, thêm base URL nếu cần
    if (avatarUrl.startsWith('/storage/')) {
      const storageUrl = import.meta.env.VITE_API_URL + avatarUrl;
      console.log('Created storage URL:', storageUrl);
      return storageUrl;
    }

    // Nếu là đường dẫn storage nhưng không có dấu / ở đầu
    if (avatarUrl.startsWith('storage/')) {
      const storageUrl = import.meta.env.VITE_API_URL + '/' + avatarUrl;
      console.log('Created storage URL with prefix:', storageUrl);
      return storageUrl;
    }

    // Trường hợp khác, trả về URL gốc
    console.log('Using original URL:', avatarUrl);
    return avatarUrl;
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
        if (attr && attr.name && attr.value) {
          attributes[attr.name] = attr.value;
        }
      });
      setSelectedVariantAttributes(attributes);
    }

    // Reset số lượng
    setQuantity(1);
  };

  // Hàm xử lý khi chọn thuộc tính biến thể (color, size, ...)
  const handleVariantAttributeChange = (variantName, value) => {
    // Cập nhật thuộc tính đã chọn
    const newAttributes = { ...selectedVariantAttributes, [variantName]: value };
    setSelectedVariantAttributes(newAttributes);

    // Tìm biến thể phù hợp với tất cả thuộc tính đã chọn
    if (product && product.variants && Array.isArray(product.variants)) {
      const matchingVariant = product.variants.find((variant) => {
        if (!variant.variant_details || !Array.isArray(variant.variant_details)) return false;
        return variant.variant_details.every((detail) => {
          return newAttributes[detail.name] === detail.value;
        });
      });
      if (matchingVariant) {
        setSelectedVariantId(matchingVariant.id);
      }
    }

    // Reset số lượng về 1 khi thay đổi biến thể
    setQuantity(1);
  };

  const getCurrentPrice = () => {
    if (!product) return null;

    try {
      console.log("Getting current price for product:", product);

      if (
        product.variants &&
        Array.isArray(product.variants) &&
        product.variants.length > 0
      ) {
        console.log(
          "Product has variants, selected variant ID:",
          selectedVariantId
        );
        const selectedVariant = product.variants.find(
          (v) => v.id === selectedVariantId
        );
        if (selectedVariant) {
          console.log("Selected variant:", selectedVariant);
          return selectedVariant.discount_price || selectedVariant.price;
        }
      }

      console.log("Using product base price");
      return product.discount_price || product.price;
    } catch (error) {
      console.error("Error getting current price:", error);
      return null;
    }
  };

  const getOriginalPrice = () => {
    if (!product) return null;

    try {
      if (
        product.variants &&
        Array.isArray(product.variants) &&
        product.variants.length > 0
      ) {
        const selectedVariant = product.variants.find(
          (v) => v.id === selectedVariantId
        );
        if (selectedVariant && selectedVariant.discount_price) {
          return selectedVariant.price;
        }
      }

      return product.discount_price ? product.price : null;
    } catch (error) {
      console.error("Error getting original price:", error);
      return null;
    }
  };

  const getStockQuantity = () => {
    if (!product) return 0;

    try {
      if (
        product.variants &&
        Array.isArray(product.variants) &&
        product.variants.length > 0
      ) {
        const selectedVariant = product.variants.find(
          (v) => v.id === selectedVariantId
        );
        if (selectedVariant) {
          return selectedVariant.quantity || 0;
        }
        return 0;
      }

      return product.quantity || 0;
    } catch (error) {
      console.error("Error getting stock quantity:", error);
      return 0;
    }
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
    console.log(
      "handleAddToCart: Checking token...",
      localStorage.getItem("authToken")
    );
    const token = localStorage.getItem("authToken");

    if (!token) {
      console.error("handleAddToCart: No token found! Navigating to sign-in.");
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

      const response = await axiosInstance.post("/cart/add", cartData, {
        headers: {
          Authorization: `Bearer ${token}`,
        },
      });

      if (response.status === 201) {
        // Lưu hoạt động của người dùng vào localStorage
        try {
          const userActivities = JSON.parse(
            localStorage.getItem("userActivities")
          ) || {
            viewedProducts: [],
            searchedKeywords: [],
            cartProducts: [],
          };

          // Thêm sản phẩm vào cartProducts
          const cartProduct = {
            id: product.id,
            name: product.name,
            category: product.category?.name || "",
            timestamp: new Date().toISOString(),
          };

          // Kiểm tra xem sản phẩm đã có trong cart chưa
          const existingIndex = userActivities.cartProducts.findIndex(
            (item) => item.id === product.id
          );
          if (existingIndex !== -1) {
            // Cập nhật timestamp nếu sản phẩm đã tồn tại
            userActivities.cartProducts[existingIndex].timestamp =
              cartProduct.timestamp;
          } else {
            // Thêm mới nếu chưa tồn tại
            userActivities.cartProducts.push(cartProduct);
          }

          localStorage.setItem(
            "userActivities",
            JSON.stringify(userActivities)
          );

          // Gửi sự kiện để thông báo userActivities đã được cập nhật
          window.dispatchEvent(new CustomEvent("userActivitiesUpdated"));
        } catch (error) {
          console.error("Lỗi khi lưu hoạt động người dùng:", error);
        }

        toast.custom(
          (t) => (
            <div
              className={`${t.visible ? "animate-enter" : "animate-leave"
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

    // Lấy thông tin biến thể nếu có
    const selectedVariant = selectedVariantId
      ? product.variants?.find((v) => v.id === selectedVariantId)
      : null;

    // Tạo dữ liệu sản phẩm để chuyển sang trang thanh toán
    const productData = {
      product: {
        id: product.id,
        name: product.name,
        discount_price: product.discount_price ?? product.price,
        price: product.price,
        image_thumbnail: product.image_thumnail,
      },
      product_variant: selectedVariantId
        ? {
          id: selectedVariantId,
          discount_price:
            selectedVariant?.discount_price ?? selectedVariant?.price, // Lấy giá từ biến thể
          price: selectedVariant?.price, // Lấy giá gốc từ biến thể
          variant_details: selectedVariant?.variant_details || {},
        }
        : null,
      quantity: quantity,
      total_price: selectedVariantId
        ? (selectedVariant?.discount_price ?? selectedVariant?.price) * quantity // Tính tổng giá dựa trên giá của biến thể
        : (product.discount_price ?? product.price) * quantity, // Tính tổng giá dựa trên giá của sản phẩm
    };

    console.log("productData", productData);

    navigate("/payment_buy_now", {
      state: {
        selectedProducts: [productData],
        total: productData.total_price,
        buyNow: true,
      },
    });
  };

  // Hàm gửi đánh giá
  const handleSubmitReview = async (e) => {
    e.preventDefault();

    if (!canReview) {
      toast.error(purchaseMessage || "Bạn cần mua và nhận sản phẩm này trước khi đánh giá");
      return;
    }

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

      const response = await axiosInstance.post("/reviews", formData, {
        headers: {
          Authorization: `Bearer ${token}`,
          "Content-Type": "multipart/form-data",
        },
      });

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

  const renderVariants = () => {
    if (
      !product ||
      !product.variants ||
      !Array.isArray(product.variants) ||
      product.variants.length === 0
    ) {
      console.log("No variants to render");
      return null;
    }

    try {
      // Lấy danh sách unique các giá trị biến thể
      const uniqueVariantDetails = product.variants.reduce((acc, variant) => {
        if (variant.variant_details && Array.isArray(variant.variant_details)) {
          variant.variant_details.forEach((detail, index) => {
            if (!acc[index]) acc[index] = { name: detail.name, values: new Set() };
            if (detail && detail.value) {
              acc[index].values.add(detail.value);
            }
          });
        }
        return acc;
      }, []);

      return (
        <div className="space-y-4">
          {uniqueVariantDetails.map((item, index) => {
            if (!item) return null;
            const variantName = item.name;
            return (
              <div key={index} className="space-y-2">
                <label className="block text-sm font-medium text-gray-700">
                  {variantName}
                </label>
                <div className="flex flex-wrap gap-2">
                  {Array.from(item.values).map((value) => (
                    <button
                      key={value}
                      onClick={() => handleVariantAttributeChange(variantName, value)}
                      className={`px-3 py-1 rounded-md text-sm font-medium ${selectedVariantAttributes[variantName] === value
                        ? "bg-amber-500 text-white"
                        : "bg-gray-100 text-gray-800 hover:bg-gray-200"
                        }`}
                    >
                      {value}
                    </button>
                  ))}
                </div>
              </div>
            );
          })}
        </div>
      );
    } catch (error) {
      console.error("Error rendering variants:", error);
      return null;
    }
  };

  // Xử lý URL hình ảnh
  const getImageUrl = (imagePath) => {
    try {
      if (!imagePath) {
        console.log("No image path provided");
        return "https://via.placeholder.com/600x600?text=No+Image";
      }

      if (imagePath.startsWith("http")) {
        return imagePath;
      }

      return `http://localhost:8000/storage/${imagePath}`;
    } catch (error) {
      console.error("Error processing image URL:", error);
      return "https://via.placeholder.com/600x600?text=Error+Loading+Image";
    }
  };

  // Xử lý lỗi hình ảnh
  const handleImageError = (e) => {
    console.log("Image load error:", e);
    e.target.src = "https://via.placeholder.com/600x600?text=Image+Error";
  };

  // Render hình ảnh chính
  const renderMainImage = () => {
    if (!product) return null;

    try {
      const mainImageUrl = getImageUrl(activeImage || product.image_thumnail);

      return (
        <div className="relative h-[650px] bg-gray-50 rounded-xl shadow-md flex justify-center items-center p-2 overflow-hidden border border-gray-100">
          <div className="absolute inset-0 bg-gradient-to-t from-gray-100 to-transparent opacity-20"></div>
          <img
            src={mainImageUrl}
            alt={product.name || "Sản phẩm"}
            className="max-h-[620px] max-w-[95%] object-contain z-10 transition-all duration-500 hover:scale-105"
            onError={handleImageError}
          />
          {product.discount_price &&
            Number(product.discount_price) !== Number(product.price) && (
              <span className="absolute top-4 left-4 bg-red-500 text-white text-sm font-bold px-3 py-1.5 rounded-full z-20 shadow-lg transform -rotate-2">
                GIẢM GIÁ
              </span>
            )}
        </div>
      );
    } catch (error) {
      console.error("Error rendering main image:", error);
      return (
        <div className="h-[650px] bg-gray-100 rounded-xl flex items-center justify-center">
          <p className="text-gray-500">Lỗi tải hình ảnh</p>
        </div>
      );
    }
  };

  // Render gallery
  const renderGallery = () => {
    if (!product || !product.gallery || !Array.isArray(product.gallery)) {
      return null;
    }

    try {
      return (
        <div className="flex space-x-2 overflow-x-auto pb-2">
          <div
            onClick={() => setActiveImage(product.image_thumnail)}
            className={`flex-shrink-0 w-20 h-20 rounded-lg overflow-hidden cursor-pointer border-2 ${activeImage === product.image_thumnail
              ? "border-amber-500"
              : "border-transparent"
              }`}
          >
            <img
              src={getImageUrl(product.image_thumnail)}
              alt="thumbnail"
              className="w-full h-full object-cover object-center"
              onError={handleImageError}
            />
          </div>

          {product.gallery.map((image, index) => (
            <div
              key={index}
              onClick={() => setActiveImage(image.image_url)}
              className={`flex-shrink-0 w-20 h-20 rounded-lg overflow-hidden cursor-pointer border-2 ${activeImage === image.image_url
                ? "border-amber-500"
                : "border-transparent"
                }`}
            >
              <img
                src={getImageUrl(image.image_url)}
                alt={`Gallery ${index + 1}`}
                className="w-full h-full object-cover object-center"
                onError={handleImageError}
              />
            </div>
          ))}
        </div>
      );
    } catch (error) {
      console.error("Error rendering gallery:", error);
      return null;
    }
  };

  return (
    <>
      <Toaster position="top-right" />
      <main className="max-w-7xl mx-auto mb-20 mt-5 px-4 sm:px-6 lg:px-8">
        {loading && (
          <div className="h-screen flex items-center justify-center">
            <div className="animate-spin rounded-full h-16 w-16 border-t-2 border-b-2 border-amber-500"></div>
          </div>
        )}

        {error && (
          <div className="text-center py-20">
            <h2 className="text-2xl font-bold text-red-500 mb-4">
              Lỗi tải thông tin sản phẩm
            </h2>
            <p className="text-gray-600">{error}</p>
          </div>
        )}

        {!loading && !error && product && (
          <div>
            {/* Breadcrumb */}
            <nav className="mb-8" aria-label="Breadcrumb">
              <ol className="flex items-center space-x-2 text-sm font-medium text-gray-500">
                <li>
                  <a
                    href="/"
                    className="hover:text-amber-500 transition-colors"
                  >
                    Trang chủ
                  </a>
                </li>
                <li className="flex items-center">
                  <span className="mx-2">/</span>
                  <a
                    href="/products"
                    className="hover:text-amber-500 transition-colors"
                  >
                    Sản phẩm
                  </a>
                </li>
                {product.category && (
                  <li className="flex items-center">
                    <span className="mx-2">/</span>
                    <a
                      href={`/category/${product.category.id}`}
                      className="hover:text-amber-500 transition-colors"
                    >
                      {product.category.name}
                    </a>
                  </li>
                )}
                <li className="flex items-center">
                  <span className="mx-2">/</span>
                  <span className="text-gray-900 font-semibold truncate max-w-[200px]">
                    {product.name}
                  </span>
                </li>
              </ol>
            </nav>

            {/* Product Detail */}
            <div className="bg-white rounded-2xl shadow-sm overflow-hidden mb-10">
              <div className="flex flex-col lg:flex-row">
                {/* Image Section */}
                <div className="lg:w-1/2 p-6 bg-gray-50">
                  {renderMainImage()}
                  <div className="mt-4">{renderGallery()}</div>
                </div>

                {/* Details Section */}
                <div className="lg:w-1/2 p-8">
                  <div className="border-b border-gray-100 pb-6">
                    <h1 className="text-3xl font-bold text-gray-800 leading-tight mb-3">
                      {product.name}
                    </h1>

                    <div className="flex items-center mb-4">
                      <div className="flex items-center">
                        {renderStars(Math.round(calculateAverageRating()))}
                        <span className="ml-2 text-sm text-gray-500">
                          ({reviews.length} đánh giá)
                        </span>
                      </div>
                      <span className="mx-3 text-gray-300">|</span>
                      <span className="text-sm text-gray-500">
                        Mã SP: {product.product_code || "N/A"}
                      </span>
                    </div>

                    <div className="flex items-center space-x-2 mb-4">
                      {getCurrentPrice() !== null && (
                        <>
                          <span className="text-3xl font-bold text-amber-600">
                            {formatPrice(getCurrentPrice())}
                          </span>
                          {getOriginalPrice() && (
                            <>
                              <span className="text-lg text-gray-400 line-through">
                                {formatPrice(getOriginalPrice())}
                              </span>
                              <span className="px-2 py-1 text-xs font-semibold bg-red-50 text-red-600 rounded-md">
                                {Math.round(
                                  (1 - getCurrentPrice() / getOriginalPrice()) *
                                  100
                                )}
                                % giảm
                              </span>
                            </>
                          )}
                        </>
                      )}
                    </div>
                  </div>

                  {/* Stock Status */}
                  <div className="py-6 border-b border-gray-100">
                    <div className="flex items-center">
                      <div
                        className={`w-3 h-3 rounded-full mr-2 ${getStockQuantity() > 10
                          ? "bg-green-500"
                          : getStockQuantity() > 0
                            ? "bg-yellow-500"
                            : "bg-red-500"
                          }`}
                      ></div>
                      <span
                        className={`font-medium ${getStockQuantity() > 10
                          ? "text-green-600"
                          : getStockQuantity() > 0
                            ? "text-yellow-600"
                            : "text-red-600"
                          }`}
                      >
                        {getStockQuantity() > 10
                          ? "Còn hàng"
                          : getStockQuantity() > 0
                            ? "Sắp hết hàng"
                            : "Hết hàng"}
                      </span>
                      {getStockQuantity() > 0 && (
                        <span className="ml-2 text-sm text-gray-500">
                          ({getStockQuantity()} sản phẩm)
                        </span>
                      )}
                    </div>
                  </div>

                  {/* Variants Section */}
                  <div className="py-6 border-b border-gray-100">
                    {renderVariants()}
                  </div>

                  {/* Quantity Section */}
                  <div className="py-6 border-b border-gray-100">
                    <label className="block text-sm font-medium text-gray-700 mb-3">
                      Số lượng
                    </label>
                    <div className="flex items-center">
                      <button
                        onClick={decreaseQuantity}
                        className="w-10 h-10 flex items-center justify-center border border-gray-300 rounded-l-lg hover:bg-gray-100 transition-colors"
                        disabled={quantity <= 1}
                      >
                        -
                      </button>
                      <div className="w-16 h-10 flex items-center justify-center border-t border-b border-gray-300">
                        {quantity}
                      </div>
                      <button
                        onClick={increaseQuantity}
                        className="w-10 h-10 flex items-center justify-center border border-gray-300 rounded-r-lg hover:bg-gray-100 transition-colors"
                        disabled={quantity >= getStockQuantity()}
                      >
                        +
                      </button>
                    </div>
                  </div>

                  {/* Shipping Features */}
                  <div className="flex flex-wrap gap-4 py-6 mb-6">
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
                      <span className="text-sm text-gray-700">
                        Bảo hành 12 tháng
                      </span>
                    </div>
                  </div>

                  {/* Action Buttons */}
                  <div className="flex flex-col md:flex-row space-y-3 md:space-y-0 md:space-x-4">
                    <button
                      onClick={handleAddToCart}
                      disabled={addingToCart || getStockQuantity() === 0}
                      className={`flex-1 py-3 px-6 text-white font-medium rounded-lg flex items-center justify-center transition-colors ${addingToCart || getStockQuantity() === 0
                        ? "bg-gray-400 cursor-not-allowed"
                        : "bg-amber-500 hover:bg-amber-600"
                        }`}
                    >
                      {addingToCart ? (
                        <>
                          <div className="mr-2 h-5 w-5 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                          Đang thêm...
                        </>
                      ) : getStockQuantity() === 0 ? (
                        "Hết hàng"
                      ) : (
                        <>
                          <IoCartOutline className="mr-2 text-xl" />
                          Thêm vào giỏ hàng
                        </>
                      )}
                    </button>

                    <button
                      onClick={handleBuyNow}
                      disabled={addingToBuy || getStockQuantity() === 0}
                      className={`flex-1 py-3 px-6 text-white font-medium rounded-lg flex items-center justify-center transition-colors ${addingToBuy || getStockQuantity() === 0
                        ? "bg-gray-400 cursor-not-allowed"
                        : "bg-green-600 hover:bg-green-700"
                        }`}
                    >
                      {addingToBuy ? (
                        <>
                          <div className="mr-2 h-5 w-5 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                          Đang xử lý...
                        </>
                      ) : getStockQuantity() === 0 ? (
                        "Hết hàng"
                      ) : (
                        "Mua ngay"
                      )}
                    </button>
                  </div>
                </div>
              </div>
            </div>

            {/* Tabs for Description, Reviews and Comments */}
            <div className="bg-white rounded-2xl shadow-sm overflow-hidden mb-10">
              <div className="border-b border-gray-100">
                <div className="flex overflow-x-auto scrollbar-hide">
                  <button
                    className={`px-8 py-4 font-medium text-sm whitespace-nowrap ${activeTab === "description"
                      ? "text-amber-500 border-b-2 border-amber-500"
                      : "text-gray-500 hover:text-gray-700"
                      }`}
                    onClick={() => setActiveTab("description")}
                  >
                    Mô tả sản phẩm
                  </button>
                  <button
                    className={`px-8 py-4 font-medium text-sm whitespace-nowrap ${activeTab === "reviews"
                      ? "text-amber-500 border-b-2 border-amber-500"
                      : "text-gray-500 hover:text-gray-700"
                      }`}
                    onClick={() => setActiveTab("reviews")}
                  >
                    Đánh giá sản phẩm ({reviews.length})
                  </button>
                  <button
                    className={`px-8 py-4 font-medium text-sm whitespace-nowrap ${activeTab === "comments"
                      ? "text-amber-500 border-b-2 border-amber-500"
                      : "text-gray-500 hover:text-gray-700"
                      }`}
                    onClick={() => setActiveTab("comments")}
                  >
                    Bình luận ({comments.length})
                  </button>
                </div>
              </div>

              <div className="p-8">
                {/* Description Tab */}
                {activeTab === "description" && (
                  <div>
                    {product.description ? (
                      <div
                        dangerouslySetInnerHTML={{
                          __html: product.description,
                        }}
                        className="prose max-w-none text-gray-700"
                      />
                    ) : (
                      <p className="text-gray-500 italic text-center py-10">
                        Sản phẩm chưa có mô tả chi tiết.
                      </p>
                    )}
                  </div>
                )}

                {/* Reviews Tab */}
                {activeTab === "reviews" && (
                  <div className="space-y-8">
                    {/* Tổng quan đánh giá */}
                    <div className="bg-gray-50 p-6 rounded-xl">
                      <div className="flex flex-col md:flex-row gap-8 items-center">
                        <div className="text-center">
                          <div className="text-5xl font-bold text-amber-500">
                            {calculateAverageRating()}
                            <span className="text-lg text-gray-500">/5</span>
                          </div>
                          <div className="flex justify-center mt-2">
                            {renderStars(Math.round(calculateAverageRating()))}
                          </div>
                          <div className="text-sm text-gray-500 mt-1">
                            Dựa trên {reviews.length} đánh giá
                          </div>
                        </div>

                        <div className="flex-1 grid grid-cols-1 gap-3">
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
                                className="flex items-center text-sm gap-3"
                              >
                                <div className="w-8 text-gray-600 font-medium">
                                  {star} sao
                                </div>
                                <div className="flex-1 bg-gray-200 rounded-full h-3">
                                  <div
                                    className="bg-amber-400 h-3 rounded-full"
                                    style={{ width: `${percentage}%` }}
                                  ></div>
                                </div>
                                <div className="w-12 text-gray-500 text-right">
                                  {percentage}%
                                </div>
                              </div>
                            );
                          })}
                        </div>
                      </div>
                    </div>

                    {/* Form đánh giá sản phẩm hoặc thông báo đăng nhập */}
                    {canReview ? (
                      <div className="bg-white border border-gray-200 rounded-xl p-6 mb-8 shadow-sm">
                        <h3 className="text-lg font-medium text-gray-800 mb-4">
                          Đánh giá sản phẩm
                        </h3>
                        <form onSubmit={handleSubmitReview}>
                          <div className="space-y-4">
                            <div>
                              <label className="block text-gray-700 mb-2 font-medium">
                                Đánh giá của bạn
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
                                className="block text-gray-700 mb-2 font-medium"
                              >
                                Chia sẻ trải nghiệm của bạn
                              </label>
                              <textarea
                                id="reviewText"
                                value={reviewText}
                                onChange={(e) => setReviewText(e.target.value)}
                                placeholder="Chia sẻ trải nghiệm của bạn về sản phẩm này..."
                                className="w-full border border-gray-300 rounded-lg p-3 focus:outline-none focus:ring-2 focus:ring-amber-500 resize-none"
                                rows={4}
                              ></textarea>
                            </div>

                            {/* Phần tải ảnh lên */}
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
                                  Chọn ảnh
                                </label>
                                <span className="ml-2 text-gray-500 text-sm">
                                  {reviewImages.length}/5 ảnh đã chọn
                                </span>
                              </div>

                              {/* Hiển thị ảnh đã chọn */}
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

                            <div className="text-right">
                              <button
                                type="submit"
                                disabled={
                                  isSubmittingReview || !reviewText.trim()
                                }
                                className="px-6 py-3 bg-amber-500 text-white rounded-lg font-medium hover:bg-amber-600 transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center ml-auto"
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
                    ) : !currentUser ? (
                      <div className="bg-amber-50 border border-amber-200 rounded-xl p-6 mb-8 flex flex-col items-center justify-center text-center">
                        <p className="font-medium text-amber-700 mb-4">
                          Vui lòng đăng nhập để đánh giá sản phẩm
                        </p>
                        <button
                          onClick={() => navigate("/sign-in")}
                          className="px-6 py-2 bg-amber-500 text-white rounded-lg font-medium hover:bg-amber-600 transition-colors shadow-md hover:shadow-lg transform hover:-translate-y-0.5 flex items-center"
                        >
                          <IoArrowForward className="mr-2" />
                          Đăng nhập ngay
                        </button>
                      </div>
                    ) : (
                      <div className="bg-amber-50 border border-amber-200 rounded-xl p-6 mb-8">
                        <p className="font-medium text-center text-amber-700">
                          Bạn cần mua và nhận sản phẩm này trước khi đánh giá
                        </p>
                      </div>
                    )}

                    {/* Danh sách đánh giá */}
                    {reviews.length > 0 ? (
                      <div className="space-y-6">
                        <h3 className="text-lg font-medium text-gray-800 mb-4">
                          Tất cả đánh giá ({reviews.length})
                        </h3>
                        <div className="space-y-6">
                          {reviews.map((review) => (
                            <div
                              key={review.id}
                              className="border border-gray-100 rounded-xl p-6 hover:shadow-md transition-shadow"
                            >
                              <div className="flex items-start gap-4">
                                <div className="w-12 h-12 rounded-full bg-gradient-to-r from-amber-400 to-amber-600 flex items-center justify-center text-white font-medium text-lg">
                                  {review.user.name.charAt(0).toUpperCase()}
                                </div>
                                <div className="flex-1">
                                  <div className="flex flex-wrap items-center gap-2 mb-2">
                                    <h3 className="text-gray-800 font-medium">
                                      {review.user.name}
                                    </h3>
                                    <div className="flex items-center">
                                      {renderStars(review.rating)}
                                    </div>
                                    <span className="text-xs text-gray-400">
                                      • {formatDateTime(review.created_at)}
                                    </span>
                                  </div>

                                  {/* Hiển thị thông tin biến thể nếu có - SỬA LỖI: Sử dụng variant_info */}
                                  {review.variant_info && (
                                    (Array.isArray(review.variant_info) && review.variant_info.length > 0) ||
                                    (typeof review.variant_info === 'object' && Object.keys(review.variant_info).length > 0)
                                  ) && (
                                      <div className="bg-gray-50 px-2 py-1 rounded-md text-xs my-2 inline-block border border-gray-200">
                                        {/* <span className="font-medium text-gray-600 mr-1">Phiên bản:</span> */}
                                        {Array.isArray(review.variant_info) ? (
                                          // Nếu variant_info là mảng
                                          review.variant_info.map((detail, idx) => (
                                            <span key={idx} className="text-gray-700">
                                              {detail.attribute_name || detail.name}: <strong>{detail.attribute_value || detail.value}</strong>
                                              {idx < review.variant_info.length - 1 ? ' - ' : ''}
                                            </span>
                                          ))
                                        ) : (
                                          // Nếu variant_info là object
                                          Object.entries(review.variant_info).map(([key, value], idx, arr) => (
                                            <span key={key} className="text-gray-700">
                                              {key}: <strong>{value}</strong>
                                              {idx < arr.length - 1 ? ' - ' : ''}
                                            </span>
                                          ))
                                        )}
                                      </div>
                                    )}

                                  <p className="text-gray-600 mb-4">
                                    {review.review_text}
                                  </p>

                                  {/* Hình ảnh đánh giá */}
                                  {review.images &&
                                    review.images.length > 0 && (
                                      <div className="flex flex-wrap gap-3 mt-3">
                                        {review.images.map((img, index) => (
                                          <div
                                            key={index}
                                            className="w-24 h-24 rounded-lg overflow-hidden border border-gray-100"
                                          >
                                            <img
                                              src={`http://localhost:8000/storage/${img}`}
                                              alt={`review-${index}`}
                                              className="w-full h-full object-cover"
                                              onError={(e) => {
                                                e.target.src =
                                                  "https://via.placeholder.com/96?text=Error";
                                              }}
                                            />
                                          </div>
                                        ))}
                                      </div>
                                    )}
                                </div>
                              </div>
                            </div>
                          ))}
                        </div>
                      </div>
                    ) : (
                      <div className="text-center py-12 bg-gray-50 rounded-xl">
                        <IoChatbubbleOutline className="mx-auto h-16 w-16 text-gray-300 mb-4" />
                        <p className="text-gray-500 font-medium">
                          Sản phẩm này chưa có đánh giá nào.
                        </p>
                        <p className="text-gray-400 text-sm mt-1">
                          Hãy là người đầu tiên đánh giá sản phẩm này!
                        </p>
                      </div>
                    )}
                  </div>
                )}

                {/* Comments Tab */}
                {activeTab === "comments" && (
                  <div className="space-y-8">
                    {/* Form bình luận */}
                    {currentUser ? (
                      <div className="bg-gray-50 p-6 rounded-xl mb-8">
                        <h3 className="text-lg font-medium text-gray-800 mb-4">
                          Để lại bình luận
                        </h3>
                        <form onSubmit={handleSubmitComment}>
                          <div className="flex gap-4">
                            {/* === START EDIT: Show user avatar correctly === */}
                            {
                              currentUser && (
                                currentUser.avatar ? (
                                  <img
                                    src={processAvatar(currentUser.avatar)}
                                    alt={currentUser.name}
                                    className="w-12 h-12 rounded-full object-cover border border-gray-200 shrink-0"
                                    onError={(e) => {
                                      e.target.onerror = null;
                                      const fallbackDiv = document.createElement('div');
                                      fallbackDiv.className = "w-12 h-12 rounded-full bg-gradient-to-r from-amber-400 to-amber-600 flex items-center justify-center text-white font-medium text-lg shrink-0";
                                      fallbackDiv.textContent = currentUser.name.charAt(0).toUpperCase();
                                      e.target.parentNode.replaceChild(fallbackDiv, e.target);
                                    }}
                                  />
                                ) : (
                                  <div className="w-12 h-12 rounded-full bg-gradient-to-r from-amber-400 to-amber-600 flex items-center justify-center text-white font-medium text-lg shrink-0">
                                    {currentUser.name.charAt(0).toUpperCase()}
                                  </div>
                                )
                              )
                            }
                            {/* === END EDIT === */}
                            <div className="flex-1 relative">
                              <textarea
                                value={commentInput}
                                onChange={(e) =>
                                  setCommentInput(e.target.value)
                                }
                                placeholder="Nhập bình luận của bạn..."
                                className="w-full border border-gray-300 rounded-lg p-4 pr-12 focus:outline-none focus:ring-2 focus:ring-amber-500 resize-none"
                                rows={4}
                              ></textarea>
                              <button
                                type="submit"
                                disabled={
                                  isSubmittingComment || !commentInput.trim()
                                }
                                className="absolute right-4 bottom-4 text-amber-500 hover:text-amber-600 disabled:opacity-50 transition-colors"
                              >
                                {isSubmittingComment ? (
                                  <div className="h-6 w-6 border-2 border-amber-500 border-t-transparent rounded-full animate-spin"></div>
                                ) : (
                                  <IoSend className="text-2xl" />
                                )}
                              </button>
                            </div>
                          </div>
                        </form>
                      </div>
                    ) : (
                      <div className="bg-amber-50 border border-amber-200 rounded-xl p-6 mb-8">
                        <p className="font-medium text-center text-amber-700">
                          Vui lòng đăng nhập để bình luận
                        </p>
                      </div>
                    )}

                    {/* Danh sách bình luận */}
                    {comments.length > 0 ? (
                      <div className="space-y-6">
                        <h3 className="text-lg font-medium text-gray-800 mb-4">
                          Tất cả bình luận ({comments.length})
                        </h3>
                        <div className="space-y-6">
                          {comments.map((comment) => (
                            <div
                              key={comment.id}
                              className="border border-gray-100 rounded-xl p-6 hover:shadow-md transition-shadow"
                            >
                              <div className="flex items-start gap-4">
                                {/* === START EDIT: Display user avatar in comments list === */}
                                {
                                  comment.user.avatar ? (
                                    <img
                                      // Avatar đã là URL đầy đủ từ API
                                      src={processAvatar(comment.user.avatar)}
                                      alt={comment.user.name}
                                      className="w-12 h-12 rounded-full object-cover border border-gray-200" // Thêm object-cover và border
                                      // Fallback nếu ảnh lỗi
                                      onError={(e) => {
                                        e.target.onerror = null; // Tránh lặp vô hạn nếu ảnh fallback cũng lỗi
                                        // Thay bằng div hiển thị chữ cái đầu
                                        const fallbackDiv = document.createElement('div');
                                        fallbackDiv.className = "w-12 h-12 rounded-full bg-gradient-to-r from-amber-400 to-amber-600 flex items-center justify-center text-white font-medium text-lg";
                                        fallbackDiv.textContent = comment.user.name.charAt(0).toUpperCase();
                                        e.target.parentNode.replaceChild(fallbackDiv, e.target);
                                      }}
                                    />
                                  ) : (
                                    // Fallback nếu user không có avatar
                                    <div className="w-12 h-12 rounded-full bg-gradient-to-r from-amber-400 to-amber-600 flex items-center justify-center text-white font-medium text-lg">
                                      {comment.user.name.charAt(0).toUpperCase()}
                                    </div>
                                  )
                                }
                                {/* === END EDIT === */}
                                <div className="flex-1">
                                  <div className="flex flex-wrap items-center gap-2 mb-2">
                                    <h3 className="text-gray-800 font-medium">
                                      {comment.user.name}
                                    </h3>
                                    <span className="text-xs text-gray-400">
                                      • {formatDateTime(comment.created_at)}
                                    </span>
                                  </div>

                                  <p className="text-gray-600">
                                    {comment.content}
                                  </p>
                                </div>
                              </div>
                            </div>
                          ))}
                        </div>
                      </div>
                    ) : (
                      <div className="text-center py-12 bg-gray-50 rounded-xl">
                        <IoChatbubbleOutline className="mx-auto h-16 w-16 text-gray-300 mb-4" />
                        <p className="text-gray-500 font-medium">
                          Chưa có bình luận nào cho sản phẩm này.
                        </p>
                        <p className="text-gray-400 text-sm mt-1">
                          Hãy là người đầu tiên bình luận về sản phẩm này!
                        </p>
                      </div>
                    )}
                  </div>
                )}
              </div>
            </div>
          </div>
        )}
      </main>
    </>
  );
};

export default ProductDetail;