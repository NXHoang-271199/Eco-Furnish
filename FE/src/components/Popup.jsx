import React, { useEffect, useState } from "react";
import { motion, AnimatePresence } from "framer-motion";

const Popup = () => {
  const [isOpen, setIsOpen] = useState(false);

  useEffect(() => {
    const timeout = setTimeout(() => {
      setIsOpen(true);
    }, 3000); // hiển thị sau 3 giây
    return () => clearTimeout(timeout);
  }, []);

  return (
    <AnimatePresence>
      {isOpen && (
        <motion.div
          initial={{ opacity: 0 }}
          animate={{ opacity: 1 }}
          exit={{ opacity: 0 }}
          className="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40"
        >
          <motion.div
            className="bg-white p-6 w-[90%] max-w-md rounded-2xl border border-gray-200 shadow-2xl"
            initial={{ scale: 0.95, opacity: 0 }}
            animate={{ scale: 1, opacity: 1 }}
            exit={{ scale: 0.95, opacity: 0 }}
            transition={{ duration: 0.25 }}
          >
            <div className="flex items-center mb-4">
              <div className="w-10 h-10 bg-pink-100 text-pink-600 rounded-full flex items-center justify-center mr-3 text-xl font-bold">
                🌐
              </div>
              <div>
                <h2 className="text-lg font-semibold text-gray-800">
                  Chào mừng
                </h2>
                <p className="text-xs text-gray-500">
                  Cập nhật tháng 4 năm 2025
                </p>
              </div>
            </div>

            <div className="text-sm text-gray-700 space-y-3">
              <div>
                <h3 className="font-medium text-gray-800 mb-1">Tổng quan</h3>
                <p>
                  Chúng tôi rất vui được có bạn ở đây. Mục tiêu của chúng tôi là
                  cung cấp một trải nghiệm mua sắm thân thuộc và bền vững.
                </p>
              </div>

              <div>
                <h3 className="font-medium text-gray-800 mb-1">
                  Tại sao bạn thấy điều này
                </h3>
                <p>
                  Popup này giúp chúng tôi giới thiệu trang web và chia sẻ thông
                  tin quan trọng về giá trị và dịch vụ của chúng tôi.
                </p>
              </div>
            </div>

            <div className="mt-6 text-right">
              <button
                onClick={() => setIsOpen(false)}
                className="bg-pink-500 hover:bg-pink-600 text-white text-sm px-5 py-2 rounded-lg transition"
              >
                Đã hiểu
              </button>
            </div>
          </motion.div>
        </motion.div>
      )}
    </AnimatePresence>
  );
};

export default Popup;
