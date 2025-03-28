import { useState } from "react";
import { FaRegEyeSlash, FaEye } from "react-icons/fa";
import ForgotPasswordModal from "./ForgotPasswordModal";
import { motion } from "framer-motion"; // npm install framer-motion để chạy hiệu ứng
import { useForm } from "react-hook-form";
import { useNavigate, useLocation } from "react-router-dom";
import axios from "axios";
import { resetSocket } from "../../../utils/socketConfig";

const SignIn = () => {
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [isPasswordVisible, setIsPasswordVisible] = useState(false);
  const {
    register,
    handleSubmit,
    formState: { errors },
  } = useForm();

  const navigate = useNavigate();
  const location = useLocation();

  const onSubmit = async (data) => {
    try {
      const response = await axios.post(
        `http://127.0.0.1:8000/api/users/login`,
        data,
        {
          headers: {
            "Content-Type": "application/json",
            Accept: "application/json",
          },
        }
      );

      if (response.data.status === "success") {
        // Lưu token và thông tin user
        localStorage.setItem("authToken", response.data.data.access_token);
        localStorage.setItem("refreshToken", response.data.data.refresh_token);
        localStorage.setItem("userData", JSON.stringify(response.data.data));

        // Khởi động lại socket connection
        setTimeout(() => {
          console.log("🔌 Khởi động lại kết nối socket sau đăng nhập...");
          resetSocket();
        }, 500);

        // Kiểm tra xem đã lưu token thành công chưa
        console.log("Token đã lưu:", localStorage.getItem("authToken"));
        console.log("User data đã lưu:", localStorage.getItem("userData"));

        // Phát sự kiện để thông báo đăng nhập thành công cho các tab khác
        const authChangeEvent = new Event("auth-change");
        window.dispatchEvent(authChangeEvent);

        // Phát sự kiện storage để cập nhật các tab khác
        try {
          const storageEvent = new StorageEvent("storage", {
            key: "authToken",
            newValue: response.data.data.access_token,
          });
          window.dispatchEvent(storageEvent);

          // Thêm sự kiện cho userData
          const userDataEvent = new StorageEvent("storage", {
            key: "userData",
            newValue: JSON.stringify(response.data.data),
          });
          window.dispatchEvent(userDataEvent);
        } catch (error) {
          console.error("Lỗi khi phát sự kiện storage:", error);
        }

        // Kiểm tra xem có returnUrl trong state không
        const returnUrl = location.state?.returnUrl || "/";

        // Thêm dữ liệu để chuyển về trang chi tiết
        if (location.state?.returnUrl) {
          localStorage.setItem("returnPath", location.state.returnUrl);
        }

        navigate(returnUrl, { replace: true });
      }
    } catch (error) {
      if (error.response?.status === 403) {
        alert("Vui lòng xác thực email trước khi đăng nhập");
      } else if (error.response?.data?.message) {
        alert(error.response.data.message);
      } else {
        alert("Đã có lỗi xảy ra khi đăng nhập");
      }
    }
  };

  // Lưu ý: Chức năng refresh token đã được chuyển sang AuthContext để xử lý tập trung
  
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
              type={isPasswordVisible ? "text" : "password"}
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
            <button
              type="button"
              onClick={() => setIsPasswordVisible(!isPasswordVisible)}
            >
              {isPasswordVisible ? (
                <FaEye className="absolute right-3 top-3 text-gray-500 cursor-pointer" />
              ) : (
                <FaRegEyeSlash className="absolute right-3 top-3 text-gray-500 cursor-pointer" />
              )}
            </button>
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
