import React from "react";
import { useState, useEffect } from "react";
import { FaUserAstronaut, FaShippingFast, FaFilter } from "react-icons/fa";
import { AiOutlineSearch } from "react-icons/ai";
import { LiaTrophySolid } from "react-icons/lia";
import { IoCartOutline, IoStar } from "react-icons/io5";
import { MdOutlineSettingsInputComponent } from "react-icons/md";
import axios from "axios";
import { Link } from "react-router-dom";
import Banner from "../../../components/Banner";
import { motion } from "framer-motion";

const Products = () => {
  const [products, setProducts] = useState([]);
  const [filteredProducts, setFilteredProducts] = useState([]);
  const [categories, setCategories] = useState([]);
  const [variants, setVariants] = useState([]);
  const [selectedCategories, setSelectedCategories] = useState([]);
  const [selectedVariants, setSelectedVariants] = useState([]);
  const [imageLoadError, setImageLoadError] = useState({});
  const [filterOpen, setFilterOpen] = useState(false);
  const [priceRange, setPriceRange] = useState([0, 10000000]);
  const [searchTerm, setSearchTerm] = useState("");
  const [isLoading, setIsLoading] = useState(true);
  const [currentPage, setCurrentPage] = useState(1);
  const [totalPages, setTotalPages] = useState(1);
  const [itemsPerPage] = useState(9);

  const fadeIn = {
    hidden: { opacity: 0, y: 20 },
    visible: {
      opacity: 1,
      y: 0,
      transition: {
        duration: 0.5,
      },
    },
  };

  const staggerContainer = {
    hidden: { opacity: 0 },
    visible: {
      opacity: 1,
      transition: {
        staggerChildren: 0.1,
      },
    },
  };

  useEffect(() => {
    const fetchProducts = async () => {
      setIsLoading(true);
      try {
        const response = await axios.get(
          `${import.meta.env.VITE_API_URL}/api/products`
        );
        console.log("API Response:", response.data);

        if (response.data.status === "success") {
          setProducts(response.data.data.data);
        }
      } catch (error) {
        console.error("Lỗi khi lấy dữ liệu:", error);
      }
    };

    const fetchCategories = async () => {
      try {
        const response = await axios.get(
          `${import.meta.env.VITE_API_URL}/api/categories`
        );
        console.log("Categories Response:", response.data);
        if (response.data.success) {
          setCategories(response.data.data);
        }
      } catch (error) {
        console.error("Lỗi khi lấy danh mục:", error);
      }
    };

    const fetchVariants = async () => {
      try {
        const response = await axios.get(
          `${import.meta.env.VITE_API_URL}/api/variants`
        );
        if (response.data.status === "success") {
          setVariants(response.data.data);
        }
      } catch (error) {
        console.error("Lỗi khi lấy biến thể:", error);
      } finally {
        setIsLoading(false);
      }
    };

    fetchProducts();
    fetchCategories();
    fetchVariants();
  }, []);

  useEffect(() => {
    if (!isLoading) {
      filterProducts();
    }
  }, [selectedCategories, selectedVariants, priceRange, searchTerm, products, isLoading, currentPage]);

  const filterProducts = () => {
    setIsLoading(true);
    let filtered = [...products];

    if (
      selectedCategories.length === 0 &&
      selectedVariants.length === 0 &&
      priceRange[0] === 0 &&
      priceRange[1] === 10000000 &&
      !searchTerm
    ) {
      setFilteredProducts(products);
      setIsLoading(false);
      return;
    }

    if (selectedCategories.length > 0) {
      filtered = filtered.filter((product) =>
        selectedCategories.includes(product.category_id)
      );
    }

    if (selectedVariants.length > 0) {
      filtered = filtered.filter((product) => {
        if (!product.variants || product.variants.length === 0) return false;

        return product.variants.some((variant) => {
          if (
            !variant.variant_details ||
            !Array.isArray(variant.variant_details)
          )
            return false;

          return selectedVariants.some((selectedValueId) => {
            return variant.variant_details.some((detail) => {
              const selectedVariantValue = variants
                .flatMap((v) => v.values)
                .find((val) => val.id === selectedValueId);

              if (!selectedVariantValue) return false;

              return detail.value === selectedVariantValue.value;
            });
          });
        });
      });
    }

    if (priceRange[0] !== 0 || priceRange[1] !== 10000000) {
      filtered = filtered.filter((product) => {
        let price;
        if (product.has_variants) {
          const variantPrices = product.variants.map(
            (v) => v.discount_price || v.price
          );
          price = Math.min(...variantPrices);
        } else {
          price = product.discount_price || product.price;
        }
        return price >= priceRange[0] && price <= priceRange[1];
      });
    }

    if (searchTerm) {
      filtered = filtered.filter((product) =>
        product.name.toLowerCase().includes(searchTerm.toLowerCase())
      );
    }

    setTotalPages(Math.ceil(filtered.length / itemsPerPage));

    const startIndex = (currentPage - 1) * itemsPerPage;
    const endIndex = startIndex + itemsPerPage;
    filtered = filtered.slice(startIndex, endIndex);

    setFilteredProducts(filtered);
    setIsLoading(false);
  };

  const handleCategoryChange = (categoryId) => {
    setSelectedCategories((prev) => {
      const newCategories = prev.includes(categoryId)
        ? prev.filter((id) => id !== categoryId)
        : [...prev, categoryId];
      return newCategories;
    });
  };

  const handleVariantValueChange = (valueId) => {
    setSelectedVariants((prev) => {
      const newVariants = prev.includes(valueId)
        ? prev.filter((id) => id !== valueId)
        : [...prev, valueId];
      return newVariants;
    });
  };

  const toggleFilter = () => {
    setFilterOpen(!filterOpen);
  };

  const handlePriceChange = (e, index) => {
    const newPriceRange = [...priceRange];
    newPriceRange[index] = parseInt(e.target.value);
    setPriceRange(newPriceRange);
  };

  const resetFilters = () => {
    setSelectedCategories([]);
    setSelectedVariants([]);
    setPriceRange([0, 10000000]);
    setSearchTerm("");
  };

  const handlePageChange = (page) => {
    setCurrentPage(page);
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

  return (
    <>
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
            />
            <button className="absolute right-4 top-1/2 transform -translate-y-1/2 text-amber-500 hover:text-amber-600 transition-colors">
              <AiOutlineSearch className="h-6 w-6" />
            </button>
          </motion.div>

          <motion.div
            className="flex gap-3"
            initial={{ opacity: 0, x: 20 }}
            animate={{ opacity: 1, x: 0 }}
            transition={{ duration: 0.5, delay: 0.4 }}
          >
            <select className="bg-white border-2 border-gray-200 rounded-full px-4 py-2 focus:outline-none focus:ring-2 focus:ring-amber-400">
              <option value="newest">Mới nhất</option>
              <option value="price-asc">Giá: Thấp đến cao</option>
              <option value="price-desc">Giá: Cao đến thấp</option>
              <option value="name-asc">Tên: A-Z</option>
            </select>
          </motion.div>
        </div>

        <div className="flex gap-6 relative">
          <motion.div
            className={`${filterOpen ? "flex" : "hidden"
              } md:flex flex-col w-full md:w-1/4 bg-white p-6 rounded-2xl shadow-sm border border-gray-100 sticky top-4 h-fit transition-all duration-300`}
            initial={{ opacity: 0, x: -20 }}
            animate={{ opacity: 1, x: 0 }}
            transition={{ duration: 0.5, delay: 0.5 }}
          >
            <h3 className="text-lg font-medium mb-6 text-gray-800 border-b pb-4">
              Lọc sản phẩm
            </h3>

            <div className="mb-8">
              <h4 className="text-base font-medium mb-4 flex items-center">
                <span className="w-2 h-5 bg-amber-500 rounded-full mr-2 inline-block"></span>
                Danh mục
              </h4>
              <div className="space-y-3 text-gray-600">
                {categories.map((category) => (
                  <label
                    key={category.id}
                    className="flex items-center space-x-3 cursor-pointer group"
                  >
                    <input
                      type="checkbox"
                      checked={selectedCategories.includes(category.id)}
                      onChange={() => handleCategoryChange(category.id)}
                      className="form-checkbox h-5 w-5 rounded text-amber-500 border-gray-300 focus:ring-amber-500 transition-all"
                    />
                    <span className="group-hover:text-amber-500 transition-colors">
                      {category.name}
                    </span>
                  </label>
                ))}
              </div>
            </div>

            {variants.map((variant) => (
              <div key={variant.id} className="mb-8">
                <h4 className="text-base font-medium mb-4 flex items-center">
                  <span className="w-2 h-5 bg-amber-500 rounded-full mr-2 inline-block"></span>
                  {variant.name}
                </h4>
                <div className="space-y-3 text-gray-600">
                  {variant.values.map((value) => (
                    <label
                      key={value.id}
                      className="flex items-center space-x-3 cursor-pointer group"
                    >
                      <input
                        type="checkbox"
                        checked={selectedVariants.includes(value.id)}
                        onChange={() => handleVariantValueChange(value.id)}
                        className="form-checkbox h-5 w-5 rounded text-amber-500 border-gray-300 focus:ring-amber-500"
                      />
                      <span className="group-hover:text-amber-500 transition-colors">
                        {value.value}
                      </span>
                    </label>
                  ))}
                </div>
              </div>
            ))}

            <div className="mb-8">
              <h4 className="text-base font-medium mb-4 flex items-center">
                <span className="w-2 h-5 bg-amber-500 rounded-full mr-2 inline-block"></span>
                Khoảng giá
              </h4>
              <div className="space-y-6">
                <div className="flex justify-between text-sm text-gray-600">
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
                <input
                  type="range"
                  min="0"
                  max="10000000"
                  step="100000"
                  value={priceRange[0]}
                  onChange={(e) => handlePriceChange(e, 0)}
                  className="w-full accent-amber-500"
                />
                <input
                  type="range"
                  min="0"
                  max="10000000"
                  step="100000"
                  value={priceRange[1]}
                  onChange={(e) => handlePriceChange(e, 1)}
                  className="w-full accent-amber-500"
                />
              </div>
            </div>
          </motion.div>

          <motion.div
            className="w-full md:w-3/4"
            variants={staggerContainer}
            initial="hidden"
            animate="visible"
          >
            <motion.div
              className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6"
              variants={staggerContainer}
              initial="hidden"
              animate="visible"
            >
              {isLoading ? (
                [1, 2, 3, 4, 5, 6].map((index) => (
                  <motion.div
                    key={index}
                    className="bg-white rounded-xl shadow-sm overflow-hidden h-[400px] animate-pulse"
                    variants={fadeIn}
                  >
                    <div className="aspect-square bg-gray-200"></div>
                    <div className="p-5">
                      <div className="h-4 bg-gray-200 rounded w-3/4 mb-3"></div>
                      <div className="h-3 bg-gray-200 rounded w-full mb-2"></div>
                      <div className="h-3 bg-gray-200 rounded w-full mb-3"></div>
                      <div className="h-5 bg-gray-200 rounded w-1/3"></div>
                    </div>
                  </motion.div>
                ))
              ) : filteredProducts.length > 0 ? (
                filteredProducts.map((product) => (
                  <motion.div
                    key={product.id}
                    className="bg-white rounded-xl shadow-sm hover:shadow-xl transition-all duration-300 overflow-hidden border border-gray-100 group"
                    variants={fadeIn}
                    whileHover={{ y: -8 }}
                  >
                    <Link
                      to={`/product-detail/${product.id}`}
                      className="block"
                    >
                      <div className="relative overflow-hidden">
                        <div className="aspect-square overflow-hidden">
                          <img
                            src={
                              product.image_thumnail
                                ? product.image_thumnail.startsWith("http")
                                  ? product.image_thumnail
                                  : `http://localhost:8000/storage/${product.image_thumnail}`
                                : "https://via.placeholder.com/300x300?text=No+Image"
                            }
                            alt={product.name}
                            className="w-full h-full object-cover group-hover:scale-110 transition-all duration-700"
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
                        </div>

                        <div className="absolute top-3 left-3 bg-amber-500 text-white text-xs font-bold px-3 py-1 rounded-full">
                          MỚI
                        </div>

                        <div className="absolute bottom-3 right-3 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                          <motion.button
                            className="bg-white text-amber-500 p-3 rounded-full shadow-md hover:bg-amber-500 hover:text-white transition-all duration-300"
                            whileHover={{ scale: 1.1 }}
                            whileTap={{ scale: 0.9 }}
                          >
                            <IoCartOutline className="text-xl" />
                          </motion.button>
                        </div>
                      </div>

                      <div className="p-5">
                        <div className="flex items-center mb-2">
                          {[1, 2, 3, 4, 5].map((star) => (
                            <IoStar
                              key={star}
                              className={`${star <= 4 ? "text-amber-400" : "text-gray-300"
                                } w-4 h-4`}
                            />
                          ))}
                          <span className="text-gray-500 text-sm ml-2">
                            (4.0)
                          </span>
                        </div>

                        <h3 className="font-semibold text-gray-800 mb-1 group-hover:text-amber-500 transition-colors">
                          {product.name}
                        </h3>

                        <div
                          className="text-gray-500 text-sm mb-3 line-clamp-2"
                          dangerouslySetInnerHTML={{
                            __html:
                              product.description ||
                              "Sản phẩm nội thất cao cấp, bền đẹp và thân thiện với môi trường",
                          }}
                        />

                        <div className="flex justify-between items-center">
                          {product.has_variants ? (
                            <div className="flex-1">
                              {product.price_range?.min_discount ? (
                                <div className="flex flex-col">
                                  <p className="text-amber-600 font-semibold text-lg">
                                    {new Intl.NumberFormat("vi-VN", {
                                      style: "currency",
                                      currency: "VND",
                                    }).format(product.price_range.min_discount)}
                                    {product.price_range.max_discount &&
                                      product.price_range.max_discount !==
                                      product.price_range.min_discount &&
                                      ` - ${new Intl.NumberFormat("vi-VN", {
                                        style: "currency",
                                        currency: "VND",
                                      }).format(
                                        product.price_range.max_discount
                                      )}`}
                                  </p>
                                  <p className="text-gray-400 line-through text-sm">
                                    {new Intl.NumberFormat("vi-VN", {
                                      style: "currency",
                                      currency: "VND",
                                    }).format(product.price_range.min)}
                                  </p>
                                </div>
                              ) : (
                                <p className="text-amber-600 font-semibold text-lg">
                                  {new Intl.NumberFormat("vi-VN", {
                                    style: "currency",
                                    currency: "VND",
                                  }).format(product.price_range?.min || 0)}
                                  {product.price_range?.max &&
                                    product.price_range.max !==
                                    product.price_range.min &&
                                    ` - ${new Intl.NumberFormat("vi-VN", {
                                      style: "currency",
                                      currency: "VND",
                                    }).format(product.price_range.max)}`}
                                </p>
                              )}
                            </div>
                          ) : (
                            <div className="flex-1">
                              {product.discount_price ? (
                                <div className="flex flex-col">
                                  <p className="text-amber-600 font-semibold text-lg">
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
                                <p className="text-amber-600 font-semibold text-lg">
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
                <div className="col-span-full flex flex-col items-center justify-center py-12">
                  <div className="text-amber-500 mb-4">
                    <svg
                      xmlns="http://www.w3.org/2000/svg"
                      className="h-16 w-16"
                      fill="none"
                      viewBox="0 0 24 24"
                      stroke="currentColor"
                    >
                      <path
                        strokeLinecap="round"
                        strokeLinejoin="round"
                        strokeWidth={2}
                        d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"
                      />
                    </svg>
                  </div>
                  <h3 className="text-lg font-semibold mb-2">
                    Không tìm thấy sản phẩm
                  </h3>
                  <p className="text-gray-500 text-center mb-6">
                    Không có sản phẩm nào phù hợp với tiêu chí lọc của bạn.
                  </p>
                  <button
                    onClick={resetFilters}
                    className="bg-amber-500 hover:bg-amber-600 text-white px-6 py-2 rounded-full transition-colors"
                  >
                    Đặt lại bộ lọc
                  </button>
                </div>
              )}
            </motion.div>
          </motion.div>
        </div>
      </section>

      <motion.div
        className="flex justify-center space-x-3 my-12"
        initial={{ opacity: 0, y: 20 }}
        animate={{ opacity: 1, y: 0 }}
        transition={{ duration: 0.5, delay: 0.8 }}
      >
        <button
          className={`bg-white text-gray-700 ${currentPage === 1 ? 'opacity-50 cursor-not-allowed' : 'hover:bg-amber-100'} border border-gray-200 px-4 py-2 rounded-full transition-all duration-300 font-medium flex items-center`}
          onClick={() => currentPage > 1 && handlePageChange(currentPage - 1)}
          disabled={currentPage === 1}
        >
          <svg
            xmlns="http://www.w3.org/2000/svg"
            className="h-4 w-4 mr-1"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
          >
            <path
              strokeLinecap="round"
              strokeLinejoin="round"
              strokeWidth={2}
              d="M15 19l-7-7 7-7"
            />
          </svg>
          <span>Trước</span>
        </button>

        {getPageNumbers().map((item, index) => (
          <button
            key={index}
            className={`${item === currentPage
              ? "bg-amber-500 text-white"
              : item === "..."
                ? "bg-white text-gray-400 cursor-default"
                : "bg-white text-gray-700 hover:bg-amber-100"
              } border border-gray-200 px-4 py-2 rounded-full transition-all duration-300 min-w-[40px] font-medium`}
            onClick={() => item !== "..." && handlePageChange(item)}
            disabled={item === "..."}
          >
            {item}
          </button>
        ))}

        <button
          className={`bg-white text-gray-700 ${currentPage === totalPages ? 'opacity-50 cursor-not-allowed' : 'hover:bg-amber-100'} border border-gray-200 px-4 py-2 rounded-full transition-all duration-300 font-medium flex items-center`}
          onClick={() => currentPage < totalPages && handlePageChange(currentPage + 1)}
          disabled={currentPage === totalPages}
        >
          <span>Tiếp</span>
          <svg
            xmlns="http://www.w3.org/2000/svg"
            className="h-4 w-4 ml-1"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
          >
            <path
              strokeLinecap="round"
              strokeLinejoin="round"
              strokeWidth={2}
              d="M9 5l7 7-7 7"
            />
          </svg>
        </button>
      </motion.div>

      <section className="bg-amber-50 py-16">
        <div className="max-w-6xl mx-auto grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 px-4">
          {[
            {
              title: "Chất lượng cao cấp",
              description: "Vật liệu bền vững thân thiện môi trường",
              icon: <LiaTrophySolid className="w-10 h-10 text-amber-500" />,
            },
            {
              title: "Hỗ trợ 24/7",
              description: "Đội ngũ tư vấn chuyên nghiệp",
              icon: <FaUserAstronaut className="w-10 h-10 text-amber-500" />,
            },
            {
              title: "Bảo hành 12 tháng",
              description: "Cam kết chất lượng sản phẩm",
              icon: <LiaTrophySolid className="w-10 h-10 text-amber-500" />,
            },
            {
              title: "Miễn phí vận chuyển",
              description: "Cho đơn hàng trên 5 triệu đồng",
              icon: <FaShippingFast className="w-10 h-10 text-amber-500" />,
            },
          ].map((item, idx) => (
            <motion.div
              key={idx}
              className="flex items-start space-x-4 bg-white p-6 rounded-xl shadow-sm border border-amber-100"
              initial={{ opacity: 0, y: 20 }}
              whileInView={{ opacity: 1, y: 0 }}
              transition={{ duration: 0.5, delay: idx * 0.1 }}
              viewport={{ once: true }}
            >
              <div className="p-3 bg-amber-100 rounded-full">{item.icon}</div>
              <div>
                <h2 className="text-lg font-semibold text-gray-800">
                  {item.title}
                </h2>
                <p className="text-gray-600">{item.description}</p>
              </div>
            </motion.div>
          ))}
        </div>
      </section>
    </>
  );
};

export default Products;
