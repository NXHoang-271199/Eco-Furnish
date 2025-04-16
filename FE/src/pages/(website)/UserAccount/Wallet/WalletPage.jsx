import React, { useState, useEffect } from "react";
import { Link, useNavigate } from "react-router-dom";
import { motion } from "framer-motion";
import {
  FaWallet,
  FaMoneyBillWave,
  FaArrowDown,
  FaArrowUp,
  FaSpinner,
  FaSync,
  FaTimes,
} from "react-icons/fa";
import axios from "axios";
import axiosInstance from "../../../../utils/axiosConfig";

const WalletPage = () => {
  const [balance, setBalance] = useState(0);
  const [transactions, setTransactions] = useState([]);
  const [loading, setLoading] = useState(true);
  const [actionLoading, setActionLoading] = useState(false);
  const navigate = useNavigate();

  // Fetch dữ liệu ví và lịch sử giao dịch
  const fetchWalletData = async () => {
    try {
      setLoading(true);

      // Lấy số dư ví
      const balanceResponse = await axiosInstance.get("/wallet/balance", {
        headers: {
          Authorization: `Bearer ${localStorage.getItem("authToken")}`,
        },
      });

      // Lấy lịch sử giao dịch
      const transactionsResponse = await axiosInstance.get(
        "/wallet/transactions",
        {
          headers: {
            Authorization: `Bearer ${localStorage.getItem("authToken")}`,
          },
        }
      );

      setBalance(parseFloat(balanceResponse.data.balance));
      setTransactions(transactionsResponse.data.transactions);

      // Kiểm tra và tự động hủy các giao dịch quá hạn (30 phút)
      const pendingTransactions = transactionsResponse.data.transactions.filter(
        (tx) => tx.type === "nap_tien" && tx.status === "cho_thanh_toan"
      );

      pendingTransactions.forEach((tx) => {
        const createdDate = new Date(tx.created_at.replace(/-/g, "/"));
        const now = new Date();
        const diffMinutes = Math.floor((now - createdDate) / (1000 * 60));

        // Nếu giao dịch đã chờ quá 30 phút, tự động hủy
        if (diffMinutes >= 30) {
          cancelTransaction(tx.id);
        }
      });
    } catch (error) {
      console.error("Lỗi khi tải dữ liệu ví:", error);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchWalletData();
  }, []);

  // Hủy giao dịch
  const cancelTransaction = async (id) => {
    try {
      setActionLoading(true);
      await axiosInstance.delete(`/wallet/transactions/${id}/cancel`, {
        headers: {
          Authorization: `Bearer ${localStorage.getItem("authToken")}`,
        },
      });

      // Cập nhật lại danh sách giao dịch
      fetchWalletData();
    } catch (error) {
      console.error("Lỗi khi hủy giao dịch:", error);
    } finally {
      setActionLoading(false);
    }
  };

  // Nạp tiền lại
  const retryPayment = async (id) => {
    try {
      setActionLoading(true);
      const response = await axiosInstance.post(
        `/payment-method/retry-deposit/${id}`,
        {},
        {
          headers: {
            Authorization: `Bearer ${localStorage.getItem("authToken")}`,
          },
        }
      );

      // Xử lý phản hồi từ server
      if (response.data.payUrl) {
        // Nếu có payUrl, chuyển hướng đến trang thanh toán
        window.location.href = response.data.payUrl;
      } else if (response.data.data) {
        // Đối với VNPAY
        window.location.href = response.data.data;
      }
    } catch (error) {
      console.error("Lỗi khi nạp tiền lại:", error);
    } finally {
      setActionLoading(false);
    }
  };

  // Định dạng tiền VNĐ
  const formatMoney = (amount) =>
    new Intl.NumberFormat("vi-VN", {
      style: "currency",
      currency: "VND",
    }).format(amount);

  // Xử lý click nút nạp tiền
  const handleDepositClick = () => {
    navigate("/account/wallet/deposit");
  };

  // Xử lý click nút rút tiền
  const handleWithdrawClick = () => {
    navigate("/account/wallet/withdraw");
  };

  // Lấy icon phù hợp với loại giao dịch
  const getTransactionIcon = (type, amount) => {
    if (type === "nap_tien" || type === "hoan_tien") {
      return <FaArrowDown className="text-green-500" />;
    } else if (type === "thanh_toan_don_hang" || type === "rut_tien") {
      return <FaArrowUp className="text-red-500" />;
    }
    return amount >= 0 ? (
      <FaArrowDown className="text-green-500" />
    ) : (
      <FaArrowUp className="text-red-500" />
    );
  };

  // Kiểm tra xem giao dịch có trong vòng 30 phút không
  const isWithin30Minutes = (createdAt) => {
    const createdDate = new Date(createdAt.replace(/-/g, "/"));
    const now = new Date();
    const diffMinutes = Math.floor((now - createdDate) / (1000 * 60));
    return diffMinutes <= 30;
  };

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
              {loading ? (
                <div className="flex items-center">
                  <FaSpinner className="animate-spin mr-2 text-indigo-600" />
                  <span>Đang tải...</span>
                </div>
              ) : (
                <h2
                  className={`text-3xl font-bold ${
                    balance > 0 ? "text-green-600" : "text-gray-400"
                  }`}
                >
                  {formatMoney(balance)}
                </h2>
              )}
            </div>
          </div>
          <div className="mt-4 md:mt-0 flex space-x-4">
            <button
              onClick={handleDepositClick}
              className="bg-indigo-600 text-white px-4 py-2 rounded-xl hover:bg-indigo-700 transition flex items-center gap-2"
            >
              <FaMoneyBillWave />
              Nạp tiền
            </button>
            <button
              onClick={handleWithdrawClick}
              className="bg-gray-200 text-gray-800 px-4 py-2 rounded-xl hover:bg-gray-300 transition flex items-center gap-2"
            >
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

          {loading ? (
            <div className="flex justify-center items-center py-8">
              <FaSpinner className="animate-spin mr-2 text-indigo-600" />
              <span>Đang tải lịch sử giao dịch...</span>
            </div>
          ) : transactions.length === 0 ? (
            <div className="text-center text-gray-500 text-sm py-8">
              Bạn chưa có giao dịch nào.
            </div>
          ) : (
            <ul className="space-y-4">
              {transactions.map((tx) => (
                <li
                  key={tx.id}
                  className="flex justify-between items-center border-b pb-3"
                >
                  <div className="flex items-center gap-3">
                    {getTransactionIcon(tx.type, tx.amount)}
                    <div>
                      <p className="text-sm font-medium text-gray-800">
                        {tx.description}
                      </p>
                      <div className="flex flex-wrap gap-2 text-xs text-gray-500">
                        <p>{tx.created_at}</p>
                        <p
                          className={`${
                            tx.status === "thanh_cong"
                              ? "text-green-600"
                              : tx.status === "cho_thanh_toan"
                              ? "text-orange-500"
                              : tx.status === "da_huy"
                              ? "text-red-500"
                              : ""
                          }`}
                        >
                          {tx.status === "thanh_cong"
                            ? "Thành công"
                            : tx.status === "cho_thanh_toan"
                            ? "Chờ thanh toán"
                            : tx.status === "da_huy"
                            ? "Đã hủy"
                            : tx.status}
                        </p>
                      </div>
                    </div>
                  </div>
                  <div className="flex items-center gap-2">
                    {/* Hiển thị nút hành động cho giao dịch nạp tiền đang chờ thanh toán */}
                    {tx.type === "nap_tien" &&
                      tx.status === "cho_thanh_toan" && (
                        <div className="flex space-x-2">
                          <button
                            onClick={() => retryPayment(tx.id)}
                            disabled={actionLoading}
                            className="text-indigo-600 hover:text-indigo-800 p-1 rounded-full hover:bg-indigo-100"
                            title="Nạp tiền lại"
                          >
                            {actionLoading ? (
                              <FaSpinner className="animate-spin" />
                            ) : (
                              <FaSync />
                            )}
                          </button>
                          {isWithin30Minutes(tx.created_at) && (
                            <button
                              onClick={() => cancelTransaction(tx.id)}
                              disabled={actionLoading}
                              className="text-red-600 hover:text-red-800 p-1 rounded-full hover:bg-red-100"
                              title="Hủy giao dịch"
                            >
                              {actionLoading ? (
                                <FaSpinner className="animate-spin" />
                              ) : (
                                <FaTimes />
                              )}
                            </button>
                          )}
                        </div>
                      )}
                    <span
                      className={`font-semibold ${
                        tx.type === "nap_tien" || tx.type === "hoan_tien"
                          ? "text-green-600"
                          : "text-red-500"
                      }`}
                    >
                      {tx.type === "nap_tien" || tx.type === "hoan_tien"
                        ? "+"
                        : "-"}
                      {formatMoney(tx.amount)}
                    </span>
                  </div>
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
