import { useState, useEffect, useRef } from "react";
import { Link } from "react-router-dom";
import {
  motion,
  useScroll,
  useTransform,
  useSpring,
  useMotionValue,
  useInView,
  useAnimation,
} from "framer-motion";
import { BsStarFill, BsStarHalf, BsStar } from "react-icons/bs";
import axiosInstance from "../../../utils/axiosConfig";
import Banner from "../../../components/Banner";
import LoadingScreen from "../../../components/LoadingScreen";
import { IoCartOutline, IoStar, IoSparkles } from "react-icons/io5";
import { HiOutlineArrowNarrowRight } from "react-icons/hi";
import Popup from "../../../components/Popup";
import {
  FaLeaf,
  FaTree,
  FaSeedling,
  FaRobot,
  FaFire,
  FaLightbulb,
} from "react-icons/fa";
import axios from "axios";

// Helper function để theo dõi hoạt động khi ChatBot chưa tải
const trackActivity = (type, data) => {
  try {
    const storedActivities = localStorage.getItem("userActivities") || "[]";
    const activities = JSON.parse(storedActivities);
    activities.unshift({ ...data, type, timestamp: new Date().toISOString() });
    localStorage.setItem(
      "userActivities",
      JSON.stringify(activities.slice(0, 30))
    );
    // Kích hoạt sự kiện nếu có thể
    if (typeof CustomEvent === "function") {
      const event = new CustomEvent("userActivityUpdate");
      window.dispatchEvent(event);
    }
  } catch (error) {
    console.error("Error tracking activity:", error);
  }
};

