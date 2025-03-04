import React from "react";
import { Link } from "react-router-dom";

const Blogs = () => {
  return (
    <div className="bg-gray-100 mt-10">
      <main className="max-w-6xl mx-auto px-4 py-8">
        <section className="relative">
          <img
            src="https://storage.googleapis.com/a1aa/image/L2WwzaWj82s6j2ILIlmI8ucu4kkoaXdYw6hBRSqLzYY.jpg"
            alt="Living room with Christmas tree"
            className="w-full h-96 object-cover rounded-lg transition-transform duration-300 hover:scale-105"
          />
          <div className="absolute top-4 left-4 bg-white p-2 rounded-full shadow">
            <i className="fas fa-bars text-gray-700"></i>
          </div>
        </section>
        <section className="mt-8">
          <div className="flex justify-between items-center mb-4">
            <div className="flex space-x-4">
              <button className="text-gray-700 font-medium">
                Tất cả bài viết
              </button>
              <button className="text-gray-700 font-medium">Nổi bật</button>
            </div>
            <div className="flex items-center space-x-2">
              {/* <input
                type="text"
                placeholder="Tìm kiếm bài viết..."
                className="border border-gray-300 rounded-lg px-4 py-2"
              /> */}
              <i className="fas fa-th text-gray-700"></i>
              <i className="fas fa-list text-gray-700"></i>
            </div>
          </div>
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            {[...Array(12)].map((_, index) => (
              <a href="/BlogDetail">
                <div key={index} className="bg-white rounded-lg shadow p-4">
                  <img
                    src={`https://picsum.photos/200/300`}
                    alt="Article"
                    className="w-full h-48 object-cover  mb-4  rounded-lg transition-transform duration-300 hover:scale-105"
                  />
                  <h2 className="text-lg font-semibold text-gray-800">
                    Article Title
                  </h2>
                  <p className="text-gray-600">Thứ Ba, 10 tháng 12, 2024</p>
                </div>
              </a>
            ))}
          </div>
          <div className="flex justify-center mt-8">
            <button className="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg">
              Xem thêm
            </button>
          </div>
        </section>
      </main>
    </div>
  );
};

export default Blogs;
