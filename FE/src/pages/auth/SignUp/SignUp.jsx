import React from "react";
import { motion } from "framer-motion";

const SignUp = () => {
  return (
    <div className="flex w-full bg-white shadow-lg">
      <div className="relative overflow-hidden w-1/2 hidden md:block">
      <motion.img
        src="https://i.pinimg.com/236x/0a/c9/ce/0ac9ce43730b62e7563a8ab9d0e8d5ba.jpg"
        alt="Background"
        className="object-cover w-full h-[800px]"
        initial={{ x: "100%" }}
        animate={{ x: "0%" }}
        transition={{ duration: 1.5, ease: "easeInOut" }
      }
      />
    </div>
      <div className="w-full md:w-1/2 p-32">
        <h2 className="text-2xl font-bold mb-2">Đăng Ký</h2>
        <p className="mb-4">
          Đã có tài khoản?{" "}
          <a href="/signin" className="text-green-500">
            Đăng Nhập
          </a>
        </p>
        <form>
          <div className="mb-4">
            <input
              type="text"
              placeholder="Tên đầy đủ của bạn"
              className="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>
          <div className="mb-4">
            <input
              type="email"
              placeholder="Địa chỉ email của bạn"
              className="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>
          <div className="mb-4 relative">
            <input
              type="password"
              placeholder="Mật khẩu"
              className="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
            <i className="fas fa-eye absolute right-3 top-3 text-gray-500 cursor-pointer"></i>
          </div>
          <div className="mb-4">
            <input
              type="password"
              placeholder="Xác nhận mật khẩu"
              className="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>
          <div className="mb-4 flex items-center">
            <input type="checkbox" id="terms" className="mr-2" />
            <label htmlFor="terms">
              Tôi đồng ý với{" "}
              <a href="#" className="text-blue-500">
                Chính Sách Bảo Mật
              </a>{" "}
              và{" "}
              <a href="#" className="text-blue-500">
                Điều Khoản Sử Dụng
              </a>
            </label>
          </div>
          <button
            type="submit"
            className="w-full bg-blue-900 text-white py-2 rounded-lg hover:bg-blue-800"
          >
            Đăng Ký
          </button>
        </form>
        <div className="flex items-center my-4">
          <hr className="flex-grow border-t border-gray-300" />
          <span className="mx-4 text-gray-500">HOẶC</span>
          <hr className="flex-grow border-t border-gray-300" />
        </div>
        <div className="flex flex-col space-y-2">
          <button className="flex items-center justify-center w-full px-4 py-2 border rounded-lg hover:bg-gray-100">
            <img
              src="https://img.icons8.com/?size=48&id=17949&format=png"
              alt="Google logo"
              className="mr-2"
              width="20"
              height="20"
            />
            Đăng nhập bằng Google
          </button>
          <button className="flex items-center justify-center w-full px-4 py-2 border rounded-lg hover:bg-gray-100">
            <img
              src="https://img.icons8.com/?size=48&id=uLWV5A9vXIPu&format=png"
              alt="Facebook logo"
              className="mr-2"
              width="20"
              height="20"
            />
            Đăng nhập bằng Facebook
          </button>
        </div>
      </div>
    </div>
  );
};

export default SignUp;
