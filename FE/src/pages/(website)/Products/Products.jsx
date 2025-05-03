import React, { useState, useEffect, useMemo } from "react";
import { FaUserAstronaut, FaShippingFast, FaFilter } from "react-icons/fa";
import { AiOutlineSearch } from "react-icons/ai";
import { LiaTrophySolid } from "react-icons/lia";
import { IoCartOutline, IoStar } from "react-icons/io5";
import { MdOutlineSettingsInputComponent } from "react-icons/md";
import axios from "axios";
import { Link, useSearchParams } from "react-router-dom";
import Banner from "../../../components/Banner";
import { motion } from "framer-motion";
import LoadingScreen from "../../../components/LoadingScreen";
import VariantSelectionModal from "../../../components/VariantSelectionModal";
import { toast } from "react-toastify";
import axiosInstance from "../../../utils/axiosConfig";

const Products = () => {
  const [products, setProducts] = useState([]);
  const [allProducts, setAllProducts] = useState([]); // Lưu trữ tất cả sản phẩm cho tìm kiếm realtime
  const [categories, setCategories] = useState([]);
  const [variants, setVariants] = useState([]);
  const [selectedCategories, setSelectedCategories] = useState([]);
  const [selectedVariants, setSelectedVariants] = useState([]);
  const [imageLoadError, setImageLoadError] = useState({});
  const [filterOpen, setFilterOpen] = useState(false);
  const [priceRange, setPriceRange] = useState([0, 10000000]);
  const [searchTerm, setSearchTerm] = useState("");
  const [debouncedSearchTerm, setDebouncedSearchTerm] = useState(""); // State for debounced search term
  const [isLoading, setIsLoading] = useState(true);
  const [isSearchLoading, setIsSearchLoading] = useState(false); // Biến loading riêng cho tìm kiếm
  const [dataLoaded, setDataLoaded] = useState(false);
  const [imagesLoaded, setImagesLoaded] = useState(false);
  const [loadedImagesCount, setLoadedImagesCount] = useState(0);
  const [currentPage, setCurrentPage] = useState(1);
  const [totalPages, setTotalPages] = useState(1);
  const [itemsPerPage] = useState(9);
  const [isInitialLoad, setIsInitialLoad] = useState(true); // Đánh dấu lần tải đầu tiên
  const [sortOption, setSortOption] = useState("newest");
  // Thêm state lưu trữ thông tin đánh giá
  const [productRatings, setProductRatings] = useState({});
  const [variantModalOpen, setVariantModalOpen] = useState(false);
  const [selectedProduct, setSelectedProduct] = useState(null);
  const [addingToCart, setAddingToCart] = useState(false);

  const [searchParams] = useSearchParams();
  const spaceFilter = searchParams.get('space');

  const fadeIn = {
    hidden: { opacity: 0, y: 20 },
    visible: {
      opacity: 1,
      y: 0,
      transition: {
        duration: 0.3,
      },
    },
  };

  const staggerContainer = {
    hidden: { opacity: 0 },
    visible: {
      opacity: 1,
      transition: {
        staggerChildren: 0.05,
      },
    },
  };

  useEffect(() => {
    const timerId = setTimeout(() => {
      setDebouncedSearchTerm(searchTerm);
      setCurrentPage(1); // Reset về trang 1 khi search term thay đổi
    }, 500); // Đợi 500ms sau khi người dùng ngừng nhập

    return () => {
      clearTimeout(timerId);
    };
  }, [searchTerm]);

  // Ban đầu, tải tất cả sản phẩm để tìm kiếm realtime
  useEffect(() => {
    // Chỉ tải tất cả sản phẩm một lần khi component mount
    const fetchAllProducts = async () => {
      try {
        const params = {
          limit: 1000, // Số lượng lớn để lấy tất cả sản phẩm
          ...(spaceFilter && { space: spaceFilter }),
        };

        const response = await axios.get(`${import.meta.env.VITE_API_URL}/api/products`, { params });

        if (response.data.status === "success" && response.data.data) {
          setAllProducts(response.data.data.data || []);
        }
      } catch (error) {
        console.error("Lỗi khi tải tất cả sản phẩm:", error);
      }
    };

    fetchAllProducts();
  }, [spaceFilter]); // Chỉ tải lại khi spaceFilter thay đổi

  // Xử lý tìm kiếm realtime - đảm bảo không gây ra hiệu ứng loading
  useEffect(() => {
    // Nếu đang tải lần đầu, bỏ qua tìm kiếm realtime
    if (isInitialLoad) return;

    // Luôn tắt trạng thái loading khi tìm kiếm để đảm bảo không hiện loading screen
    setIsLoading(false);

    // Nếu có searchTerm hoặc searchTerm là chuỗi rỗng, thực hiện tìm kiếm realtime
    if (searchTerm !== undefined) {
      const keyword = searchTerm.toLowerCase().trim();
      const filteredResults = allProducts.filter(product =>
        product.name.toLowerCase().includes(keyword)
      );

      setProducts(filteredResults);
      setTotalPages(Math.ceil(filteredResults.length / itemsPerPage));
      setCurrentPage(1);

      // Đánh dấu dữ liệu đã được tải để ngăn hiệu ứng loading
      setDataLoaded(true);
      setImagesLoaded(true);
      setIsSearchLoading(false);
    }
  }, [searchTerm, allProducts, isInitialLoad, itemsPerPage]);

  // Tạo flag để đánh dấu đang tìm kiếm
  const [isSearching, setIsSearching] = useState(false);

  // Cập nhật isSearching khi searchTerm thay đổi
  useEffect(() => {
    setIsSearching(searchTerm !== undefined);
  }, [searchTerm]);

  useEffect(() => {
    // Tải lại dữ liệu khi các bộ lọc thay đổi
    const fetchData = async () => {
      // Đánh dấu kết thúc lần tải đầu tiên
      setIsInitialLoad(false);

      // Nếu đang tìm kiếm, không gọi API và không hiển thị loading
      if (isSearching) {
        setIsLoading(false);
        return;
      }

      // Chỉ hiển thị loading toàn màn hình khi tải trang lần đầu hoặc khi không đang tìm kiếm
      if (!dataLoaded && !isSearching) {
        setIsLoading(true);
      }

      setDataLoaded(false);
      try {
        const params = {
          page: currentPage,
          limit: itemsPerPage,
          ...(spaceFilter && { space: spaceFilter }),
          // Không sử dụng debouncedSearchTerm vì đã xử lý tìm kiếm realtime riêng
        };

        const [productsResponse, categoriesResponse, variantsResponse] = await Promise.all([
          axios.get(`${import.meta.env.VITE_API_URL}/api/products`, { params }),
          axios.get(`${import.meta.env.VITE_API_URL}/api/categories/all`, {
            params: { ...(spaceFilter && { space: spaceFilter }) }
          }),
          axios.get(`${import.meta.env.VITE_API_URL}/api/variants`)
        ]);

        // Xử lý dữ liệu sản phẩm
        if (productsResponse.data.status === "success" && productsResponse.data.data) {
          setProducts(productsResponse.data.data.data || []);
          setTotalPages(productsResponse.data.data.last_page || 1);
          setCurrentPage(productsResponse.data.data.current_page || 1);
        } else {
          setProducts([]);
          setTotalPages(1);
          setCurrentPage(1);
        }

        // Xử lý dữ liệu danh mục
        if (categoriesResponse.data.success) {
          setCategories(categoriesResponse.data.data || []);
        } else {
          setCategories([]);
        }

        // Xử lý dữ liệu biến thể
        if (variantsResponse.data.status === "success") {
          setVariants(variantsResponse.data.data || []);
        }

        // Đánh dấu dữ liệu đã được tải xong
        setDataLoaded(true);
        setIsSearchLoading(false); // Tắt trạng thái loading tìm kiếm
      } catch (error) {
        console.error("Lỗi khi tải dữ liệu:", error);
        setProducts([]);
        setTotalPages(1);
        setCurrentPage(1);
        // Đánh dấu dữ liệu đã được tải xong ngay cả khi có lỗi
        setDataLoaded(true);
        setIsSearchLoading(false); // Tắt trạng thái loading tìm kiếm
      }
    };

    fetchData();
  }, [currentPage, spaceFilter, itemsPerPage]);

  // Theo dõi trạng thái tải dữ liệu và hình ảnh để cập nhật isLoading
  useEffect(() => {
    // Nếu đang tìm kiếm, không hiển thị loading
    if (isSearching) {
      setIsLoading(false);
      return;
    }

    if (dataLoaded && imagesLoaded) {
      // Thêm một khoảng thời gian nhỏ trước khi tắt loading
      // để tránh hiệu ứng nhấp nháy khi tải dữ liệu quá nhanh
      const timer = setTimeout(() => {
        // Không tắt loading nếu đang tìm kiếm, để hiển thị loading inline
        if (!isSearchLoading && !isSearching) {
          setIsLoading(false);
        }
      }, 300);

      return () => clearTimeout(timer);
    } else {
      // Chỉ hiển thị loading toàn màn hình nếu không phải đang tìm kiếm
      if (!isSearchLoading && !isSearching) {
        setIsLoading(true);
      }
    }
  }, [dataLoaded, imagesLoaded, isSearchLoading, isSearching]);

  // Reset trạng thái tải hình ảnh khi chuyển trang hoặc thay đổi bộ lọc
  useEffect(() => {
    // Khi thay đổi trang hoặc bộ lọc, resetLoadedImages
    setLoadedImagesCount(0);

    // Nếu là do tìm kiếm, không reset trạng thái imagesLoaded
    // để tránh hiển thị loading toàn màn hình
    if (!isSearching) {
      setImagesLoaded(false);
    }
  }, [currentPage, spaceFilter, isSearching]);

  const filteredProducts = useMemo(() => {
    if (isLoading) return [];

    let filtered = [...products];

    if (selectedCategories.length > 0) {
      filtered = filtered.filter((product) =>
        selectedCategories.includes(product.category_id)
      );
    }

    if (selectedVariants.length > 0) {
      filtered = filtered.filter((product) => {
        if (!product.variants || product.variants.length === 0) return false;
        return product.variants.some((variant) => {
          if (!variant.variant_details || !Array.isArray(variant.variant_details))
            return false;
          return selectedVariants.some((selectedValueId) =>
            variant.variant_details.some((detail) => {
              const selectedVariantValue = variants
                .flatMap((v) => v.values)
                .find((val) => val.id === selectedValueId);
              return selectedVariantValue && detail.value === selectedVariantValue.value;
            })
          );
        });
      });
    }

    if (priceRange[0] !== 0 || priceRange[1] !== 10000000) {
      filtered = filtered.filter((product) => {
        let price;
        if (product.has_variants && product.variants && product.variants.length > 0) {
          price = product.variants[0].discount_price ?? product.variants[0].price;
        } else {
          price = product.discount_price ?? product.price;
        }
        if (price === null || price === undefined) return false;
        return price >= priceRange[0] && price <= priceRange[1];
      });
    }

    // Thêm logic sắp xếp sản phẩm theo tùy chọn đã chọn
    switch (sortOption) {
      case "newest":
        // Giả sử sản phẩm mới nhất có id cao hơn
        filtered.sort((a, b) => b.id - a.id);
        break;
      case "oldest":
        // Sản phẩm cũ nhất có id thấp hơn
        filtered.sort((a, b) => a.id - b.id);
        break;
      case "price-asc":
        filtered.sort((a, b) => {
          const priceA = a.has_variants && a.variants && a.variants.length > 0
            ? (a.variants[0].discount_price ?? a.variants[0].price)
            : (a.discount_price ?? a.price);
          const priceB = b.has_variants && b.variants && b.variants.length > 0
            ? (b.variants[0].discount_price ?? b.variants[0].price)
            : (b.discount_price ?? b.price);
          // Nếu giá bằng nhau, sắp xếp theo tên a-z
          if (priceA === priceB) {
            return a.name.localeCompare(b.name);
          }
          return priceA - priceB;
        });
        break;
      case "price-desc":
        filtered.sort((a, b) => {
          const priceA = a.has_variants && a.variants && a.variants.length > 0
            ? (a.variants[0].discount_price ?? a.variants[0].price)
            : (a.discount_price ?? a.price);
          const priceB = b.has_variants && b.variants && b.variants.length > 0
            ? (b.variants[0].discount_price ?? b.variants[0].price)
            : (b.discount_price ?? b.price);
          // Nếu giá bằng nhau, vẫn sắp xếp theo tên a-z
          if (priceA === priceB) {
            return a.name.localeCompare(b.name);
          }
          return priceB - priceA;
        });
        break;
      case "name-asc":
        filtered.sort((a, b) => a.name.localeCompare(b.name));
        break;
      case "name-desc":
        filtered.sort((a, b) => b.name.localeCompare(a.name));
        break;
      default:
        break;
    }

    return filtered;
  }, [products, selectedCategories, selectedVariants, priceRange, variants, isLoading, sortOption]);

  const handleCategoryChange = (categoryId) => {
    setSelectedCategories((prev) =>
      prev.includes(categoryId)
        ? prev.filter((id) => id !== categoryId)
        : [...prev, categoryId]
    );
  };

  const handleVariantValueChange = (valueId) => {
    setSelectedVariants((prev) =>
      prev.includes(valueId)
        ? prev.filter((id) => id !== valueId)
        : [...prev, valueId]
    );
  };

  const handlePriceChange = (e, index) => {
    const newPriceRange = [...priceRange];
    const value = parseInt(e.target.value);
    if (index === 0 && value > newPriceRange[1]) {
      newPriceRange[0] = newPriceRange[1];
    } else if (index === 1 && value < newPriceRange[0]) {
      newPriceRange[1] = newPriceRange[0];
    } else {
      newPriceRange[index] = value;
    }
    setPriceRange(newPriceRange);
  };

  const handlePageChange = (page) => {
    if (page >= 1 && page <= totalPages) {
      setCurrentPage(page);
    }
  };

  const handleSearch = () => {
    // Không cần làm gì ở đây vì debouncedSearchTerm đã tự động cập nhật và trigger useEffect
  };

  const handleSearchKeyPress = (e) => {
    if (e.key === 'Enter') {
      handleSearch();
    }
  };

  const resetClientFilters = () => {
    setSelectedCategories([]);
    setSelectedVariants([]);
    setPriceRange([0, 10000000]);
  };

  const resetAllFilters = () => {
    resetClientFilters();
    setSearchTerm("");
    setDebouncedSearchTerm(""); // Reset cả debouncedSearchTerm
    setCurrentPage(1);

    // Khôi phục dữ liệu ban đầu từ allProducts
    if (allProducts.length > 0) {
      const pagedProducts = allProducts.slice(0, itemsPerPage);
      setProducts(pagedProducts);
      setTotalPages(Math.ceil(allProducts.length / itemsPerPage));
    }
  };

  const toggleFilter = () => {
    setFilterOpen(!filterOpen);
  };

  // Hàm kiểm tra sản phẩm còn hàng hay không
  const checkProductInStock = (product) => {
    if (!product) return false;

    if (product.has_variants) {
      // Kiểm tra tổng số lượng các biến thể
      return product.variants && product.variants.some(variant => variant.quantity > 0);
    } else {
      // Kiểm tra số lượng sản phẩm thường
      return product.quantity > 0;
    }
  };

  const getPageNumbers = () => {
    const pageNumbers = [];
    const maxVisiblePages = 5;

    if (totalPages <= maxVisiblePages) {
      for (let i = 1; i <= totalPages; i++) {
        pageNumbers.push(i);
      }
    } else {
      if (currentPage <= 3) {
        for (let i = 1; i <= 4; i++) {
          pageNumbers.push(i);
        }
        pageNumbers.push('...');
        pageNumbers.push(totalPages);
      } else if (currentPage >= totalPages - 2) {
        pageNumbers.push(1);
        pageNumbers.push('...');
        for (let i = totalPages - 3; i <= totalPages; i++) {
          pageNumbers.push(i);
        }
      } else {
        pageNumbers.push(1);
        pageNumbers.push('...');
        pageNumbers.push(currentPage - 1);
        pageNumbers.push(currentPage);
        pageNumbers.push(currentPage + 1);
        pageNumbers.push('...');
        pageNumbers.push(totalPages);
      }
    }
    return pageNumbers;
  };

  // Hàm xử lý khi hình ảnh tải xong
  const handleImageLoad = (productId) => {
    // Không cập nhật loadedImagesCount khi đang tìm kiếm để tránh kích hoạt loading
    if (isSearching) return;

    setLoadedImagesCount(prev => {
      const newCount = prev + 1;
      // Nếu đã tải đủ số lượng ảnh hoặc đã tải ít nhất 50% và đã mất hơn 1 giây
      if (newCount === products.length || (newCount >= products.length / 2 && Date.now() - loadStartTime > 1000)) {
        // Đánh dấu đã tải xong để cải thiện trải nghiệm người dùng
        setImagesLoaded(true);
      }
      return newCount;
    });
  };

  // Biến để lưu thời điểm bắt đầu tải trang
  const [loadStartTime, setLoadStartTime] = useState(Date.now());

  // Reset thời gian bắt đầu tải khi thay đổi trang hoặc bộ lọc
  useEffect(() => {
    // Chỉ cập nhật loadStartTime và thực hiện reset khi không đang tìm kiếm
    if (!isSearching) {
      setLoadStartTime(Date.now());
      setImagesLoaded(false);
      setLoadedImagesCount(0);
    }
  }, [currentPage, spaceFilter, debouncedSearchTerm, isSearching]);

  // Thêm hàm xử lý thay đổi tùy chọn sắp xếp
  const handleSortOptionChange = (e) => {
    setSortOption(e.target.value);
  };

  // Thêm useEffect mới để tải thông tin đánh giá cho các sản phẩm đã tải
  useEffect(() => {
    // Chỉ tải đánh giá khi có sản phẩm và không phải đang tìm kiếm
    if (products.length > 0 && !isSearchLoading && !isLoading) {
      const fetchRatings = async () => {
        const ratingsData = {};

        // Tạo mảng các promise để tải đánh giá cho tất cả sản phẩm
        const ratingPromises = products.map(product =>
          axios.get(`${import.meta.env.VITE_API_URL}/api/products/${product.id}/reviews`)
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

      fetchRatings();
    }
  }, [products, isSearchLoading, isLoading]);

  // Hàm xử lý thêm sản phẩm vào giỏ hàng
  const handleAddToCart = async (product) => {
    const token = localStorage.getItem("authToken");

    if (!token) {
      toast.error("Vui lòng đăng nhập để thêm sản phẩm vào giỏ hàng");
      return;
    }

    // Kiểm tra xem sản phẩm có biến thể hay không
    if (product.has_variants) {
      // Nếu có, mở modal chọn biến thể
      setSelectedProduct(product);
      setVariantModalOpen(true);
    } else {
      // Nếu không, thêm trực tiếp vào giỏ hàng
      try {
        setAddingToCart(true);
        const response = await axiosInstance.post("/cart/add", {
          product_id: product.id,
          quantity: 1
        });

        if (response.status === 200 || response.status === 201) {
          toast.success("Đã thêm sản phẩm vào giỏ hàng!");
        }
      } catch (error) {
        console.error("Lỗi khi thêm vào giỏ hàng:", error);
        toast.error(error.response?.data?.message || "Có lỗi xảy ra khi thêm vào giỏ hàng");
      } finally {
        setAddingToCart(false);
      }
    }
  };

  // Hàm xử lý sau khi thêm sản phẩm vào giỏ hàng từ modal
  const handleVariantAddedToCart = () => {
    setVariantModalOpen(false);
    setSelectedProduct(null);
  };

  return (
    <>
      {isLoading && !isSearching && <LoadingScreen />}

      <motion.section
        className="w-full mx-auto mt-4 mb-8"
        initial={{ opacity: 0, y: -20 }}
        animate={{ opacity: 1, y: 0 }}
        transition={{ duration: 0.6 }}
      >
        <Banner />
      </motion.section>

      <motion.div
        className="max-w-6xl mx-auto mb-8 px-4"
        initial={{ opacity: 0 }}
        animate={{ opacity: 1 }}
        transition={{ duration: 0.6, delay: 0.2 }}
      >
        <h1 className="text-3xl font-bold text-gray-800 mb-2">
          Sản phẩm nội thất
        </h1>
        <p className="text-gray-600">
          Khám phá bộ sưu tập nội thất độc đáo và bền vững cho không gian sống
          của bạn
        </p>
      </motion.div>

      <section className="max-w-6xl mx-auto mb-8 px-4">
        <div className="flex justify-between items-center mb-6 flex-wrap gap-4">
          <motion.button
            className="flex items-center gap-2 bg-amber-500 hover:bg-amber-600 text-white px-4 py-2 rounded-full transition-all duration-300 md:hidden"
            onClick={toggleFilter}
            whileHover={{ scale: 1.05 }}
            whileTap={{ scale: 0.95 }}
          >
            <FaFilter />
            <span>Bộ lọc</span>
          </motion.button>

          <motion.div
            className="relative flex-grow max-w-lg"
            initial={{ opacity: 0, x: -20 }}
            animate={{ opacity: 1, x: 0 }}
            transition={{ duration: 0.5, delay: 0.3 }}
          >
            <input
              type="text"
              placeholder="Tìm kiếm sản phẩm..."
              className="px-5 py-3 border-2 border-gray-200 rounded-full w-full focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent transition-all"
              value={searchTerm}
              onChange={(e) => setSearchTerm(e.target.value)}
              onKeyPress={handleSearchKeyPress}
            />
            <button
              className="absolute right-4 top-1/2 transform -translate-y-1/2 text-amber-500 hover:text-amber-600 transition-colors"
              onClick={handleSearch}
            >
              <AiOutlineSearch className="h-6 w-6" />
            </button>
          </motion.div>

          <motion.div
            className="flex gap-3"
            initial={{ opacity: 0, x: 20 }}
            animate={{ opacity: 1, x: 0 }}
            transition={{ duration: 0.5, delay: 0.4 }}
          >
            <select
              className="bg-white border-2 border-gray-200 rounded-full px-4 py-2 focus:outline-none focus:ring-2 focus:ring-amber-400"
              value={sortOption}
              onChange={handleSortOptionChange}
            >
              <option value="newest">Mới nhất</option>
              <option value="oldest">Cũ nhất</option>
              <option value="price-asc">Giá: Thấp đến cao</option>
              <option value="price-desc">Giá: Cao đến thấp</option>
              <option value="name-asc">Tên: A-Z</option>
              <option value="name-desc">Tên: Z-A</option>
            </select>
          </motion.div>
        </div>

        <div className="flex gap-6 relative">
          {/* Filter Section - Added subtle shadow and border */}
          <motion.div
            className={`${filterOpen ? "flex" : "hidden"
              } md:flex flex-col w-full md:w-1/4 bg-white p-6 rounded-2xl shadow-lg border border-gray-100 sticky top-24 h-fit transition-all duration-300`}
            initial={{ opacity: 0, x: -20 }}
            animate={{ opacity: 1, x: 0 }}
            transition={{ duration: 0.5, delay: 0.5 }}
          >
            <h3 className="text-lg font-semibold mb-6 text-gray-800 border-b pb-4">
              Lọc sản phẩm
            </h3>

            {/* Category Filter */}
            <div className="mb-8">
              <h4 className="text-base font-semibold mb-4 flex items-center text-gray-700">
                <span className="w-2 h-5 bg-amber-500 rounded-full mr-2 inline-block"></span>
                Danh mục
              </h4>
              <div className="space-y-3 text-gray-600 max-h-60 overflow-y-auto pr-2 custom-scrollbar">
                {Array.isArray(categories) && categories.length > 0 ? (
                  categories.map((category) => (
                    <label
                      key={category.id}
                      className="flex items-center space-x-3 cursor-pointer group p-1 rounded hover:bg-amber-50 transition-colors"
                    >
                      <input
                        type="checkbox"
                        checked={selectedCategories.includes(category.id)}
                        onChange={() => handleCategoryChange(category.id)}
                        className="form-checkbox h-5 w-5 rounded text-amber-500 border-gray-300 focus:ring-amber-500 transition-all"
                      />
                      <span className="group-hover:text-amber-600 transition-colors font-medium">
                        {category.name}
                      </span>
                    </label>
                  ))
                ) : (
                  <p className="text-sm text-gray-500 italic">
                    {spaceFilter ? `Không có danh mục con.` : `Đang tải danh mục...`}
                  </p>
                )}
              </div>
            </div>

            {/* Variant Filters */}
            {variants.map((variant) => (
              <div key={variant.id} className="mb-8">
                <h4 className="text-base font-semibold mb-4 flex items-center text-gray-700">
                  <span className="w-2 h-5 bg-amber-500 rounded-full mr-2 inline-block"></span>
                  {variant.name}
                </h4>
                <div className="space-y-3 text-gray-600 max-h-60 overflow-y-auto pr-2 custom-scrollbar">
                  {variant.values.map((value) => (
                    <label
                      key={value.id}
                      className="flex items-center space-x-3 cursor-pointer group p-1 rounded hover:bg-amber-50 transition-colors"
                    >
                      <input
                        type="checkbox"
                        checked={selectedVariants.includes(value.id)}
                        onChange={() => handleVariantValueChange(value.id)}
                        className="form-checkbox h-5 w-5 rounded text-amber-500 border-gray-300 focus:ring-amber-500"
                      />
                      <span className="group-hover:text-amber-600 transition-colors font-medium">
                        {value.value}
                      </span>
                    </label>
                  ))}
                </div>
              </div>
            ))}

            {/* Price Range Filter */}
            <div className="mb-8">
              <h4 className="text-base font-semibold mb-4 flex items-center text-gray-700">
                <span className="w-2 h-5 bg-amber-500 rounded-full mr-2 inline-block"></span>
                Khoảng giá
              </h4>
              <div className="space-y-4">
                <div className="flex justify-between text-sm text-gray-700 font-medium">
                  <span>
                    {new Intl.NumberFormat("vi-VN", {
                      style: "currency",
                      currency: "VND",
                    }).format(priceRange[0])}
                  </span>
                  <span>
                    {new Intl.NumberFormat("vi-VN", {
                      style: "currency",
                      currency: "VND",
                    }).format(priceRange[1])}
                  </span>
                </div>
                {/* Custom styled range inputs might be complex, keeping default for now */}
                <input
                  type="range"
                  min="0"
                  max="10000000"
                  step="100000"
                  value={priceRange[0]}
                  onChange={(e) => handlePriceChange(e, 0)}
                  className="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-amber-500"
                />
                <input
                  type="range"
                  min="0"
                  max="10000000"
                  step="100000"
                  value={priceRange[1]}
                  onChange={(e) => handlePriceChange(e, 1)}
                  className="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-amber-500"
                />
              </div>
            </div>
            <button
              onClick={resetClientFilters}
              className="mt-4 text-sm text-amber-600 hover:text-amber-700 font-medium hover:underline"
            >Đặt lại bộ lọc</button>
          </motion.div>

          {/* Product Grid */}
          <motion.div
            className="w-full md:w-3/4"
            variants={staggerContainer}
            initial="hidden"
            animate="visible"
          >
            <motion.div
              className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8" // Increased gap
              variants={staggerContainer}
              initial="hidden"
              animate="visible"
            >
              {isLoading ? (
                // Skeleton Loader - Improved Styling
                [...Array(itemsPerPage)].map((_, index) => (
                  <motion.div
                    key={`skeleton-${index}`} // Use a more specific key
                    className="bg-white rounded-2xl shadow-md overflow-hidden border border-gray-100" // Softer corners, subtle border, slightly more shadow
                    variants={fadeIn}
                  >
                    <div className="aspect-square bg-gray-200 animate-pulse"></div>
                    <div className="p-5">
                      <div className="h-5 bg-gray-200 rounded w-3/4 mb-3 animate-pulse"></div>
                      <div className="h-4 bg-gray-200 rounded w-full mb-2 animate-pulse"></div>
                      <div className="h-4 bg-gray-200 rounded w-5/6 mb-4 animate-pulse"></div>
                      <div className="h-6 bg-gray-200 rounded w-1/3 animate-pulse"></div>
                    </div>
                  </motion.div>
                ))
              ) : filteredProducts.length > 0 ? (
                filteredProducts.map((product) => (
                  <motion.div
                    key={product.id}
                    className="bg-white rounded-2xl shadow-md hover:shadow-lg transition-all duration-300 overflow-hidden border border-gray-100 group flex flex-col" // Softer corners, flex column
                    variants={fadeIn}
                    initial="hidden"
                    animate="visible"
                    whileHover={{ y: -6, scale: 1.02 }} // Subtle lift and scale on hover
                  >
                    <Link
                      to={`/product-detail/${product.id}`}
                      className="block flex flex-col flex-grow" // Make link fill the card and grow
                    >
                      <div className="relative overflow-hidden">
                        {/* Image Container */}
                        <div className="aspect-square overflow-hidden bg-gray-50">
                          <motion.img
                            src={
                              product.image_thumnail
                                ? product.image_thumnail.startsWith("http")
                                  ? product.image_thumnail
                                  : `${import.meta.env.VITE_API_URL}/storage/${product.image_thumnail}` // Use VITE_API_URL
                                : "/images/no-image.png" // Default image path
                            }
                            alt={product.name}
                            className={`w-full h-full object-cover transition-transform duration-500 ease-in-out ${!checkProductInStock(product) ? 'opacity-60 grayscale' : 'group-hover:scale-105'}`} // Grayscale and less opacity if out of stock, zoom on hover
                            loading="lazy"
                            onLoad={() => handleImageLoad(product.id)}
                            onError={(e) => {
                              const productId = product.id;
                              handleImageLoad(productId);
                              if (!imageLoadError[productId]) {
                                setImageLoadError((prev) => ({
                                  ...prev,
                                  [productId]: true,
                                }));
                                e.target.src = "/images/no-image.png";
                              }
                            }}
                            initial={{ opacity: 0 }}
                            animate={{ opacity: 1 }}
                            transition={{ duration: 0.5 }}
                          />
                        </div>

                        {/* Badges */}
                        <div className="absolute top-3 left-3 flex flex-col gap-2">
                          <span className="bg-amber-500 text-white text-xs font-bold px-3 py-1 rounded-full shadow">
                            MỚI
                          </span>
                          {/* Add other badges like discount percentage if available */}
                          {/* Example: product.discount_percent && <span className="bg-red-500 text-white text-xs font-bold px-3 py-1 rounded-full shadow">-{product.discount_percent}%</span> */}
                        </div>


                        {/* Out of Stock Overlay */}
                        {!checkProductInStock(product) && (
                          <div className="absolute inset-0 flex items-center justify-center bg-black bg-opacity-40">
                            <span className="bg-red-600 text-white font-bold px-4 py-2 rounded-md text-base shadow-lg">Hết hàng</span>
                          </div>
                        )}

                        {/* Add to Cart Button - Improved visibility and animation */}
                        <div className="absolute bottom-0 left-0 right-0 p-3 bg-gradient-to-t from-black/30 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex justify-end">
                          <motion.button
                            onClick={(e) => {
                              e.preventDefault(); // Prevent link navigation
                              e.stopPropagation(); // Prevent event bubbling
                              handleAddToCart(product);
                            }}
                            className={`bg-white text-amber-600 p-3 rounded-full shadow-lg hover:bg-amber-500 hover:text-white transition-all duration-300 transform hover:scale-110 ${!checkProductInStock(product) ? 'opacity-50 cursor-not-allowed' : ''}`}
                            whileHover={{ scale: checkProductInStock(product) ? 1.15 : 1, rotate: checkProductInStock(product) ? 5 : 0 }}
                            whileTap={{ scale: checkProductInStock(product) ? 0.95 : 1 }}
                            disabled={!checkProductInStock(product)}
                            title="Thêm vào giỏ hàng"
                          >
                            <IoCartOutline className="text-xl" />
                          </motion.button>
                        </div>
                      </div>

                      {/* Product Info */}
                      <div className="p-5 flex flex-col flex-grow"> {/* Flex grow for content */}
                        {/* Rating */}
                        <div className="flex items-center mb-2">
                          {[1, 2, 3, 4, 5].map((star) => {
                            // Lấy đánh giá từ state productRatings
                            const rating = productRatings[product.id]?.average || 0;
                            return (
                              <IoStar
                                key={star}
                                className={`${star <= Math.round(rating)
                                  ? "text-yellow-400"
                                  : "text-gray-300" // Brighter yellow
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

                        {/* Product Name */}
                        <h3 className="font-semibold text-gray-800 text-lg mb-1 group-hover:text-amber-600 transition-colors duration-300 line-clamp-2"> {/* Larger text, line clamp */}
                          {product.name}
                        </h3>

                        {/* Description */}
                        <div
                          className="text-gray-600 text-sm mb-4 line-clamp-2 flex-grow" // Flex grow for description
                          dangerouslySetInnerHTML={{
                            __html:
                              product.description ||
                              "Sản phẩm nội thất cao cấp, bền đẹp và thân thiện với môi trường.",
                          }}
                        />

                        {/* Price */}
                        <div className="flex justify-between items-end mt-auto pt-2 border-t border-gray-100"> {/* Align price to bottom */}
                          {product.has_variants ? (
                            <div className="flex-1">
                              {product.variants && product.variants.length > 0 ? (
                                <div className="flex flex-col items-start">
                                  <p className="text-amber-600 font-bold text-xl"> {/* Larger, bolder price */}
                                    {new Intl.NumberFormat("vi-VN", {
                                      style: "currency",
                                      currency: "VND",
                                    }).format(product.variants[0].discount_price || product.variants[0].price || 0)}
                                  </p>
                                  {product.variants[0].discount_price && (
                                    <p className="text-gray-400 line-through text-sm">
                                      {new Intl.NumberFormat("vi-VN", {
                                        style: "currency",
                                        currency: "VND",
                                      }).format(product.variants[0].price || 0)}
                                    </p>
                                  )}
                                </div>
                              ) : (
                                <p className="text-amber-600 font-bold text-xl">
                                  {new Intl.NumberFormat("vi-VN", {
                                    style: "currency",
                                    currency: "VND",
                                  }).format(0)}
                                </p>
                              )}
                            </div>
                          ) : (
                            <div className="flex-1">
                              {product.discount_price ? (
                                <div className="flex flex-col items-start">
                                  <p className="text-amber-600 font-bold text-xl">
                                    {new Intl.NumberFormat("vi-VN", {
                                      style: "currency",
                                      currency: "VND",
                                    }).format(product.discount_price)}
                                  </p>
                                  <p className="text-gray-400 line-through text-sm">
                                    {new Intl.NumberFormat("vi-VN", {
                                      style: "currency",
                                      currency: "VND",
                                    }).format(product.price)}
                                  </p>
                                </div>
                              ) : (
                                <p className="text-amber-600 font-bold text-xl">
                                  {new Intl.NumberFormat("vi-VN", {
                                    style: "currency",
                                    currency: "VND",
                                  }).format(product.price || 0)}
                                </p>
                              )}
                            </div>
                          )}
                        </div>
                      </div>
                    </Link>
                  </motion.div>
                ))
              ) : (
                // No Products Found - Enhanced Styling
                <div className="col-span-full flex flex-col items-center justify-center py-20 text-center bg-gray-50 rounded-2xl shadow-inner">
                  <motion.div
                    initial={{ scale: 0.5, opacity: 0 }}
                    animate={{ scale: 1, opacity: 1 }}
                    transition={{ type: "spring", stiffness: 260, damping: 20, delay: 0.2 }}
                    className="text-amber-500 mb-6"
                  >
                    <svg
                      xmlns="http://www.w3.org/2000/svg"
                      className="h-20 w-20" // Larger icon
                      fill="none"
                      viewBox="0 0 24 24"
                      stroke="currentColor"
                      strokeWidth={1.5} // Thinner stroke
                    >
                      <path
                        strokeLinecap="round"
                        strokeLinejoin="round"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 10.5a.5.5 0 11-1 0 .5.5 0 011 0zM14 10.5a.5.5 0 11-1 0 .5.5 0 011 0z" // Simple search icon variation
                      />
                      <path strokeLinecap="round" strokeLinejoin="round" d="M9.75 17.25a.75.75 0 01-.75-.75V14.25m0-2.25v-2.25a.75.75 0 011.5 0v2.25m0 2.25a.75.75 0 01-.75.75zm3.75-9a.75.75 0 01.75.75v6.75a.75.75 0 01-1.5 0V9a.75.75 0 01.75-.75z" /> {/* Question mark elements */}
                    </svg>
                  </motion.div>
                  <h3 className="text-xl font-semibold mb-3 text-gray-700">
                    Không tìm thấy sản phẩm phù hợp
                  </h3>
                  <p className="text-gray-500 text-center mb-8 max-w-md">
                    Rất tiếc, chúng tôi không tìm thấy sản phẩm nào khớp với lựa chọn của bạn. Hãy thử điều chỉnh bộ lọc hoặc tìm kiếm lại nhé.
                  </p>
                  <motion.button
                    onClick={resetAllFilters}
                    className="bg-amber-500 hover:bg-amber-600 text-white px-8 py-3 rounded-full transition-colors duration-300 font-semibold shadow-md hover:shadow-lg"
                    whileHover={{ scale: 1.05 }}
                    whileTap={{ scale: 0.95 }}
                  >
                    Đặt lại tất cả bộ lọc
                  </motion.button>
                </div>
              )}
            </motion.div>
          </motion.div>
        </div>
      </section>

      {/* Pagination - Enhanced Styling */}
      {!isLoading && totalPages > 1 && (
        <motion.div
          className="flex justify-center items-center space-x-2 sm:space-x-3 my-16" // Increased margin
          initial={{ opacity: 0, y: 20 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.5, delay: 0.8 }}
        >
          <motion.button
            className={`bg-white text-gray-600 ${currentPage === 1 ? 'opacity-50 cursor-not-allowed' : 'hover:bg-amber-100 hover:text-amber-700'} border border-gray-200 px-4 py-2 rounded-full transition-all duration-300 font-medium flex items-center shadow-sm hover:shadow-md`}
            onClick={() => currentPage > 1 && handlePageChange(currentPage - 1)}
            disabled={currentPage === 1}
            whileHover={{ scale: currentPage !== 1 ? 1.05 : 1 }}
            whileTap={{ scale: currentPage !== 1 ? 0.95 : 1 }}
          >
            <svg xmlns="http://www.w3.org/2000/svg" className="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
              <path strokeLinecap="round" strokeLinejoin="round" d="M15 19l-7-7 7-7" />
            </svg>
            <span>Trước</span>
          </motion.button>

          {getPageNumbers().map((item, index) => (
            <motion.button
              key={index}
              className={`border border-gray-200 rounded-full transition-all duration-300 min-w-[40px] h-10 flex items-center justify-center font-medium shadow-sm hover:shadow-md ${item === currentPage
                ? "bg-amber-500 text-white scale-110 shadow-lg" // Highlight current page
                : item === "..."
                  ? "bg-white text-gray-400 cursor-default px-2" // Ellipsis styling
                  : "bg-white text-gray-700 hover:bg-amber-100 hover:text-amber-700"
                }`}
              onClick={() => item !== "..." && handlePageChange(item)}
              disabled={item === "..."}
              whileHover={{ scale: item !== "..." && item !== currentPage ? 1.1 : (item === currentPage ? 1.1 : 1) }}
              whileTap={{ scale: item !== "..." ? 0.95 : 1 }}
            >
              {item}
            </motion.button>
          ))}

          <motion.button
            className={`bg-white text-gray-600 ${currentPage === totalPages ? 'opacity-50 cursor-not-allowed' : 'hover:bg-amber-100 hover:text-amber-700'} border border-gray-200 px-4 py-2 rounded-full transition-all duration-300 font-medium flex items-center shadow-sm hover:shadow-md`}
            onClick={() => currentPage < totalPages && handlePageChange(currentPage + 1)}
            disabled={currentPage === totalPages}
            whileHover={{ scale: currentPage !== totalPages ? 1.05 : 1 }}
            whileTap={{ scale: currentPage !== totalPages ? 0.95 : 1 }}
          >
            <span>Tiếp</span>
            <svg xmlns="http://www.w3.org/2000/svg" className="h-4 w-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
              <path strokeLinecap="round" strokeLinejoin="round" d="M9 5l7 7-7 7" />
            </svg>
          </motion.button>
        </motion.div>
      )}

      {/* Features Section - Enhanced Styling */}
      <section className="bg-gradient-to-br from-amber-50 to-orange-100 py-16 md:py-20"> {/* Gradient background */}
        <div className="max-w-6xl mx-auto grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8 px-4">
          {[
            {
              title: "Chất lượng cao cấp",
              description: "Vật liệu bền vững, thân thiện môi trường.",
              icon: <LiaTrophySolid className="w-10 h-10 text-amber-600" />, // Slightly darker icon
            },
            {
              title: "Hỗ trợ 24/7",
              description: "Đội ngũ tư vấn chuyên nghiệp, tận tâm.",
              icon: <FaUserAstronaut className="w-10 h-10 text-amber-600" />,
            },
            {
              title: "Bảo hành 12 tháng",
              description: "Cam kết chất lượng, an tâm sử dụng.",
              icon: <MdOutlineSettingsInputComponent className="w-10 h-10 text-amber-600" />, // Changed icon
            },
            {
              title: "Miễn phí vận chuyển",
              description: "Cho đơn hàng trên 5 triệu đồng.",
              icon: <FaShippingFast className="w-10 h-10 text-amber-600" />,
            },
          ].map((item, idx) => (
            <motion.div
              key={idx}
              className="flex items-center space-x-4 bg-white p-6 rounded-xl shadow-lg border border-amber-100 transform transition duration-300 hover:-translate-y-2 hover:shadow-xl" // Lift effect on hover
              initial={{ opacity: 0, y: 30 }}
              whileInView={{ opacity: 1, y: 0 }}
              transition={{ duration: 0.5, delay: idx * 0.1 }}
              viewport={{ once: true, amount: 0.3 }} // Trigger animation when 30% visible
            >
              <div className="p-4 bg-amber-100 rounded-full shadow-inner"> {/* Inner shadow on icon bg */}
                {item.icon}
              </div>
              <div>
                <h2 className="text-lg font-semibold text-gray-800 mb-1">
                  {item.title}
                </h2>
                <p className="text-gray-600 text-sm"> {/* Slightly smaller description */}
                  {item.description}
                </p>
              </div>
            </motion.div>
          ))}
        </div>
      </section>

      {/* Modal chọn biến thể sản phẩm */}
      {variantModalOpen && selectedProduct && (
        <VariantSelectionModal
          isOpen={variantModalOpen}
          onClose={() => {
            setVariantModalOpen(false);
            setSelectedProduct(null);
          }}
          product={selectedProduct}
          onAddToCart={handleVariantAddedToCart}
        />
      )}
    </>
  );
};

export default Products;
