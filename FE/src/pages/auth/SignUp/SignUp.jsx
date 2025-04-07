import React from "react";
import { useState } from "react";
import { motion, AnimatePresence } from "framer-motion";
import { useForm } from "react-hook-form";
import { useNavigate } from "react-router-dom";
import {
  FaRegEyeSlash,
  FaEye,
  FaGoogle,
  FaFacebook,
  FaArrowRight,
} from "react-icons/fa";
import axios from "axios";

const BackgroundDecoration = () => {
  return (
    <div className="absolute inset-0 overflow-hidden pointer-events-none z-0">
      <div className="absolute -right-40 -top-40 h-[500px] w-[500px] rounded-full bg-green-500/5 blur-3xl" />
      <div className="absolute -bottom-40 -left-40 h-[500px] w-[500px] rounded-full bg-blue-500/5 blur-3xl" />
      <svg
        className="absolute right-0 top-0 opacity-20"
        width="400"
        height="400"
        viewBox="0 0 400 400"
        fill="none"
        xmlns="http://www.w3.org/2000/svg"
      >
        <g clipPath="url(#clip0_1_2)">
          <path
            d="M400 0H0V400H400V0Z"
            stroke="url(#paint0_linear_1_2)"
            strokeWidth="2"
          />
          <path
            d="M133.5 0H0V133.5H133.5V0Z"
            stroke="url(#paint1_linear_1_2)"
            strokeWidth="2"
          />
          <path
            d="M267 0H133.5V133.5H267V0Z"
            stroke="url(#paint2_linear_1_2)"
            strokeWidth="2"
          />
          <path
            d="M400 0H267V133.5H400V0Z"
            stroke="url(#paint3_linear_1_2)"
            strokeWidth="2"
          />
          <path
            d="M133.5 133.5H0V267H133.5V133.5Z"
            stroke="url(#paint4_linear_1_2)"
            strokeWidth="2"
          />
          <path
            d="M267 133.5H133.5V267H267V133.5Z"
            stroke="url(#paint5_linear_1_2)"
            strokeWidth="2"
          />
          <path
            d="M400 133.5H267V267H400V133.5Z"
            stroke="url(#paint6_linear_1_2)"
            strokeWidth="2"
          />
          <path
            d="M133.5 267H0V400H133.5V267Z"
            stroke="url(#paint7_linear_1_2)"
            strokeWidth="2"
          />
          <path
            d="M267 267H133.5V400H267V267Z"
            stroke="url(#paint8_linear_1_2)"
            strokeWidth="2"
          />
          <path
            d="M400 267H267V400H400V267Z"
            stroke="url(#paint9_linear_1_2)"
            strokeWidth="2"
          />
        </g>
        <defs>
          <linearGradient
            id="paint0_linear_1_2"
            x1="200"
            y1="0"
            x2="200"
            y2="400"
            gradientUnits="userSpaceOnUse"
          >
            <stop stopColor="#4CAF50" />
            <stop offset="1" stopColor="#4CAF50" stopOpacity="0" />
          </linearGradient>
          <linearGradient
            id="paint1_linear_1_2"
            x1="66.75"
            y1="0"
            x2="66.75"
            y2="133.5"
            gradientUnits="userSpaceOnUse"
          >
            <stop stopColor="#4CAF50" />
            <stop offset="1" stopColor="#4CAF50" stopOpacity="0" />
          </linearGradient>
          <linearGradient
            id="paint2_linear_1_2"
            x1="200.25"
            y1="0"
            x2="200.25"
            y2="133.5"
            gradientUnits="userSpaceOnUse"
          >
            <stop stopColor="#4CAF50" />
            <stop offset="1" stopColor="#4CAF50" stopOpacity="0" />
          </linearGradient>
          <linearGradient
            id="paint3_linear_1_2"
            x1="333.5"
            y1="0"
            x2="333.5"
            y2="133.5"
            gradientUnits="userSpaceOnUse"
          >
            <stop stopColor="#4CAF50" />
            <stop offset="1" stopColor="#4CAF50" stopOpacity="0" />
          </linearGradient>
          <linearGradient
            id="paint4_linear_1_2"
            x1="66.75"
            y1="133.5"
            x2="66.75"
            y2="267"
            gradientUnits="userSpaceOnUse"
          >
            <stop stopColor="#4CAF50" />
            <stop offset="1" stopColor="#4CAF50" stopOpacity="0" />
          </linearGradient>
          <linearGradient
            id="paint5_linear_1_2"
            x1="200.25"
            y1="133.5"
            x2="200.25"
            y2="267"
            gradientUnits="userSpaceOnUse"
          >
            <stop stopColor="#4CAF50" />
            <stop offset="1" stopColor="#4CAF50" stopOpacity="0" />
          </linearGradient>
          <linearGradient
            id="paint6_linear_1_2"
            x1="333.5"
            y1="133.5"
            x2="333.5"
            y2="267"
            gradientUnits="userSpaceOnUse"
          >
            <stop stopColor="#4CAF50" />
            <stop offset="1" stopColor="#4CAF50" stopOpacity="0" />
          </linearGradient>
          <linearGradient
            id="paint7_linear_1_2"
            x1="66.75"
            y1="267"
            x2="66.75"
            y2="400"
            gradientUnits="userSpaceOnUse"
          >
            <stop stopColor="#4CAF50" />
            <stop offset="1" stopColor="#4CAF50" stopOpacity="0" />
          </linearGradient>
          <linearGradient
            id="paint8_linear_1_2"
            x1="200.25"
            y1="267"
            x2="200.25"
            y2="400"
            gradientUnits="userSpaceOnUse"
          >
            <stop stopColor="#4CAF50" />
            <stop offset="1" stopColor="#4CAF50" stopOpacity="0" />
          </linearGradient>
          <linearGradient
            id="paint9_linear_1_2"
            x1="333.5"
            y1="267"
            x2="333.5"
            y2="400"
            gradientUnits="userSpaceOnUse"
          >
            <stop stopColor="#4CAF50" />
            <stop offset="1" stopColor="#4CAF50" stopOpacity="0" />
          </linearGradient>
          <clipPath id="clip0_1_2">
            <rect width="400" height="400" fill="white" />
          </clipPath>
        </defs>
      </svg>
    </div>
  );
};

