import React from "react";
import { FaArrowRight } from "react-icons/fa";
import { Link } from "react-router-dom";
const Homes = () => {
  return (
    <>
      <div>
        <img
          src=".\src\assets\img\slider-banner\homepage01-slide1.jpg"
          alt=""
          className="w-full"
        />
      </div>

      <section>
        <div className="max-w-6xl mx-auto mt-20 my-5">
          <div className="grid grid-rows-6 grid-cols-3 ">
            <div className="row-span-4">
              <div>
                <a href="">
                  <img
                    src=".\src\assets\img\banners\banner-homepage2_1.png"
                    alt=""
                  />
                </a>
              </div>
            </div>
            <div className="col-span-2 row-span-2">
              <div>
                <a href="">
                  <img
                    src=".\src\assets\img\banners\banner-homepage2_3.png"
                    alt=""
                  />
                </a>
              </div>
            </div>
            <div className=" col-span-2 row-span-4">
              <div className="pt-3">
                <a href="">
                  <img
                    src=".\src\assets\img\banners\banner-homepage2_4.png"
                    alt=""
                  />
                </a>
              </div>
            </div>
            <div className=" row-span-2">
              <div>
                <a href="">
                  <img
                    src=".\src\assets\img\banners\banner-homepage2_2.png"
                    alt=""
                  />
                </a>
              </div>
            </div>
          </div>
        </div>
      </section>

      {/* list products */}

      <section>
        <div className="max-w-6xl mx-auto mt-20 my-5">
          <div>
            <div>

              <div className="text-center m-auto">
                <h2 className="mb-6 text-4xl font-semibold">New Product</h2>
                <p className="w-[65%] m-auto">
                  Our traditional dining tables, chairs, case pieces and other
                  traditional dining furniture are geared toward those who
                  appreciate the simplicity and true craftsmanship.
                </p>
              </div>
            </div>
            <div className="grid grid-cols-4 grid-rows-1 gap-4 my-12 ">
              <div>
                <Link to={"product"}>
                  <div className="mb-2">
                    <img
                      src="https://picsum.photos/291/301"
                      alt="sanpham"
                      className="rounded-lg"
                    />
                  </div>
                  <div>
                    <h3 className="mb-2 font-semibold ">Name Product</h3>
                    <p className="font-medium">1.000.000</p>
                  </div>
                </Link>
              </div>

              <div>
                <Link to={"product"}>
                  <div className="mb-2">
                    <img
                      src="https://picsum.photos/291/301"
                      alt="sanpham"
                      className="rounded-lg"
                    />
                  </div>
                  <div>
                    <h3 className="mb-2 font-semibold ">Name Product</h3>
                    <p className="font-medium">1.000.000</p>
                  </div>
                </Link>
              </div>
              <div>
                <Link to={"product"}>
                  <div className="mb-2">
                    <img
                      src="https://picsum.photos/291/301"
                      alt="sanpham"
                      className="rounded-lg"
                    />
                  </div>
                  <div>
                    <h3 className="mb-2 font-semibold ">Name Product</h3>
                    <p className="font-medium">1.000.000</p>
                  </div>
                </Link>
              </div>
              <div>
                <Link to={"product"}>
                  <div className="mb-2">
                    <img
                      src="https://picsum.photos/291/301"
                      alt="sanpham"
                      className="rounded-lg"
                    />
                  </div>
                  <div>
                    <h3 className="mb-2 font-semibold ">Name Product</h3>
                    <p className="font-medium">1.000.000</p>
                  </div>
                </Link>
              </div>
            </div>
          </div>
        </div>
      </section>

      {/*  */}
      <section>
        <div className="max-w-6xl mx-auto mt-20 mb-20">
          <div>
            <div className="text-center m-auto">
              <h2 className="mb-6 text-4xl font-semibold">From Our Blog</h2>
            </div>
          </div>
          <div className="flex ">
            <div className="grid grid-cols-3 gap-4">
              <div>
                <img src=".\src\assets\img\blog\blog-1.jpg" alt="" />
                <div>
                  <h3 className="mt-3 mb-1">
                    <a href="">Blog name</a>
                  </h3>
                  <p className="flex items-center gap-1 text-center">
                    <a href="" className="underline">
                      Read more
                    </a>
                    <svg
                      xmlns="http://www.w3.org/2000/svg"
                      fill="none"
                      viewBox="0 0 24 24"
                      strokeWidth={1.5}
                      stroke="currentColor"
                      className="size-5 pt-1"
                    >
                      <path
                        strokeLinecap="round"
                        strokeLinejoin="round"
                        d="M17.25 8.25 21 12m0 0-3.75 3.75M21 12H3"
                      />
                    </svg>
                  </p>
                </div>
              </div>
              <div>
                <img src=".\src\assets\img\blog\blog-1.jpg" alt="" />
                <div>
                  <h3 className="mt-3 mb-1">
                    <a href="">Blog name</a>
                  </h3>
                  <p className="flex items-center gap-1 text-center">
                    <a href="" className="underline">
                      Read more
                    </a>
                    <svg
                      xmlns="http://www.w3.org/2000/svg"
                      fill="none"
                      viewBox="0 0 24 24"
                      strokeWidth={1.5}
                      stroke="currentColor"
                      className="size-5 pt-1"
                    >
                      <path
                        strokeLinecap="round"
                        strokeLinejoin="round"
                        d="M17.25 8.25 21 12m0 0-3.75 3.75M21 12H3"
                      />
                    </svg>
                  </p>
                </div>
              </div>
              <div>
                <img src=".\src\assets\img\blog\blog-1.jpg" alt="" />
                <div>
                  <h3 className="mt-3 mb-1">
                    <a href="">Blog name</a>
                  </h3>
                  <p className="flex items-center gap-1 text-center">
                    <a href="" className="underline">
                      Read more
                    </a>
                    <svg
                      xmlns="http://www.w3.org/2000/svg"
                      fill="none"
                      viewBox="0 0 24 24"
                      strokeWidth={1.5}
                      stroke="currentColor"
                      className="size-5 pt-1"
                    >
                      <path
                        strokeLinecap="round"
                        strokeLinejoin="round"
                        d="M17.25 8.25 21 12m0 0-3.75 3.75M21 12H3"
                      />
                    </svg>
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
      {/* end blog */}

      {/* brand */}
      <section className="">
        <div className="w-full bg-gray-200">
          <div class="w-full mx-auto py-12 px-6">
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-6 items-center">
              <img
                src=".\src\assets\img\brands\brand-2.png"
                alt="Brand 1"
                class="mx-auto"
              />
              <img
                src=".\src\assets\img\brands\brand-3.png"
                alt="Brand 2"
                class="mx-auto"
              />
              <img
                src=".\src\assets\img\brands\brand-4.png"
                alt="Brand 3"
                class="mx-auto"
              />
              <img
                src=".\src\assets\img\brands\brand-6.png"
                alt="Brand 4"
                class="mx-auto"
              />
              <img
                src=".\src\assets\img\brands\brand-7.png"
                alt="Brand 5"
                class="mx-auto"
              />
              <img
                src=".\src\assets\img\brands\brand-11.png"
                alt="Brand 6"
                class="mx-auto"
              />
            </div>
          </div>
        </div>
      </section>
    </>
  );
};

export default Homes;
