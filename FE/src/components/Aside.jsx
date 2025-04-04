import React from "react";
import { FaCamera } from "react-icons/fa";
import { Link } from "react-router-dom";
import axios from "axios";

const Aside = () => {
  const handleLogout = async (e) => {
    if (e) e.preventDefault();

    try {
      // Lấy token từ localStorage
      const token = localStorage.getItem("authToken");
      console.log("Token trước khi đăng xuất:", token);

      if (!token) {
        console.log("Không tìm thấy token");
        localStorage.clear();
        window.location.href = "/sign-in";
        return;
      }

      try {
        // Gọi API đăng xuất
        const response = await axios.post(
          "http://localhost:8000/api/users/logout",
          null, // Thay {} bằng null
          {
            headers: {
              Authorization: `Bearer ${token}`, // Thêm dấu nháy đơn
              "Content-Type": "application/json",
              Accept: "application/json",
            },
          }
        );
        console.log("API Response:", response.data);
      } catch (apiError) {
        console.error("Lỗi API:", apiError.response?.data);
      }

      // Luôn xóa dữ liệu và chuyển hướng, bất kể API thành công hay thất bại
      localStorage.clear(); // Thay vì removeItem
      window.location.href = "/"; // Chuyển về trang đăng nhập thay vì trang chủ
    } catch (error) {
      console.error("Lỗi tổng thể:", error);
      // Đảm bảo vẫn đăng xuất được
      // localStorage.clear();
      // window.location.href = "/signin";
    }
  };

  return (
    <aside className="w-full md:w-1/4 bg-gray-200 p-6">
      <div className="flex flex-col items-center">
        <div className="relative">
          <img
            src="https://via.placeholder.com/100"
            alt="Avatar"
            className="rounded-full w-24 h-24"
          />
          <span className="absolute bottom-0 right-0 bg-black p-1 rounded-full text-white text-xs cursor-pointer">
            <FaCamera />
          </span>
        </div>
        {/* <h2 className="mt-3 font-bold">Name</h2> */}
      </div>
      <nav className="mt-6">
        <ul>
          <li className="py-2 border-b">
            <Link to="/account" className="text-gray-700 hover:text-black">
              Tài khoản
            </Link>
          </li>
          <li className="py-2 border-b">
            <Link
              to="/account/address"
              className="text-gray-700 hover:text-black"
            >
              Địa chỉ
            </Link>
          </li>
          <li className="py-2 border-b">
            <Link
              to="/account/list_order"
              className="text-gray-700 hover:text-black"
            >
              Đơn hàng
            </Link>
          </li>
          <li className="py-2">
            <button
              onClick={handleLogout}
              className="text-red-500 font-bold hover:text-red-700 bg-transparent border-none cursor-pointer p-0"
            >
              Đăng xuất
            </button>
          </li>
        </ul>
      </nav>
    </aside>
  );
};

export default Aside;
