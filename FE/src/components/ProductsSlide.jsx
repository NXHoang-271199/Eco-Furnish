import React, { useState, useRef, useEffect } from "react";
import { IoCartOutline } from "react-icons/io5";
import { BsStarFill, BsStarHalf, BsStar } from "react-icons/bs";
import axios from "axios";
import { Link, Links } from "react-router-dom";
const ProductsSlide = () => {
  const [startIndex, setStartIndex] = useState(0);
  const [products, setProducts] = useState([]);
  // Sample product data - you would replace this with your actual data

  useEffect(() => {
    axios
      .get("http://localhost:8000/api/products")
      .then((response) => {
        if (
          response.data.status === "success" &&
          Array.isArray(response.data.data.data)
        ) {
          console.log("Dữ liệu sản phẩm:", response.data.data.data);
          setProducts(response.data.data.data);
        } else {
          console.log("Dữ liệu không phải là mảng hoặc API trả về lỗi");
        }
      })
      .catch((error) => {
        console.log("Lỗi khi gọi API:", error);
      });
  }, []);
  const productsPerPage = 4;
  const maxStartIndex = products.length - productsPerPage;

  const sliderRef = useRef(null);

  const nextSlide = () => {
    setStartIndex((prev) => Math.min(prev + 1, maxStartIndex));
  };

  const prevSlide = () => {
    setStartIndex((prev) => Math.max(prev - 1, 0));
  };

  const visibleProducts = products.slice(
    startIndex,
    startIndex + productsPerPage
  );

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

  return (
    <section>
      <div className="max-w-6xl mx-auto mt-20 my-5">
        <div>
          <div className="">
            <div className="text-center m-auto">
              <h2 className="mb-6 text-4xl font-semibold">New Product</h2>
              <p className="w-[65%] m-auto">
                Our traditional dining tables, chairs, case pieces and other
                traditional dining furniture are geared toward those who
                appreciate the simplicity and true craftsmanship.
              </p>
            </div>
          </div>

          <div className="relative my-12">
            {/* Previous button */}
            <button
              onClick={prevSlide}
              disabled={startIndex === 0}
              className={`absolute left-0 top-1/2 -translate-y-1/2 -ml-6 z-10 bg-white rounded-full p-2 shadow-md hover:bg-gray-100 transition-all ${
                startIndex === 0
                  ? "opacity-50 cursor-not-allowed"
                  : "opacity-100"
              }`}
            >
              <svg
                xmlns="http://www.w3.org/2000/svg"
                className="h-6 w-6"
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
            </button>

            {/* Products display */}
            <div ref={sliderRef} className="grid grid-cols-4 grid-rows-1 gap-4">
              {visibleProducts.map((product) => (
                <div key={product.id} className="cursor-pointer group">
                  <Link to={`/product/${product.id}`}>
                    {/* Link thêm sản phẩm vào giỏ hàng */}
                    <div className="transition-all duration-300 hover:shadow-lg rounded-lg p-2 group-hover:scale-[1.02]">
                      <div className="mb-2 relative overflow-hidden rounded-lg">
                        <img
                          src={
                            product.image_thumnail
                              ? product.image_thumnail.startsWith("http")
                                ? product.image_thumnail
                                : `http://localhost:8000/storage/${product.image_thumnail}`
                              : "https://via.placeholder.com/300x200?text=No+Image"
                          }
                          alt={product.name}
                          className="rounded-lg transition-all duration-300 group-hover:brightness-90 w-full h-[300px] object-cover"
                          onError={(e) => {
                            console.log("Lỗi tải ảnh:", e);
                            e.target.src =
                              "https://via.placeholder.com/300x200?text=No+Image";
                          }}
                        />
                        <div className="absolute inset-0 mt-[75%] ml-[42%] opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                          <button className="bg-white p-3 rounded-full shadow-md hover:bg-gray-100 transition-colors">
                            <Link to={`/product/${product.id}`}>
                              <IoCartOutline className="h-5 w-5 text-gray-800" />
                            </Link>
                          </button>
                        </div>
                      </div>
                      <div>
                        <h3 className="mb-2 hover:text-yellow-400">
                          {product.name}
                        </h3>
                        {/* Hiển thị đánh giá sao */}
                        <div className="flex items-center mb-2">
                          {product.rating ? (
                            <>
                              {renderStars(product.rating)}
                              <span className="text-xs text-gray-500 ml-1">
                                ({product.rating_count || 0})
                              </span>
                            </>
                          ) : (
                            <div className="flex text-gray-300">
                              {Array(5)
                                .fill()
                                .map((_, i) => (
                                  <BsStar key={i} size={14} />
                                ))}
                              <span className="text-xs text-gray-400 ml-1">
                                (0)
                              </span>
                            </div>
                          )}
                        </div>
                        <p className="font-medium">
                          {new Intl.NumberFormat("vi-VN", {
                            style: "currency",
                            currency: "VND",
                          }).format(product.price)}
                        </p>
                      </div>
                    </div>
                  </Link>
                </div>
              ))}
            </div>

            {/* Next button */}
            <button
              onClick={nextSlide}
              disabled={startIndex >= maxStartIndex}
              className={`absolute right-0 top-1/2 -translate-y-1/2 -mr-6 z-10 bg-white rounded-full p-2 shadow-md hover:bg-gray-100 transition-all ${
                startIndex >= maxStartIndex
                  ? "opacity-50 cursor-not-allowed"
                  : "opacity-100"
              }`}
            >
              <svg
                xmlns="http://www.w3.org/2000/svg"
                className="h-6 w-6"
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
          </div>

          {/* Navigation dots */}
          <div className="flex justify-center mt-4">
            {Array.from({
              length: Math.ceil(products.length / productsPerPage),
            }).map((_, index) => (
              <button
                key={index}
                onClick={() => setStartIndex(index * productsPerPage)}
                className={`mx-1 w-2 h-2 rounded-full ${
                  startIndex === index * productsPerPage
                    ? "bg-gray-800"
                    : "bg-gray-300"
                }`}
                aria-label={`Go to slide ${index + 1}`}
              />
            ))}
          </div>
        </div>
      </div>
    </section>
  );
};

export default ProductsSlide;
