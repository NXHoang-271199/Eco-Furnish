import React from "react";
import { Link } from "react-router-dom";
import { IoCartOutline } from "react-icons/io5";
import { AiOutlineUser, AiOutlineSearch, AiOutlineHeart } from "react-icons/ai";

const Header = () => {
  return (
    <>
      <header class="bg-white fixed top-0 left-0 w-full z-50">
        <div class="max-w-6xl mx-auto flex justify-between items-center p-4">
          {/* <!-- Logo --> */}
          <div className="text-2xl font-bold text-black">
            <div>
              <p>
                <span className="text-yellow-300">E</span>co-
                <span className="text-yellow-300">F</span>urnish
              </p>
            </div>
          </div>

          {/* <!-- Navigation --> */}
          <nav className="hidden md:flex space-x-6">
            <ul className="flex justify-center space-x-6 ">
              <li>
                <Link to="/" className="hover:text-yellow-400">
                  Home
                </Link>
              </li>
              <li className="relative group">
                <Link to="/products" className="hover:text-yellow-400">
                  Shop
                </Link>
                <div class="absolute hidden group-hover:flex bg-white shadow-lg rounded-lg w-[600px] p-6 md\:w-auto">
                  <ul class="grid grid-cols-2 gap-6 w-full">
                    <li>
                      <a href="#" class="block font-semibold">
                        Phòng khách
                      </a>
                      <p class="text-sm text-gray-500">
                        Không gian tiếp đón khách...
                      </p>
                    </li>
                    <li>
                      <a href="#" class="block font-semibold">
                        Phòng ngủ
                      </a>
                      <p class="text-sm text-gray-500">
                        Nơi nghỉ ngơi thư giãn...
                      </p>
                    </li>
                    <li>
                      <a href="#" class="block font-semibold">
                        Phòng bếp
                      </a>
                      <p class="text-sm text-gray-500">
                        Trang bị đầy đủ tiện nghi...
                      </p>
                    </li>
                    <li>
                      <a href="#" class="block font-semibold">
                        Phòng làm việc
                      </a>
                      <p class="text-sm text-gray-500">
                        Không gian làm việc hiệu quả...
                      </p>
                    </li>
                    <li>
                      <a href="#" class="block font-semibold">
                        Trang trí nội thất
                      </a>
                      <p class="text-sm text-gray-500">
                        Thêm màu sắc phong cách...
                      </p>
                    </li>
                    <li>
                      <a href="#" class="block font-semibold">
                        Đồ nội thất ngoài trời
                      </a>
                      <p class="text-sm text-gray-500">
                        Thư giãn với bộ bàn ghế...
                      </p>
                    </li>
                    <li>
                      <a href="#" class="block font-semibold">
                        Thảm và Rèm cửa
                      </a>
                      <p class="text-sm text-gray-500">
                        Làm mới không gian sống...
                      </p>
                    </li>
                    <li>
                      <a href="#" class="block font-semibold">
                        Phụ kiện nội thất
                      </a>
                      <p class="text-sm text-gray-500">
                        Những chi tiết tinh tế...
                      </p>
                    </li>
                  </ul>
                </div>
              </li>
              <li>
                <Link to="/about" className="hover:text-yellow-400">
                  About
                </Link>
              </li>
              <li>
                <Link to="/contact" className="hover:text-yellow-400">
                  Contact
                </Link>
              </li>
              <li>
                <Link to="/blog" className="hover:text-yellow-400">
                  Blog
                </Link>
              </li>
            </ul>
          </nav>

          {/* <!-- Icons --> */}
          <div className="flex items-center space-x-4">
            {/* <!-- Login --> */}
            {/* <a href="#" class="text-gray-700 hidden md:block hover:text-black">
              Login / Register
            </a> */}

            <Link to="/search">
              <AiOutlineSearch />
            </Link>

            <Link to="/cart">
              <IoCartOutline />
            </Link>
            <Link to="/signin">
              <AiOutlineUser />
            </Link>
          </div>
        </div>
      </header>

      {/* <!-- Để tránh nội dung bị che khuất do header cố định --> */}
      {/* <div class="mt-10"></div> */}
    </>
  );
};

export default Header;
