import React, { useEffect, useState } from "react";
import { Link, useNavigate } from "react-router-dom";
import { IoCartOutline } from "react-icons/io5";
import { AiOutlineUser } from "react-icons/ai";
import {
  MdLiving,
  MdOutlineBed,
  MdOutlineKitchen,
  MdOutlineDining,
  MdOutlineComputer,
  MdOutlineYard,
  MdOutlineBathtub,
  MdOutlineMore,
} from "react-icons/md";
import CartBadge from "./CartBadge";
import {
  ChevronDown,
  User,
  CreditCard,
  LogOut,
  BookOpen,
  Key,
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
import Notifications from "./Notifications";
import PaymentReminder from "./PaymentReminder";
import { closeSocket } from "../utils/socketConfig";

const Header = () => {
  const [isLoggedIn, setIsLoggedIn] = useState(false);
  const [userData, setUserData] = useState(null);
  const [isUserDropdownOpen, setIsUserDropdownOpen] = useState(false);
  const [isDropdownOpen, setIsDropdownOpen] = useState(false);
  const [hasLevel2Password, setHasLevel2Password] = useState(false);
  const [closeTimeout, setCloseTimeout] = useState(null);
  const [userCloseTimeout, setUserCloseTimeout] = useState(null);
  const navigate = useNavigate();
  const [categoriesBySpace, setCategoriesBySpace] = useState({});
  const [spaceKeyMap, setSpaceKeyMap] = useState({});

  const spaceDisplayNames = {
    living_room: "Phòng Khách",
    bedroom: "Phòng Ngủ",
    kitchen: "Phòng Bếp",
    dining_room: "Phòng Ăn",
    office: "Văn Phòng",
    outdoor: "Ngoài Trời",
    bathroom: "Phòng Tắm",
    other: "Khác",
  };

  // Map icons to space keys
  const spaceIcons = {
    "Phòng Khách": <MdLiving className="text-green-600" size={20} />,
    "Phòng Ngủ": <MdOutlineBed className="text-blue-500" size={20} />,
    "Phòng Bếp": <MdOutlineKitchen className="text-orange-500" size={20} />,
    "Phòng Ăn": <MdOutlineDining className="text-yellow-600" size={20} />,
    "Văn Phòng": <MdOutlineComputer className="text-purple-500" size={20} />,
    "Ngoài Trời": <MdOutlineYard className="text-emerald-500" size={20} />,
    "Phòng Tắm": <MdOutlineBathtub className="text-cyan-500" size={20} />,
    "Khác": <MdOutlineMore className="text-gray-500" size={20} />,
  };

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

          // Kiểm tra trạng thái mật khẩu cấp 2
          checkLevel2PasswordStatus(token);
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

    // Hàm kiểm tra trạng thái mật khẩu cấp 2
    const checkLevel2PasswordStatus = async (token) => {
      try {
        if (!token) return;

        const response = await axios.get("http://localhost:8000/api/users/level2-password/status", {
          headers: {
            Authorization: `Bearer ${token}`,
            "Content-Type": "application/json",
            Accept: "application/json",
          },
        });

        if (response.data.status === "success") {
          setHasLevel2Password(response.data.data.has_level2_password);
        }
      } catch (error) {
        console.error("Lỗi khi kiểm tra trạng thái mật khẩu cấp 2:", error);
      }
    };

    const handleAvatarUpdate = (e) => {
      if (e.detail && e.detail.avatar) {
        setUserData((prevData) => ({
          ...prevData,
          avatar: e.detail.avatar,
        }));
      }
    };

    // Lắng nghe sự kiện cập nhật trạng thái mật khẩu cấp 2
    const handleLevel2PasswordUpdate = (e) => {
      if (e.detail && e.detail.hasLevel2Password !== undefined) {
        setHasLevel2Password(e.detail.hasLevel2Password);
      }
    };

    checkLoginStatus();
    window.addEventListener("user-login", checkLoginStatus);
    window.addEventListener("user-logout", checkLoginStatus);
    window.addEventListener("avatar-updated", handleAvatarUpdate);
    window.addEventListener("level2password-updated", handleLevel2PasswordUpdate);

    const fetchCategories = async () => {
      try {
        const response = await axios.get("http://localhost:8000/api/categories");
        if (response.data.success) {
          setCategoriesBySpace(response.data.data);
          const keyMap = Object.entries(spaceDisplayNames).reduce((acc, [key, name]) => {
            acc[name] = key;
            return acc;
          }, {});
          setSpaceKeyMap(keyMap);
        } else {
          console.error("Lỗi khi lấy danh mục:", response.data.message);
          setCategoriesBySpace({});
          setSpaceKeyMap({});
        }
      } catch (error) {
        console.error("Lỗi mạng hoặc server khi lấy danh mục:", error);
        setCategoriesBySpace({});
        setSpaceKeyMap({});
      }
    };

    fetchCategories();

    return () => {
      window.removeEventListener("user-login", checkLoginStatus);
      window.removeEventListener("user-logout", checkLoginStatus);
      window.removeEventListener("avatar-updated", handleAvatarUpdate);
      window.removeEventListener("level2password-updated", handleLevel2PasswordUpdate);
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
      closeSocket();
      localStorage.clear();
      window.dispatchEvent(new Event("user-logout"));
      navigate("/sign-in");
      setIsUserDropdownOpen(false);
    }
  };

  const handleUserNavigate = (path) => {
    navigate(path);
    setIsUserDropdownOpen(false);
  };

  const handleSpaceNavigate = (spaceName) => {
    const spaceKey = spaceKeyMap[spaceName];
    if (spaceKey) {
      navigate(`/products?space=${spaceKey}`);
    }
  };

  const toggleUserDropdown = () => {
    setIsUserDropdownOpen((prevState) => !prevState);
  };

  const handleMouseEnter = () => {
    // Xóa bỏ timeout đóng dropdown nếu có
    if (closeTimeout) {
      clearTimeout(closeTimeout);
      setCloseTimeout(null);
    }
    setIsDropdownOpen(true);
  };

  const handleMouseLeave = () => {
    // Thiết lập timeout để đóng dropdown sau một khoảng thời gian
    const timeout = setTimeout(() => {
      setIsDropdownOpen(false);
    }, 100); // Đợi 100ms trước khi đóng dropdown
    setCloseTimeout(timeout);
  };

  const handleUserMouseEnter = () => {
    // Xóa bỏ timeout đóng dropdown nếu có
    if (userCloseTimeout) {
      clearTimeout(userCloseTimeout);
      setUserCloseTimeout(null);
    }
    setIsUserDropdownOpen(true);
  };

  const handleUserMouseLeave = () => {
    // Thiết lập timeout để đóng dropdown sau một khoảng thời gian
    const timeout = setTimeout(() => {
      setIsUserDropdownOpen(false);
    }, 100); // Đợi 100ms trước khi đóng dropdown
    setUserCloseTimeout(timeout);
  };

  // Xóa timeout khi component unmount
  useEffect(() => {
    return () => {
      if (closeTimeout) clearTimeout(closeTimeout);
      if (userCloseTimeout) clearTimeout(userCloseTimeout);
    };
  }, [closeTimeout, userCloseTimeout]);

  return (
    <>
      {isLoggedIn && <PaymentReminder />}
      <header className="bg-white/95 fixed top-0 left-0 w-full z-50 shadow-md border-b backdrop-blur-md">
        <div className="max-w-7xl mx-auto flex justify-between items-center px-8 h-20">
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

          <nav className="hidden md:flex space-x-12 items-center">
            <Link
              to="/"
              className="text-gray-700 hover:text-green-600 text-base font-medium transition-all duration-300 hover:-translate-y-0.5 relative after:content-[''] after:absolute after:left-0 after:bottom-[-4px] after:h-[2px] after:w-0 after:bg-green-600 after:transition-all after:duration-300 hover:after:w-full"
            >
              Trang chủ
            </Link>

            <div
              onMouseEnter={handleMouseEnter}
              onMouseLeave={handleMouseLeave}
              className="relative"
            >
              <Button
                variant="ghost"
                className="text-gray-700 hover:text-green-600 text-base font-medium transition-all duration-300 hover:-translate-y-0.5 relative after:content-[''] after:absolute after:left-0 after:bottom-[-4px] after:h-[2px] after:w-0 after:bg-green-600 after:transition-all after:duration-300 hover:after:w-full flex items-center p-0 h-auto hover:bg-transparent focus-visible:ring-0 focus-visible:ring-offset-0"
              >
                Danh mục
                <ChevronDown
                  size={16}
                  strokeWidth={2}
                  className={`ms-1 opacity-60 transition-transform ${isDropdownOpen ? "rotate-180" : ""}`}
                  aria-hidden="true"
                />
              </Button>

              {isDropdownOpen && (
                <div
                  className="absolute left-0 w-[520px] mt-2 border-0 shadow-xl rounded-xl bg-white overflow-hidden"
                  onMouseEnter={handleMouseEnter}
                  onMouseLeave={handleMouseLeave}
                >
                  <div className="p-3 bg-gradient-to-r from-green-50 to-emerald-50 border-b">
                    <h3 className="text-base font-semibold text-green-800">Danh mục theo không gian</h3>
                    <p className="text-sm text-green-600 mt-1">Chọn không gian bạn muốn khám phá</p>
                  </div>

                  <div className="p-3 grid grid-cols-3 gap-3">
                    {Object.keys(categoriesBySpace).length > 0 ? (
                      Object.keys(categoriesBySpace).map((spaceName) => (
                        <div
                          key={spaceName}
                          onClick={() => handleSpaceNavigate(spaceName)}
                          className="cursor-pointer hover:bg-green-50 p-3 text-sm transition-all duration-200 rounded-lg flex flex-col items-start h-auto"
                        >
                          <div className="flex items-center gap-2 mb-1.5">
                            <div className="flex-shrink-0 w-6 h-6 flex items-center justify-center">
                              {spaceIcons[spaceName] || <MdOutlineMore className="text-gray-500" size={18} />}
                            </div>
                            <p className="font-medium text-gray-800 text-sm">{spaceName}</p>
                          </div>
                          <p className="text-xs text-gray-500 pl-8">
                            {categoriesBySpace[spaceName]?.length || 0} danh mục
                          </p>
                        </div>
                      ))
                    ) : (
                      <div className="px-3 py-3 text-sm text-gray-500 col-span-3">
                        <div className="flex items-center gap-2 justify-center w-full">
                          <div className="h-4 w-4 border-2 border-gray-300 rounded-full border-t-transparent animate-spin"></div>
                          Đang tải...
                        </div>
                      </div>
                    )}
                  </div>

                  <div className="p-2 bg-gray-50 border-t text-center">
                    <Link
                      to="/products"
                      className="text-xs text-green-600 hover:text-green-700 font-medium inline-flex items-center gap-1"
                    >
                      Xem tất cả sản phẩm
                      <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                        <path d="M5 12h14M12 5l7 7-7 7" />
                      </svg>
                    </Link>
                  </div>
                </div>
              )}
            </div>

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

          <div className="flex items-center space-x-6">
            {isLoggedIn && <Notifications />}
            <Link
              to="/cart"
              className="text-gray-600 hover:text-green-600 transition-all duration-300 p-2 hover:bg-gray-100 rounded-full relative"
            >
              <IoCartOutline size={20} className="transition-transform" />
              <CartBadge />
            </Link>

            {isLoggedIn && userData ? (
              <div
                className="relative"
                onMouseEnter={handleUserMouseEnter}
                onMouseLeave={handleUserMouseLeave}
              >
                <Button
                  variant="ghost"
                  className="flex items-center gap-2 p-2 rounded-full transition-all hover:bg-gray-100 h-auto"
                >
                  <Avatar className="h-8 w-8 border-2 border-primary/10 group-hover:border-primary/30 transition-all">
                    <AvatarImage
                      src={
                        userData.avatar && !userData.avatar.includes("placeholder.com")
                          ? userData.avatar
                          : "/images/avatarEmpty/avatarUser.png"
                      }
                      alt={userData.name}
                    />
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
                    className={`ms-1 opacity-60 transition-transform ${isUserDropdownOpen ? "rotate-180" : ""}`}
                    aria-hidden="true"
                  />
                </Button>

                {isUserDropdownOpen && (
                  <div
                    className="absolute right-0 w-56 mt-1 border-0 shadow-xl rounded-xl bg-white overflow-hidden z-50"
                    onMouseEnter={handleUserMouseEnter}
                    onMouseLeave={handleUserMouseLeave}
                  >
                    <div className="flex items-start gap-3 p-3 border-b">
                      <img
                        src={
                          userData.avatar && !userData.avatar.includes("placeholder.com")
                            ? userData.avatar
                            : "/images/avatarEmpty/avatarUser.png"
                        }
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
                    </div>

                    <div className="border-b"></div>

                    <div className="p-1">
                      <div
                        onClick={() => handleUserNavigate("/account")}
                        className="flex items-center gap-2 px-3 py-2 text-sm rounded-lg hover:bg-gray-100 cursor-pointer"
                      >
                        <User
                          size={16}
                          strokeWidth={2}
                          className="opacity-60"
                          aria-hidden="true"
                        />
                        <span>Tài khoản của tôi</span>
                      </div>

                      <div
                        onClick={() => handleUserNavigate("/account/list_order")}
                        className="flex items-center gap-2 px-3 py-2 text-sm rounded-lg hover:bg-gray-100 cursor-pointer"
                      >
                        <CreditCard
                          size={16}
                          strokeWidth={2}
                          className="opacity-60"
                          aria-hidden="true"
                        />
                        <span>Đơn hàng của tôi</span>
                      </div>

                      <div
                        onClick={() => handleUserNavigate("/account/address")}
                        className="flex items-center gap-2 px-3 py-2 text-sm rounded-lg hover:bg-gray-100 cursor-pointer"
                      >
                        <BookOpen
                          size={16}
                          strokeWidth={2}
                          className="opacity-60"
                          aria-hidden="true"
                        />
                        <span>Địa chỉ của tôi</span>
                      </div>

                      <div
                        onClick={() => handleUserNavigate("/account?tab=level2password")}
                        className="flex items-center gap-2 px-3 py-2 text-sm rounded-lg hover:bg-gray-100 cursor-pointer relative"
                      >
                        <Key
                          size={16}
                          strokeWidth={2}
                          className="opacity-60"
                          aria-hidden="true"
                        />
                        <span>Mật khẩu cấp 2</span>
                        {hasLevel2Password ? (
                          <span className="absolute right-2 top-1/2 transform -translate-y-1/2 bg-green-500 text-white text-[10px] px-1.5 py-0.5 rounded-full">
                            Đã thiết lập
                          </span>
                        ) : (
                          <span className="absolute right-2 top-1/2 transform -translate-y-1/2 bg-gray-300 text-gray-700 text-[10px] px-1.5 py-0.5 rounded-full">
                            Chưa thiết lập
                          </span>
                        )}
                      </div>
                    </div>

                    <div className="border-t"></div>

                    <div
                      onClick={handleLogout}
                      className="flex items-center gap-2 px-3 py-2 text-sm hover:bg-gray-100 cursor-pointer"
                    >
                      <LogOut
                        size={16}
                        strokeWidth={2}
                        className="opacity-60"
                        aria-hidden="true"
                      />
                      <span>Đăng xuất</span>
                    </div>
                  </div>
                )}
              </div>
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
