import React, { useState } from "react";
import ForgotPasswordModal from "./ForgotPasswordModal";
import { motion } from "framer-motion"; // npm install framer-motion để chạy hiệu ứng
import { useForm } from "react-hook-form";
import { useNavigate } from "react-router-dom";
import axios from "axios";

const SignIn = () => {
  const [isModalOpen, setIsModalOpen] = useState(false);
  const {
    register,
    handleSubmit,
    formState: { errors },
  } = useForm();

  const navigate = useNavigate();

  const onSubmit = async (data) => {
    console.log(data);

    try {
      const response = await axios.post(
        `http://localhost:8000/api/users/login`,
        data
      );
      localStorage.setItem("token", response.data.accessToken);
      navigate("/");
    } catch (error) {
      // Hiển thị lỗi validation cụ thể nếu có
      if (error.response && error.response.data && error.response.data.errors) {
        console.error("Lỗi validation:", error.response.data.errors);
        // Hiển thị lỗi cho người dùng
      } else {
        console.error("Lỗi đăng nhập:", error);
      }
    }
  };

  return (
    <div className="flex w-full bg-white shadow-lg">
      {/* đang chạy hiệu ứng ảnh */}
      <div className="relative overflow-hidden w-1/2 hidden md:block">
        <motion.img
          src="https://i.pinimg.com/236x/0a/c9/ce/0ac9ce43730b62e7563a8ab9d0e8d5ba.jpg"
          alt="Background"
          className="object-cover w-full h-[800px]"
          initial={{ x: "100%" }}
          animate={{ x: "0%" }}
          transition={{ duration: 0.8, ease: "easeInOut" }}
        />
      </div>
      <motion.div
        className="w-full md:w-1/2 p-16  rounded-lg"
        initial={{ x: "-8%" }} // Bắt đầu ngoài màn hình (bên phải)
        animate={{ x: "0%" }} // Trượt vào màn hình
        transition={{ duration: 1.5, ease: "easeOut" }} // Hiệu ứng mượt hơn
      >
        <h2 className="text-2xl font-bold mb-2">Đăng Nhập</h2>
        <p className="mb-4">
          Chưa có tài khoản?{" "}
          <a href="/signup" className="text-green-500">
            Đăng Ký
          </a>
        </p>
        <form onSubmit={handleSubmit(onSubmit)}>
          <div className="mb-4">
            <input
              type="email"
              placeholder="Địa chỉ email của bạn"
              name="email"
              className="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              {...register("email", {
                required: "email is required",
              })}
            />
            {errors?.email && (
              <p className="text-red-400">{errors?.email?.message}</p>
            )}
          </div>
          <div className="mb-4 relative">
            <input
              type="password"
              placeholder="Mật khẩu"
              className="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              name="password"
              {...register("password", {
                required: "password is required",
              })}
            />
            {errors?.password && (
              <p className="text-red-400">{errors?.password?.message}</p>
            )}
            <i className="fas fa-eye absolute right-3 top-3 text-gray-500 cursor-pointer"></i>
          </div>
          <div className="flex items-center mb-4">
            <input type="checkbox" id="remember" className="mr-2" />
            <label htmlFor="remember" className="text-sm">
              Ghi nhớ
            </label>
          </div>
          <div className="flex justify-between items-center mb-4">
            <button className="w-full bg-blue-900 text-white py-2 rounded-md hover:bg-blue-800">
              Đăng Nhập
            </button>
          </div>
          <div className="text-center mb-4">
            <p
              className="text-blue-500 cursor-pointer"
              onClick={() => setIsModalOpen(true)}
            >
              Bạn quên mật khẩu?
            </p>
          </div>
          <div className="flex items-center mb-4">
            <div className="flex-grow border-t border-gray-300"></div>
            <span className="mx-4 text-gray-500">HOẶC</span>
            <div className="flex-grow border-t border-gray-300"></div>
          </div>
          <div className="flex flex-col space-y-2">
            <button className="flex items-center justify-center w-full py-2 border border-gray-300 rounded-md">
              <img
                src="https://img.icons8.com/?size=48&id=17949&format=png"
                alt="Google logo"
                className="mr-2"
                width="20"
                height="20"
              />
              Đăng nhập với Google
            </button>
            <button className="flex items-center justify-center w-full py-2 border border-gray-300 rounded-md">
              <img
                src="https://img.icons8.com/?size=48&id=uLWV5A9vXIPu&format=png"
                alt="Facebook logo"
                className="mr-2"
                width="20"
                height="20"
              />
              Đăng nhập với Facebook
            </button>
          </div>
        </form>
      </motion.div>

      {/* Popup Quên Mật Khẩu */}
      <ForgotPasswordModal
        isOpen={isModalOpen}
        onClose={() => setIsModalOpen(false)}
      />
    </div>
  );
};

export default SignIn;
