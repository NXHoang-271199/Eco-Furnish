import React from "react";
import { useState, useEffect } from "react";
import { FaUserAstronaut, FaShippingFast } from "react-icons/fa";
import { LiaTrophySolid } from "react-icons/lia";
import axios from "axios";
import { Link } from "react-router-dom";
const Products = () => {
  const [products, setProducts] = useState([]);

  useEffect(() => {
    axios
      .get("http://localhost:8000/api/products")
      .then((response) => {
        // Kiểm tra dữ liệu trả về
        if (
          response.data.status === "success" &&
          Array.isArray(response.data.data.data)
        ) {
          setProducts(response.data.data.data); // Lấy danh sách sản phẩm từ response.data.data.data
        } else {
          console.log("Dữ liệu không phải là mảng hoặc API trả về lỗi");
        }
      })
      .catch((error) => {
        console.log("Lỗi khi gọi API:", error);
      });
  }, []);
  return (
    <>
      {/* banner */}
      <section className="">
        <div className="max-w-6xl mx-auto">
          <div className="my-20">
            <img
              src=".\src\assets\img\banners\homepage01-slide2.jpg"
              alt=""
              className="rounded-br-lg rounded-bl-lg "
            />
          </div>
        </div>
      </section>

      {/* list products */}
      <section className="max-w-6xl mx-auto mt-16 my-6">
        <div className="flex ">
          <div className="mr-60">
            <h2 className="font-medium text-lg mb-5">Categories</h2>
            <nav>
              <ul>
                <li className="mb-3 text-gray-500 hover:text-amber-300 font-semibold hover:underline">
                  Cafe Chair
                </li>
                <li className="mb-3 text-gray-500 hover:text-amber-300 font-semibold hover:underline">
                  Sofa
                </li>
                <li className="mb-3 text-gray-500 hover:text-amber-300 font-semibold hover:underline">
                  Lamp
                </li>
                <li className="mb-3 text-gray-500 hover:text-amber-300 font-semibold hover:underline">
                  Carpet
                </li>
                <li className="mb-3 text-gray-500 hover:text-amber-300 font-semibold hover:underline">
                  Cabinet
                </li>
                <li className="mb-3 text-gray-500 hover:text-amber-300 font-semibold hover:underline">
                  Tea table
                </li>
              </ul>
            </nav>
          </div>

          <div className="">
            <div className="grid grid-cols-3 gap-6 my-4 ">
              {products.length > 0 ? (
                products.map((product, index) => (
                  <div key={index}>
                    <div>
                      <img
                        src={
                          product.image_thumnail
                            ? `http://localhost:8000/storage/${product.image_thumnail}`
                            : "https://via.placeholder.com/300x200?text=No+Image"
                        }
                        alt={product.name}
                        className="rounded-md w-[296] h-[301]"
                        onError={(e) => {
                          console.log("Lỗi tải ảnh:", product.image_thumnail);
                          e.target.src =
                            "https://via.placeholder.com/300x200?text=Error+Loading";
                        }}
                      />
                    </div>
                    <div className="my-4">
                      <Link to={`/product/${product.id}`}>
                        <h4 className="font-bold">{product.name}</h4>
                        <p className="text-1xl font-semibold text-red-600 pt-1">
                          {new Intl.NumberFormat("vi-VN", {
                            style: "currency",
                            currency: "VND",
                          }).format(product.price)}
                        </p>
                      </Link>
                    </div>
                  </div>
                ))
              ) : (
                <p>Đang tải sản phẩm...</p>
              )}

              {/* end product */}
            </div>

            <div className="flex">
              <div className="border rounded-lg py-3 px-5 bg-gray-400 text-stone-50 hover:bg-orange-200 mr-4">
                1
              </div>
              <div className="border rounded-lg py-3 px-5 bg-gray-400 text-stone-50 hover:bg-orange-200 mr-4">
                2
              </div>
              <div className="border rounded-lg py-3 px-5 bg-gray-400 text-stone-50 hover:bg-orange-200 mr-4">
                3
              </div>
              <div className="border rounded-lg py-3 px-5 bg-gray-400 text-stone-50 hover:bg-orange-200 mr-4">
                Next
              </div>
            </div>
          </div>
        </div>
      </section>

      <section className=" bg-gray-100">
        <div className="max-w-6xl mx-auto mt-16 ">
          <div className="grid grid-cols-4 gap-8 py-16 ">
            <div className="flex ">
              <LiaTrophySolid className="w-10 h-11" />
              <div className="pl-5">
                <h2 className="text-xl font-semibold">High Quality</h2>
                <p className="text-slate-600 ">Crafted from top materials</p>
              </div>
            </div>

            <div className="flex ">
              <FaUserAstronaut className="w-10 h-11" />
              <div className="pl-5">
                <h2 className="text-xl font-semibold">24 / 7 Support</h2>
                <p className="text-slate-600 ">Dedicated support</p>
              </div>
            </div>

            <div className="flex ">
              <LiaTrophySolid className="w-10 h-11" />
              <div className="pl-5">
                <h2 className="text-xl font-semibold">Warranty Protection</h2>
                <p className="text-slate-600 ">Over 2 years</p>
              </div>
            </div>

            <div className="flex ">
              <FaShippingFast className="w-10 h-11" />
              <div className="pl-5">
                <h2 className="text-xl font-semibold">Free Shipping</h2>
                <p className="text-slate-600 ">Order over 150 $</p>
              </div>
            </div>
          </div>
        </div>
      </section>
    </>
  );
};

export default Products;