const Homes = () => {
  const [products, setProducts] = useState([]);
  const [bestSellers, setBestSellers] = useState([]);
  const [posts, setPosts] = useState([]);
  const [aiRecommendations, setAiRecommendations] = useState([]);
  const [hasActivityData, setHasActivityData] = useState(false);
  const [isLoading, setIsLoading] = useState(true);
  const dataFetchedRef = useRef(false);
  // Thêm state lưu trữ thông tin đánh giá
  const [productRatings, setProductRatings] = useState({});

  const bannerRef = useRef(null);
  const bannerInView = useInView(bannerRef, { once: false, amount: 0.5 });
  const bannerControls = useAnimation();
  const { scrollY } = useScroll();

  // Hiệu ứng parallax cho banner
  const y = useTransform(scrollY, [0, 1000], [0, 300]);
  const opacity = useTransform(scrollY, [0, 300], [1, 0.3]);
  const scale = useTransform(scrollY, [0, 500], [1, 1.2]);
  const springY = useSpring(y, { stiffness: 50, damping: 15 });
  const springOpacity = useSpring(opacity, { stiffness: 50, damping: 15 });
  const springScale = useSpring(scale, { stiffness: 50, damping: 15 });

  // Lắng nghe cập nhật hoạt động người dùng và sinh gợi ý sản phẩm
  useEffect(() => {
    // Function để phân tích hoạt động người dùng và tạo gợi ý
    const analyzeUserActivities = () => {
      try {
        const storedActivities = localStorage.getItem("userActivities");
        if (!storedActivities) {
          setHasActivityData(false);
          return;
        }

        const activities = JSON.parse(storedActivities);

        if (activities.length === 0) {
          setHasActivityData(false);
          return;
        }

        setHasActivityData(true);

        // Phân tích hoạt động người dùng để tạo gợi ý
        const viewedProducts = activities
          .filter((activity) => activity.type === "view_product")
          .map((activity) => ({
            id: activity.productId,
            name: activity.productName,
            category: activity.category,
          }));

        const searchedKeywords = activities
          .filter((activity) => activity.type === "search_product")
          .map((activity) => activity.keyword);

        const cartProducts = activities
          .filter((activity) => activity.type === "add_to_cart")
          .map((activity) => ({
            id: activity.productId,
            name: activity.productName,
            category: activity.category,
          }));

        // Gửi request để nhận gợi ý
        fetchRecommendations(viewedProducts, searchedKeywords, cartProducts);
      } catch (error) {
        console.error("Error analyzing user activities:", error);
      }
    };

    // Lắng nghe sự kiện khi có hoạt động mới
    const handleActivityUpdate = () => {
      analyzeUserActivities();
    };

    // Đăng ký lắng nghe sự kiện
    window.addEventListener("userActivityUpdate", handleActivityUpdate);

    // Phân tích lần đầu khi component mount
    analyzeUserActivities();

    // Cleanup listener
    return () => {
      window.removeEventListener("userActivityUpdate", handleActivityUpdate);
    };
  }, []);

  // Thêm useEffect mới để kiểm tra trực tiếp localStorage mỗi khi component re-render
  useEffect(() => {
    const checkUserActivities = () => {
      try {
        const storedActivities = localStorage.getItem("userActivities");
        if (storedActivities) {
          const activities = JSON.parse(storedActivities);
          if (activities.length > 0) {
            setHasActivityData(true);

            // Phân tích hoạt động người dùng để tạo gợi ý
            const viewedProducts = activities
              .filter((activity) => activity.type === "view_product")
              .map((activity) => ({
                id: activity.productId,
                name: activity.productName,
                category: activity.category,
              }));

            const searchedKeywords = activities
              .filter((activity) => activity.type === "search_product")
              .map((activity) => activity.keyword);

            const cartProducts = activities
              .filter((activity) => activity.type === "add_to_cart")
              .map((activity) => ({
                id: activity.productId,
                name: activity.productName,
                category: activity.category,
              }));

            // Gửi request để nhận gợi ý
            fetchRecommendations(
              viewedProducts,
              searchedKeywords,
              cartProducts
            );
          }
        }
      } catch (error) {
        console.error("Error checking user activities:", error);
      }
    };

    // Kiểm tra mỗi khi component mount hoặc được render lại
    checkUserActivities();
  }, []);

  // Function gọi API để lấy sản phẩm gợi ý dựa trên hoạt động người dùng
  const fetchRecommendations = async (
    viewedProducts,
    searchedKeywords,
    cartProducts
  ) => {
    try {
      const response = await axiosInstance.post("ai-recommendations", {
        viewedProducts,
        searchedKeywords,
        cartProducts,
      });

      if (
        response.data.status === "success" &&
        Array.isArray(response.data.recommendations)
      ) {
        setAiRecommendations(response.data.recommendations);
      }
    } catch (error) {
      console.log("Lỗi khi gọi API gợi ý:", error);

      // Fallback: Phân tích nâng cao khi API chưa hoạt động
      if (products.length > 0) {
        // Nếu có hoạt động người dùng, tạo gợi ý dựa trên hoạt động
        if (
          viewedProducts.length > 0 ||
          searchedKeywords.length > 0 ||
          cartProducts.length > 0
        ) {
          // 1. Thu thập các danh mục đã quan tâm
          const interestedCategories = [
            ...new Set([
              ...viewedProducts.map((p) => p.category),
              ...cartProducts.map((p) => p.category),
            ]),
          ].filter(Boolean);

          // 2. Thu thập các ID sản phẩm đã xem để loại trừ
          const viewedIds = viewedProducts.map((p) => p.id);

          // 3. Tìm các sản phẩm liên quan đến từ khóa tìm kiếm
          let keywordRelatedProducts = [];
          if (searchedKeywords.length > 0) {
            const keywords = searchedKeywords
              .join(" ")
              .toLowerCase()
              .split(" ");
            keywordRelatedProducts = products.filter(
              (p) =>
                keywords.some(
                  (keyword) =>
                    p.name.toLowerCase().includes(keyword) ||
                    (p.description &&
                      p.description.toLowerCase().includes(keyword))
                ) && !viewedIds.includes(p.id)
            );
          }

          // 4. Tìm các sản phẩm cùng danh mục
          let categoryRelatedProducts = [];
          if (interestedCategories.length > 0) {
            categoryRelatedProducts = products.filter(
              (p) =>
                interestedCategories.includes(p.category) &&
                !viewedIds.includes(p.id) &&
                !keywordRelatedProducts.some((kp) => kp.id === p.id)
            );
          }

          // 5. Kết hợp kết quả, ưu tiên sản phẩm theo từ khóa trước
          const combinedResults = [
            ...keywordRelatedProducts,
            ...categoryRelatedProducts,
          ].slice(0, 4); // Giới hạn kết quả

          // 6. Nếu vẫn thiếu sản phẩm, bổ sung thêm sản phẩm ngẫu nhiên
          if (combinedResults.length < 4) {
            const randomProducts = products
              .filter(
                (p) =>
                  !viewedIds.includes(p.id) &&
                  !combinedResults.some((cp) => cp.id === p.id)
              )
              .sort(() => 0.5 - Math.random())
              .slice(0, 4 - combinedResults.length);

            combinedResults.push(...randomProducts);
          }

          setAiRecommendations(combinedResults);
        }
      }
    }
  };

  // Hiệu ứng vị trí mặt trời (gradient)
  const mouseX = useMotionValue(0);
  const mouseY = useMotionValue(0);
  const rotateX = useTransform(mouseY, [-300, 300], [5, -5]);
  const rotateY = useTransform(mouseX, [-300, 300], [-5, 5]);
  const gradientX = useTransform(mouseX, [-300, 300], ["30%", "70%"]);
  const gradientY = useTransform(mouseY, [-300, 300], ["30%", "70%"]);

  useEffect(() => {
    if (bannerInView) {
      bannerControls.start({
        scale: 1,
        opacity: 1,
        transition: {
          type: "spring",
          stiffness: 60,
          damping: 20,
          delay: 0.2,
        },
      });
    }
  }, [bannerInView, bannerControls]);

  const handleMouseMove = (e) => {
    const { clientX, clientY } = e;
    const boundingRect = e.currentTarget.getBoundingClientRect();
    const centerX = boundingRect.width / 2;
    const centerY = boundingRect.height / 2;
    mouseX.set(clientX - boundingRect.left - centerX);
    mouseY.set(clientY - boundingRect.top - centerY);
  };

  useEffect(() => {
    if (dataFetchedRef.current) return;

    const fetchAllData = async () => {
      setIsLoading(true); // Bắt đầu loading
      try {
        dataFetchedRef.current = true;
        const [productsResponse, bestSellersResponse, postsResponse] =
          await Promise.all([
            axiosInstance.get("/products"),
            axiosInstance.get("/best-sellers"),
            axiosInstance.get("/posts"),
          ]);

        // Xử lý products
        let processedProducts = [];
        if (
          productsResponse.data.status === "success" &&
          Array.isArray(productsResponse.data.data.data)
        ) {
          processedProducts = productsResponse.data.data.data.map((product) => {
            // Tạo bản sao sâu của sản phẩm để tránh tham chiếu
            const newProduct = JSON.parse(JSON.stringify(product));

            // Chuẩn hóa has_variants thành boolean
            newProduct.has_variants = Boolean(
              newProduct.has_variants === 1 ||
              newProduct.has_variants === true ||
              newProduct.has_variants === "1" ||
              newProduct.has_variants === "true"
            );

            // Đảm bảo variants là một mảng
            if (!Array.isArray(newProduct.variants)) {
              newProduct.variants = [];
            }

            // Tính price_range
            if (newProduct.has_variants && newProduct.variants.length > 0) {
              // Lọc ra các giá trị hợp lệ
              const validVariants = newProduct.variants.filter(
                (v) => v && typeof v === "object"
              );

              if (validVariants.length > 0) {
                const prices = validVariants
                  .map((v) => parseFloat(v.price) || 0)
                  .filter((p) => p > 0);

                const discountPrices = validVariants
                  .map((v) => {
                    const discount = parseFloat(v.discount_price) || 0;
                    return discount > 0 ? discount : 0;
                  })
                  .filter((p) => p > 0);

                // Chỉ tính nếu có ít nhất một giá hợp lệ
                if (prices.length > 0) {
                  newProduct.price_range = {
                    min: Math.min(...prices),
                    max: Math.max(...prices),
                    min_discount:
                      discountPrices.length > 0
                        ? Math.min(...discountPrices)
                        : 0,
                    max_discount:
                      discountPrices.length > 0
                        ? Math.max(...discountPrices)
                        : 0,
                  };
                } else {
                  // Fallback nếu không có giá hợp lệ
                  newProduct.price_range = {
                    min: parseFloat(newProduct.price) || 0,
                    max: parseFloat(newProduct.price) || 0,
                    min_discount: parseFloat(newProduct.discount_price) || 0,
                    max_discount: parseFloat(newProduct.discount_price) || 0,
                  };
                }
              } else {
                // Không có variants hợp lệ
                newProduct.price_range = {
                  min: parseFloat(newProduct.price) || 0,
                  max: parseFloat(newProduct.price) || 0,
                  min_discount: parseFloat(newProduct.discount_price) || 0,
                  max_discount: parseFloat(newProduct.discount_price) || 0,
                };
              }
            } else {
              // Sản phẩm không có variants, nhưng vẫn tạo price_range để đồng nhất
              newProduct.price_range = {
                min: parseFloat(newProduct.price) || 0,
                max: parseFloat(newProduct.price) || 0,
                min_discount: parseFloat(newProduct.discount_price) || 0,
                max_discount: parseFloat(newProduct.discount_price) || 0,
              };
            }

            // Đảm bảo thuộc tính price_range luôn tồn tại
            if (!newProduct.price_range) {
              newProduct.price_range = {
                min: parseFloat(newProduct.price) || 0,
                max: parseFloat(newProduct.price) || 0,
                min_discount: parseFloat(newProduct.discount_price) || 0,
                max_discount: parseFloat(newProduct.discount_price) || 0,
              };
            }

            return newProduct;
          });
          console.log("Processed Products:", processedProducts);
          setProducts(processedProducts);
        } else {
          console.log("Lỗi tải products hoặc dữ liệu không hợp lệ");
          setProducts([]); // Reset nếu lỗi
        }

        // Xử lý bestSellers
        let processedBestSellers = [];
        if (
          bestSellersResponse.data.status === "success" &&
          Array.isArray(bestSellersResponse.data.data)
        ) {
          processedBestSellers = bestSellersResponse.data.data.map(
            (product) => {
              // Tạo bản sao sâu của sản phẩm để tránh tham chiếu
              const newProduct = JSON.parse(JSON.stringify(product));

              // Chuẩn hóa has_variants thành boolean
              newProduct.has_variants = Boolean(
                newProduct.has_variants === 1 ||
                newProduct.has_variants === true ||
                newProduct.has_variants === "1" ||
                newProduct.has_variants === "true"
              );

              // Đảm bảo variants là một mảng
              if (!Array.isArray(newProduct.variants)) {
                newProduct.variants = [];
              }

              // Tính price_range
              if (newProduct.has_variants && newProduct.variants.length > 0) {
                // Lọc ra các giá trị hợp lệ
                const validVariants = newProduct.variants.filter(
                  (v) => v && typeof v === "object"
                );

                if (validVariants.length > 0) {
                  const prices = validVariants
                    .map((v) => parseFloat(v.price) || 0)
                    .filter((p) => p > 0);

                  const discountPrices = validVariants
                    .map((v) => {
                      const discount = parseFloat(v.discount_price) || 0;
                      return discount > 0 ? discount : 0;
                    })
                    .filter((p) => p > 0);

                  // Chỉ tính nếu có ít nhất một giá hợp lệ
                  if (prices.length > 0) {
                    newProduct.price_range = {
                      min: Math.min(...prices),
                      max: Math.max(...prices),
                      min_discount:
                        discountPrices.length > 0
                          ? Math.min(...discountPrices)
                          : 0,
                      max_discount:
                        discountPrices.length > 0
                          ? Math.max(...discountPrices)
                          : 0,
                    };
                  } else {
                    // Fallback nếu không có giá hợp lệ
                    newProduct.price_range = {
                      min: parseFloat(newProduct.price) || 0,
                      max: parseFloat(newProduct.price) || 0,
                      min_discount: parseFloat(newProduct.discount_price) || 0,
                      max_discount: parseFloat(newProduct.discount_price) || 0,
                    };
                  }
                } else {
                  // Không có variants hợp lệ
                  newProduct.price_range = {
                    min: parseFloat(newProduct.price) || 0,
                    max: parseFloat(newProduct.price) || 0,
                    min_discount: parseFloat(newProduct.discount_price) || 0,
                    max_discount: parseFloat(newProduct.discount_price) || 0,
                  };
                }
              } else {
                // Sản phẩm không có variants, nhưng vẫn tạo price_range để đồng nhất
                newProduct.price_range = {
                  min: parseFloat(newProduct.price) || 0,
                  max: parseFloat(newProduct.price) || 0,
                  min_discount: parseFloat(newProduct.discount_price) || 0,
                  max_discount: parseFloat(newProduct.discount_price) || 0,
                };
              }

              // Đảm bảo thuộc tính price_range luôn tồn tại
              if (!newProduct.price_range) {
                newProduct.price_range = {
                  min: parseFloat(newProduct.price) || 0,
                  max: parseFloat(newProduct.price) || 0,
                  min_discount: parseFloat(newProduct.discount_price) || 0,
                  max_discount: parseFloat(newProduct.discount_price) || 0,
                };
              }

              return newProduct;
            }
          );
          console.log("Processed Best Sellers:", processedBestSellers);
          setBestSellers(processedBestSellers);
        } else {
          console.log("Lỗi tải best sellers hoặc dữ liệu không hợp lệ");
          setBestSellers([]); // Reset nếu lỗi
        }

        // Xử lý posts
        if (
          postsResponse.data.status === "success" &&
          Array.isArray(postsResponse.data.data)
        ) {
          setPosts(postsResponse.data.data);
        } else {
          console.log("Lỗi tải posts hoặc dữ liệu không hợp lệ");
          setPosts([]);
        }
      } catch (error) {
        dataFetchedRef.current = false; // Đặt lại trạng thái khi có lỗi để có thể thử lại
        console.error("Lỗi nghiêm trọng khi tải dữ liệu trang chủ:", error);
        setProducts([]);
        setBestSellers([]);
        setPosts([]);
      } finally {
        setIsLoading(false); // Kết thúc loading bất kể thành công hay lỗi
      }
    };

    fetchAllData();
  }, []); // Dependency rỗng đảm bảo chỉ chạy 1 lần khi mount

  // Tạo gợi ý mặc định khi có sản phẩm
  useEffect(() => {
    if (
      products.length > 0 &&
      aiRecommendations.length === 0
    ) {
      // Tạo gợi ý ngẫu nhiên từ các sản phẩm
      const randomRecommendations = [...products]
        .sort(() => 0.5 - Math.random())
        .slice(0, 4);
      setAiRecommendations(randomRecommendations);
    }
  }, [products, aiRecommendations]); // Bỏ dependency hasActivityData

  // Animation variants
  const fadeInUp = {
    hidden: { opacity: 0, y: 60 },
    visible: {
      opacity: 1,
      y: 0,
      transition: {
        duration: 1.2,
        ease: [0.22, 0.03, 0.21, 1], // chuyển động cực kỳ mượt
      },
    },
  };

  const staggerContainer = {
    hidden: { opacity: 0 },
    visible: {
      opacity: 1,
      transition: {
        staggerChildren: 0.5,
        delayChildren: 0.6,
      },
    },
  };

  const scaleIn = {
    hidden: { scale: 0.92, opacity: 0 },
    visible: {
      scale: 1,
      opacity: 1,
      transition: {
        duration: 1.2,
        ease: [0.22, 1.2, 0.36, 1], // easeOutBack với nhẹ hơn
      },
    },
  };

  // Banner text variants
  const bannerTextVariants = {
    hidden: {
      opacity: 0,
      y: 100,
    },
    visible: {
      opacity: 1,
      y: 0,
      transition: {
        duration: 1.5,
        ease: "easeOut",
        delay: 0.3,
      },
    },
  };

  // Hiệu ứng shine cho banner texture
  const shineVariants = {
    initial: {
      background:
        "linear-gradient(45deg, rgba(255,255,255,0) 0%, rgba(255,255,255,0) 75%, rgba(255,255,255,0.5) 90%, rgba(255,255,255,0) 100%)",
      backgroundSize: "200% 200%",
      backgroundPosition: "0% 0%",
    },
    animate: {
      backgroundPosition: "200% 200%",
      transition: {
        duration: 15,
        ease: "linear",
        repeat: Infinity,
      },
    },
  };

  // Render sao đánh giá
  const renderStars = (rating) => {
    const stars = [];
    const totalStars = 5;

    for (let i = 1; i <= totalStars; i++) {
      if (i <= rating) {
        stars.push(<BsStarFill key={i} className="text-yellow-500" />);
      } else if (i - 0.5 <= rating) {
        stars.push(<BsStarHalf key={i} className="text-yellow-500" />);
      } else {
        stars.push(<BsStar key={i} className="text-yellow-500" />);
      }
    }

    return <div className="flex space-x-1">{stars}</div>;
  };

  // Thêm useEffect để tải thông tin đánh giá cho sản phẩm
  useEffect(() => {
    const fetchRatings = async (productList) => {
      if (!productList || productList.length === 0) return;

      const ratingsData = { ...productRatings }; // Copy state hiện tại

      // Lọc ra các sản phẩm chưa có đánh giá
      const productsToFetch = productList.filter(p => !ratingsData[p.id]);

      if (productsToFetch.length === 0) return; // Không có sản phẩm mới cần tải

      // Tạo mảng các promise để tải đánh giá
      const ratingPromises = productsToFetch.map(product =>
        axios.get(`http://localhost:8000/api/products/${product.id}/reviews`)
          .then(response => {
            if (response.data.success && Array.isArray(response.data.data)) {
              const reviews = response.data.data;
              if (reviews.length > 0) {
                const totalRating = reviews.reduce((sum, review) => sum + review.rating, 0);
                const avgRating = (totalRating / reviews.length).toFixed(1);
                ratingsData[product.id] = {
                  average: parseFloat(avgRating),
                  count: reviews.length
                };
              } else {
                ratingsData[product.id] = { average: 0, count: 0 };
              }
            }
          })
          .catch(error => {
            console.error(`Error fetching ratings for product ${product.id}:`, error);
            ratingsData[product.id] = { average: 0, count: 0 };
          })
      );

      // Đợi tất cả promise hoàn thành
      await Promise.all(ratingPromises);
      setProductRatings(ratingsData);
    };

    // Tải đánh giá cho tất cả các loại sản phẩm
    if (products.length > 0) fetchRatings(products);
    if (bestSellers.length > 0) fetchRatings(bestSellers);
    if (aiRecommendations.length > 0) fetchRatings(aiRecommendations);

  }, [products, bestSellers, aiRecommendations]);

  return (
    <>
      {isLoading && <LoadingScreen />}
      {/* Banner chính */}
      <motion.div
        initial={{ opacity: 0 }}
        animate={{ opacity: 1 }}
        transition={{ duration: 0.8 }}
        className="max-w-[1600px] mx-auto px-4 mt-4"
      >
        <div className="rounded-2xl overflow-hidden">
          <Banner />
        </div>
      </motion.div>

      {/* Giới thiệu nhiệm vụ - Mở đầu trang chủ */}
      <motion.section
        className="py-16 bg-gradient-to-b from-amber-50 to-white"
        initial={{ opacity: 0, y: 20 }}
        animate={{ opacity: 1, y: 0 }}
        transition={{ duration: 0.8, delay: 0.3 }}
      >
        <div className="max-w-6xl mx-auto px-4">
          <div className="text-center max-w-3xl mx-auto mb-12">
            <motion.div
              className="flex justify-center mb-5"
              initial={{ scale: 0, opacity: 0 }}
              animate={{ scale: 1, opacity: 1 }}
              transition={{ duration: 0.8, delay: 0.5 }}
            >
              <span className="p-3 bg-amber-100 rounded-full text-amber-600">
                <FaLeaf size={28} />
              </span>
            </motion.div>
            <motion.h2
              className="text-3xl md:text-4xl font-bold mb-4 text-gray-800"
              initial={{ opacity: 0, y: 20 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ duration: 0.8, delay: 0.6 }}
            >
              Nội thất bền vững cho ngôi nhà của bạn
            </motion.h2>
            <motion.p
              className="text-gray-600 text-lg"
              initial={{ opacity: 0, y: 20 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ duration: 0.8, delay: 0.7 }}
            >
              Tại Eco-Furnish, chúng tôi cam kết cung cấp sản phẩm nội thất được
              làm từ nguyên liệu tự nhiên, thân thiện với môi trường và mang đến
              không gian sống xanh, bền vững cho mọi gia đình.
            </motion.p>
          </div>

          {/* Các đặc điểm */}
          <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
            {[
              {
                icon: <FaTree className="text-green-600 mb-1" size={24} />,
                title: "Nguyên liệu bền vững",
                description:
                  "Sử dụng gỗ từ các khu rừng được quản lý bền vững, đảm bảo nguồn tài nguyên dài hạn.",
              },
              {
                icon: <FaLeaf className="text-green-600 mb-1" size={24} />,
                title: "Thân thiện môi trường",
                description:
                  "Quy trình sản xuất thân thiện với môi trường, giảm thiểu khí thải và chất thải.",
              },
              {
                icon: <FaSeedling className="text-green-600 mb-1" size={24} />,
                title: "Không gian sống xanh",
                description:
                  "Thiết kế hiện đại kết hợp với chất liệu tự nhiên tạo nên không gian sống xanh mát.",
              },
            ].map((feature, index) => (
              <motion.div
                key={index}
                className="bg-white p-8 rounded-2xl shadow-sm text-center border border-gray-100"
                initial={{ opacity: 0, y: 30 }}
                whileInView={{ opacity: 1, y: 0 }}
                transition={{ duration: 0.6, delay: index * 0.2 }}
                viewport={{ once: true }}
              >
                <div className="inline-flex items-center justify-center w-16 h-16 rounded-full bg-amber-50 mb-5">
                  {feature.icon}
                </div>
                <h3 className="text-xl font-semibold mb-3 text-gray-800">
                  {feature.title}
                </h3>
                <p className="text-gray-600">{feature.description}</p>
              </motion.div>
            ))}
          </div>
        </div>
      </motion.section>
      {/* Sản phẩm được AI gợi ý */}
      {aiRecommendations.length > 0 && (
        <motion.section
          initial="hidden"
          whileInView="visible"
          viewport={{ once: true, amount: 0.05 }}
          variants={fadeInUp}
          className="py-20 bg-gradient-to-b from-blue-50 to-white"
        >
          <div className="max-w-6xl mx-auto px-4">
            <div className="flex flex-col md:flex-row justify-between items-start md:items-end mb-12">
              <div className="max-w-2xl">
                <motion.div
                  className="inline-flex items-center px-4 py-1 bg-blue-100 rounded-full text-blue-700 font-medium text-sm mb-4"
                  initial={{ opacity: 0, x: -20 }}
                  whileInView={{ opacity: 1, x: 0 }}
                  transition={{ duration: 0.6 }}
                  viewport={{ once: true }}
                >
                  <FaLightbulb className="mr-2" /> GỢI Ý SẢN PHẨM
                </motion.div>
                <motion.h2
                  className="text-3xl md:text-4xl font-bold mb-4 text-gray-800"
                  initial={{ opacity: 0, y: 20 }}
                  whileInView={{ opacity: 1, y: 0 }}
                  transition={{ duration: 0.8 }}
                  viewport={{ once: true }}
                >
                  Sản phẩm được <span className="text-blue-500">gợi ý</span>
                </motion.h2>
                <motion.p
                  className="text-gray-600"
                  initial={{ opacity: 0, y: 20 }}
                  whileInView={{ opacity: 1, y: 0 }}
                  transition={{ duration: 0.8, delay: 0.2 }}
                  viewport={{ once: true }}
                >
                  Dựa trên hoạt động gần đây của bạn, Trang web của chúng tôi đã
                  chọn ra những sản phẩm bạn có thể quan tâm.
                </motion.p>
              </div>
              <motion.div
                initial={{ opacity: 0, x: 20 }}
                whileInView={{ opacity: 1, x: 0 }}
                transition={{ duration: 0.8 }}
                viewport={{ once: true }}
              >
                <Link
                  to="/products"
                  className="inline-flex items-center text-blue-600 font-medium hover:text-blue-700 group mt-6 md:mt-0"
                >
                  <span>Khám phá thêm</span>
                  <HiOutlineArrowNarrowRight className="ml-2 group-hover:translate-x-1 transition-transform w-5 h-5" />
                </Link>
              </motion.div>
            </div>

            <motion.div
              className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6"
              variants={staggerContainer}
              initial="hidden"
              whileInView="visible"
              viewport={{ once: true, amount: 0.1 }}
            >
              {aiRecommendations.map((product, index) => (
                <motion.div
                  key={product.id}
                  variants={fadeInUp}
                  custom={index}
                  whileHover={{
                    y: -12,
                    transition: { duration: 0.3, ease: "easeOut" },
                  }}
                  className="bg-white rounded-xl shadow-sm hover:shadow-xl transition-all duration-300 overflow-hidden border border-gray-100 group relative z-0"
                >
                  <Link
                    to={`product-detail/${product.id}`}
                    className="block absolute inset-0 z-10"
                    onClick={() => {
                      // Sử dụng window.trackProductView nếu có, nếu không thì dùng helper function
                      if (window.trackProductView) {
                        window.trackProductView(
                          product.id,
                          product.name,
                          product.category
                        );
                      } else {
                        trackActivity("view_product", {
                          productId: product.id,
                          productName: product.name,
                          category: product.category,
                        });
                      }
                    }}
                  >
                    <span className="sr-only">Xem chi tiết {product.name}</span>
                  </Link>
                  <div className="relative">
                    <div className="relative overflow-hidden">
                      <div className="aspect-square overflow-hidden">
                        <motion.img
                          src={
                            product.image_thumnail
                              ? product.image_thumnail.startsWith("http")
                                ? product.image_thumnail
                                : `http://localhost:8000/storage/${product.image_thumnail}`
                              : "https://via.placeholder.com/300x300?text=No+Image"
                          }
                          alt={product.name}
                          className="w-full h-full object-cover group-hover:scale-110 transition-all duration-700 bg-gray-100"
                          loading="lazy"
                          onError={(e) => {
                            e.target.onerror = null;
                            e.target.src = "/images/no-image.png";
                          }}
                          initial={{ scale: 1.2, y: 20 }}
                          animate={{ scale: 1, y: 0 }}
                          transition={{ duration: 0.8, delay: index * 0.1 }}
                        />
                      </div>

                      {/* Nhãn AI Gợi ý */}
                      <motion.div
                        className="absolute top-3 right-3 bg-blue-500 text-white text-xs font-bold px-3 py-1 rounded-full z-20 flex items-center"
                        initial={{ opacity: 0, scale: 0.5 }}
                        whileInView={{ opacity: 1, scale: 1 }}
                        transition={{
                          type: "spring",
                          stiffness: 500,
                          delay: 0.5 + index * 0.1,
                        }}
                      >
                        <FaLightbulb className="mr-1" /> Gợi ý
                      </motion.div>

                      {/* Nút mua nhanh */}
                      <div className="absolute bottom-3 right-3 opacity-0 group-hover:opacity-100 transition-opacity duration-300 z-20">
                        <motion.button
                          className="bg-white text-blue-500 p-3 rounded-full shadow-md hover:bg-blue-500 hover:text-white transition-all duration-300"
                          whileHover={{ scale: 1.1 }}
                          whileTap={{ scale: 0.9 }}
                          onClick={(e) => {
                            e.preventDefault();
                            e.stopPropagation();
                            // Sử dụng window.trackAddToCart nếu có, nếu không thì dùng helper function
                            if (window.trackAddToCart) {
                              window.trackAddToCart(
                                product.id,
                                product.name,
                                product.category
                              );
                            } else {
                              trackActivity("add_to_cart", {
                                productId: product.id,
                                productName: product.name,
                                category: product.category,
                              });
                            }
                            window.location.href = `/cart/add/${product.id}`;
                          }}
                        >
                          <IoCartOutline className="text-xl" />
                        </motion.button>
                      </div>
                    </div>

                    <div className="p-5">
                      {/* Sao đánh giá */}
                      <div className="flex items-center mb-2">
                        {[1, 2, 3, 4, 5].map((star) => {
                          // Lấy đánh giá từ state productRatings
                          const rating = productRatings[product.id]?.average || 0;
                          return (
                            <IoStar
                              key={star}
                              className={`${star <= Math.round(rating)
                                ? "text-blue-400" : "text-gray-300"
                                } w-4 h-4`}
                            />
                          );
                        })}
                        <span className="text-gray-500 text-sm ml-2">
                          {/* Hiển thị số đánh giá từ state */}
                          {productRatings[product.id]
                            ? productRatings[product.id].average.toFixed(1)
                            : "0.0"}
                        </span>
                      </div>

                      <h3 className="font-semibold text-gray-800 mb-1 group-hover:text-blue-500 transition-colors">
                        {product.name}
                      </h3>

                      <p className="text-gray-500 text-sm mb-3 line-clamp-2">
                        Phù hợp với sở thích của bạn
                      </p>

                      {Boolean(product.has_variants) ? (
                        // Sản phẩm có biến thể
                        <div className="relative">
                          {product.price_range &&
                            product.price_range.min_discount > 0 ? (
                            // Có giá khuyến mãi
                            <div className="flex flex-col">
                              <span className="font-semibold text-amber-600 text-lg">
                                {new Intl.NumberFormat("vi-VN", {
                                  style: "currency",
                                  currency: "VND",
                                }).format(product.price_range.min_discount)}
                                {product.price_range.max_discount > 0 &&
                                  product.price_range.max_discount !==
                                  product.price_range.min_discount &&
                                  ` - ${new Intl.NumberFormat("vi-VN", {
                                    style: "currency",
                                    currency: "VND",
                                  }).format(product.price_range.max_discount)}`}
                              </span>
                              <span className="text-gray-400 line-through text-sm">
                                {new Intl.NumberFormat("vi-VN", {
                                  style: "currency",
                                  currency: "VND",
                                }).format(product.price_range.min)}
                              </span>
                            </div>
                          ) : (
                            // Không có khuyến mãi
                            <span className="font-semibold text-amber-600 text-lg">
                              {new Intl.NumberFormat("vi-VN", {
                                style: "currency",
                                currency: "VND",
                              }).format(
                                product.price_range && product.price_range.min
                                  ? product.price_range.min
                                  : product.price || 0
                              )}
                              {product.price_range &&
                                product.price_range.max &&
                                product.price_range.min &&
                                product.price_range.max !==
                                product.price_range.min &&
                                ` - ${new Intl.NumberFormat("vi-VN", {
                                  style: "currency",
                                  currency: "VND",
                                }).format(product.price_range.max)}`}
                            </span>
                          )}
                        </div>
                      ) : (
                        // Sản phẩm thường
                        <div className="z-20 relative">
                          {product.discount_price ? (
                            // Có giá khuyến mãi
                            <div className="flex flex-col">
                              <span className="font-semibold text-blue-600 text-lg">
                                {new Intl.NumberFormat("vi-VN", {
                                  style: "currency",
                                  currency: "VND",
                                }).format(product.discount_price)}
                              </span>
                              <span className="text-gray-400 line-through text-sm">
                                {new Intl.NumberFormat("vi-VN", {
                                  style: "currency",
                                  currency: "VND",
                                }).format(product.price)}
                              </span>
                            </div>
                          ) : (
                            // Không có khuyến mãi
                            <span className="font-semibold text-blue-600 text-lg">
                              {new Intl.NumberFormat("vi-VN", {
                                style: "currency",
                                currency: "VND",
                              }).format(product.price || 0)}
                            </span>
                          )}
                        </div>
                      )}
                    </div>
                  </div>
                </motion.div>
              ))}
            </motion.div>
          </div>
        </motion.section>
      )}

      {/* Sản phẩm bán chạy */}
      <motion.section
        initial="hidden"
        whileInView="visible"
        viewport={{ once: true, amount: 0.05 }}
        variants={fadeInUp}
        className="py-20 bg-white"
      >
        <div className="max-w-6xl mx-auto px-4">
          <div className="flex flex-col md:flex-row justify-between items-start md:items-end mb-12">
            <div className="max-w-2xl">
              <motion.div
                className="inline-block px-4 py-1 bg-rose-100 rounded-full text-rose-700 font-medium text-sm mb-4"
                initial={{ opacity: 0, x: -20 }}
                whileInView={{ opacity: 1, x: 0 }}
                transition={{ duration: 0.6 }}
                viewport={{ once: true }}
              >
                <FaFire className="mr-1" /> BÁN CHẠY NHẤT
              </motion.div>
              <motion.h2
                className="text-3xl md:text-4xl font-bold mb-4 text-gray-800"
                initial={{ opacity: 0, y: 20 }}
                whileInView={{ opacity: 1, y: 0 }}
                transition={{ duration: 0.8 }}
                viewport={{ once: true }}
              >
                Sản phẩm <span className="text-rose-500">bán chạy nhất</span>
              </motion.h2>
              <motion.p
                className="text-gray-600"
                initial={{ opacity: 0, y: 20 }}
                whileInView={{ opacity: 1, y: 0 }}
                transition={{ duration: 0.8, delay: 0.2 }}
                viewport={{ once: true }}
              >
                Những sản phẩm được khách hàng yêu thích và chọn mua nhiều nhất
                tại Eco-Furnish.
              </motion.p>
            </div>
            <motion.div
              initial={{ opacity: 0, x: 20 }}
              whileInView={{ opacity: 1, x: 0 }}
              transition={{ duration: 0.8 }}
              viewport={{ once: true }}
            >
              <Link
                to="/products"
                className="inline-flex items-center text-rose-600 font-medium hover:text-rose-700 group mt-6 md:mt-0"
              >
                <span>Xem tất cả sản phẩm</span>
                <HiOutlineArrowNarrowRight className="ml-2 group-hover:translate-x-1 transition-transform w-5 h-5" />
              </Link>
            </motion.div>
          </div>

          <motion.div
            className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6"
            variants={staggerContainer}
            initial="hidden"
            whileInView="visible"
            viewport={{ once: true, amount: 0.1 }}
          >
            {bestSellers.length > 0 ? (
              bestSellers.map((product, index) => (
                <motion.div
                  key={product.id}
                  variants={fadeInUp}
                  custom={index}
                  whileHover={{
                    y: -12,
                    transition: { duration: 0.3, ease: "easeOut" },
                  }}
                  className="bg-white rounded-xl shadow-sm hover:shadow-xl transition-all duration-300 overflow-hidden border border-gray-100 group relative z-0"
                >
                  <Link
                    to={`product-detail/${product.id}`}
                    className="block absolute inset-0 z-10"
                    onClick={() => {
                      // Sử dụng window.trackProductView nếu có, nếu không thì dùng helper function
                      if (window.trackProductView) {
                        window.trackProductView(
                          product.id,
                          product.name,
                          product.category
                        );
                      } else {
                        trackActivity("view_product", {
                          productId: product.id,
                          productName: product.name,
                          category: product.category,
                        });
                      }
                    }}
                  >
                    <span className="sr-only">Xem chi tiết {product.name}</span>
                  </Link>
                  <div className="relative">
                    <div className="relative overflow-hidden">
                      <div className="aspect-square overflow-hidden">
                        <motion.img
                          src={
                            product.image_thumnail
                              ? product.image_thumnail.startsWith("http")
                                ? product.image_thumnail
                                : `http://localhost:8000/storage/${product.image_thumnail}`
                              : "https://via.placeholder.com/300x300?text=No+Image"
                          }
                          alt={product.name}
                          className="w-full h-full object-cover group-hover:scale-110 transition-all duration-700 bg-gray-100"
                          loading="lazy"
                          onError={(e) => {
                            e.target.onerror = null;
                            e.target.src = "/images/no-image.png";
                          }}
                          initial={{ scale: 1.2, y: 20 }}
                          animate={{ scale: 1, y: 0 }}
                          transition={{ duration: 0.8, delay: index * 0.1 }}
                        />
                      </div>

                      {/* Nhãn bán chạy */}
                      <motion.div
                        className="absolute top-3 right-3 bg-rose-500 text-white text-xs font-bold px-3 py-1 rounded-full z-20 flex items-center"
                        initial={{ opacity: 0, scale: 0.5 }}
                        whileInView={{ opacity: 1, scale: 1 }}
                        transition={{
                          type: "spring",
                          stiffness: 500,
                          delay: 0.5 + index * 0.1,
                        }}
                      >
                        <FaFire className="mr-1" /> BÁN CHẠY
                      </motion.div>

                      {/* Số thứ tự xếp hạng */}
                      <motion.div
                        className="absolute top-3 left-3 bg-rose-500 text-white w-8 h-8 rounded-full flex items-center justify-center font-bold shadow-lg z-20"
                        initial={{ opacity: 0, scale: 0.5 }}
                        whileInView={{ opacity: 1, scale: 1 }}
                        transition={{
                          type: "spring",
                          stiffness: 500,
                          delay: 0.7 + index * 0.1,
                        }}
                      >
                        {index + 1}
                      </motion.div>

                      {/* Nút mua nhanh */}
                      <div className="absolute bottom-3 right-3 opacity-0 group-hover:opacity-100 transition-opacity duration-300 z-20">
                        <motion.button
                          className="bg-white text-rose-500 p-3 rounded-full shadow-md hover:bg-rose-500 hover:text-white transition-all duration-300"
                          whileHover={{ scale: 1.1 }}
                          whileTap={{ scale: 0.9 }}
                          onClick={(e) => {
                            e.preventDefault();
                            e.stopPropagation();
                            // Sử dụng window.trackAddToCart nếu có, nếu không thì dùng helper function
                            if (window.trackAddToCart) {
                              window.trackAddToCart(
                                product.id,
                                product.name,
                                product.category
                              );
                            } else {
                              trackActivity("add_to_cart", {
                                productId: product.id,
                                productName: product.name,
                                category: product.category,
                              });
                            }
                            window.location.href = `/cart/add/${product.id}`;
                          }}
                        >
                          <IoCartOutline className="text-xl" />
                        </motion.button>
                      </div>
                    </div>

                    <div className="p-5">
                      {/* Sao đánh giá */}
                      <div className="flex items-center mb-2">
                        {[1, 2, 3, 4, 5].map((star) => {
                          // Lấy đánh giá từ state productRatings
                          const rating = productRatings[product.id]?.average || 0;
                          return (
                            <IoStar
                              key={star}
                              className={`${star <= Math.round(rating)
                                ? "text-rose-400" : "text-gray-300"
                                } w-4 h-4`}
                            />
                          );
                        })}
                        <span className="text-gray-500 text-sm ml-2">
                          {/* Hiển thị số đánh giá từ state */}
                          {productRatings[product.id]
                            ? productRatings[product.id].average.toFixed(1)
                            : "0.0"}
                        </span>
                      </div>

                      <h3 className="font-semibold text-gray-800 mb-1 group-hover:text-rose-500 transition-colors">
                        {product.name}
                      </h3>

                      <p className="text-gray-500 text-sm mb-3 line-clamp-2">
                        Sản phẩm bán chạy hàng đầu
                      </p>

                      {Boolean(product.has_variants) ? (
                        // Sản phẩm có biến thể
                        <div className="relative">
                          {product.price_range &&
                            product.price_range.min_discount > 0 ? (
                            // Có giá khuyến mãi
                            <div className="flex flex-col">
                              <span className="font-semibold text-rose-600 text-lg">
                                {new Intl.NumberFormat("vi-VN", {
                                  style: "currency",
                                  currency: "VND",
                                }).format(product.price_range.min_discount)}
                                {product.price_range.max_discount > 0 &&
                                  product.price_range.max_discount !==
                                  product.price_range.min_discount &&
                                  ` - ${new Intl.NumberFormat("vi-VN", {
                                    style: "currency",
                                    currency: "VND",
                                  }).format(product.price_range.max_discount)}`}
                              </span>
                              <span className="text-gray-400 line-through text-sm">
                                {new Intl.NumberFormat("vi-VN", {
                                  style: "currency",
                                  currency: "VND",
                                }).format(product.price_range.min)}
                              </span>
                            </div>
                          ) : (
                            // Không có khuyến mãi
                            <span className="font-semibold text-rose-600 text-lg">
                              {new Intl.NumberFormat("vi-VN", {
                                style: "currency",
                                currency: "VND",
                              }).format(
                                product.price_range && product.price_range.min
                                  ? product.price_range.min
                                  : product.price || 0
                              )}
                              {product.price_range &&
                                product.price_range.max &&
                                product.price_range.min &&
                                product.price_range.max !==
                                product.price_range.min &&
                                ` - ${new Intl.NumberFormat("vi-VN", {
                                  style: "currency",
                                  currency: "VND",
                                }).format(product.price_range.max)}`}
                            </span>
                          )}
                        </div>
                      ) : (
                        // Sản phẩm thường
                        <div className="z-20 relative">
                          {product.discount_price ? (
                            // Có giá khuyến mãi
                            <div className="flex flex-col">
                              <span className="font-semibold text-rose-600 text-lg">
                                {new Intl.NumberFormat("vi-VN", {
                                  style: "currency",
                                  currency: "VND",
                                }).format(product.discount_price)}
                              </span>
                              <span className="text-gray-400 line-through text-sm">
                                {new Intl.NumberFormat("vi-VN", {
                                  style: "currency",
                                  currency: "VND",
                                }).format(product.price)}
                              </span>
                            </div>
                          ) : (
                            // Không có khuyến mãi
                            <span className="font-semibold text-rose-600 text-lg">
                              {new Intl.NumberFormat("vi-VN", {
                                style: "currency",
                                currency: "VND",
                              }).format(product.price || 0)}
                            </span>
                          )}
                        </div>
                      )}
                    </div>
                  </div>
                </motion.div>
              ))
            ) : (
              <>
                {[1, 2, 3, 4].map((index) => (
                  <motion.div
                    key={index}
                    variants={fadeInUp}
                    custom={index}
                    className="bg-white rounded-xl shadow-sm overflow-hidden animate-pulse"
                  >
                    <div className="aspect-square bg-gray-200"></div>
                    <div className="p-5">
                      <div className="h-4 bg-gray-200 rounded w-3/4 mb-3"></div>
                      <div className="h-3 bg-gray-200 rounded w-full mb-2"></div>
                      <div className="h-3 bg-gray-200 rounded w-full mb-3"></div>
                      <div className="h-5 bg-gray-200 rounded w-1/3"></div>
                    </div>
                  </motion.div>
                ))}
              </>
            )}
          </motion.div>
        </div>
      </motion.section>

      {/* Sản phẩm mới */}
      <motion.section
        initial="hidden"
        whileInView="visible"
        viewport={{ once: true, amount: 0.05 }}
        variants={fadeInUp}
        className="py-20 bg-white"
      >
        <div className="max-w-6xl mx-auto px-4">
          <div className="flex flex-col md:flex-row justify-between items-start md:items-end mb-12">
            <div className="max-w-2xl">
              <motion.div
                className="inline-block px-4 py-1 bg-amber-100 rounded-full text-amber-700 font-medium text-sm mb-4"
                initial={{ opacity: 0, x: -20 }}
                whileInView={{ opacity: 1, x: 0 }}
                transition={{ duration: 0.6 }}
                viewport={{ once: true }}
              >
                MỚI NHẤT
              </motion.div>
              <motion.h2
                className="text-3xl md:text-4xl font-bold mb-4 text-gray-800"
                initial={{ opacity: 0, y: 20 }}
                whileInView={{ opacity: 1, y: 0 }}
                transition={{ duration: 0.8 }}
                viewport={{ once: true }}
              >
                Khám phá bộ sưu tập{" "}
                <span className="text-amber-500">mới nhất</span>
              </motion.h2>
              <motion.p
                className="text-gray-600"
                initial={{ opacity: 0, y: 20 }}
                whileInView={{ opacity: 1, y: 0 }}
                transition={{ duration: 0.8, delay: 0.2 }}
                viewport={{ once: true }}
              >
                Các sản phẩm nội thất được thiết kế hiện đại, tinh tế và chất
                lượng cao, mang đến không gian sống tiện nghi và sang trọng.
              </motion.p>
            </div>
            <motion.div
              initial={{ opacity: 0, x: 20 }}
              whileInView={{ opacity: 1, x: 0 }}
              transition={{ duration: 0.8 }}
              viewport={{ once: true }}
            >
              <Link
                to="/products"
                className="inline-flex items-center text-amber-600 font-medium hover:text-amber-700 group mt-6 md:mt-0"
              >
                <span>Xem tất cả sản phẩm</span>
                <HiOutlineArrowNarrowRight className="ml-2 group-hover:translate-x-1 transition-transform w-5 h-5" />
              </Link>
            </motion.div>
          </div>

          <motion.div
            className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6"
            variants={staggerContainer}
            initial="hidden"
            whileInView="visible"
            viewport={{ once: true, amount: 0.1 }}
          >
            {products.length > 0 ? (
              products.slice(0, 4).map((product, index) => (
                <motion.div
                  key={product.id}
                  variants={fadeInUp}
                  custom={index}
                  whileHover={{
                    y: -12,
                    transition: { duration: 0.3, ease: "easeOut" },
                  }}
                  className="bg-white rounded-xl shadow-sm hover:shadow-xl transition-all duration-300 overflow-hidden border border-gray-100 group relative z-0"
                >
                  <Link
                    to={`product-detail/${product.id}`}
                    className="block absolute inset-0 z-10"
                    onClick={() => {
                      // Sử dụng window.trackProductView nếu có, nếu không thì dùng helper function
                      if (window.trackProductView) {
                        window.trackProductView(
                          product.id,
                          product.name,
                          product.category
                        );
                      } else {
                        trackActivity("view_product", {
                          productId: product.id,
                          productName: product.name,
                          category: product.category,
                        });
                      }
                    }}
                  >
                    <span className="sr-only">Xem chi tiết {product.name}</span>
                  </Link>
                  <div className="relative">
                    <div className="relative overflow-hidden">
                      <div className="aspect-square overflow-hidden">
                        <motion.img
                          src={
                            product.image_thumnail
                              ? product.image_thumnail.startsWith("http")
                                ? product.image_thumnail
                                : `http://localhost:8000/storage/${product.image_thumnail}`
                              : "https://via.placeholder.com/300x300?text=No+Image"
                          }
                          alt={product.name}
                          className="w-full h-full object-cover group-hover:scale-110 transition-all duration-700 bg-gray-100"
                          loading="lazy"
                          onError={(e) => {
                            e.target.onerror = null;
                            e.target.src = "/images/no-image.png";
                          }}
                          initial={{ scale: 1.2, y: 20 }}
                          animate={{ scale: 1, y: 0 }}
                          transition={{ duration: 0.8, delay: index * 0.1 }}
                        />
                      </div>

                      {/* Nhãn mới */}
                      <motion.div
                        className="absolute top-3 right-3 bg-amber-500 text-white text-xs font-bold px-3 py-1 rounded-full z-20"
                        initial={{ opacity: 0, scale: 0.5 }}
                        whileInView={{ opacity: 1, scale: 1 }}
                        transition={{
                          type: "spring",
                          stiffness: 500,
                          delay: 0.5 + index * 0.1,
                        }}
                      >
                        MỚI
                      </motion.div>

                      {/* Nút mua nhanh */}
                      <div className="absolute bottom-3 right-3 opacity-0 group-hover:opacity-100 transition-opacity duration-300 z-20">
                        <motion.button
                          className="bg-white text-amber-500 p-3 rounded-full shadow-md hover:bg-amber-500 hover:text-white transition-all duration-300"
                          whileHover={{ scale: 1.1 }}
                          whileTap={{ scale: 0.9 }}
                          onClick={(e) => {
                            e.preventDefault();
                            e.stopPropagation();
                            // Sử dụng window.trackAddToCart nếu có, nếu không thì dùng helper function
                            if (window.trackAddToCart) {
                              window.trackAddToCart(
                                product.id,
                                product.name,
                                product.category
                              );
                            } else {
                              trackActivity("add_to_cart", {
                                productId: product.id,
                                productName: product.name,
                                category: product.category,
                              });
                            }
                            window.location.href = `/cart/add/${product.id}`;
                          }}
                        >
                          <IoCartOutline className="text-xl" />
                        </motion.button>
                      </div>
                    </div>

                    <div className="p-5">
                      {/* Sao đánh giá */}
                      <div className="flex items-center mb-2">
                        {[1, 2, 3, 4, 5].map((star) => {
                          // Lấy đánh giá từ state productRatings
                          const rating = productRatings[product.id]?.average || 0;
                          return (
                            <IoStar
                              key={star}
                              className={`${star <= Math.round(rating)
                                ? "text-amber-400" : "text-gray-300"
                                } w-4 h-4`}
                            />
                          );
                        })}
                        <span className="text-gray-500 text-sm ml-2">
                          {/* Hiển thị số đánh giá từ state */}
                          {productRatings[product.id]
                            ? productRatings[product.id].average.toFixed(1)
                            : "0.0"}
                        </span>
                      </div>

                      <h3 className="font-semibold text-gray-800 mb-1 group-hover:text-amber-500 transition-colors">
                        {product.name}
                      </h3>

                      <p className="text-gray-500 text-sm mb-3 line-clamp-2">
                        Sản phẩm nội thất cao cấp, bền đẹp
                      </p>

                      {Boolean(product.has_variants) ? (
                        // Sản phẩm có biến thể
                        <div className="relative">
                          {product.price_range &&
                            product.price_range.min_discount > 0 ? (
                            // Có giá khuyến mãi
                            <div className="flex flex-col">
                              <span className="font-semibold text-amber-600 text-lg">
                                {new Intl.NumberFormat("vi-VN", {
                                  style: "currency",
                                  currency: "VND",
                                }).format(product.price_range.min_discount)}
                                {product.price_range.max_discount > 0 &&
                                  product.price_range.max_discount !==
                                  product.price_range.min_discount &&
                                  ` - ${new Intl.NumberFormat("vi-VN", {
                                    style: "currency",
                                    currency: "VND",
                                  }).format(product.price_range.max_discount)}`}
                              </span>
                              <span className="text-gray-400 line-through text-sm">
                                {new Intl.NumberFormat("vi-VN", {
                                  style: "currency",
                                  currency: "VND",
                                }).format(product.price_range.min)}
                              </span>
                            </div>
                          ) : (
                            // Không có khuyến mãi
                            <span className="font-semibold text-amber-600 text-lg">
                              {new Intl.NumberFormat("vi-VN", {
                                style: "currency",
                                currency: "VND",
                              }).format(
                                product.price_range && product.price_range.min
                                  ? product.price_range.min
                                  : product.price || 0
                              )}
                              {product.price_range &&
                                product.price_range.max &&
                                product.price_range.min &&
                                product.price_range.max !==
                                product.price_range.min &&
                                ` - ${new Intl.NumberFormat("vi-VN", {
                                  style: "currency",
                                  currency: "VND",
                                }).format(product.price_range.max)}`}
                            </span>
                          )}
                        </div>
                      ) : (
                        // Sản phẩm thường
                        <div className="z-20 relative">
                          {product.discount_price ? (
                            // Có giá khuyến mãi
                            <div className="flex flex-col">
                              <span className="font-semibold text-amber-600 text-lg">
                                {new Intl.NumberFormat("vi-VN", {
                                  style: "currency",
                                  currency: "VND",
                                }).format(product.discount_price)}
                              </span>
                              <span className="text-gray-400 line-through text-sm">
                                {new Intl.NumberFormat("vi-VN", {
                                  style: "currency",
                                  currency: "VND",
                                }).format(product.price)}
                              </span>
                            </div>
                          ) : (
                            // Không có khuyến mãi
                            <span className="font-semibold text-amber-600 text-lg">
                              {new Intl.NumberFormat("vi-VN", {
                                style: "currency",
                                currency: "VND",
                              }).format(product.price || 0)}
                            </span>
                          )}
                        </div>
                      )}
                    </div>
                  </div>
                </motion.div>
              ))
            ) : (
              <>
                {[1, 2, 3, 4].map((index) => (
                  <motion.div
                    key={index}
                    variants={fadeInUp}
                    custom={index}
                    className="bg-white rounded-xl shadow-sm overflow-hidden animate-pulse"
                  >
                    <div className="aspect-square bg-gray-200"></div>
                    <div className="p-5">
                      <div className="h-4 bg-gray-200 rounded w-3/4 mb-3"></div>
                      <div className="h-3 bg-gray-200 rounded w-full mb-2"></div>
                      <div className="h-3 bg-gray-200 rounded w-full mb-3"></div>
                      <div className="h-5 bg-gray-200 rounded w-1/3"></div>
                    </div>
                  </motion.div>
                ))}
              </>
            )}
          </motion.div>
        </div>
      </motion.section>

      {/* Danh mục bộ sưu tập */}
      <motion.section
        initial="hidden"
        whileInView="visible"
        viewport={{ once: true, amount: 0.05 }}
        variants={fadeInUp}
        className="py-20 bg-white"
      >
        <div className="max-w-6xl mx-auto px-4 relative">
          <motion.h2
            className="text-3xl md:text-4xl font-bold mb-8 text-center text-gray-800"
            initial={{ opacity: 0, y: 20 }}
            whileInView={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.8 }}
            viewport={{ once: true }}
          >
            Bộ sưu tập <span className="text-amber-500">nổi bật</span>
          </motion.h2>

          <motion.div
            className="grid grid-cols-12 grid-rows-12 gap-6 h-[900px]"
            variants={staggerContainer}
          >
            {/* Hình 1 - Lớn nhất */}
            <motion.div
              className="col-span-8 row-span-8 rounded-2xl overflow-hidden"
              variants={scaleIn}
              whileHover={{ scale: 1.02 }}
              transition={{ duration: 0.5 }}
            >
              <Link
                to="/products"
                className="block h-full w-full relative group"
              >
                <div className="absolute inset-0 bg-gradient-to-t from-black/50 via-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity z-10"></div>
                <motion.div
                  className="absolute bottom-8 left-8 text-white z-10 transform translate-y-8 opacity-0 group-hover:translate-y-0 group-hover:opacity-100 transition-all duration-500"
                  whileHover={{ x: 5 }}
                >
                  <h3 className="text-2xl font-bold mb-2">
                    Phòng khách hiện đại
                  </h3>
                  <p className="text-white/80 mb-4">
                    Không gian thoáng đãng, sang trọng
                  </p>
                  <span className="flex items-center text-amber-300 font-medium">
                    Xem bộ sưu tập{" "}
                    <HiOutlineArrowNarrowRight className="ml-2" />
                  </span>
                </motion.div>
                <img
                  src=".\src\assets\img\banners\banner-homepage2_4.png"
                  alt="Phòng khách hiện đại"
                  className="h-full w-full object-cover transform group-hover:scale-105 transition-transform duration-700"
                />
              </Link>
            </motion.div>

            {/* Hình 2 */}
            <motion.div
              className="col-span-4 row-span-5 rounded-2xl overflow-hidden"
              variants={scaleIn}
              whileHover={{ scale: 1.02 }}
              transition={{ duration: 0.5 }}
            >
              <Link
                to="/products"
                className="block h-full w-full relative group"
              >
                <div className="absolute inset-0 bg-gradient-to-t from-black/50 via-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity z-10"></div>
                <motion.div
                  className="absolute bottom-6 left-6 text-white z-10 transform translate-y-6 opacity-0 group-hover:translate-y-0 group-hover:opacity-100 transition-all duration-500"
                  whileHover={{ x: 5 }}
                >
                  <h3 className="text-xl font-bold mb-1">Thiết kế cổ điển</h3>
                  <span className="flex items-center text-amber-300 font-medium text-sm">
                    Xem bộ sưu tập{" "}
                    <HiOutlineArrowNarrowRight className="ml-2" />
                  </span>
                </motion.div>
                <img
                  src=".\src\assets\img\banners\banner-homepage2_1.png"
                  alt="Thiết kế cổ điển"
                  className="h-full w-full object-cover transform group-hover:scale-105 transition-transform duration-700"
                />
              </Link>
            </motion.div>

            {/* Hình 3 */}
            <motion.div
              className="col-span-4 row-span-3 rounded-2xl overflow-hidden"
              variants={scaleIn}
              whileHover={{ scale: 1.02 }}
              transition={{ duration: 0.5 }}
            >
              <Link
                to="/products"
                className="block h-full w-full relative group"
              >
                <div className="absolute inset-0 bg-gradient-to-t from-black/50 via-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity z-10"></div>
                <motion.div
                  className="absolute bottom-4 left-4 text-white z-10 transform translate-y-4 opacity-0 group-hover:translate-y-0 group-hover:opacity-100 transition-all duration-500"
                  whileHover={{ x: 5 }}
                >
                  <h3 className="text-lg font-bold mb-1">Phòng ngủ</h3>
                  <span className="flex items-center text-amber-300 font-medium text-sm">
                    Xem ngay <HiOutlineArrowNarrowRight className="ml-1" />
                  </span>
                </motion.div>
                <img
                  src=".\src\assets\img\banners\banner-homepage2_2.png"
                  alt="Phòng ngủ"
                  className="h-full w-full object-cover transform group-hover:scale-105 transition-transform duration-700"
                />
              </Link>
            </motion.div>

            {/* Hình 4 */}
            <motion.div
              className="col-span-8 row-span-4 rounded-2xl overflow-hidden"
              variants={scaleIn}
              whileHover={{ scale: 1.02 }}
              transition={{ duration: 0.5 }}
            >
              <Link
                to="/products"
                className="block h-full w-full relative group"
              >
                <div className="absolute inset-0 bg-gradient-to-t from-black/50 via-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity z-10"></div>
                <motion.div
                  className="absolute bottom-6 left-6 text-white z-10 transform translate-y-6 opacity-0 group-hover:translate-y-0 group-hover:opacity-100 transition-all duration-500"
                  whileHover={{ x: 5 }}
                >
                  <h3 className="text-xl font-bold mb-1">
                    Không gian làm việc
                  </h3>
                  <span className="flex items-center text-amber-300 font-medium">
                    Xem bộ sưu tập{" "}
                    <HiOutlineArrowNarrowRight className="ml-2" />
                  </span>
                </motion.div>
                <img
                  src=".\src\assets\img\banners\banner-homepage2_3.png"
                  alt="Không gian làm việc"
                  className="h-full w-full object-cover transform group-hover:scale-105 transition-transform duration-700"
                />
              </Link>
            </motion.div>
          </motion.div>
        </div>
      </motion.section>

      {/* Phần bài viết */}
      <motion.section
        className="py-20 bg-white relative overflow-hidden"
        initial="hidden"
        whileInView="visible"
        viewport={{ once: true, amount: 0.05 }}
        variants={fadeInUp}
      >
        {/* Decorative Elements */}
        <motion.div
          className="absolute top-0 right-0 w-72 h-72 rounded-full bg-amber-200 filter blur-3xl opacity-20"
          animate={{
            x: [50, -50, 50],
            y: [20, -20, 20],
            scale: [1, 1.1, 1],
          }}
          transition={{ duration: 20, repeat: Infinity, ease: "easeInOut" }}
        />
        <motion.div
          className="absolute bottom-0 left-20 w-64 h-64 rounded-full bg-amber-400 filter blur-3xl opacity-10"
          animate={{
            x: [-30, 30, -30],
            y: [30, 0, 30],
            scale: [1, 1.2, 1],
          }}
          transition={{ duration: 25, repeat: Infinity, ease: "easeInOut" }}
        />

        <div className="max-w-6xl mx-auto px-4 relative z-10">
          <motion.div className="mb-16 text-center" variants={fadeInUp}>
            <motion.span
              className="text-amber-500 font-medium mb-2 block"
              initial={{ opacity: 0, y: 20 }}
              whileInView={{ opacity: 1, y: 0 }}
              transition={{ duration: 1.2, delay: 0.3 }}
              viewport={{ once: true }}
            >
              Tin tức & ý tưởng
            </motion.span>
            <motion.h2
              className="text-3xl md:text-4xl font-bold mb-6 relative inline-block"
              initial={{ opacity: 0, y: 20 }}
              whileInView={{ opacity: 1, y: 0 }}
              transition={{ duration: 1.2, delay: 0.6 }}
              viewport={{ once: true }}
            >
              Bài Viết Mới Nhất
              <motion.span
                className="absolute -bottom-2 left-0 right-0 h-1 bg-gradient-to-r from-amber-400 to-amber-600"
                initial={{ width: 0 }}
                whileInView={{ width: "100%" }}
                transition={{ duration: 1.2, delay: 1.2 }}
                viewport={{ once: true }}
              />
            </motion.h2>
            <motion.p
              className="text-gray-600 max-w-2xl mx-auto"
              initial={{ opacity: 0, y: 20 }}
              whileInView={{ opacity: 1, y: 0 }}
              transition={{ duration: 1.2, delay: 0.9 }}
              viewport={{ once: true }}
            >
              Khám phá những ý tưởng thiết kế nội thất mới nhất và các bí quyết
              để tạo nên không gian sống hoàn hảo cho ngôi nhà của bạn.
            </motion.p>
          </motion.div>

          <motion.div
            className="grid grid-cols-1 md:grid-cols-3 gap-8"
            variants={staggerContainer}
          >
            {posts.length > 0 ? (
              posts.slice(0, 3).map((post, index) => (
                <motion.div
                  key={post.id}
                  className="group"
                  variants={fadeInUp}
                  custom={index}
                  whileHover={{ y: -10 }}
                  transition={{ duration: 0.4 }}
                >
                  <Link to={`/blog-detail/${post.slug}`} className="block">
                    <motion.article
                      className="bg-white rounded-2xl overflow-hidden shadow-lg h-full transform transition-all duration-300 border border-gray-100"
                      initial={{
                        boxShadow: "0 4px 6px -1px rgba(0, 0, 0, 0.1)",
                      }}
                      whileHover={{
                        y: -5,
                        boxShadow:
                          "0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04)",
                      }}
                      transition={{ duration: 0.6 }}
                    >
                      <div className="relative overflow-hidden aspect-[16/10]">
                        <motion.div
                          className="absolute inset-0 bg-gradient-to-br from-amber-500/20 to-purple-600/30 z-10 opacity-0 mix-blend-overlay"
                          whileHover={{ opacity: 1 }}
                          transition={{ duration: 0.5 }}
                        />
                        <motion.img
                          src={
                            post.thumbnail
                              ? post.thumbnail.startsWith("http")
                                ? post.thumbnail
                                : post.thumbnail.startsWith("/")
                                  ? `http://localhost:8000${post.thumbnail}`
                                  : `http://localhost:8000/${post.thumbnail}`
                              : "http://localhost:5173/src/assets/img/blog/blog-1.jpg"
                          }
                          alt={post.title}
                          className="w-full h-full object-cover"
                          whileHover={{ scale: 1.08 }}
                          transition={{ duration: 1.8 }}
                          onError={(e) => {
                            console.log("Lỗi khi tải ảnh:", post.thumbnail);
                            e.target.src =
                              "http://localhost:5173/src/assets/img/blog/blog-1.jpg";
                          }}
                        />
                        <motion.div
                          className="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"
                          initial={{ opacity: 0 }}
                          whileHover={{ opacity: 1 }}
                          transition={{ duration: 1.2 }}
                        ></motion.div>

                        {/* Thẻ danh mục */}
                        <motion.div
                          className="absolute top-4 left-4 z-20"
                          initial={{ opacity: 0, x: -20 }}
                          whileInView={{ opacity: 1, x: 0 }}
                          transition={{ delay: 0.4 + index * 0.2 }}
                        >
                          <span className="bg-amber-500 text-white text-xs font-medium px-3 py-1 rounded-full shadow-lg backdrop-blur-sm bg-opacity-80">
                            {post.category?.title || "Nội thất"}
                          </span>
                        </motion.div>
                      </div>

                      <div className="p-6 relative">
                        {/* Background decorative element */}
                        <div className="absolute top-0 right-0 -mt-8 -mr-8 w-16 h-16 bg-amber-50 rounded-full opacity-70" />

                        {/* Thông tin thời gian */}
                        <div className="flex items-center text-gray-500 text-sm mb-3 relative z-10">
                          <svg
                            xmlns="http://www.w3.org/2000/svg"
                            className="h-4 w-4 mr-1 text-amber-500"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                          >
                            <path
                              strokeLinecap="round"
                              strokeLinejoin="round"
                              strokeWidth={2}
                              d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                            />
                          </svg>
                          <span>{post.created_at}</span>
                        </div>

                        {/* Tiêu đề */}
                        <motion.h3
                          className="font-bold text-xl mb-3 text-gray-800 group-hover:text-amber-600 transition-colors line-clamp-2 relative z-10"
                          initial={{ opacity: 0, y: 10 }}
                          animate={{ opacity: 1, y: 0 }}
                          transition={{
                            duration: 0.8,
                            delay: 0.2 + index * 0.1,
                          }}
                        >
                          {post.title}
                        </motion.h3>

                        {/* Mô tả ngắn */}
                        <motion.p
                          className="text-gray-600 mb-5 line-clamp-3 relative z-10"
                          initial={{ opacity: 0, y: 10 }}
                          animate={{ opacity: 1, y: 0 }}
                          transition={{
                            duration: 0.8,
                            delay: 0.3 + index * 0.1,
                          }}
                        >
                          {post.short_content}
                        </motion.p>

                        {/* Nút đọc thêm */}
                        <motion.div
                          className="flex items-center text-amber-500 font-medium transition-all duration-200 group-hover:text-amber-600 relative z-10"
                          whileHover={{ x: 5 }}
                          transition={{ duration: 0.3 }}
                        >
                          <span>Đọc tiếp</span>
                          <svg
                            xmlns="http://www.w3.org/2000/svg"
                            className="h-5 w-5 ml-2 transform transition-transform duration-300 group-hover:translate-x-1"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                          >
                            <path
                              strokeLinecap="round"
                              strokeLinejoin="round"
                              strokeWidth={2}
                              d="M17 8l4 4m0 0l-4 4m4-4H3"
                            />
                          </svg>
                        </motion.div>
                      </div>
                    </motion.article>
                  </Link>
                </motion.div>
              ))
            ) : (
              <>
                {[1, 2, 3].map((index) => (
                  <motion.div
                    key={index}
                    className="animate-pulse bg-white rounded-xl overflow-hidden shadow-sm"
                    variants={fadeInUp}
                    custom={index}
                  >
                    <div className="bg-gray-300 aspect-[16/10]"></div>
                    <div className="p-6">
                      <div className="h-4 bg-gray-300 rounded w-3/4 mb-3"></div>
                      <div className="h-6 bg-gray-300 rounded w-3/4 mb-3"></div>
                      <div className="h-4 bg-gray-300 rounded w-full mb-2"></div>
                      <div className="h-4 bg-gray-300 rounded w-full mb-2"></div>
                      <div className="h-4 bg-gray-300 rounded w-2/3 mb-4"></div>
                      <div className="h-4 bg-gray-300 rounded w-1/4"></div>
                    </div>
                  </motion.div>
                ))}
              </>
            )}
          </motion.div>

          {/* Nút xem tất cả bài viết */}
          <motion.div
            className="text-center mt-12"
            initial={{ opacity: 0, y: 20 }}
            whileInView={{ opacity: 1, y: 0 }}
            transition={{ duration: 1.2, delay: 1 }}
            viewport={{ once: true }}
          >
            <Link
              to="/blogs"
              className="relative inline-flex items-center bg-amber-500 hover:bg-amber-600 text-white font-medium py-3 px-8 rounded-full transition-all duration-300 overflow-hidden group"
            >
              <motion.span
                className="absolute top-0 left-0 w-full h-full bg-gradient-to-r from-amber-600 to-amber-400 opacity-0 group-hover:opacity-100"
                transition={{ duration: 0.5 }}
              />
              <span className="relative z-10">Xem tất cả bài viết</span>
              <motion.svg
                xmlns="http://www.w3.org/2000/svg"
                className="h-5 w-5 ml-2 relative z-10"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
                whileHover={{ x: 5 }}
                transition={{ duration: 0.3 }}
              >
                <path
                  strokeLinecap="round"
                  strokeLinejoin="round"
                  strokeWidth={2}
                  d="M17 8l4 4m0 0l-4 4m4-4H3"
                />
              </motion.svg>
            </Link>
          </motion.div>
        </div>
      </motion.section>
      {/* end blog */}

      {/* Thương hiệu đối tác */}
      <motion.section
        className="py-20 bg-gray-50"
        initial="hidden"
        whileInView="visible"
        viewport={{ once: true, amount: 0.05 }}
        variants={fadeInUp}
      >
        <div className="max-w-6xl mx-auto px-6">
          <motion.div
            className="text-center mb-12"
            initial={{ opacity: 0, y: 20 }}
            whileInView={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.8 }}
            viewport={{ once: true }}
          >
            <motion.span
              className="inline-block px-4 py-1 bg-amber-100 rounded-full text-amber-700 font-medium text-sm mb-4"
              initial={{ opacity: 0, y: 10 }}
              whileInView={{ opacity: 1, y: 0 }}
              transition={{ duration: 0.6 }}
              viewport={{ once: true }}
            >
              ĐỐI TÁC TIN CẬY
            </motion.span>
            <motion.h2
              className="text-3xl md:text-4xl font-bold mb-4 text-gray-800"
              initial={{ opacity: 0, y: 20 }}
              whileInView={{ opacity: 1, y: 0 }}
              transition={{ duration: 0.8, delay: 0.2 }}
              viewport={{ once: true }}
            >
              Thương hiệu <span className="text-amber-500">đồng hành</span>
            </motion.h2>
            <motion.p
              className="text-gray-600 max-w-2xl mx-auto"
              initial={{ opacity: 0, y: 20 }}
              whileInView={{ opacity: 1, y: 0 }}
              transition={{ duration: 0.8, delay: 0.4 }}
              viewport={{ once: true }}
            >
              Chúng tôi hợp tác với các thương hiệu hàng đầu để đảm bảo chất
              lượng và tính bền vững cho từng sản phẩm.
            </motion.p>
          </motion.div>

          <motion.div
            className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-6 items-center"
            initial={{ opacity: 0, y: 20 }}
            whileInView={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.8, staggerChildren: 0.1 }}
            viewport={{ once: true }}
          >
            {[2, 3, 4, 6, 7, 11].map((num, index) => (
              <motion.div
                key={num}
                variants={fadeInUp}
                custom={index * 0.1}
                whileHover={{
                  y: -5,
                  boxShadow: "0 10px 30px -15px rgba(0,0,0,0.1)",
                }}
                className="bg-white p-6 rounded-xl shadow-sm flex items-center justify-center h-24 border border-gray-100 transition-all duration-300"
              >
                <img
                  src={`.\\src\\assets\\img\\brands\\brand-${num}.png`}
                  alt={`Brand ${index + 1}`}
                  className="max-h-12 w-auto filter grayscale hover:grayscale-0 transition-all duration-300"
                />
              </motion.div>
            ))}
          </motion.div>

          <motion.div
            className="text-center mt-12"
            initial={{ opacity: 0, y: 20 }}
            whileInView={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.8, delay: 0.6 }}
            viewport={{ once: true }}
          >
            <Link
              to="/about"
              className="inline-flex items-center text-amber-600 font-medium hover:text-amber-700 group"
            >
              <span>Tìm hiểu thêm về chúng tôi</span>
              <HiOutlineArrowNarrowRight className="ml-2 group-hover:translate-x-1 transition-transform w-5 h-5" />
            </Link>
          </motion.div>
        </div>
      </motion.section>
      <Popup />
    </>
  );
};

export default Homes;
