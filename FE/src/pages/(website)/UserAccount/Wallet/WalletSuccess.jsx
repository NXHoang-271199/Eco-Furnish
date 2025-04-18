import React, { useEffect } from "react";
import { useNavigate } from "react-router-dom";
import { motion } from "framer-motion";
import { FaCheckCircle, FaSpinner } from "react-icons/fa";

const WalletSuccess = () => {
    const navigate = useNavigate();

    useEffect(() => {
        // Đặt timeout để chuyển hướng tự động sau 2 giây
        const timer = setTimeout(() => {
            navigate("/account/wallet", {
                state: { fromDepositSuccess: true },
                replace: true
            });
        }, 2000);

        return () => clearTimeout(timer);
    }, [navigate]);

    return (
        <motion.div
            initial={{ opacity: 0 }}
            animate={{ opacity: 1 }}
            transition={{ duration: 0.5 }}
            className="min-h-screen flex flex-col items-center justify-center p-4 bg-gray-50"
        >
            <div className="text-center max-w-lg">
                <motion.div
                    initial={{ scale: 0.8, opacity: 0 }}
                    animate={{ scale: 1, opacity: 1 }}
                    transition={{ delay: 0.2, duration: 0.5 }}
                    className="mb-6"
                >
                    <FaCheckCircle className="mx-auto text-green-500 text-7xl" />
                </motion.div>

                <h1 className="text-3xl font-bold text-gray-800 mb-2">Nạp tiền thành công!</h1>
                <p className="text-gray-600 mb-8">
                    Tiền đã được cộng vào ví của bạn thành công. Bạn sẽ được chuyển hướng tự động.
                </p>

                <div className="flex items-center justify-center">
                    <FaSpinner className="animate-spin text-indigo-600 mr-2" />
                    <span className="text-indigo-600">Đang chuyển hướng...</span>
                </div>
            </div>
        </motion.div>
    );
};

export default WalletSuccess; 