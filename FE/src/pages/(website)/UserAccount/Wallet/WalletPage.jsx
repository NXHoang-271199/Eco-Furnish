import React, { useState, useEffect, useRef } from "react";
import { Link, useNavigate, useLocation } from "react-router-dom";
import { motion, AnimatePresence } from "framer-motion";
import {
  FaWallet,
  FaMoneyBillWave,
  FaArrowDown,
  FaArrowUp,
  FaSpinner,
  FaSync,
  FaTimes,
  FaReceipt,
  FaInfoCircle,
  FaRegCopy,
  FaExchangeAlt,
} from "react-icons/fa";
import axios from "axios";
import axiosInstance from "../../../../utils/axiosConfig";
import { showWalletDepositToast } from "../../../../components/ui/toast";
import { useMemo } from "react";

const WalletPage = () => {
  const [balance, setBalance] = useState(0);
  const [transactions, setTransactions] = useState([]);
  const [loading, setLoading] = useState(true);
  const [actionLoading, setActionLoading] = useState(false);
  const [selectedTransaction, setSelectedTransaction] = useState(null);
  const [showModal, setShowModal] = useState(false);
  const [copySuccess, setCopySuccess] = useState("");
  const [balanceBeforeTransaction, setBalanceBeforeTransaction] = useState(0);
  const [balanceAfterTransaction, setBalanceAfterTransaction] = useState(0);
  const [previousBalance, setPreviousBalance] = useState(0);
  const navigate = useNavigate();
  const location = useLocation();
  const [filterType, setFilterType] = useState("all"); // "all", "nap_tien", "rut_tien"
  const [isFetching, setIsFetching] = useState(false);
  const initialFetchDone = useRef(false);

  // Fetch dữ liệu ví và lịch sử giao dịch
  const fetchWalletData = async (isBackground = false) => {
    if (isFetching) return;

    try {
      setIsFetching(true);
      if (!isBackground) {
        setLoading(true);
      }

      // Lấy số dư ví
      const balanceResponse = await axiosInstance.get("/wallet/balance", {
        headers: {
          Authorization: `Bearer ${localStorage.getItem("authToken")}`,
        },
      });

      // Lưu giá trị số dư cũ nếu đang từ trang nạp tiền thành công quay về
      if (location.state && location.state.fromDepositSuccess) {
        setPreviousBalance(
          parseFloat(localStorage.getItem("previousBalance") || 0)
        );
      } else {
        setPreviousBalance(parseFloat(balanceResponse.data.balance));
      }

      // Lấy lịch sử giao dịch
      const transactionsResponse = await axiosInstance.get(
        "/wallet/transactions",
        {
          headers: {
            Authorization: `Bearer ${localStorage.getItem("authToken")}`,
          },
        }
      );

      const currentBalance = parseFloat(balanceResponse.data.balance);
      setBalance(currentBalance);
      setTransactions(transactionsResponse.data.transactions);

      // Kiểm tra và hiển thị thông báo toast nếu từ trang nạp tiền thành công quay về
      if (location.state && location.state.fromDepositSuccess) {
        // Tìm giao dịch nạp tiền thành công gần nhất
        const recentSuccessfulDeposit =
          transactionsResponse.data.transactions.find(
            (tx) => tx.type === "nap_tien" && tx.status === "thanh_cong"
          );

        if (recentSuccessfulDeposit) {
          // Kiểm tra xem thông báo này đã được xử lý thông qua socket hay chưa
          const processedIds = JSON.parse(
            sessionStorage.getItem("processedNotificationIds") || "[]"
          );
          const alreadyProcessed = processedIds.includes(
            recentSuccessfulDeposit.id.toString()
          );

          if (!alreadyProcessed) {
            console.log(
              "Hiển thị thông báo nạp tiền thành công vì chưa được xử lý qua socket"
            );
            const depositAmount = parseFloat(recentSuccessfulDeposit.amount);
            const prevBalance = currentBalance - depositAmount;

            // Thêm ID vào danh sách đã xử lý để tránh hiển thị lại
            processedIds.push(recentSuccessfulDeposit.id.toString());
            sessionStorage.setItem(
              "processedNotificationIds",
              JSON.stringify(processedIds)
            );

            showWalletDepositToast({
              amount: depositAmount,
              balance_after: currentBalance,
              message: `Tài khoản của bạn vừa được cộng ${new Intl.NumberFormat(
                "vi-VN",
                { style: "currency", currency: "VND" }
              ).format(depositAmount)}`,
              id: recentSuccessfulDeposit.id,
            });
          } else {
            console.log(
              "Không hiển thị thông báo nạp tiền vì đã được xử lý qua socket:",
              recentSuccessfulDeposit.id
            );
          }

          // Xóa trạng thái sau khi đã xử lý
          localStorage.removeItem("previousBalance");
          navigate(location.pathname, { replace: true, state: {} });
        }
      }
    } catch (error) {
      console.error("Lỗi khi tải dữ liệu ví:", error);
      if (!isBackground) {
        setError("Lỗi khi tải dữ liệu ví");
        toast.error("Không thể tải dữ liệu ví");
      }
    } finally {
      setLoading(false);
      setIsFetching(false);
    }
  };

  useEffect(() => {
    if (!initialFetchDone.current) {
      fetchWalletData();
      initialFetchDone.current = true;
    }

    const handleFocus = () => {
      console.log("Tab focused, refetching wallet data...");
      fetchWalletData(true);
    };

    const handleVisibilityChange = () => {
      if (document.visibilityState === "visible") {
        console.log("Tab visible, refetching wallet data...");
        fetchWalletData(true);
      }
    };

    window.addEventListener("focus", handleFocus);
    document.addEventListener("visibilitychange", handleVisibilityChange);

    return () => {
      window.removeEventListener("focus", handleFocus);
      document.removeEventListener("visibilitychange", handleVisibilityChange);
    };
  }, []);

  useEffect(() => {
    if (location.state && location.state.fromDepositSuccess) {
      console.log("Returned from successful deposit, refetching data...");
      fetchWalletData().then(() => {
        localStorage.removeItem("previousBalance");
        navigate(location.pathname, { replace: true, state: {} });
      });
    }
  }, [location.state]);

  useEffect(() => {
    const searchParams = new URLSearchParams(location.search);
    const fromLevel2Setup = searchParams.get("from_level2_setup");
    if (fromLevel2Setup === "true") {
      console.log(
        "Returned from level 2 setup, refetching status and maybe form data..."
      );
      checkLevel2PasswordStatus();
      navigate(location.pathname, { replace: true });
    }
  }, [location.search, navigate]);

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

  // Hàm mở popup chi tiết giao dịch
  const openTransactionDetail = (tx) => {
    setSelectedTransaction(tx);

    setShowModal(true);
  };

  // Hàm đóng popup
  const closeTransactionDetail = () => {
    setShowModal(false);
    setTimeout(() => setSelectedTransaction(null), 300); // Đợi hiệu ứng hoàn thành
  };

  // Hàm sao chép ID giao dịch
  const copyToClipboard = (text) => {
    navigator.clipboard.writeText(text).then(
      () => {
        setCopySuccess("Đã sao chép!");
        setTimeout(() => setCopySuccess(""), 2000);
      },
      (err) => {
        console.error("Không thể sao chép: ", err);
      }
    );
  };

  // Lấy màu trạng thái giao dịch
  const getStatusColor = (status) => {
    if (status === "thanh_cong") return "bg-green-100 text-green-800";
    if (status === "cho_thanh_toan") return "bg-yellow-100 text-yellow-800";
    if (status === "da_huy") return "bg-red-100 text-red-800";
    if (status === "that_bai") return "bg-red-100 text-red-800";
    return "bg-gray-100 text-gray-800";
  };

  // Lấy tên hiển thị của trạng thái
  const getStatusName = (status) => {
    if (status === "thanh_cong") return "Thành công";
    if (status === "cho_thanh_toan") return "Chờ thanh toán";
    if (status === "da_huy") return "Đã hủy";
    if (status === "that_bai") return "Thất bại";
    return status;
  };

  // Lấy tên hiển thị cho loại giao dịch
  const getTransactionTypeName = (type) => {
    if (type === "nap_tien") return "Nạp tiền";
    if (type === "thanh_toan_don_hang") return "Thanh toán đơn hàng";
    if (type === "hoan_tien") return "Hoàn tiền";
    if (type === "rut_tien") return "Rút tiền";
    return type;
  };

  // Lọc giao dịch theo loại
  const filteredTransactions = useMemo(() => {
    return transactions.filter((tx) => {
      if (filterType === "all") return true;
      if (filterType === "nap_tien")
        return tx.type === "nap_tien" || tx.type === "hoan_tien";
      if (filterType === "rut_tien") return tx.type === "rut_tien";
      return false;
    });
  }, [transactions, filterType]);
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
                  className={`text-3xl font-bold ${balance > 0 ? "text-green-600" : "text-gray-400"
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
          {/* Bộ lọc */}
          <div className="flex gap-2 mb-4">
            <button
              onClick={() => setFilterType("all")}
              className={`px-4 py-2 rounded-xl transition ${filterType === "all"
                ? "bg-indigo-600 text-white"
                : "bg-gray-200 text-gray-800"
                }`}
            >
              Toàn bộ
            </button>
            <button
              onClick={() => setFilterType("nap_tien")}
              className={`px-4 py-2 rounded-xl transition ${filterType === "nap_tien"
                ? "bg-indigo-600 text-white"
                : "bg-gray-200 text-gray-800"
                }`}
            >
              Nạp tiền
            </button>
            <button
              onClick={() => setFilterType("rut_tien")}
              className={`px-4 py-2 rounded-xl transition ${filterType === "rut_tien"
                ? "bg-indigo-600 text-white"
                : "bg-gray-200 text-gray-800"
                }`}
            >
              Rút tiền
            </button>
          </div>
          {loading ? (
            <div className="flex justify-center items-center py-8">
              <FaSpinner className="animate-spin mr-2 text-indigo-600" />
              <span>Đang tải lịch sử giao dịch...</span>
            </div>
          ) : transactions.filter((tx) => {
            if (filterType === "all") return true;
            if (filterType === "nap_tien")
              return tx.type === "nap_tien" || tx.type === "hoan_tien";
            if (filterType === "rut_tien") return tx.type === "rut_tien";
            return false;
          }).length === 0 ? (
            <div className="text-center text-gray-500 text-sm py-8">
              {filterType === "all"
                ? "Bạn chưa có giao dịch nào."
                : filterType === "nap_tien"
                  ? "Bạn chưa có giao dịch nạp tiền nào."
                  : "Bạn chưa có giao dịch rút tiền nào."}
            </div>
          ) : (
            <ul className="space-y-4">
              {transactions
                .filter((tx) => {
                  if (filterType === "all") return true;
                  if (filterType === "nap_tien")
                    return tx.type === "nap_tien" || tx.type === "hoan_tien"; // Có thể bao gồm hoàn tiền nếu cần
                  if (filterType === "rut_tien") return tx.type === "rut_tien";
                  return false;
                })
                .map((tx) => (
                  <li
                    key={tx.id}
                    className="flex justify-between items-center border-b pb-3 hover:bg-gray-50 cursor-pointer transition-colors rounded p-2"
                    onClick={() => openTransactionDetail(tx)}
                  >
                    <div className="flex items-center gap-3">
                      {getTransactionIcon(tx.type, tx.amount)}
                      <div>
                        <p className="text-sm font-medium text-gray-800">
                          {(() => {
                            if (tx.type === "nap_tien")
                              return "Nạp tiền vào ví";
                            if (tx.type === "rut_tien")
                              return "Rút tiền khỏi ví";
                            if (tx.type === "thanh_toan_don_hang")
                              return `Thanh toán đơn hàng ${tx.order?.code ? "#" + tx.order.code : ""
                                }`;
                            if (tx.type === "hoan_tien")
                              return `Hoàn tiền đơn hàng ${tx.order?.code ? "#" + tx.order.code : ""
                                }`;
                            return tx.description || "Giao dịch ví";
                          })()}
                        </p>

                        <div className="flex flex-wrap gap-2 text-xs text-gray-500">
                          <p>{tx.created_at}</p>
                          <p
                            className={`${tx.status === "thanh_cong"
                              ? "text-green-600"
                              : tx.status === "cho_thanh_toan"
                                ? "text-orange-500"
                                : tx.status === "da_huy"
                                  ? "text-red-500"
                                  : tx.status === "that_bai"
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
                                  : tx.status === "that_bai"
                                    ? "Thất bại"
                                    : tx.status}
                          </p>
                        </div>
                      </div>
                    </div>
                    <div className="flex items-center gap-2">
                      {tx.type === "nap_tien" &&
                        tx.status === "cho_thanh_toan" && (
                          <div
                            className="flex space-x-2"
                            onClick={(e) => e.stopPropagation()}
                          >
                            <button
                              onClick={(e) => {
                                e.stopPropagation();
                                retryPayment(tx.id);
                              }}
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
                            {/* Always show cancel button for pending deposits */}
                            <button
                              onClick={(e) => {
                                e.stopPropagation();
                                cancelTransaction(tx.id);
                              }}
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
                          </div>
                        )}
                      <span
                        className={`font-semibold ${tx.type === "nap_tien" || tx.type === "hoan_tien"
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

      {/* Modal chi tiết giao dịch */}
      <AnimatePresence>
        {showModal && selectedTransaction && (
          <motion.div
            initial={{ opacity: 0 }}
            animate={{ opacity: 1 }}
            exit={{ opacity: 0 }}
            className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
            onClick={closeTransactionDetail}
          >
            <motion.div
              initial={{ scale: 0.9, y: 20 }}
              animate={{ scale: 1, y: 0 }}
              exit={{ scale: 0.9, y: 20 }}
              className="bg-white rounded-2xl shadow-xl max-w-md w-full p-6 max-h-[90vh] overflow-y-auto"
              onClick={(e) => e.stopPropagation()}
            >
              {/* Header */}
              <div className="flex justify-between items-center mb-4">
                <h3 className="text-xl font-semibold text-gray-800 flex items-center gap-2">
                  <FaReceipt className="text-indigo-600" />
                  Chi tiết giao dịch
                </h3>
                <button
                  onClick={closeTransactionDetail}
                  className="text-gray-400 hover:text-gray-600 transition p-1"
                >
                  <FaTimes />
                </button>
              </div>

              {/* Nội dung */}
              <div className="space-y-6">
                {/* Số tiền và trạng thái */}
                <div className="text-center py-4 bg-gray-50 rounded-xl">
                  <div className="text-2xl font-bold mb-2">
                    <span
                      className={`${selectedTransaction.type === "nap_tien" ||
                        selectedTransaction.type === "hoan_tien"
                        ? "text-green-600"
                        : "text-red-500"
                        }`}
                    >
                      {selectedTransaction.type === "nap_tien" ||
                        selectedTransaction.type === "hoan_tien"
                        ? "+"
                        : "-"}
                      {formatMoney(selectedTransaction.amount)}
                    </span>
                  </div>
                  <div className="inline-block px-3 py-1 rounded-full text-sm font-medium mb-1 mt-2">
                    <span
                      className={`${getStatusColor(
                        selectedTransaction.status
                      )} px-3 py-1 rounded-full`}
                    >
                      {getStatusName(selectedTransaction.status)}
                    </span>
                  </div>
                </div>

                {/* Số dư trước và sau giao dịch */}
                {selectedTransaction.status === "thanh_cong" &&
                  !(
                    selectedTransaction.type === "thanh_toan_don_hang" &&
                    selectedTransaction.payment_method !== "Ví"
                  ) && (
                    <div className="bg-indigo-50 rounded-xl overflow-hidden">
                      <div className="grid grid-cols-2 divide-x divide-indigo-100">
                        <div className="p-3 text-center">
                          <p className="text-xs text-indigo-600 font-medium mb-1">
                            Số dư trước
                          </p>
                          <p className="text-indigo-800 font-bold">
                            {formatMoney(selectedTransaction.balance_before)}
                          </p>
                        </div>
                        <div className="p-3 text-center">
                          <p className="text-xs text-indigo-600 font-medium mb-1">
                            Số dư sau
                          </p>
                          <p className="text-indigo-800 font-bold">
                            {formatMoney(selectedTransaction.balance_after)}
                          </p>
                        </div>
                      </div>
                      <div className="bg-indigo-100 px-3 py-2 flex justify-center items-center">
                        <FaExchangeAlt className="text-indigo-500 mr-2" />
                        <span className="text-xs text-indigo-700">
                          {selectedTransaction.type === "nap_tien" ||
                            selectedTransaction.type === "hoan_tien"
                            ? `+${formatMoney(selectedTransaction.amount)}`
                            : `-${formatMoney(selectedTransaction.amount)}`}
                        </span>
                      </div>
                    </div>
                  )}

                {/* Thông tin chi tiết */}
                <div className="space-y-3">
                  {selectedTransaction.wallet_code ||
                    (selectedTransaction.type === "thanh_toan_don_hang" &&
                      selectedTransaction.order_code) ||
                    (selectedTransaction.type === "hoan_tien" &&
                      selectedTransaction.order_code) ? (
                    <div className="flex justify-between py-2 border-b">
                      <span className="text-gray-600">Mã giao dịch</span>
                      <div className="flex items-center gap-2">
                        <span className="text-gray-800 font-medium">
                          {selectedTransaction.wallet_code
                            ? selectedTransaction.wallet_code
                            : selectedTransaction.order_code}
                        </span>
                        <button
                          className="text-indigo-600 hover:text-indigo-800"
                          onClick={() =>
                            copyToClipboard(
                              selectedTransaction.wallet_code
                                ? selectedTransaction.wallet_code
                                : selectedTransaction.order_code
                            )
                          }
                          title="Sao chép"
                        >
                          <FaRegCopy />
                        </button>
                        {copySuccess && (
                          <span className="text-green-500 text-xs">
                            {copySuccess}
                          </span>
                        )}
                      </div>
                    </div>
                  ) : null}

                  <div className="flex justify-between py-2 border-b">
                    <span className="text-gray-600">Loại giao dịch</span>
                    <span className="text-gray-800 font-medium">
                      {getTransactionTypeName(selectedTransaction.type)}
                    </span>
                  </div>

                  <div className="flex justify-between py-2 border-b">
                    <span className="text-gray-600">Mô tả</span>
                    <span className="text-gray-800 font-medium text-right">
                      {selectedTransaction.description}
                    </span>
                  </div>

                  {/* Hiển thị thông tin tài khoản ngân hàng khi giao dịch là rút tiền */}
                  {selectedTransaction.type === "rut_tien" &&
                    selectedTransaction.withdraw_request && (
                      <>
                        <div className="flex justify-between py-2 border-b">
                          <span className="text-gray-600">Ngân hàng</span>
                          <span className="text-gray-800 font-medium">
                            {selectedTransaction.withdraw_request.bank_name}
                          </span>
                        </div>

                        <div className="flex justify-between py-2 border-b">
                          <span className="text-gray-600">Số tài khoản</span>
                          <span className="text-gray-800 font-medium">
                            {
                              selectedTransaction.withdraw_request
                                .bank_account_number
                            }
                          </span>
                        </div>

                        <div className="flex justify-between py-2 border-b">
                          <span className="text-gray-600">Chủ tài khoản</span>
                          <span className="text-gray-800 font-medium">
                            {
                              selectedTransaction.withdraw_request
                                .account_holder_name
                            }
                          </span>
                        </div>
                        <div className="flex justify-between py-2 border-b">
                          <span className="text-gray-600">
                            Trạng thái duyệt
                          </span>
                          <span
                            className={`font-medium ${selectedTransaction.withdraw_request.status ===
                              "da_duyet"
                              ? "text-green-600"
                              : selectedTransaction.withdraw_request
                                .status === "tu_choi"
                                ? "text-red-600"
                                : selectedTransaction.withdraw_request
                                  .status === "da_huy"
                                  ? "text-gray-500"
                                  : "text-orange-500"
                              }`}
                          >
                            {selectedTransaction.withdraw_request.status ===
                              "da_duyet"
                              ? "Đã duyệt"
                              : selectedTransaction.withdraw_request.status ===
                                "tu_choi"
                                ? "Từ chối"
                                : selectedTransaction.withdraw_request.status ===
                                  "da_huy"
                                  ? "Đã hủy"
                                  : "Đang xử lý"}
                          </span>
                        </div>
                      </>
                    )}

                  <div className="flex justify-between py-2 border-b">
                    <span className="text-gray-600">Thời gian</span>
                    <span className="text-gray-800 font-medium">
                      {new Date(selectedTransaction.created_at).toLocaleString(
                        "vi-VN"
                      )}
                    </span>
                  </div>

                  {selectedTransaction.payment_method && (
                    <div className="flex justify-between py-2 border-b">
                      <span className="text-gray-600">
                        Phương thức thanh toán
                      </span>
                      <span className="text-gray-800 font-medium">
                        {selectedTransaction.payment_method}
                      </span>
                    </div>
                  )}

                  {selectedTransaction.order_id && (
                    <div className="flex justify-between py-2 border-b">
                      <span className="text-gray-600">Mã đơn hàng</span>
                      <span className="text-gray-800 font-medium">
                        {selectedTransaction.order_id}
                      </span>
                    </div>
                  )}
                </div>

                {/* Nút hành động */}
                {selectedTransaction.type === "nap_tien" &&
                  selectedTransaction.status === "cho_thanh_toan" && (
                    <div className="flex gap-3 mt-4">
                      <button
                        onClick={() => {
                          retryPayment(selectedTransaction.id);
                          closeTransactionDetail();
                        }}
                        className="flex-1 bg-indigo-600 text-white py-2 px-4 rounded-xl hover:bg-indigo-700 transition flex items-center justify-center gap-2"
                        disabled={actionLoading}
                      >
                        {actionLoading ? (
                          <FaSpinner className="animate-spin" />
                        ) : (
                          <FaSync />
                        )}
                        Thanh toán lại
                      </button>
                      {/* Always show cancel button for pending deposits */}
                      <button
                        onClick={() => {
                          cancelTransaction(selectedTransaction.id);
                          closeTransactionDetail();
                        }}
                        className="flex-1 bg-red-600 text-white py-2 px-4 rounded-xl hover:bg-red-700 transition flex items-center justify-center gap-2"
                        disabled={actionLoading}
                      >
                        {actionLoading ? (
                          <FaSpinner className="animate-spin" />
                        ) : (
                          <FaTimes />
                        )}
                        Hủy giao dịch
                      </button>
                    </div>
                  )}

                {/* Lưu ý */}
                <div className="bg-blue-50 p-3 rounded-xl text-sm text-blue-800 flex items-start gap-2">
                  <FaInfoCircle className="mt-0.5 flex-shrink-0" />
                  <p>
                    {selectedTransaction.status === "thanh_cong"
                      ? "Giao dịch đã được xử lý thành công và đã được cập nhật vào số dư tài khoản của bạn."
                      : selectedTransaction.status === "cho_thanh_toan"
                        ? "Giao dịch đang chờ xử lý. Vui lòng hoàn tất thanh toán hoặc đợi hệ thống xử lý."
                        : selectedTransaction.status === "da_huy"
                          ? "Giao dịch đã bị hủy và không được xử lý."
                          : "Cảm ơn bạn đã sử dụng dịch vụ của chúng tôi."}
                  </p>
                </div>
              </div>
            </motion.div>
          </motion.div>
        )}
      </AnimatePresence>
    </motion.div>
  );
};

export default WalletPage;