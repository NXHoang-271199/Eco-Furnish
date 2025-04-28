import React, { useState, useEffect } from "react";
import axios from "axios";
import { FaChevronLeft, FaChevronRight } from "react-icons/fa";

const Banner = () => {
  const [banners, setBanners] = useState([]);
  const [currentIndex, setCurrentIndex] = useState(0);
  const [loading, setLoading] = useState(true);

  // Lấy dữ liệu banner từ API
  useEffect(() => {
    const fetchBanners = async () => {
      try {
        console.log("API URL:", import.meta.env.VITE_API_URL);
        const response = await axios.get(
          `${import.meta.env.VITE_API_URL}/api/banners`
        );
        console.log("Response data:", response.data);
        if (response.data.success) {
          // Chỉ lấy các banner có trạng thái hiển thị
          const activeBanners = response.data.data.filter(
            (banner) => banner.status
          );
          console.log("Danh sách banners:", activeBanners);
          setBanners(activeBanners);
        }
      } catch (error) {
        console.error("Lỗi khi lấy dữ liệu banner:", error);
      } finally {
        setLoading(false);
      }
    };

    fetchBanners();
  }, []);

  // Chuyển đến banner tiếp theo
  const nextSlide = () => {
    setCurrentIndex((prevIndex) =>
      prevIndex === banners.length - 1 ? 0 : prevIndex + 1
    );
  };

  // Chuyển đến banner trước đó
  const prevSlide = () => {
    setCurrentIndex((prevIndex) =>
      prevIndex === 0 ? banners.length - 1 : prevIndex - 1
    );
  };

  // Tự động chuyển banner sau mỗi 5 giây
  useEffect(() => {
    if (banners.length <= 1) return;

    const interval = setInterval(() => {
      nextSlide();
    }, 5000);

    return () => clearInterval(interval);
  }, [banners.length, currentIndex]);

  // Xử lý khi người dùng click vào banner
  const handleBannerClick = (link) => {
    if (link) {
      window.open(link, "_blank");
    }
  };

  if (loading) {
    return (
      <div className="w-full mx-auto h-[520px] bg-gray-100 animate-pulse flex items-center justify-center">
        <div className="text-gray-500 flex flex-col items-center">
          <div className="w-12 h-12 border-4 border-t-4 border-t-primary border-gray-300 rounded-full animate-spin mb-2"></div>
          <p>Đang tải...</p>
        </div>
      </div>
    );
  }

  if (banners.length === 0) {
    return null;
  }

  return (
    <div className="relative w-full h-[520px] overflow-hidden rounded-lg shadow-md mt-18">
      {/* Hiển thị các banner */}
      <div className="relative w-full mx-auto h-full bg-gray-100">
        {banners.map((banner, index) => (
          <div
            key={banner.id}
            className={`absolute top-0 left-0 w-full px-auto h-full transition-all duration-700 ease-in-out transform text-center ${
              index === currentIndex
                ? "opacity-100 z-10 scale-100"
                : "opacity-0 z-0 scale-105"
            }`}
            onClick={() => handleBannerClick(banner.link)}
            style={{ cursor: banner.link ? "pointer" : "default" }}
          >
            <img
              src={
                banner.image_url ||
                `${import.meta.env.VITE_API_BASE_URL}/${banner.image}`
              }
              alt="Banner"
              className="h-[520] mx-auto object-contain w-full py-0"
              onError={(e) => {
                console.error("Lỗi load ảnh:", e);
                console.log(
                  "URL ảnh gốc:",
                  banner.image_url ||
                    `${import.meta.env.VITE_API_BASE_URL}/${banner.image}`
                );
                e.target.onerror = () => {
                  e.target.src =
                    "https://via.placeholder.com/800x450?text=Banner+Image+Error";
                };
              }}
            />
          </div>
        ))}
      </div>

      {/* Nút điều hướng */}
      {banners.length > 1 && (
        <>
          <button
            className="absolute left-4 top-1/2 transform -translate-y-1/2 bg-white bg-opacity-70 hover:bg-opacity-90 rounded-full p-3 z-20 transition-all duration-300 hover:scale-110 shadow-md"
            onClick={prevSlide}
            aria-label="Previous banner"
          >
            <FaChevronLeft className="text-gray-800" size={18} />
          </button>
          <button
            className="absolute right-4 top-1/2 transform -translate-y-1/2 bg-white bg-opacity-70 hover:bg-opacity-90 rounded-full p-3 z-20 transition-all duration-300 hover:scale-110 shadow-md"
            onClick={nextSlide}
            aria-label="Next banner"
          >
            <FaChevronRight className="text-gray-800" size={18} />
          </button>

          {/* Chỉ số banner */}
          <div className="absolute bottom-6 left-1/2 transform -translate-x-1/2 z-20 flex space-x-3">
            {banners.map((_, index) => (
              <button
                key={index}
                className={`w-3 h-3 rounded-full transition-all duration-300 ${
                  index === currentIndex
                    ? "bg-white w-6 shadow-md"
                    : "bg-white bg-opacity-60 hover:bg-opacity-80"
                }`}
                onClick={() => setCurrentIndex(index)}
                aria-label={`Go to banner ${index + 1}`}
              />
            ))}
          </div>
        </>
      )}
    </div>
  );
};

export default Banner;