const SignUp = () => {
  const [showPassword, setShowPassword] = useState(false);
  const [showConfirmPassword, setShowConfirmPassword] = useState(false);
  const [isLoading, setIsLoading] = useState(false);
  const {
    register,
    handleSubmit,
    formState: { errors },
  } = useForm();

  const navigate = useNavigate();

  const onSubmit = async (data) => {
    setIsLoading(true);
    try {
      const response = await axios.post(
        `http://127.0.0.1:8000/api/users/register`,
        data
      );

      if (response.data.status === "success") {
        alert(
          "Đăng ký thành công! Vui lòng kiểm tra email của bạn để xác thực tài khoản."
        );
        navigate("/auth/verify-email");
      }
    } catch (error) {
      if (error.response?.data?.message) {
        alert(error.response.data.message);
      }
    } finally {
      setIsLoading(false);
    }
  };

  return (
    <div className="relative flex min-h-screen w-full items-center justify-center overflow-hidden bg-gray-50 px-4 py-12">
      <BackgroundDecoration />

      <motion.div
        initial={{ opacity: 0, y: 20 }}
        animate={{ opacity: 1, y: 0 }}
        transition={{ duration: 0.5 }}
        className="z-10 w-full max-w-md"
      >
        <div className="overflow-hidden rounded-2xl border border-gray-200 bg-white/80 shadow-xl backdrop-blur-sm">
          <div className="p-8">
            <motion.div
              initial={{ scale: 0.9, opacity: 0 }}
              animate={{ scale: 1, opacity: 1 }}
              transition={{ delay: 0.1, duration: 0.5 }}
              className="mb-6 flex justify-center"
            >
              <div className="flex h-16 w-16 items-center justify-center rounded-full bg-green-500/10">
                <FaArrowRight className="h-6 w-6 text-green-600" />
              </div>
            </motion.div>

            <h2 className="mb-2 text-center text-2xl font-bold text-gray-900">
              Đăng Ký
            </h2>
            <p className="mb-6 text-center text-gray-600">
              Đã có tài khoản?{" "}
              <a
                href="/sign-in"
                className="text-green-500 font-medium hover:underline"
              >
                Đăng Nhập
              </a>
            </p>

            <motion.div
              className="mb-6 grid grid-cols-2 gap-x-4"
              initial={{ opacity: 0, y: 10 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ delay: 0.2, duration: 0.5 }}
            >
              <motion.button
                whileHover={{
                  y: -2,
                  boxShadow: "0 4px 12px rgba(0, 0, 0, 0.1)",
                }}
                className="group flex items-center justify-center rounded-lg border border-gray-300 px-4 py-2 transition-all hover:border-green-500/50"
              >
                <FaGoogle className="mr-2 h-4 w-4 text-red-500" />
                <span className="text-sm">Google</span>
              </motion.button>
              <motion.button
                whileHover={{
                  y: -2,
                  boxShadow: "0 4px 12px rgba(0, 0, 0, 0.1)",
                }}
                className="group flex items-center justify-center rounded-lg border border-gray-300 px-4 py-2 transition-all hover:border-green-500/50"
              >
                <FaFacebook className="mr-2 h-4 w-4 text-blue-600" />
                <span className="text-sm">Facebook</span>
              </motion.button>
            </motion.div>

            <motion.div
              className="relative mb-6 flex items-center"
              initial={{ opacity: 0 }}
              animate={{ opacity: 1 }}
              transition={{ delay: 0.3, duration: 0.5 }}
            >
              <div className="flex-grow border-t border-gray-300"></div>
              <span className="mx-4 flex-shrink text-gray-500">HOẶC</span>
              <div className="flex-grow border-t border-gray-300"></div>
            </motion.div>

            <form onSubmit={handleSubmit(onSubmit)}>
              <motion.div
                className="mb-4"
                initial={{ opacity: 0, y: 10 }}
                animate={{ opacity: 1, y: 0 }}
                transition={{ delay: 0.4, duration: 0.5 }}
              >
                <input
                  type="text"
                  placeholder="Tên đầy đủ của bạn"
                  name="name"
                  className="w-full rounded-lg border border-gray-300 px-4 py-2.5 focus:border-green-500 focus:outline-none focus:ring-2 focus:ring-green-500/30"
                  {...register("name", { required: "Phải có tên đăng ký" })}
                />
                {errors?.name && (
                  <p className="mt-1 text-sm text-red-500">
                    {errors.name.message}
                  </p>
                )}
              </motion.div>

              <motion.div
                className="mb-4"
                initial={{ opacity: 0, y: 10 }}
                animate={{ opacity: 1, y: 0 }}
                transition={{ delay: 0.5, duration: 0.5 }}
              >
                <input
                  type="email"
                  placeholder="Địa chỉ email của bạn"
                  name="email"
                  className="w-full rounded-lg border border-gray-300 px-4 py-2.5 focus:border-green-500 focus:outline-none focus:ring-2 focus:ring-green-500/30"
                  {...register("email", { required: "Phải có email" })}
                />
                {errors?.email && (
                  <p className="mt-1 text-sm text-red-500">
                    {errors.email.message}
                  </p>
                )}
              </motion.div>

              <motion.div
                className="mb-4 relative"
                initial={{ opacity: 0, y: 10 }}
                animate={{ opacity: 1, y: 0 }}
                transition={{ delay: 0.6, duration: 0.5 }}
              >
                <input
                  type={showPassword ? "text" : "password"}
                  placeholder="Mật khẩu"
                  name="password"
                  className="w-full rounded-lg border border-gray-300 px-4 py-2.5 pr-10 focus:border-green-500 focus:outline-none focus:ring-2 focus:ring-green-500/30"
                  {...register("password", { required: "Phải có mật khẩu" })}
                />
                <button
                  type="button"
                  onClick={() => setShowPassword(!showPassword)}
                  className="absolute right-3 top-3 text-gray-500 hover:text-gray-700"
                >
                  {showPassword ? (
                    <FaEye className="h-5 w-5" />
                  ) : (
                    <FaRegEyeSlash className="h-5 w-5" />
                  )}
                </button>
                {errors?.password && (
                  <p className="mt-1 text-sm text-red-500">
                    {errors.password.message}
                  </p>
                )}
              </motion.div>

              <motion.div
                className="mb-6 relative"
                initial={{ opacity: 0, y: 10 }}
                animate={{ opacity: 1, y: 0 }}
                transition={{ delay: 0.7, duration: 0.5 }}
              >
                <input
                  type={showConfirmPassword ? "text" : "password"}
                  placeholder="Xác nhận mật khẩu"
                  name="password_confirmation"
                  className="w-full rounded-lg border border-gray-300 px-4 py-2.5 pr-10 focus:border-green-500 focus:outline-none focus:ring-2 focus:ring-green-500/30"
                  {...register("password_confirmation", {
                    required: "Phải có mật khẩu xác nhận",
                  })}
                />
                <button
                  type="button"
                  onClick={() => setShowConfirmPassword(!showConfirmPassword)}
                  className="absolute right-3 top-3 text-gray-500 hover:text-gray-700"
                >
                  {showConfirmPassword ? (
                    <FaEye className="h-5 w-5" />
                  ) : (
                    <FaRegEyeSlash className="h-5 w-5" />
                  )}
                </button>
                {errors?.password_confirmation && (
                  <p className="mt-1 text-sm text-red-500">
                    {errors.password_confirmation.message}
                  </p>
                )}
              </motion.div>

              <motion.div
                className="mb-6 flex items-center"
                initial={{ opacity: 0, y: 10 }}
                animate={{ opacity: 1, y: 0 }}
                transition={{ delay: 0.8, duration: 0.5 }}
              >
                <input
                  type="checkbox"
                  id="terms"
                  className="h-4 w-4 rounded border-gray-300 text-green-600 focus:ring-green-500"
                  required
                />
                <label htmlFor="terms" className="ml-2 text-sm text-gray-600">
                  Tôi đồng ý với{" "}
                  <a
                    href="https://policies.google.com/privacy?hl=vi"
                    className="text-green-500 hover:underline"
                  >
                    Chính Sách Bảo Mật
                  </a>{" "}
                  và{" "}
                  <a
                    href="https://policies.google.com/terms?hl=vi"
                    className="text-green-500 hover:underline"
                  >
                    Điều Khoản Sử Dụng
                  </a>
                </label>
              </motion.div>

              <motion.button
                type="submit"
                className="relative flex w-full items-center justify-center overflow-hidden rounded-lg bg-green-600 py-2.5 text-white transition-all hover:bg-green-700"
                whileHover={{ scale: 1.02 }}
                whileTap={{ scale: 0.98 }}
                initial={{ opacity: 0, y: 10 }}
                animate={{ opacity: 1, y: 0 }}
                transition={{ delay: 0.9, duration: 0.5 }}
                disabled={isLoading}
              >
                <AnimatePresence mode="wait">
                  {isLoading ? (
                    <motion.div
                      key="loading"
                      initial={{ opacity: 0 }}
                      animate={{ opacity: 1 }}
                      exit={{ opacity: 0 }}
                      className="absolute inset-0 flex items-center justify-center"
                    >
                      <svg
                        className="h-5 w-5 animate-spin text-white"
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                      >
                        <circle
                          className="opacity-25"
                          cx="12"
                          cy="12"
                          r="10"
                          stroke="currentColor"
                          strokeWidth="4"
                        ></circle>
                        <path
                          className="opacity-75"
                          fill="currentColor"
                          d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                        ></path>
                      </svg>
                    </motion.div>
                  ) : (
                    <motion.span
                      key="text"
                      initial={{ opacity: 0 }}
                      animate={{ opacity: 1 }}
                      exit={{ opacity: 0 }}
                      className="flex items-center justify-center"
                    >
                      Đăng Ký
                      <motion.span
                        initial={{ x: -5, opacity: 0 }}
                        animate={{ x: 0, opacity: 1 }}
                        transition={{ delay: 0.2 }}
                        className="ml-2"
                      >
                        <FaArrowRight size={16} />
                      </motion.span>
                    </motion.span>
                  )}
                </AnimatePresence>
              </motion.button>
            </form>
          </div>
        </div>
      </motion.div>
    </div>
  );
};

export default SignUp;
