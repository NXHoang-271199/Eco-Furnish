import React from "react";
import { Link } from "react-router-dom";
import { AiFillStar } from "react-icons/ai";

const ProductDetail = () => {
  return (
    <main className="max-w-6xl mx-auto mb-20 mt-32">
      {/* Product_info */}
      <div className=" grid grid-cols-2 gap-8 my-16">
        {/* IMAGE */}
        <div className="grid grid-cols-1">
          <div className="col-span-5">
            <img
              src="https://picsum.photos/515/515"
              alt=""
              className="w-full rounded-md"
            />
          </div>
          <div className="flex justify-start space-x-5">
            <div className="mt-4">
              <img
                src="https://picsum.photos/77/77"
                alt=""
                className="w-20 rounded-md"
              />
            </div>
            <div className="mt-4">
              <img
                src="https://picsum.photos/77/77"
                alt=""
                className="w-20 rounded-md"
              />
            </div>
            <div className="mt-4">
              <img
                src="https://picsum.photos/77/77"
                alt=""
                className="w-20 rounded-md"
              />
            </div>
            <div className="mt-4">
              <img
                src="https://picsum.photos/77/77"
                alt=""
                className="w-20 rounded-md"
              />
            </div>
            <div className="mt-4">
              <img
                src="https://picsum.photos/77/77"
                alt=""
                className="w-20 rounded-md"
              />
            </div>
          </div>
        </div>
        {/* Info */}
        <div className="bg-red">
          {/* name */}
          <h5 className="text-[20px] font-semibold text-3xl">Asgaard sofa</h5>
          {/* price */}
          <h3 className="text-[40px] font-bold mt-2 text-[#EF4444]">
            25.000.000đ
          </h3>

          {/* descript */}
          <p className="mt-3 text-[#16px] font-medium ">
            Setting the bar as one of the loudest speakers in its class, the
            Kilburn is a compact, stout-hearted hero with a well-balanced audio
            which boasts a clear midrange and extended highs for a sound.
          </p>

          {/* color */}
          <div className="mt-4">
            <p className="text-[#A3A3A3]">Color</p>
            <div className="flex gap-4 mt-1">
              <div className="w-[30px] h-[30px] bg-[#816DFA] rounded-[50%]"></div>
              <div className="w-[30px] h-[30px] bg-[#000000] rounded-[50%]"></div>
              <div className="w-[30px] h-[30px] bg-[#B88E2F] rounded-[50%]"></div>
            </div>
          </div>

          {/* Button quantity/buy/compare */}
          <div className="mt-8 flex   pb-8 ">
            <div className="grid grid-cols-3 w-[123px] h-[44px] border border-[#A3A3A3] rounded-[5px] ">
              <button className="justify-center flex items-center">+</button>
              <p className=" flex justify-center items-center">1</p>
              <button className="justify-center flex items-center">-</button>
            </div>
            <div>
              <button className="justify-center flex items-center border border-[#CA8A04] rounded-[5px] w-[215px] h-[44px] ml-3 text-[#CA8A04]">
                <a href="cart">Add To Cart</a>
              </button>
            </div>
            <div>
              {" "}
              <button className="justify-center flex items-center border border-[#262626] rounded-[5px] w-[215px] h-[44px] ml-3 text-[#262626]">
                Add to wish list
              </button>
            </div>
          </div>
          <div className="w-full mx-auto">
            <button className="w-full border rounded-md mx-auto py-2 font-semibold hover:bg-yellow-200 ">
              Buy now
            </button>
          </div>
          {/* inf_end */}
          <div className="mt-3">
            <div className="text-[#A3A3A3] text-[16px] mb-3 ">
              Hàng tồn kho: 100
            </div>
            <div className="text-[#A3A3A3] text-[16px] mb-3 ">
              Danh mục: Sofa
            </div>
            <div className="text-[#A3A3A3] text-[16px] mb-3 ">
              Thẻ: Sofa, Chair, Home, Shop
            </div>
          </div>
        </div>
      </div>
      {/* product_description / comment */}
      <div className="mt-4">
        <ul className="flex gap-x-16 mb-4">
          <li className="text-[20px] font-semibold text-[#000000]">
            <Link to="/">Comment</Link>
          </li>
          <li className="text-[20px] font-semibold text-[#A3A3A3]">
            <Link to="/">Additional Information</Link>
          </li>
          <li className="text-[20px] font-semibold text-[#A3A3A3]">
            <Link to="/">Description</Link>
          </li>
        </ul>
        {/* content */}
        {/* <div className=" border-t border-[#A3A3A3] pt-8">
          <p className="text-[#A3A3A3] font-medium">
            Embodying the raw, wayward spirit of rock ‘n’ roll, the Kilburn
            portable active stereo speaker takes the unmistakable look and sound
            of Marshall, unplugs the chords, and takes the show on the road.
          </p>
          <p className="text-[#A3A3A3] mt-2 font-medium">
            Weighing in under 7 pounds, the Kilburn is a lightweight piece of
            vintage styled engineering. Setting the bar as one of the loudest
            speakers in its class, the Kilburn is a compact, stout-hearted hero
            with a well-balanced audio which boasts a clear midrange and
            extended highs for a sound that is both articulate and pronounced.
            The analogue knobs allow you to fine tune the controls to your
            personal preferences while the guitar-influenced leather strap
            enables easy and stylish travel.
          </p>

          img
          <div className="grid grid-cols-2 gap-8 mt-8">
            <div>
              <img src="https://picsum.photos/id/1/624/378" alt="" />
            </div>
            <div>
              <img src="https://picsum.photos/id/1/624/378" alt="" />
            </div>
          </div>
        </div> */}

        <div class="flex justify-between items-center border p-2 rounded-md">
          <input
            type="text"
            class="w-3/4 p-2 outline-none"
            placeholder="Nhập đánh giá của bạn ở đây"
          />
          <button class="ml-4 border p-2 rounded-md bg-blue-500 text-white">
            Gửi đánh giá
          </button>
        </div>

        <div class="space-y-4 mt-5">
          <div class="flex items-center space-x-4 border-b pb-3">
            <div class="w-10 h-10 rounded-full bg-gray-300 flex items-center justify-center">
              <span class="text-white font-bold">N</span>
            </div>
            <div class="flex flex-col">
              <div class="font-semibold">Nguyen Tien Dat</div>

              <div class="text-gray-600">Sản phẩm tốt</div>
            </div>
          </div>

          <div class="flex items-center space-x-4 border-b pb-3">
            <div class="w-10 h-10 rounded-full bg-gray-300 flex items-center justify-center">
              <span class="text-white font-bold">N</span>
            </div>
            <div class="flex flex-col">
              <div class="font-semibold">Người dùng ẩn danh</div>

              <div class="text-gray-600">Sản phẩm rất tốt</div>
            </div>
          </div>

          <div class="flex items-center space-x-4 border-b pb-3">
            <div class="w-10 h-10 rounded-full bg-gray-300 flex items-center justify-center">
              <span class="text-white font-bold">N</span>
            </div>
            <div class="flex flex-col">
              <div class="font-semibold">Người dùng ẩn danh</div>

              <div class="text-gray-600">Sản phẩm tệ</div>
            </div>
          </div>

          <div class="flex items-center space-x-4 border-b pb-3">
            <div class="w-10 h-10 rounded-full bg-gray-300 flex items-center justify-center">
              <span class="text-white font-bold">N</span>
            </div>
            <div class="flex flex-col">
              <div class="font-semibold">Nguyen Tien Dat</div>

              <div class="text-gray-600">Sản phẩm cần làm việc trí hơn</div>
            </div>
          </div>
        </div>
      </div>
      {/* Prodcut same categories */}
      <div className="container max-w-6xl mx-auto mt-16">
        <div>
          {/* head */}
          <div className="flex justify-center mb-4 ">
            <h3 className="font-semibold text-[40px] ">Related Products</h3>
          </div>

          {/* Pro_inf */}
          <div className="grid grid-cols-4 gap-8">
            <div>
              {/* One_pro */}
              <div>
                <img
                  src="https://picsum.photos/296/301"
                  alt=""
                  className="rounded-lg"
                />
              </div>
              <div className="">
                <Link to="/">
                  <h5 className="font-semibold text-[20px] pt-4">Syltherine</h5>
                </Link>

                <p className="font-semibold  text-[#EF4444] ">2.500.000đ</p>
              </div>
            </div>
            <div>
              {/* One_pro */}
              <div>
                <img
                  src="https://picsum.photos/296/301"
                  alt=""
                  className="rounded-lg"
                />
              </div>
              <div className="">
                <Link to="/">
                  <h5 className="font-semibold text-[20px] pt-4">Syltherine</h5>
                </Link>

                <p className="font-semibold  text-[#EF4444] ">2.500.000đ</p>
              </div>
            </div>
            <div>
              {/* One_pro */}
              <div>
                <img
                  src="https://picsum.photos/296/301"
                  alt=""
                  className="rounded-lg"
                />
              </div>
              <div className="">
                <Link to="/">
                  <h5 className="font-semibold text-[20px] pt-4">Syltherine</h5>
                </Link>

                <p className="font-semibold  text-[#EF4444] ">2.500.000đ</p>
              </div>
            </div>
            <div>
              {/* One_pro */}
              <div>
                <img
                  src="https://picsum.photos/296/301"
                  alt=""
                  className="rounded-lg"
                />
              </div>
              <div className="">
                <Link to="/">
                  <h5 className="font-semibold text-[20px] pt-4">Syltherine</h5>
                </Link>

                <p className="font-semibold  text-[#EF4444] ">2.500.000đ</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>
  );
};

export default ProductDetail;
