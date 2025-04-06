import React, { useEffect, useState } from "react";
import { Link } from "react-router-dom";
import { IoCartOutline } from "react-icons/io5";
import { AiOutlineUser, AiOutlineSearch, AiOutlineHeart } from "react-icons/ai";
import CartBadge from "./CartBadge";
import {
  ChevronDown,
  User,
  Shield,
  CreditCard,
  Bolt,
  Edit,
  Key,
  LogOut,
  BookOpen,
  CircleUserRound,
} from "lucide-react";
import { Button } from "./ui/button";
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuGroup,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from "./ui/dropdown-menu";
import { Avatar, AvatarFallback, AvatarImage } from "./ui/avatar";
import axios from "axios";
import { useNavigate } from "react-router-dom";
import { motion } from "framer-motion";

const Header = () => {
  const [isLoggedIn, setIsLoggedIn] = useState(false);
  const [userData, setUserData] = useState(null);
  const [isDropdownOpen, setIsDropdownOpen] = useState(false);
  const [showSearchInput, setShowSearchInput] = useState(false);
  const [searchValue, setSearchValue] = useState("");
  const navigate = useNavigate();

  useEffect(() => {
    const checkLoginStatus = () => {
      const token = localStorage.getItem("authToken");
      const storedUserData = localStorage.getItem("userData");
      if (token && storedUserData) {
        setIsLoggedIn(true);
        try {
          const parsedUserData = JSON.parse(storedUserData);
          setUserData({
            ...parsedUserData,
            avatar: parsedUserData.avatar || "https://via.placeholder.com/100",
            role: parsedUserData.role || "Khách hàng",
          });
        } catch (e) {
          console.error("Lỗi phân tích dữ liệu người dùng:", e);
          localStorage.removeItem("userData");
          localStorage.removeItem("authToken");
          setIsLoggedIn(false);
          setUserData(null);
        }
      } else {
        setIsLoggedIn(false);
        setUserData(null);
      }
    };

    // Thêm listener cho sự kiện cập nhật avatar
    const handleAvatarUpdate = (e) => {
      if (e.detail && e.detail.avatar) {
        setUserData((prevData) => ({
          ...prevData,
          avatar: e.detail.avatar,
        }));
      }
    };

    checkLoginStatus();
    window.addEventListener("user-login", checkLoginStatus);
    window.addEventListener("user-logout", checkLoginStatus);
    window.addEventListener("avatar-updated", handleAvatarUpdate);

    return () => {
      window.removeEventListener("user-login", checkLoginStatus);
      window.removeEventListener("user-logout", checkLoginStatus);
      window.removeEventListener("avatar-updated", handleAvatarUpdate);
    };
  }, []);

  const handleLogout = async (e) => {
    if (e) e.preventDefault();
    const token = localStorage.getItem("authToken");
    if (!token) {
      localStorage.clear();
      window.dispatchEvent(new Event("user-logout"));
      navigate("/sign-in");
      return;
    }
    try {
      await axios.post("http://localhost:8000/api/users/logout", null, {
        headers: { Authorization: `Bearer ${token}` },
      });
    } catch (error) {
      console.error("Lỗi khi gọi API logout:", error.response?.data);
    } finally {
      localStorage.clear();
      window.dispatchEvent(new Event("user-logout"));
      navigate("/sign-in");
      setIsDropdownOpen(false);
    }
  };

  const handleNavigate = (path) => {
    navigate(path);
    setIsDropdownOpen(false);
  };

  const toggleDropdown = () => setIsDropdownOpen(!isDropdownOpen);

  return (
    <>
      <header className="bg-white/95 fixed top-0 left-0 w-full z-50 shadow-md border-b backdrop-blur-md">
        <div className="max-w-7xl mx-auto flex justify-between items-center px-8 h-20">
          {/* Logo */}
          <div className="text-2xl font-bold text-black transition-transform hover:scale-105 duration-300">
            <div>
              <Link to="/">
                <img
                  src="/logoweb5.png"
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
              to="/cart"
              className="text-gray-600 hover:text-green-600 transition-all duration-300 p-2 hover:bg-gray-100 rounded-full relative"
            >
              <IoCartOutline size={20} className="transition-transform" />
              <CartBadge />
            </Link>

            {isLoggedIn && userData ? (
              <DropdownMenu>
                <DropdownMenuTrigger asChild>
                  <Button
                    variant="ghost"
                    className="flex items-center gap-2 p-2 rounded-full transition-all hover:bg-gray-100 h-auto"
                    onClick={toggleDropdown}
                  >
                    <Avatar className="h-8 w-8 border-2 border-primary/10 group-hover:border-primary/30 transition-all">
                      <AvatarImage src={userData.avatar} alt={userData.name} />
                      <AvatarFallback>
                        {userData.name ? userData.name.charAt(0) : "U"}
                      </AvatarFallback>
                    </Avatar>
                    <span className="font-medium text-sm hidden md:inline">
                      {userData.name}
                    </span>
                    <ChevronDown
                      size={16}
                      strokeWidth={2}
                      className={`ms-1 opacity-60 transition-transform ${
                        isDropdownOpen ? "rotate-180" : ""
                      }`}
                      aria-hidden="true"
                    />
                  </Button>
                </DropdownMenuTrigger>
                {isDropdownOpen && (
                  <DropdownMenuContent className="w-56 mt-1" align="end">
                    <DropdownMenuLabel className="flex items-start gap-3">
                      <img
                        src={userData.avatar}
                        alt="Avatar"
                        width={32}
                        height={32}
                        className="shrink-0 rounded-full"
                      />
                      <div className="flex min-w-0 flex-col">
                        <span className="truncate text-sm font-medium">
                          {userData.name}
                        </span>
                        <span className="truncate text-xs text-muted-foreground">
                          {userData.email}
                        </span>
                      </div>
                    </DropdownMenuLabel>
                    <DropdownMenuSeparator />
                    <DropdownMenuGroup>
                      <DropdownMenuItem
                        onClick={() => handleNavigate("/account")}
                      >
                        <User
                          size={16}
                          strokeWidth={2}
                          className="mr-2 opacity-60"
                          aria-hidden="true"
                        />
                        <span>Tài khoản của tôi</span>
                      </DropdownMenuItem>
                      <DropdownMenuItem
                        onClick={() => handleNavigate("/account/list_order")}
                      >
                        <CreditCard
                          size={16}
                          strokeWidth={2}
                          className="mr-2 opacity-60"
                          aria-hidden="true"
                        />
                        <span>Đơn hàng của tôi</span>
                      </DropdownMenuItem>
                      <DropdownMenuItem
                        onClick={() => handleNavigate("/account/address")}
                      >
                        <BookOpen
                          size={16}
                          strokeWidth={2}
                          className="mr-2 opacity-60"
                          aria-hidden="true"
                        />
                        <span>Địa chỉ của tôi</span>
                      </DropdownMenuItem>
                    </DropdownMenuGroup>
                    <DropdownMenuSeparator />
                    <DropdownMenuItem onClick={handleLogout}>
                      <LogOut
                        size={16}
                        strokeWidth={2}
                        className="mr-2 opacity-60"
                        aria-hidden="true"
                      />
                      <span>Đăng xuất</span>
                    </DropdownMenuItem>
                  </DropdownMenuContent>
                )}
              </DropdownMenu>
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
      <div className="h-20"></div>
    </>
  );
};

export default Header;
