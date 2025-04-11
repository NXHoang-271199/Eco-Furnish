import React from "react";
import { Link } from "react-router-dom";
import { motion } from "framer-motion";
import { FaCheckCircle } from "react-icons/fa";

const WalletSuccess = () => {
  return (
    <div className="min-h-screen flex flex-col items-center justify-center bg-green-50 px-4">
      <motion.div
        initial={{ scale: 0.9, opacity: 0 }}
        animate={{ scale: 1, opacity: 1 }}
        transition={{ duration: 0.4 }}
        className="bg-white rounded-2xl shadow-lg p-8 max-w-md text-center"
      >
        {/* Icon */}
        <motion.div
          initial={{ scale: 0 }}
          animate={{ scale: 1 }}
          transition={{ delay: 0.2, type: "spring", stiffness: 300 }}
          className="flex justify-center mb-4"
        >
          <FaCheckCircle className="text-green-500 text-6xl" />
        </motion.div>

        {/* Text */}
        <h1 className="text-2xl font-bold text-green-600 mb-3">
          Nạp tiền thành công!
        </h1>
        <p className="text-gray-600 mb-6">
          Số dư ví của bạn đã được cập nhật. Cảm ơn bạn đã sử dụng dịch vụ của
          chúng tôi.
        </p>

        {/* Button */}
        <motion.div
          initial={{ opacity: 0, y: 10 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ delay: 0.5 }}
        >
          <Link
            to="/account"
            className="bg-green-600 text-white px-6 py-2 rounded-xl hover:bg-green-700 transition"
          >
            Quay lại tài khoản
          </Link>
        </motion.div>
      </motion.div>
    </div>
  );
};

export default WalletSuccess;
