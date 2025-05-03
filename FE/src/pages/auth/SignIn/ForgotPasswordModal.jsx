import React from "react";
import { useState } from "react";
import { motion, AnimatePresence } from "framer-motion";
import { FiMail, FiAlertCircle, FiCheckCircle } from "react-icons/fi";
import axios from "axios";

const ForgotPasswordModal = ({ isOpen, onClose }) => {
  const [email, setEmail] = useState("");
  const [message, setMessage] = useState("");
  const [isSuccess, setIsSuccess] = useState(false);
  const [isLoading, setIsLoading] = useState(false);

  const handleSubmit = async (e) => {
    e.preventDefault();
    setIsLoading(true);
    try {
      const response = await axios.post(
        "http://127.0.0.1:8000/api/users/forgot-password",
        { email }
      );
      console.log("Response:", response.data);

      setEmail(response.data.data.email);
      setMessage(
        "Chúng tôi đã gửi liên kết đặt lại mật khẩu vào email của bạn."
      );
      setIsSuccess(true);
    } catch (error) {
      setMessage(error.response?.data?.message || "Có lỗi xảy ra, vui lòng thử lại!");
      setIsSuccess(false);
    } finally {
      setIsLoading(false);
    }
  };

  const handleClose = () => {
    setEmail("");
    setMessage("");
    setIsSuccess(false);
    onClose();
  };

  const modalVariants = {
    hidden: {
      opacity: 0,
      scale: 0.8,
    },
    visible: {
      opacity: 1,
      scale: 1,
      transition: {
        duration: 0.3,
        ease: "easeOut",
      },
    },
    exit: {
      opacity: 0,
      scale: 0.8,
      transition: {
        duration: 0.2,
      },
    },
  };

  const overlayVariants = {
    hidden: { opacity: 0 },
    visible: {
      opacity: 1,
      transition: { duration: 0.3 },
    },
    exit: {
      opacity: 0,
      transition: { duration: 0.2 },
    },
  };

  return (
    <AnimatePresence>
      {isOpen && (
        <motion.div
          className="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50"
          variants={overlayVariants}
          initial="hidden"
          animate="visible"
          exit="exit"
          onClick={handleClose}
        >
          <motion.div
            className="bg-white p-8 rounded-xl shadow-2xl w-full max-w-md mx-4"
            variants={modalVariants}
            initial="hidden"
            animate="visible"
            exit="exit"
            onClick={(e) => e.stopPropagation()}
          >
            <h2 className="text-2xl font-bold text-gray-800 mb-2">
              Quên mật khẩu
            </h2>
            <p className="text-gray-600 mb-6">
              Nhập email của bạn và chúng tôi sẽ gửi liên kết đặt lại mật khẩu.
            </p>

            <form onSubmit={handleSubmit}>
              {!isSuccess ? (
                <div className="mb-5">
                  <div className="relative">
                    <span className="absolute left-3 top-3.5 text-gray-400">
                      <FiMail size={18} />
                    </span>
                    <input
                      type="email"
                      placeholder="Nhập email của bạn"
                      className="w-full px-10 py-3 border bg-gray-50 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all"
                      value={email}
                      onChange={(e) => setEmail(e.target.value)}
                      required
                    />
                  </div>

                  {message && !isSuccess && (
                    <div className="mt-3 flex items-start text-red-500 text-sm">
                      <FiAlertCircle className="mr-1.5 mt-0.5 flex-shrink-0" />
                      <span>{message}</span>
                    </div>
                  )}
                </div>
              ) : (
                <div className="mb-5 p-4 bg-green-50 rounded-lg">
                  <div className="flex items-center text-green-600">
                    <FiCheckCircle className="mr-2 flex-shrink-0" size={20} />
                    <span>{message}</span>
                  </div>
                </div>
              )}

              <div className="flex justify-end space-x-3 mt-6">
                <motion.button
                  type="button"
                  className="px-5 py-2.5 border border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-50 transition-colors"
                  onClick={handleClose}
                  whileHover={{ scale: 1.02 }}
                  whileTap={{ scale: 0.98 }}
                >
                  Đóng
                </motion.button>

                {!isSuccess && (
                  <motion.button
                    type="submit"
                    className="px-5 py-2.5 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition-colors flex items-center justify-center min-w-[90px]"
                    whileHover={{ scale: 1.02 }}
                    whileTap={{ scale: 0.98 }}
                    disabled={isLoading}
                  >
                    {isLoading ? (
                      <svg
                        className="animate-spin h-5 w-5 text-white"
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
                    ) : (
                      "Gửi"
                    )}
                  </motion.button>
                )}
              </div>
            </form>
          </motion.div>
        </motion.div>
      )}
    </AnimatePresence>
  );
};

export default ForgotPasswordModal;
