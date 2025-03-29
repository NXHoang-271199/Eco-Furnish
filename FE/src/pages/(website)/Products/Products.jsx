import React from "react";
import { useState, useEffect } from "react";
import {
  FaUserAstronaut,
  FaShippingFast,
  FaStar,
  FaFilter,
  FaTimes,
} from "react-icons/fa";
import { AiOutlineSearch } from "react-icons/ai";
import { LiaTrophySolid } from "react-icons/lia";
import { IoCartOutline } from "react-icons/io5";
import { MdOutlineSettingsInputComponent, MdLocalOffer } from "react-icons/md";
import { BsStarFill, BsStarHalf, BsStar } from "react-icons/bs";
import axios from "axios";
import { Link } from "react-router-dom";
import Banner from "../../../components/Banner";
import { toast } from "react-toastify";

const Products = () => {
  const [products, setProducts] = useState([]);
  const [filteredProducts, setFilteredProducts] = useState([]);
  const [imageLoadError, setImageLoadError] = useState({});
  const [searchTerm, setSearchTerm] = useState("");
  const [isFilterActive, setIsFilterActive] = useState(false);
  const [isLoading, setIsLoading] = useState(true);

  // Bộ lọc
  const [filters, setFilters] = useState({
    colors: [],
    priceRange: { min: "", max: "" },
    priceRanges: [],
    categories: [],
    sizes: [],
    ratings: 0,
    inStock: false,
    onSale: false,
    sortBy: "",
  });

  // Các dữ liệu mẫu
  const colorOptions = ["Trắng", "Đen", "Nâu", "Xám", "Xanh", "Đỏ"];
  const categoryOptions = ["Bàn", "Ghế", "Giường", "Tủ", "Kệ", "Đèn"];
  const sizeOptions = ["Nhỏ", "Vừa", "Lớn"];
  const sortOptions = [
    { value: "newest", label: "Mới nhất" },
    { value: "bestselling", label: "Bán chạy" },
    { value: "price_asc", label: "Giá: Thấp đến cao" },
    { value: "price_desc", label: "Giá: Cao đến thấp" },
  ];
  const priceRangeOptions = [
    { min: 0, max: 500000, label: "Dưới 500.000đ" },
    { min: 500000, max: 1000000, label: "500.000đ - 1.000.000đ" },
    { min: 1000000, max: 2000000, label: "1.000.000đ - 2.000.000đ" },
    { min: 2000000, max: 5000000, label: "2.000.000đ - 5.000.000đ" },
    { min: 5000000, max: Infinity, label: "Trên 5.000.000đ" },
  ];

  useEffect(() => {
    const fetchProducts = async () => {
      try {
        const response = await axios.get(
          `${import.meta.env.VITE_API_URL}/api/products`
        );
        console.log("API Response:", response.data);

        if (
          response.data.status === "success" &&
          Array.isArray(response.data.data.data)
        ) {
          // Log từng sản phẩm để kiểm tra đường dẫn ảnh
          response.data.data.data.forEach((product) => {
            console.log("Tên sản phẩm:", product.name);
            console.log("Đường dẫn ảnh:", product.image_thumnail);
          });
          setProducts(response.data.data.data);
          setFilteredProducts(response.data.data.data);
        }
      } catch (error) {
        console.error("Lỗi khi lấy dữ liệu:", error);
      } finally {
        setIsLoading(false);
      }
    };

    fetchProducts();
  }, []);

  // Xử lý khi thay đổi bộ lọc
  useEffect(() => {
    applyFilters();
  }, [filters, searchTerm, products]);

  // Xử lý thay đổi checkbox
  const handleCheckboxChange = (filterType, value) => {
    setFilters((prevFilters) => {
      const updatedFilters = { ...prevFilters };

      if (filterType === "inStock" || filterType === "onSale") {
        updatedFilters[filterType] = !updatedFilters[filterType];
      } else {
        // Đối với mảng như colors, categories, sizes
        if (updatedFilters[filterType].includes(value)) {
          updatedFilters[filterType] = updatedFilters[filterType].filter(
            (item) => item !== value
          );
        } else {
          updatedFilters[filterType] = [...updatedFilters[filterType], value];
        }
      }

      setIsFilterActive(checkIfFiltersActive(updatedFilters));
      return updatedFilters;
    });
  };

  // Xử lý thay đổi rating
  const handleRatingChange = (rating) => {
    setFilters((prevFilters) => ({
      ...prevFilters,
      ratings: prevFilters.ratings === rating ? 0 : rating,
    }));
    setIsFilterActive(checkIfFiltersActive({ ...filters, ratings: rating }));
  };

  // Xử lý thay đổi giá
  const handlePriceChange = (type, value) => {
    setFilters((prevFilters) => ({
      ...prevFilters,
      priceRange: {
        ...prevFilters.priceRange,
        [type]: value,
      },
    }));

    const newFilters = {
      ...filters,
      priceRange: {
        ...filters.priceRange,
        [type]: value,
      },
    };

    setIsFilterActive(checkIfFiltersActive(newFilters));
  };

  // Xử lý thay đổi sắp xếp
  const handleSortChange = (e) => {
    const value = e.target.value;
    setFilters((prevFilters) => ({
      ...prevFilters,
      sortBy: value,
    }));

    setIsFilterActive(checkIfFiltersActive({ ...filters, sortBy: value }));
  };

  // Xử lý thay đổi khoảng giá
  const handlePriceRangeChange = (range) => {
    setFilters((prevFilters) => {
      const isRangeSelected = prevFilters.priceRanges.some(
        (r) => r.min === range.min && r.max === range.max
      );

      let updatedPriceRanges;
      if (isRangeSelected) {
        // Nếu khoảng giá đã được chọn, loại bỏ khỏi danh sách
        updatedPriceRanges = prevFilters.priceRanges.filter(
          (r) => !(r.min === range.min && r.max === range.max)
        );
      } else {
        // Nếu khoảng giá chưa được chọn, thêm vào danh sách
        updatedPriceRanges = [...prevFilters.priceRanges, range];
      }

      const updatedFilters = {
        ...prevFilters,
        priceRanges: updatedPriceRanges,
        // Xóa giá trị của priceRange nếu đang sử dụng khoảng giá
        priceRange:
          updatedPriceRanges.length > 0
            ? { min: "", max: "" }
            : prevFilters.priceRange,
      };

      setIsFilterActive(checkIfFiltersActive(updatedFilters));
      return updatedFilters;
    });
  };

  // Hàm kiểm tra xem có bộ lọc nào đang hoạt động không
  const checkIfFiltersActive = (currentFilters) => {
    return (
      currentFilters.colors.length > 0 ||
      currentFilters.categories.length > 0 ||
      currentFilters.sizes.length > 0 ||
      currentFilters.ratings > 0 ||
      currentFilters.priceRange.min !== "" ||
      currentFilters.priceRange.max !== "" ||
      currentFilters.priceRanges.length > 0 ||
      currentFilters.inStock ||
      currentFilters.onSale ||
      currentFilters.sortBy !== ""
    );
  };

  // Áp dụng tất cả bộ lọc
  const applyFilters = () => {
    let result = [...products];

    // Tìm kiếm theo tên
    if (searchTerm.trim() !== "") {
      result = result.filter((product) =>
        product.name.toLowerCase().includes(searchTerm.toLowerCase())
      );
    }

    // Lọc theo màu sắc
    if (filters.colors.length > 0) {
      // Thêm logic lọc màu thực tế dựa trên dữ liệu API
      // Giả định: mỗi product có thuộc tính color
      result = result.filter(
        (product) =>
          filters.colors.includes(product.color) ||
          // Xử lý tạm cho demo - cho qua nếu không có thuộc tính color
          !product.color
      );
    }

    // Lọc theo danh mục
    if (filters.categories.length > 0) {
      // Giả định: mỗi product có thuộc tính category (có thể là object hoặc string)
      result = result.filter(
        (product) =>
          (typeof product.category === "object" &&
            product.category &&
            filters.categories.includes(product.category.name)) ||
          (typeof product.category === "string" &&
            filters.categories.includes(product.category)) ||
          // Xử lý tạm cho demo
          !product.category
      );
    }

    // Lọc theo kích thước
    if (filters.sizes.length > 0) {
      // Giả định: mỗi product có thuộc tính size
      result = result.filter(
        (product) =>
          filters.sizes.includes(product.size) ||
          // Xử lý tạm cho demo
          !product.size
      );
    }

    // Lọc theo giá - phương thức nhập thủ công
    if (filters.priceRange.min !== "") {
      result = result.filter(
        (product) => product.price >= Number(filters.priceRange.min)
      );
    }

    if (filters.priceRange.max !== "") {
      result = result.filter(
        (product) => product.price <= Number(filters.priceRange.max)
      );
    }

    // Lọc theo khoảng giá - phương thức chọn khoảng
    if (filters.priceRanges.length > 0) {
      result = result.filter((product) => {
        return filters.priceRanges.some(
          (range) => product.price >= range.min && product.price <= range.max
        );
      });
    }

    // Lọc theo đánh giá
    if (filters.ratings > 0) {
      // Giả định: mỗi product có thuộc tính rating
      result = result.filter(
        (product) =>
          product.rating >= filters.ratings ||
          // Xử lý tạm cho demo
          !product.rating
      );
    }

    // Lọc sản phẩm còn hàng
    if (filters.inStock) {
      // Giả định: mỗi product có thuộc tính stock_quantity
      result = result.filter(
        (product) =>
          (product.stock_quantity && product.stock_quantity > 0) ||
          // Xử lý tạm cho demo
          !product.stock_quantity
      );
    }

    // Lọc sản phẩm đang giảm giá
    if (filters.onSale) {
      // Giả định: mỗi product có thuộc tính discount_percentage hoặc is_on_sale
      result = result.filter(
        (product) =>
          (product.discount_percentage && product.discount_percentage > 0) ||
          product.is_on_sale ||
          // Xử lý tạm cho demo
          (!product.discount_percentage && !product.is_on_sale)
      );
    }

    // Sắp xếp sản phẩm
    if (filters.sortBy) {
      switch (filters.sortBy) {
        case "price_asc":
          result.sort((a, b) => a.price - b.price);
          break;
        case "price_desc":
          result.sort((a, b) => b.price - a.price);
          break;
        case "newest":
          // Giả định: mỗi product có thuộc tính created_at
          result.sort(
            (a, b) => new Date(b.created_at || 0) - new Date(a.created_at || 0)
          );
          break;
        case "bestselling":
          // Giả định: mỗi product có thuộc tính sales_count
          result.sort((a, b) => (b.sales_count || 0) - (a.sales_count || 0));
          break;
        default:
          break;
      }
    }

    setFilteredProducts(result);
  };

  // Reset tất cả bộ lọc
  const resetFilters = () => {
    setFilters({
      colors: [],
      priceRange: { min: "", max: "" },
      priceRanges: [],
      categories: [],
      sizes: [],
      ratings: 0,
      inStock: false,
      onSale: false,
      sortBy: "",
    });
    setSearchTerm("");
    setIsFilterActive(false);
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

  // Hàm thêm sản phẩm vào giỏ hàng
  const addToCart = async (product) => {
    try {
      // Lấy token từ localStorage
      const token = localStorage.getItem("token");

      if (!token) {
        toast.error("Vui lòng đăng nhập để thêm sản phẩm vào giỏ hàng");
        return;
      }

      // Kiểm tra tồn kho
      if (product.stock_quantity === 0) {
        toast.error("Sản phẩm đã hết hàng!");
        return;
      }

      const response = await axios.post(
        `${import.meta.env.VITE_API_URL}/api/cart/add`,
        {
          product_id: product.id,
          quantity: 1, // Mặc định thêm 1 sản phẩm
        },
        {
          headers: {
            Authorization: `Bearer ${token}`,
          },
        }
      );

      if (response.data.status === "success") {
        toast.success("Đã thêm sản phẩm vào giỏ hàng!");
      } else {
        toast.error(response.data.message || "Có lỗi xảy ra");
      }
    } catch (error) {
      console.error("Lỗi khi thêm vào giỏ hàng:", error);
      toast.error(
        error.response?.data?.message || "Không thể thêm vào giỏ hàng"
      );
    }
  };

  return (
    <div className="pt-20">
      {/* Banner */}
      <section className="max-w-6xl mx-auto mt-10">
        <Banner />
      </section>

      {/* Bộ lọc & tìm kiếm */}
      <section className="max-w-6xl mx-auto my-8">
        <div className="flex justify-between items-center mb-6">
          <h2 className="text-xl font-semibold flex items-center">
            <MdOutlineSettingsInputComponent className="mr-2 text-gray-700" />{" "}
            Bộ lọc
            {isFilterActive && (
              <span className="ml-2 bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded-full">
                Đang lọc
              </span>
            )}
          </h2>
          <div className="flex items-center space-x-4">
            {isFilterActive && (
              <button
                onClick={resetFilters}
                className="text-sm text-red-600 flex items-center"
              >
                <FaTimes className="mr-1" /> Xóa lọc
              </button>
            )}
            <div className="relative">
              <input
                type="text"
                placeholder="Tìm kiếm sản phẩm..."
                value={searchTerm}
                onChange={(e) => setSearchTerm(e.target.value)}
                className="px-4 py-2 border rounded-full w-72 focus:outline-none focus:ring-2 focus:ring-blue-400"
              />
              <button className="absolute right-3 top-2 text-gray-500 hover:text-gray-700">
                <AiOutlineSearch className="h-5 w-5" />
              </button>
            </div>
          </div>
        </div>

        <div className="flex gap-10">
          {/* Bộ lọc */}
          <div className="w-1/4 bg-gray-50 p-4 rounded-lg">
            {/* Sắp xếp */}
            <div className="mb-6">
              <h3 className="text-lg font-medium mb-3">Sắp xếp theo</h3>
              <select
                value={filters.sortBy}
                onChange={handleSortChange}
                className="w-full p-2 border rounded-lg"
              >
                <option value="">Mặc định</option>
                {sortOptions.map((option) => (
                  <option key={option.value} value={option.value}>
                    {option.label}
                  </option>
                ))}
              </select>
            </div>

            {/* Lọc màu sắc */}
            <div className="mb-6">
              <h3 className="text-lg font-medium mb-3">Màu sắc</h3>
              <div className="space-y-2 flex flex-wrap gap-2">
                {colorOptions.map((color) => (
                  <label
                    key={color}
                    className="flex items-center space-x-2 cursor-pointer"
                  >
                    <input
                      type="checkbox"
                      className="accent-blue-500"
                      checked={filters.colors.includes(color)}
                      onChange={() => handleCheckboxChange("colors", color)}
                    />
                    <span>{color}</span>
                  </label>
                ))}
              </div>
            </div>

            {/* Lọc danh mục */}
            <div className="mb-6">
              <h3 className="text-lg font-medium mb-3">Danh mục</h3>
              <div className="space-y-2 flex flex-wrap gap-2">
                {categoryOptions.map((category) => (
                  <label
                    key={category}
                    className="flex items-center space-x-2 cursor-pointer"
                  >
                    <input
                      type="checkbox"
                      className="accent-blue-500"
                      checked={filters.categories.includes(category)}
                      onChange={() =>
                        handleCheckboxChange("categories", category)
                      }
                    />
                    <span>{category}</span>
                  </label>
                ))}
              </div>
            </div>

            {/* Lọc kích thước */}
            <div className="mb-6">
              <h3 className="text-lg font-medium mb-3">Kích thước</h3>
              <div className="space-y flex flex-wrap gap-2">
                {sizeOptions.map((size) => (
                  <label
                    key={size}
                    className="flex items-center space-x-2 cursor-pointer"
                  >
                    <input
                      type="checkbox"
                      className="accent-blue-500"
                      checked={filters.sizes.includes(size)}
                      onChange={() => handleCheckboxChange("sizes", size)}
                    />
                    <span>{size}</span>
                  </label>
                ))}
              </div>
            </div>

            {/* Lọc giá */}
            <div className="mb-6">
              <h3 className="text-lg font-medium mb-3">Giá (VNĐ)</h3>
              <div className="space-y-2">
                {priceRangeOptions.map((range, index) => (
                  <label
                    key={index}
                    className="flex items-center space-x-2 cursor-pointer"
                  >
                    <input
                      type="checkbox"
                      className="accent-blue-500"
                      checked={filters.priceRanges.some(
                        (r) => r.min === range.min && r.max === range.max
                      )}
                      onChange={() => handlePriceRangeChange(range)}
                    />
                    <span>{range.label}</span>
                  </label>
                ))}
              </div>
              <div className="mt-4 border-t pt-3">
                <p className="text-sm font-medium mb-2">
                  Hoặc chọn giá thủ công:
                </p>
                <div className="flex gap-2">
                  <input
                    type="number"
                    placeholder="Tối thiểu"
                    value={filters.priceRange.min}
                    onChange={(e) => handlePriceChange("min", e.target.value)}
                    className="border rounded-lg px-3 py-2 w-1/2"
                    min={0}
                  />
                  <input
                    type="number"
                    placeholder="Tối đa"
                    value={filters.priceRange.max}
                    onChange={(e) => handlePriceChange("max", e.target.value)}
                    className="border rounded-lg px-3 py-2 w-1/2"
                    min={0}
                  />
                </div>
              </div>
            </div>

            {/* Lọc đánh giá */}
            <div className="mb-6">
              <h3 className="text-lg font-medium mb-3">Đánh giá</h3>
              <div className="space-y-2">
                {[5, 4, 3, 2, 1].map((rating) => (
                  <div
                    key={rating}
                    className="flex items-center space-x-2 cursor-pointer"
                    onClick={() => handleRatingChange(rating)}
                  >
                    <div
                      className={`flex ${
                        filters.ratings === rating
                          ? "text-yellow-500"
                          : "text-gray-400"
                      }`}
                    >
                      {Array(rating)
                        .fill()
                        .map((_, i) => (
                          <FaStar key={i} />
                        ))}
                      {Array(5 - rating)
                        .fill()
                        .map((_, i) => (
                          <FaStar key={i} className="text-gray-200" />
                        ))}
                    </div>
                    <span>{rating} sao trở lên</span>
                  </div>
                ))}
              </div>
            </div>

            {/* Lọc tình trạng */}
            <div className="mb-6">
              <h3 className="text-lg font-medium mb-3">Tình trạng</h3>
              <div className="space-y-2">
                <label className="flex items-center space-x-2 cursor-pointer">
                  <input
                    type="checkbox"
                    className="accent-blue-500"
                    checked={filters.inStock}
                    onChange={() => handleCheckboxChange("inStock")}
                  />
                  <span>Còn hàng</span>
                </label>
                <label className="flex items-center space-x-2 cursor-pointer">
                  <input
                    type="checkbox"
                    className="accent-blue-500"
                    checked={filters.onSale}
                    onChange={() => handleCheckboxChange("onSale")}
                  />
                  <span>Đang giảm giá</span>
                </label>
              </div>
            </div>
          </div>

          {/* Danh sách sản phẩm */}
          <div className="w-3/4 grid grid-cols-2 md:grid-cols-2 lg:grid-cols-3 gap-5">
            {filteredProducts.length > 0 ? (
              filteredProducts.map((product) => (
                <div
                  key={product.id}
                  className="bg-white rounded-lg shadow-md hover:shadow-lg transition-all duration-300 overflow-hidden relative h-[440px]"
                >
                  {/* Badges */}
                  {product.is_on_sale && (
                    <span className="absolute top-2 left-2 z-10 bg-red-500 text-white text-xs px-2 py-1 rounded-full">
                      Giảm giá
                    </span>
                  )}

                  {product.stock_quantity === 0 && (
                    <span className="absolute top-2 right-2 z-10 bg-gray-500 text-white text-xs px-2 py-1 rounded-full">
                      Hết hàng
                    </span>
                  )}

                  {/* Phần ảnh */}
                  <div className="h-64 overflow-hidden">
                    <Link to={`/product/${product.id}`}>
                      <img
                        src={
                          product.image_thumnail
                            ? product.image_thumnail.startsWith("http")
                              ? product.image_thumnail
                              : `http://localhost:8000/storage/${product.image_thumnail}`
                            : "https://via.placeholder.com/300x200?text=No+Image"
                        }
                        alt={product.name}
                        className="w-full h-64 object-cover object-center hover:scale-105 transition-transform duration-500"
                        onError={(e) => {
                          const productId = product.id;
                          if (!imageLoadError[productId]) {
                            setImageLoadError((prev) => ({
                              ...prev,
                              [productId]: true,
                            }));
                            e.target.src = "/images/no-image.png";
                          }
                        }}
                      />
                    </Link>
                  </div>

                  {/* Phần thông tin sản phẩm */}
                  <div className="p-4 flex flex-col h-[176px]">
                    {/* Hiển thị danh mục nếu có */}
                    {product.category && (
                      <p className="text-xs text-gray-500 mb-1 font-medium uppercase tracking-wider truncate">
                        {typeof product.category === "object"
                          ? product.category.name
                          : product.category}
                      </p>
                    )}

                    <h3
                      onClick={() =>
                        (window.location.href = `/product/${product.id}`)
                      }
                      className="text-gray-800 font-semibold mb-auto line-clamp-2 text-base cursor-pointer hover:text-blue-600"
                    >
                      {product.name}
                    </h3>

                    {/* Hiển thị đánh giá nếu có */}
                    <div className="flex items-center mb-2 mt-2">
                      {product.rating ? (
                        <>
                          {renderStars(product.rating)}
                          <span className="text-sm text-gray-500 ml-1">
                            ({product.rating_count || 0})
                          </span>
                        </>
                      ) : (
                        <div className="flex text-gray-300">
                          {Array(5)
                            .fill()
                            .map((_, i) => (
                              <BsStar key={i} />
                            ))}
                          <span className="text-sm text-gray-400 ml-1">
                            (0)
                          </span>
                        </div>
                      )}
                    </div>

                    <div className="flex justify-between items-center mt-auto pt-2 border-t border-gray-100">
                      <p className="text-blue-600 font-bold">
                        {new Intl.NumberFormat("vi-VN", {
                          style: "currency",
                          currency: "VND",
                        }).format(product.price)}
                      </p>

                      <div className="flex items-center space-x-2">
                        <Link
                          to={`/product/${product.id}`}
                          className="text-sm bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded-md transition-colors"
                        >
                          Xem chi tiết
                        </Link>
                        <button
                          onClick={() => addToCart(product)}
                          className="w-8 h-8 bg-gray-100 hover:bg-blue-50 rounded-full flex items-center justify-center transition-colors duration-300"
                          title="Thêm vào giỏ hàng"
                        >
                          <IoCartOutline className="text-gray-700 text-lg" />
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
              ))
            ) : (
              <div className="col-span-2 md:col-span-2 lg:col-span-3 py-10 text-center">
                <div className="bg-gray-50 rounded-lg p-6 max-w-lg mx-auto">
                  <AiOutlineSearch className="text-4xl text-gray-400 mx-auto mb-3" />
                  <p className="text-gray-500">
                    {searchTerm || isFilterActive
                      ? "Không tìm thấy sản phẩm phù hợp"
                      : "Đang tải sản phẩm..."}
                  </p>
                  {isFilterActive && (
                    <button
                      onClick={resetFilters}
                      className="mt-3 px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors text-sm"
                    >
                      Xóa bộ lọc
                    </button>
                  )}
                </div>
              </div>
            )}
          </div>
        </div>
      </section>

      {/* Phân trang */}
      <div className="flex justify-center space-x-3 my-10">
        {[1, 2, 3, "Next"].map((item) => (
          <button
            key={item}
            className="border px-4 py-2 rounded-full bg-gray-100 hover:bg-orange-300 transition"
          >
            {item}
          </button>
        ))}
      </div>

      {/* Footer */}
      <section className="bg-gray-100 py-16">
        <div className="max-w-6xl mx-auto grid grid-cols-4 gap-8">
          {[
            "High Quality",
            "24/7 Support",
            "Warranty Protection",
            "Free Shipping",
          ].map((title, idx) => (
            <div key={idx} className="flex items-center space-x-4">
              {idx === 0 || idx === 2 ? (
                <LiaTrophySolid className="w-10 h-10 text-gray-600" />
              ) : null}
              {idx === 1 ? (
                <FaUserAstronaut className="w-10 h-10 text-gray-600" />
              ) : null}
              {idx === 3 ? (
                <FaShippingFast className="w-10 h-10 text-gray-600" />
              ) : null}
              <div>
                <h2 className="text-lg font-semibold">{title}</h2>
                <p className="text-gray-500">
                  {idx === 3 ? "Order over $150" : "Best in class"}
                </p>
              </div>
            </div>
          ))}
        </div>
      </section>
    </div>
  );
};

export default Products;
