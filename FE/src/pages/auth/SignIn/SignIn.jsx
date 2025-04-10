import { useState, useEffect } from "react";
import { FaRegEyeSlash, FaEye } from "react-icons/fa";
import { FiMail, FiLock, FiFacebook, FiGithub } from "react-icons/fi";
import { FcGoogle } from "react-icons/fc";
import ForgotPasswordModal from "./ForgotPasswordModal";
import { motion } from "framer-motion";
import { useForm } from "react-hook-form";
import { Link, useNavigate, useLocation } from "react-router-dom";
import axios from "axios";
import { resetSocket } from "../../../utils/socketConfig";

const SignIn = () => {
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [isPasswordVisible, setIsPasswordVisible] = useState(false);
  const [authError, setAuthError] = useState(null);
  const {
    register,
    handleSubmit,
    formState: { errors },
  } = useForm();

  const navigate = useNavigate();
  const location = useLocation();

  useEffect(() => {
    const params = new URLSearchParams(location.search);
    const errorParam = params.get("error");
    if (errorParam) {
      let errorMessage = "Đã có lỗi xảy ra trong quá trình đăng nhập.";
      if (errorParam === "google_callback_failed") {
        errorMessage = "Đăng nhập bằng Google thất bại. Vui lòng thử lại.";
      } else if (errorParam === "facebook_callback_failed") {
        errorMessage = "Đăng nhập bằng Facebook thất bại. Vui lòng thử lại.";
      }
      setAuthError(errorMessage);
      navigate(location.pathname, { replace: true });
    }
  }, [location, navigate]);

  const onSubmit = async (data) => {
    try {
      const response = await axios.post(
        `http://localhost:8000/api/users/login`,
        {
          ...data,
          remember_me: data.remember_me || false,
        },
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
        navigate("/");
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

  // phải thông qua email-vẻ
  const refreshToken = async () => {
    try {
      const response = await axios.post(
        `http://localhost:8000/api/users/refresh-token`,
        {
          refresh_token: localStorage.getItem("refreshToken"), // Lưu refresh token trong localStorage
        },
        {
          headers: {
            "Content-Type": "application/json",
            Accept: "application/json",
          },
        }
      );

      if (response.data.status === "success") {
        localStorage.setItem("authToken", response.data.data.access_token);
        console.log("Token đã được làm mới:", response.data.data.access_token);
        return response.data.data.access_token;
      }
    } catch (error) {
      console.error("Lỗi làm mới token:", error);
    }
  };

  // Animation variants
  const containerVariants = {
    hidden: { opacity: 0 },
    visible: {
      opacity: 1,
      transition: {
        staggerChildren: 0.1,
        delayChildren: 0.3,
      },
    },
  };

  const itemVariants = {
    hidden: { y: 20, opacity: 0 },
    visible: {
      y: 0,
      opacity: 1,
      transition: { type: "spring", stiffness: 300, damping: 24 },
    },
  };

  const backgroundImages = [
    "https://i.pinimg.com/736x/3f/53/99/3f5399c11ba0eb487705bfeeeb7a4c97.jpg",
  ];

  return (
    <div className="flex min-h-screen w-full items-center justify-center bg-gradient-to-br from-slate-50 to-slate-100">
      <div className="container mx-auto px-4 py-8 flex flex-col md:flex-row w-full max-w-5xl rounded-xl overflow-hidden shadow-2xl">
        {/* Left panel - Image with parallax effect */}
        <motion.div
          className="relative overflow-hidden w-full md:w-1/2 h-[400px] md:h-auto rounded-t-xl md:rounded-l-xl md:rounded-tr-none bg-blue-900"
          initial={{ opacity: 0, x: -50 }}
          animate={{ opacity: 1, x: 0 }}
          transition={{ duration: 0.8, ease: "easeOut" }}
        >
          <motion.div
            className="absolute inset-0 w-full h-full"
            initial={{ scale: 1.2 }}
            animate={{ scale: 1 }}
            transition={{ duration: 1.5, ease: "easeOut" }}
          >
            <img
              src={backgroundImages[0]}
              alt="Eco-Furnish"
              className="object-cover w-full h-full"
            />
            <div className="absolute inset-0 bg-blue-900/30 backdrop-blur-[2px]"></div>
          </motion.div>

          <div className="absolute inset-0 flex flex-col justify-center items-center text-white p-10 z-10">
            <motion.div
              initial={{ opacity: 0, y: 30 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ delay: 0.5, duration: 0.8 }}
              className="text-center"
            >
              <h2 className="text-3xl md:text-4xl font-bold mb-4">
                Eco-Furnish
              </h2>
              <p className="text-lg opacity-90 mb-6">
                Nội thất bền vững cho cuộc sống hiện đại
              </p>
              <div className="w-16 h-1 bg-white/60 mx-auto rounded-full"></div>
            </motion.div>
          </div>
        </motion.div>

        {/* Right panel - Login form */}
        <motion.div
          className="w-full md:w-1/2 bg-white p-8 md:p-10 rounded-b-xl md:rounded-r-xl md:rounded-bl-none"
          variants={containerVariants}
          initial="hidden"
          animate="visible"
        >
          <motion.div variants={itemVariants}>
            <h2 className="text-2xl font-bold mb-2 text-gray-800">Đăng Nhập</h2>
            <p className="mb-6 text-gray-600">
              Chưa có tài khoản?{" "}
              <Link
                to="/sign-up"
                className="text-blue-600 hover:text-blue-800 font-medium transition-colors"
              >
                Đăng Ký
              </Link>
            </p>
          </motion.div>

          {authError && (
            <motion.div
              variants={itemVariants}
              className="mb-4 p-3 bg-red-100 border border-red-400 text-red-700 rounded"
            >
              {authError}
            </motion.div>
          )}

          <form onSubmit={handleSubmit(onSubmit)}>
            <motion.div variants={itemVariants} className="mb-5">
              <div className="relative">
                <span className="absolute left-3 top-3.5 text-gray-400">
                  <FiMail size={18} />
                </span>
                <input
                  type="email"
                  placeholder="Địa chỉ email của bạn"
                  name="email"
                  autoComplete="email"
                  className="w-full px-10 py-3 border bg-gray-50 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all"
                  {...register("email", {
                    required: "Email là bắt buộc",
                  })}
                />
              </div>
              {errors?.email && (
                <p className="text-red-500 text-sm mt-1">
                  {errors?.email?.message}
                </p>
              )}
            </motion.div>

            <motion.div variants={itemVariants} className="mb-4 relative">
              <div className="relative">
                <span className="absolute left-3 top-3.5 text-gray-400">
                  <FiLock size={18} />
                </span>
                <input
                  type={isPasswordVisible ? "text" : "password"}
                  placeholder="Mật khẩu"
                  className="w-full px-10 py-3 border bg-gray-50 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all"
                  name="password"
                  autoComplete="current-password"
                  {...register("password", {
                    required: "Mật khẩu là bắt buộc",
                  })}
                />
                <button
                  type="button"
                  onClick={() => setIsPasswordVisible(!isPasswordVisible)}
                  className="absolute right-3 top-3.5 text-gray-400 hover:text-gray-600 transition-colors"
                >
                  {isPasswordVisible ? (
                    <FaEye size={18} />
                  ) : (
                    <FaRegEyeSlash size={18} />
                  )}
                </button>
              </div>
              {errors?.password && (
                <p className="text-red-500 text-sm mt-1">
                  {errors?.password?.message}
                </p>
              )}
            </motion.div>

            <motion.div
              variants={itemVariants}
              className="flex items-center justify-between mb-6"
            >
              <div className="flex items-center">
                <input
                  type="checkbox"
                  id="remember_me"
                  {...register("remember_me")}
                  className="mr-2 h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                />
                <label htmlFor="remember_me" className="text-sm text-gray-600">
                  Ghi nhớ
                </label>
              </div>
              <div>
                <button
                  type="button"
                  className="text-sm text-blue-600 hover:text-blue-800 transition-colors"
                  onClick={() => setIsModalOpen(true)}
                >
                  Quên mật khẩu?
                </button>
              </div>
            </motion.div>

            <motion.div variants={itemVariants}>
              <motion.button
                type="submit"
                className="w-full bg-blue-600 text-white py-3 rounded-lg hover:bg-blue-700 transition-colors font-medium"
                whileHover={{ scale: 1.02 }}
                whileTap={{ scale: 0.98 }}
              >
                Đăng Nhập
              </motion.button>
            </motion.div>

            <motion.div
              variants={itemVariants}
              className="flex items-center my-6"
            >
              <div className="flex-grow border-t border-gray-200"></div>
              <span className="flex-shrink-0 mx-4 text-gray-500 text-sm">
                HOẶC
              </span>
              <div className="flex-grow border-t border-gray-200"></div>
            </motion.div>

            <motion.div
              variants={itemVariants}
              className="grid grid-cols-1 gap-3"
            >
              <motion.button
                type="button"
                className="flex items-center justify-center w-full py-3 px-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors"
                whileHover={{ scale: 1.02 }}
                whileTap={{ scale: 0.98 }}
                onClick={() =>
                (window.location.href =
                  "http://localhost:8000/api/auth/google/redirect")
                }
              >
                <FcGoogle className="mr-3" size={20} />
                <span className="text-gray-700">Đăng nhập với Google</span>
              </motion.button>

              <motion.button
                type="button"
                className="flex items-center justify-center w-full py-3 px-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors"
                whileHover={{ scale: 1.02 }}
                whileTap={{ scale: 0.98 }}
                onClick={() =>
                (window.location.href =
                  "http://localhost:8000/api/auth/facebook/redirect")
                }
              >
                <FiFacebook className="mr-3 text-blue-600" size={20} />
                <span className="text-gray-700">Đăng nhập với Facebook</span>
              </motion.button>
            </motion.div>
          </form>
        </motion.div>
      </div>

      {/* Popup Quên Mật Khẩu */}
      <ForgotPasswordModal
        isOpen={isModalOpen}
        onClose={() => setIsModalOpen(false)}
      />
    </div>
  );
};

export default SignIn;
