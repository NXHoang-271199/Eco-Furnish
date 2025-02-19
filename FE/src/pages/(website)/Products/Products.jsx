import React from "react";
import { FaUserAstronaut, FaShippingFast } from "react-icons/fa";
import { LiaTrophySolid } from "react-icons/lia";

const Products = () => {
  return (
    <>
      {/* banner */}
      <section className="">
        <div className="max-w-6xl mx-auto">
          <div
            className="my-20
          "
          >
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
              <div>
                <div>
                  <img
                    src="https://picsum.photos/296/301"
                    alt=""
                    className="rounded-md"
                  />
                </div>
                <div className="my-4">
                  <h4 className="font-bold">Syltherine</h4>

                  <p className="text-1xl font-somibold text-red-600 pt-1">
                    2.500.000đ
                  </p>
                </div>
              </div>
              {/* end product */}
              <div>
                <div>
                  <img
                    src="https://picsum.photos/296/301"
                    alt=""
                    className="rounded-md"
                  />
                </div>
                <div className="my-4">
                  <h4 className="font-bold">Syltherine</h4>

                  <p className="text-1xl font-somibold text-red-600 pt-1">
                    2.500.000đ
                  </p>
                </div>
              </div>
              {/* end product */}
              <div>
                <div>
                  <img
                    src="https://picsum.photos/296/301"
                    alt=""
                    className="rounded-md"
                  />
                </div>
                <div className="my-4">
                  <h4 className="font-bold">Syltherine</h4>

                  <p className="text-1xl font-somibold text-red-600 pt-1">
                    2.500.000đ
                  </p>
                </div>
              </div>
              {/* end product */}
            </div>
            <div className="grid grid-cols-3 gap-6 my-4">
              <div>
                <div>
                  <img
                    src="https://picsum.photos/296/301"
                    alt=""
                    className="rounded-md"
                  />
                </div>
                <div className="my-4">
                  <h4 className="font-bold">Syltherine</h4>

                  <p className="text-1xl font-somibold text-red-600 py-2">
                    2.500.000đ
                  </p>
                </div>
              </div>
              <div>
                <div>
                  <img
                    src="https://picsum.photos/296/301"
                    alt=""
                    className="rounded-md"
                  />
                </div>
                <div className="my-4">
                  <h4 className="font-bold">Syltherine</h4>

                  <p className="text-1xl font-somibold text-red-600 py-2">
                    2.500.000đ
                  </p>
                </div>
              </div>
              <div>
                <div>
                  <img
                    src="https://picsum.photos/296/301"
                    alt=""
                    className="rounded-md"
                  />
                </div>
                <div className="my-4">
                  <h4 className="font-bold">Syltherine</h4>

                  <p className="text-1xl font-somibold text-red-600 py-2">
                    2.500.000đ
                  </p>
                </div>
              </div>
            </div>
            <div className="grid grid-cols-3 gap-6 my-4">
              <div>
                <div>
                  <img
                    src="https://picsum.photos/296/301"
                    alt=""
                    className="rounded-md"
                  />
                </div>
                <div className="my-4">
                  <h4 className="font-bold">Syltherine</h4>

                  <p className="text-1xl font-somibold text-red-600 py-2">
                    2.500.000đ
                  </p>
                </div>
              </div>
              <div>
                <div>
                  <img
                    src="https://picsum.photos/296/301"
                    alt=""
                    className="rounded-md"
                  />
                </div>
                <div className="my-4">
                  <h4 className="font-bold">Syltherine</h4>

                  <p className="text-1xl font-somibold text-red-600 py-2">
                    2.500.000đ
                  </p>
                </div>
              </div>
              <div>
                <div>
                  <img
                    src="https://picsum.photos/296/301"
                    alt=""
                    className="rounded-md"
                  />
                </div>
                <div className="my-4">
                  <h4 className="font-bold">Syltherine</h4>

                  <p className="text-1xl font-somibold text-red-600 py-2">
                    2.500.000đ
                  </p>
                </div>
              </div>
            </div>

            {/* end products-list */}

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
