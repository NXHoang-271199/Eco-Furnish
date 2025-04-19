import React, { useEffect, useState } from "react";
import { motion, AnimatePresence } from "framer-motion";
import { useLoading } from "../context/LoadingContext";

const Popup = () => {
  const [isOpen, setIsOpen] = useState(false);
  const { isLoading } = useLoading(); // Tham chiếu đến loading state

  useEffect(() => {
    // Kiểm tra xem popup đã được hiển thị và đóng trước đó chưa
    const hasPopupBeenShown = localStorage.getItem("popupShown");

    if (!hasPopupBeenShown) {
      // Đặt timeout dài hơn để đảm bảo hiển thị sau khi loading screen đã biến mất
      const timeout = setTimeout(() => {
        // Chỉ mở popup khi không còn loading
        if (!isLoading) {
          setIsOpen(true);
        } else {
          // Nếu vẫn đang loading, đặt một listener để kiểm tra khi nào loading kết thúc
          const checkLoadingInterval = setInterval(() => {
            if (!isLoading) {
              setIsOpen(true);
              clearInterval(checkLoadingInterval);
            }
          }, 50); // Kiểm tra mỗi 50ms

          // Đảm bảo dừng interval sau một thời gian nhất định
          setTimeout(() => clearInterval(checkLoadingInterval), 10000);

          return () => clearInterval(checkLoadingInterval);
        }
      }, 2500); // Tăng thời gian lên 2.5 giây để đảm bảo loading đã kết thúc

      return () => clearTimeout(timeout);
    }
  }, [isLoading]); // Thêm isLoading vào dependencies để useEffect chạy lại khi loading state thay đổi

  // Hàm đóng popup và lưu trạng thái vào localStorage
  const closePopup = () => {
    setIsOpen(false);
    // Lưu thông tin rằng popup đã được hiển thị
    localStorage.setItem("popupShown", "true");
  };

  const containerVariants = {
    hidden: { opacity: 0 },
    visible: {
      opacity: 1,
      transition: {
        when: "beforeChildren",
        staggerChildren: 0.1,
        delayChildren: 0.2
      }
    },
    exit: {
      opacity: 0,
      transition: {
        when: "afterChildren",
        staggerChildren: 0.05,
        staggerDirection: -1
      }
    }
  };

  const childVariants = {
    hidden: { y: 20, opacity: 0 },
    visible: { y: 0, opacity: 1, transition: { type: "spring", damping: 12 } },
    exit: { y: -10, opacity: 0 }
  };

  const buttonVariants = {
    initial: { scale: 1, boxShadow: "0px 4px 10px rgba(0, 0, 0, 0.1)" },
    hover: {
      scale: 1.05,
      boxShadow: "0px 7px 15px rgba(0, 0, 0, 0.15)",
      transition: { duration: 0.2 }
    },
    tap: {
      scale: 0.95,
      boxShadow: "0px 2px 5px rgba(0, 0, 0, 0.1)",
      transition: { duration: 0.1 }
    }
  };

  const iconVariants = {
    hidden: { rotate: -10, scale: 0.8, opacity: 0 },
    visible: {
      rotate: 0,
      scale: 1,
      opacity: 1,
      transition: {
        type: "spring",
        stiffness: 200,
        damping: 10
      }
    }
  };

  const decorationVariants = {
    hidden: { pathLength: 0, opacity: 0 },
    visible: {
      pathLength: 1,
      opacity: 1,
      transition: {
        duration: 1.5,
        ease: "easeInOut",
        delay: 0.5
      }
    }
  };

  const shimmerVariants = {
    hidden: { x: "-100%", opacity: 0.1 },
    visible: {
      x: "100%",
      opacity: 0.5,
      transition: {
        repeat: Infinity,
        repeatType: "mirror",
        duration: 2,
        ease: "easeInOut",
        delay: 1
      }
    }
  };

  // Chỉ render khi không còn loading
  return (
    !isLoading && (
      <AnimatePresence>
        {isOpen && (
          <motion.div
            initial={{ opacity: 0 }}
            animate={{ opacity: 1 }}
            exit={{ opacity: 0 }}
            className="fixed inset-0 z-[9999] flex items-center justify-center backdrop-blur-md bg-black/40"
          >
            <motion.div
              className="bg-white p-8 w-[90%] max-w-md rounded-[32px] border border-gray-100 shadow-[0_35px_80px_-25px_rgba(0,0,0,0.25)] relative overflow-hidden"
              initial={{ scale: 0.9, y: 30, opacity: 0 }}
              animate={{ scale: 1, y: 0, opacity: 1 }}
              exit={{ scale: 0.9, y: 20, opacity: 0 }}
              transition={{
                type: "spring",
                damping: 30,
                stiffness: 350,
                duration: 0.5
              }}
            >
              {/* Decorative elements */}
              <motion.div
                className="absolute -top-16 -right-16 w-48 h-48 bg-gradient-to-br from-amber-100/60 to-amber-300/60 rounded-full blur-md"
                initial={{ scale: 0, opacity: 0 }}
                animate={{ scale: 1, opacity: 0.7 }}
                transition={{ delay: 0.2, duration: 0.8 }}
              />
              <motion.div
                className="absolute -bottom-20 -left-20 w-56 h-56 bg-gradient-to-tr from-brown-100/60 via-amber-200/60 to-amber-300/60 rounded-full blur-md"
                initial={{ scale: 0, opacity: 0 }}
                animate={{ scale: 1, opacity: 0.7 }}
                transition={{ delay: 0.3, duration: 0.8 }}
              />

              {/* Wood-grain SVG pattern */}
              <motion.div className="absolute inset-0 opacity-10 overflow-hidden pointer-events-none">
                <svg width="100%" height="100%" xmlns="http://www.w3.org/2000/svg">
                  <pattern id="wood-pattern" width="100" height="100" patternUnits="userSpaceOnUse">
                    <motion.path
                      d="M10,10 Q30,30 50,10 T90,10 M10,30 Q30,50 50,30 T90,30 M10,50 Q30,70 50,50 T90,50 M10,70 Q30,90 50,70 T90,70"
                      fill="none"
                      stroke="#8B4513"
                      strokeWidth="0.5"
                      variants={decorationVariants}
                      initial="hidden"
                      animate="visible"
                    />
                  </pattern>
                  <rect width="100%" height="100%" fill="url(#wood-pattern)" />
                  <motion.rect
                    width="100%"
                    height="100%"
                    fill="url(#wood-pattern)"
                    variants={shimmerVariants}
                    initial="hidden"
                    animate="visible"
                    style={{
                      filter: "brightness(3)",
                      mixBlendMode: "overlay"
                    }}
                  />
                </svg>
              </motion.div>

              <motion.div
                variants={containerVariants}
                initial="hidden"
                animate="visible"
                exit="exit"
                className="relative z-10"
              >
                <div className="flex items-center mb-8 pb-2 border-b border-amber-200/50">
                  <motion.div
                    variants={iconVariants}
                    className="w-14 h-14 bg-gradient-to-br from-amber-400 to-amber-600 text-white rounded-2xl flex items-center justify-center mr-4 shadow-lg"
                    style={{
                      boxShadow: "0 10px 15px -3px rgba(180, 83, 9, 0.2), 0 4px 6px -4px rgba(180, 83, 9, 0.2)",
                    }}
                  >
                    <span className="text-3xl">🪑</span>
                  </motion.div>
                  <div>
                    <motion.h2
                      variants={childVariants}
                      className="text-2xl font-bold text-amber-900 tracking-tight"
                    >
                      Chào mừng
                    </motion.h2>
                    <motion.p
                      variants={childVariants}
                      className="text-xs text-amber-700/70"
                    >
                      Cập nhật tháng 4 năm 2025
                    </motion.p>
                  </div>
                </div>

                <motion.div
                  variants={childVariants}
                  className="text-sm text-gray-700 space-y-5"
                >
                  <div className="p-5 bg-gradient-to-r from-amber-50 to-amber-100/60 rounded-2xl border border-amber-200/50 shadow-sm relative overflow-hidden">
                    <div className="absolute inset-0 opacity-10">
                      <svg width="100%" height="100%">
                        <pattern id="furniture-pattern" width="20" height="20" patternUnits="userSpaceOnUse">
                          <path d="M0,10 L20,10 M10,0 L10,20" stroke="#8B4513" strokeWidth="0.5" strokeDasharray="1,3" />
                        </pattern>
                        <rect width="100%" height="100%" fill="url(#furniture-pattern)" />
                      </svg>
                    </div>
                    <h3 className="font-medium text-amber-900 mb-3 text-base relative">
                      <span className="inline-block w-2 h-2 bg-amber-500 rounded-full mr-2"></span>
                      Tổng quan
                    </h3>
                    <p className="text-amber-800">
                      Chúng tôi rất vui được có bạn ở đây. Mục tiêu của chúng tôi là
                      cung cấp nội thất bền vững, sang trọng và thân thiện với môi trường cho ngôi nhà của bạn.
                    </p>
                  </div>

                  <div className="p-5 bg-gradient-to-r from-amber-50 to-amber-100/60 rounded-2xl border border-amber-200/50 shadow-sm relative overflow-hidden">
                    <div className="absolute inset-0 opacity-10">
                      <svg width="100%" height="100%">
                        <pattern id="furniture-pattern-2" width="20" height="20" patternUnits="userSpaceOnUse">
                          <path d="M5,5 L15,15 M15,5 L5,15" stroke="#8B4513" strokeWidth="0.5" strokeDasharray="1,3" />
                        </pattern>
                        <rect width="100%" height="100%" fill="url(#furniture-pattern-2)" />
                      </svg>
                    </div>
                    <h3 className="font-medium text-amber-900 mb-3 text-base relative">
                      <span className="inline-block w-2 h-2 bg-amber-500 rounded-full mr-2"></span>
                      Tại sao bạn thấy điều này
                    </h3>
                    <p className="text-amber-800">
                      Popup này giới thiệu bộ sưu tập nội thất mới của chúng tôi và các dịch vụ độc quyền
                      dành cho khách hàng thân thiết.
                    </p>
                  </div>
                </motion.div>

                <motion.div
                  variants={childVariants}
                  className="mt-8 flex justify-end"
                >
                  <motion.button
                    onClick={closePopup}
                    className="bg-gradient-to-r from-amber-500 to-amber-600 text-white text-sm px-7 py-3 rounded-xl font-medium"
                    variants={buttonVariants}
                    initial="initial"
                    whileHover="hover"
                    whileTap="tap"
                    style={{
                      boxShadow: "0 10px 15px -3px rgba(180, 83, 9, 0.2), 0 4px 6px -4px rgba(180, 83, 9, 0.2)",
                    }}
                  >
                    <span className="relative z-10 flex items-center">
                      <span>Khám phá ngay</span>
                      <svg xmlns="http://www.w3.org/2000/svg" className="h-4 w-4 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M14 5l7 7m0 0l-7 7m7-7H3" />
                      </svg>
                    </span>
                  </motion.button>
                </motion.div>
              </motion.div>
            </motion.div>
          </motion.div>
        )}
      </AnimatePresence>
    )
  );
};

export default Popup;
