import React, { useEffect, useState } from "react";
import { Link } from "react-router-dom";
import { IoCartOutline } from "react-icons/io5";
import { AiOutlineUser, AiOutlineSearch, AiOutlineHeart } from "react-icons/ai";
import CartBadge from "./CartBadge";

const Header = () => {
  const [isLoggedIn, setIsLoggedIn] = useState(false);
  const [showSearchInput, setShowSearchInput] = useState(false);
  const [searchValue, setSearchValue] = useState("");

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
      <header className="bg-white fixed top-0 left-0 w-full z-50">
        <div className="max-w-6xl mx-auto flex justify-between items-center p-4">
          {/* Logo */}
          <div className="text-2xl font-bold text-black">
            <div>
              <Link to="/">
                <span className="text-yellow-300">E</span>co-
                <span className="text-yellow-300">F</span>urnish
              </Link>
            </div>
          </div>

          {/* Navigation */}
          <nav className="hidden md:flex space-x-8">
            <Link to="/" className="text-gray-700 hover:text-black">
              Trang chủ
            </Link>
            <Link to="/products" className="text-gray-700 hover:text-black">
              Sản phẩm
            </Link>
            <Link to="/blogs" className="text-gray-700 hover:text-black">
              Blog
            </Link>
            <Link to="/about" className="text-gray-700 hover:text-black">
              Về chúng tôi
            </Link>
            <Link to="/contact" className="text-gray-700 hover:text-black">
              Liên hệ
            </Link>
          </nav>

          {/* Icons */}
          <div className="flex items-center space-x-6">
            <div className="relative">
              {showSearchInput ? (
                <input
                  type="text"
                  value={searchValue}
                  onChange={(e) => setSearchValue(e.target.value)}
                  placeholder="Tìm kiếm..."
                  className="border rounded-md px-3 py-1 focus:outline-none focus:ring-2 focus:ring-blue-400"
                  // Bạn có thể xử lý submit hoặc blur để ẩn input nếu cần
                  onBlur={() => setShowSearchInput(false)}
                  autoFocus
                />
              ) : (
                <Link
                  to="#"
                  onClick={(e) => {
                    e.preventDefault();
                    setShowSearchInput(true);
                  }}
                  className="text-gray-700 hover:text-black"
                >
                  <AiOutlineSearch size={20} />
                </Link>
              )}
            </div>
            {/* <Link to="/search" className="text-gray-700 hover:text-black">
              <AiOutlineSearch size={20} />
            </Link> */}
            <Link to="/wishlist" className="text-gray-700 hover:text-black">
              <AiOutlineHeart size={20} />
            </Link>
            <Link
              to="/cart"
              className="text-gray-700 hover:text-black relative"
            >
              <IoCartOutline size={20} />
              <CartBadge />
            </Link>
            {isLoggedIn ? (
              <Link
                to="/account"
                className="text-gray-700 hidden md:block hover:text-black"
              >
                <AiOutlineUser className="inline mr-1" />
              </Link>
            ) : (
              <Link
                to="/signin"
                className="text-gray-700 hidden md:block hover:text-black"
              >
                <AiOutlineUser className="inline mr-1" />
              </Link>
            )}
          </div>
        </div>
      </header>
    </>
  );
};

export default Header;
