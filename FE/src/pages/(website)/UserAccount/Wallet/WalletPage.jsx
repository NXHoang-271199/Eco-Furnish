import React from "react";
import { Link } from "react-router-dom";
import { motion } from "framer-motion";
import {
  FaWallet,
  FaMoneyBillWave,
  FaArrowDown,
  FaArrowUp,
} from "react-icons/fa";

const WalletPage = () => {
  const balance = 1500000;

  const transactions = [
    {
      description: "Nạp tiền từ MoMo",
      date: "10/04/2025",
      amount: 500000,
    },
    {
      description: "Thanh toán đơn hàng #12345",
      date: "09/04/2025",
      amount: -200000,
    },
    {
      description: "Hoàn tiền đơn hàng #12220",
      date: "08/04/2025",
      amount: 150000,
    },
  ];

  const formatMoney = (amount) =>
    new Intl.NumberFormat("vi-VN", {
      style: "currency",
      currency: "VND",
    }).format(amount);

  return (
    <motion.div
      initial={{ opacity: 0, y: 20 }}
      animate={{ opacity: 1, y: 0 }}
      transition={{ duration: 1 }}
      className="min-h-screen p-4 bg-gray-50"
    >
      <div className="max-w-6xl mx-auto">
        {/* Balance Card */}
        <motion.div
          initial={{ scale: 0.95, opacity: 0 }}
          animate={{ scale: 1, opacity: 1 }}
          transition={{ delay: 0.8 }}
          className="bg-white rounded-2xl shadow p-6 flex flex-col md:flex-row justify-between items-center mb-6"
        >
          <div className="flex items-center gap-3 text-center md:text-left">
            <FaWallet className="text-4xl text-indigo-600" />
            <div>
              <p className="text-gray-500 text-sm">Số dư hiện tại</p>
              <h2
                className={`text-3xl font-bold ${
                  balance > 0 ? "text-green-600" : "text-gray-400"
                }`}
              >
                {formatMoney(balance)}
              </h2>
            </div>
          </div>
          <div className="mt-4 md:mt-0 flex space-x-4">
            <Link to="wallet/deposit-success">
              <button className="bg-indigo-600 text-white px-4 py-2 rounded-xl hover:bg-indigo-700 transition flex items-center gap-2">
                <FaMoneyBillWave />
                Nạp tiền
              </button>
            </Link>
            <button className="bg-gray-200 text-gray-800 px-4 py-2 rounded-xl hover:bg-gray-300 transition flex items-center gap-2">
              <FaArrowDown />
              Rút tiền
            </button>
          </div>
        </motion.div>

        {/* Transaction History */}
        <motion.div
          initial={{ opacity: 0, y: 10 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ delay: 0.4 }}
          className="bg-white rounded-2xl shadow p-4"
        >
          <h3 className="text-lg font-semibold text-gray-700 mb-4">
            Lịch sử giao dịch
          </h3>

          {transactions.length === 0 ? (
            <div className="text-center text-gray-500 text-sm py-8">
              Bạn chưa có giao dịch nào.
            </div>
          ) : (
            <ul className="space-y-4">
              {transactions.map((tx, index) => (
                <li
                  key={index}
                  className="flex justify-between items-center border-b pb-3"
                >
                  <div className="flex items-center gap-3">
                    {tx.amount >= 0 ? (
                      <FaArrowDown className="text-green-500" />
                    ) : (
                      <FaArrowUp className="text-red-500" />
                    )}
                    <div>
                      <p className="text-sm font-medium text-gray-800">
                        {tx.description}
                      </p>
                      <p className="text-xs text-gray-500">{tx.date}</p>
                    </div>
                  </div>
                  <span
                    className={`font-semibold ${
                      tx.amount >= 0 ? "text-green-600" : "text-red-500"
                    }`}
                  >
                    {tx.amount >= 0 ? "+" : ""}
                    {formatMoney(tx.amount)}
                  </span>
                </li>
              ))}
            </ul>
          )}
        </motion.div>
      </div>
    </motion.div>
  );
};

export default WalletPage;
