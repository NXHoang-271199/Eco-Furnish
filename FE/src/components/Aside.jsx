import React from "react";
import { FaCamera } from "react-icons/fa";
import { Link, useNavigate } from "react-router-dom";
import axios from "axios";

const Aside = () => {
  const navigate = useNavigate();

  const handleLogout = async (e) => {
    if (e) e.preventDefault();

    try {
      // Lấy token từ localStorage
      const token = localStorage.getItem("authToken");
      // console.log("Token trước khi đăng xuất:", token);

      if (token) {
        // Log headers để debug
        // const headers = {
        //   Authorization: `Bearer ${token}`,
        //   "Content-Type": "application/json",
        //   Accept: "application/json",
        // };
        // console.log("Headers gửi đi:", headers);

        // Gọi API đăng xuất với headers giống Postman
        const response = await axios.post(
          "http://localhost:8000/api/users/logout",
          {}, // empty body
          {
            Headers: {
              Authorization: `Bearer ${token}`,
              "Content-Type": "application/json",
              Accept: "application/json",
            },
          }
        );

        console.log("API Response:", response.data);
      }

      // Luôn xóa dữ liệu khỏi localStorage
      localStorage.removeItem("authToken");
      localStorage.removeItem("userData");

      // Chuyển hướng về trang chủ
      navigate("/");
    } catch (error) {
      console.error("Lỗi đăng xuất chi tiết:", error);

      // Vẫn đăng xuất client-side
      // localStorage.removeItem("authToken");
      // localStorage.removeItem("userData");
      // window.location.href = "/";
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
              to="/account/editpass"
              className="text-gray-700 hover:text-black"
            >
              Thay đổi mật khẩu
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
