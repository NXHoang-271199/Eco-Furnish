import React from "react";
import { Link } from "react-router-dom";
import { motion } from "framer-motion";

const Blogs = () => {
  return (
    <div className="bg-gray-100 mt-10">
      <main className="max-w-6xl mx-auto px-4 py-8">
        <section className="relative">
          <motion.img
            src="https://storage.googleapis.com/a1aa/image/L2WwzaWj82s6j2ILIlmI8ucu4kkoaXdYw6hBRSqLzYY.jpg"
            alt="Living room with Christmas tree"
            className="w-full h-64 md:h-96 lg:h-[600px] object-cover rounded-lg mx-auto px-4 md:px-8 lg:px-16 py-4 md:py-6 lg:py-8"
            initial={{ scale: 1 }}
            animate={{ scale: 1.05 }}
            transition={{ duration: 1.5, ease: "easeOut" }}
          />
          <div className="absolute top-6 left-6 bg-white p-2 rounded-full shadow">
            <i className="fas fa-bars text-gray-700"></i>
          </div>
        </section>

        <section className="mt-8">
          <div className="flex flex-col sm:flex-row justify-between items-center mb-4">
            <div className="flex space-x-4 mb-4 sm:mb-0">
              <button className="text-gray-700 font-medium hover:text-gray-900 transition-colors">
                Tất cả bài viết
              </button>
              <button className="text-gray-700 font-medium hover:text-gray-900 transition-colors">
                Nổi bật
              </button>
            </div>
            <div className="flex items-center space-x-2 w-full sm:w-auto">
              {/* Search functionality can be added here */}
            </div>
          </div>

          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-6">
            {[...Array(12)].map((_, index) => (
              <Link to="/BlogDetail" key={index} className="block">
                <div className="bg-white rounded-lg shadow p-4 h-full hover:shadow-lg transition-shadow duration-300">
                  <div className="overflow-hidden rounded-lg mb-4">
                    <img
                      src={`http://localhost:5173/src/assets/img/banners/homepage01-slide2.jpg`}
                      alt="Article"
                      className="w-full h-48 object-cover rounded-lg transition-transform duration-300 hover:scale-105"
                    />
                  </div>
                  <h2 className="text-lg font-semibold text-gray-800 line-clamp-2">
                    Article Title {index + 1}
                  </h2>
                  <p className="text-gray-600 text-sm mt-2">
                    Thứ Ba, 10 tháng 12, 2024
                  </p>
                </div>
              </Link>
            ))}
          </div>

          <div className="flex justify-center mt-8">
            <button className="bg-gray-200 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-300 transition-colors">
              Xem thêm
            </button>
          </div>
        </section>
      </main>
    </div>
  );
};

export default Blogs;