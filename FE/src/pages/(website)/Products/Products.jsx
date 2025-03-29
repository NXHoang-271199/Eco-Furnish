import React from "react";
import { useState, useEffect } from "react";
import { FaUserAstronaut, FaShippingFast } from "react-icons/fa";
import { AiOutlineSearch } from "react-icons/ai";
import { LiaTrophySolid } from "react-icons/lia";
import { IoCartOutline } from "react-icons/io5";
import { MdOutlineSettingsInputComponent } from "react-icons/md";
import axios from "axios";
import { Link } from "react-router-dom";
import Banner from "../../../components/Banner";
const Products = () => {
  const [products, setProducts] = useState([]);
  const [imageLoadError, setImageLoadError] = useState({});

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
        }
      } catch (error) {
        console.error("Lỗi khi lấy dữ liệu:", error);
      }
    };

    fetchProducts();
  }, []);
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
          </h2>
          <div className="relative">
            <input
              type="text"
              placeholder="Tìm kiếm sản phẩm..."
              className="px-4 py-2 border rounded-full w-72 focus:outline-none focus:ring-2 focus:ring-blue-400"
            />
            <button className="absolute right-3 top-2 text-gray-500 hover:text-gray-700">
              <AiOutlineSearch className="h-5 w-5" />
            </button>
          </div>
        </div>

        <div className="flex gap-10">
          {/* Bộ lọc */}
          <div className="w-1/4">
            <h3 className="text-lg font-medium mb-4">Kích cỡ</h3>
            <div className="space-y-3 text-gray-600">
              {["Nhỏ", "Vừa", "Lớn"].map((size) => (
                <label
                  key={size}
                  className="flex items-center space-x-2 cursor-pointer"
                >
                  <input type="checkbox" className="accent-blue-500" />
                  <span>{size}</span>
                </label>
              ))}
            </div>

            {/* Lọc giá */}
            <h3 className="text-lg font-medium mt-6 mb-4">Giá trị sản phẩm</h3>
            <div className="flex gap-2">
              <input
                type="number"
                placeholder="Tối thiểu"
                className="border rounded-lg px-3 py-2 w-1/2"
                min={100}
              />
              <input
                type="number"
                placeholder="Tối đa"
                className="border rounded-lg px-3 py-2 w-1/2"
                min={100}
                max={500}
              />
            </div>
          </div>

          {/* Danh sách sản phẩm */}
          <div className="w-3/4 grid grid-cols-3 gap-6">
            {products.length > 0 ? (
              products.map((product) => (
                <div
                  key={product.id}
                  className="bg-white rounded-lg shadow-md p-4 hover:shadow-lg transition"
                >
                  <Link to={`/product/${product.id}`}>
                    <div className="relative overflow-hidden rounded-lg">
                      <img
                        src={
                          product.image_thumnail
                            ? product.image_thumnail.startsWith("http")
                              ? product.image_thumnail
                              : `http://localhost:8000/storage/${product.image_thumnail}`
                            : "https://via.placeholder.com/300x200?text=No+Image"
                        }
                        alt={product.name}
                        className="w-full h-48 object-cover rounded-md"
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
                    <h3 className="mt-3 font-semibold text-gray-800">
                      {product.name}
                    </h3>
                    <p className="text-blue-600 font-medium">
                      {new Intl.NumberFormat("vi-VN", {
                        style: "currency",
                        currency: "VND",
                      }).format(product.price)}
                    </p>
                  </Link>
                </div>
              ))
            ) : (
              <p className="col-span-3 text-center text-gray-500">
                Đang tải sản phẩm...
              </p>
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
