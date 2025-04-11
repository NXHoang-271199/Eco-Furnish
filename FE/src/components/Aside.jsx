import React from "react";
import { FaCamera } from "react-icons/fa";
import { Link } from "react-router-dom";
import axios from "axios";
import axiosInstance from "../utils/axiosConfig";
import { closeSocket } from "../utils/socketConfig";

const Aside = () => {
  const handleLogout = async (e) => {
    if (e) e.preventDefault();

    const token = localStorage.getItem("authToken");
    console.log("Token trước khi gửi lên API logout:", token);

    if (!token) {
      console.log("Không tìm thấy token");
      closeSocket();
      localStorage.clear();
      window.dispatchEvent(new Event("user-logout"));
      window.location.href = "/sign-in";
      return;
    }

    try {
      const response = await axiosInstance.post("/users/logout", null, {
        headers: {
          Authorization: `Bearer ${token}`,
        },
      });

      console.log("API Response:", response.data);
      closeSocket();
      localStorage.clear();
      window.dispatchEvent(new Event("user-logout"));
      window.location.href = "/sign-in";
    } catch (error) {
      console.error("Lỗi khi gọi API logout:", error.response?.data);
      closeSocket();
      localStorage.clear();
      window.dispatchEvent(new Event("user-logout"));
      window.location.href = "/sign-in";
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
