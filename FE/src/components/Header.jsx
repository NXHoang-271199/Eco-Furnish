import React, { useEffect, useState } from "react";
import { Link } from "react-router-dom";
import { IoCartOutline } from "react-icons/io5";
import { AiOutlineUser, AiOutlineSearch, AiOutlineHeart } from "react-icons/ai";
import CartBadge from "./CartBadge";

const Header = () => {
  const [isLoggedIn, setIsLoggedIn] = useState(false);

  useEffect(() => {
    // Kiểm tra trạng thái đăng nhập khi component được tải
    const checkLoginStatus = () => {
      const token = localStorage.getItem("authToken");
      if (token) {
        setIsLoggedIn(true);
      } else {
        setIsLoggedIn(false);
      }
    };

    checkLoginStatus();

    // Lắng nghe sự kiện đăng nhập từ các component khác
    window.addEventListener("user-login", checkLoginStatus);

    return () => {
      window.removeEventListener("user-login", checkLoginStatus);
    };
  }, []);

  return (
    <>
      <header className="bg-white/95 fixed top-0 left-0 w-full z-50 shadow-md border-b backdrop-blur-md">
        <div className="max-w-7xl mx-auto flex justify-between items-center px-8 h-20">
          {/* Logo */}
          <div className="text-2xl font-bold text-black transition-transform hover:scale-105 duration-300">
            <div>
              <Link to="/">
                <img
                  src="./logoweb5.png"
                  alt="Eco-Furnish Logo"
                  className="w-[70px] h-[70px] rounded-xl object-contain hover:shadow-lg transition-all duration-300"
                />
              </Link>
            </div>
          </div>

          {/* Navigation */}
          <nav className="hidden md:flex space-x-12">
            <Link
              to="/"
              className="text-gray-700 hover:text-green-600 text-base font-medium transition-all duration-300 hover:-translate-y-0.5 relative after:content-[''] after:absolute after:left-0 after:bottom-[-4px] after:h-[2px] after:w-0 after:bg-green-600 after:transition-all after:duration-300 hover:after:w-full"
            >
              Trang chủ
            </Link>
            <Link
              to="/products"
              className="text-gray-700 hover:text-green-600 text-base font-medium transition-all duration-300 hover:-translate-y-0.5 relative after:content-[''] after:absolute after:left-0 after:bottom-[-4px] after:h-[2px] after:w-0 after:bg-green-600 after:transition-all after:duration-300 hover:after:w-full"
            >
              Sản phẩm
            </Link>
            <Link
              to="/blogs"
              className="text-gray-700 hover:text-green-600 text-base font-medium transition-all duration-300 hover:-translate-y-0.5 relative after:content-[''] after:absolute after:left-0 after:bottom-[-4px] after:h-[2px] after:w-0 after:bg-green-600 after:transition-all after:duration-300 hover:after:w-full"
            >
              Bài viết
            </Link>
            <Link
              to="/contact"
              className="text-gray-700 hover:text-green-600 text-base font-medium transition-all duration-300 hover:-translate-y-0.5 relative after:content-[''] after:absolute after:left-0 after:bottom-[-4px] after:h-[2px] after:w-0 after:bg-green-600 after:transition-all after:duration-300 hover:after:w-full"
            >
              Liên hệ
            </Link>
          </nav>

          {/* Icons */}
          <div className="flex items-center space-x-6">
            <Link
              to="/search"
              className="text-gray-600 hover:text-green-600 transition-all duration-300 p-2 hover:bg-gray-100 rounded-full"
            >
              <AiOutlineSearch size={20} className="transition-transform" />
            </Link>
            <Link
              to="/cart"
              className="text-gray-600 hover:text-green-600 transition-all duration-300 p-2 hover:bg-gray-100 rounded-full relative"
            >
              <IoCartOutline size={20} className="transition-transform" />
              <CartBadge />
            </Link>
            {isLoggedIn ? (
              <Link
                to="/account"
                className="text-gray-600 hidden md:block hover:text-green-600 transition-all duration-300 p-2 hover:bg-gray-100 rounded-full"
              >
                <AiOutlineUser size={20} className="transition-transform" />
              </Link>
            ) : (
              <Link
                to="/sign-in"
                className="text-gray-600 hidden md:block hover:text-green-600 transition-all duration-300 p-2 hover:bg-gray-100 rounded-full"
              >
                <AiOutlineUser size={20} className="transition-transform" />
              </Link>
            )}
          </div>
        </div>
      </header>
      <div className="h-20"></div> {/* Spacer để tránh content bị đẩy lên */}
    </>
  );
};

export default Header;
