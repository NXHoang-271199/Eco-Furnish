import React, { useState, useEffect } from "react";
import { Link, useNavigate } from "react-router-dom";
import { motion } from "framer-motion";
import {
  FaWallet,
  FaMoneyBillWave,
  FaCreditCard,
  FaSpinner,
} from "react-icons/fa";
import axios from "axios";
import axiosInstance from "../../../../utils/axiosConfig";
const WalletDeposit = () => {
  const [amount, setAmount] = useState("");
  const [paymentMethods, setPaymentMethods] = useState([]);
  const [selectedPaymentMethod, setSelectedPaymentMethod] = useState(null);
  const [loading, setLoading] = useState(false);
  const [fetchingMethods, setFetchingMethods] = useState(true);
  const [error, setError] = useState("");

  const navigate = useNavigate();

  // Fetch danh sách phương thức thanh toán cho nạp tiền
  useEffect(() => {
    const fetchPaymentMethods = async () => {
      try {
        setFetchingMethods(true);
        const response = await axiosInstance.get("/payment-methods/deposit", {
          headers: {
            Authorization: `Bearer ${localStorage.getItem("authToken")}`,
          },
        });

        if (response.data && response.data.data) {
          setPaymentMethods(response.data.data);
          // Auto-select phương thức đầu tiên nếu có
          if (response.data.data.length > 0) {
            setSelectedPaymentMethod(response.data.data[0].id);
          }
        }
      } catch (error) {
        console.error("Lỗi khi tải phương thức thanh toán:", error);
        setError("Không thể tải phương thức thanh toán. Vui lòng thử lại sau.");
      } finally {
        setFetchingMethods(false);
      }
    };

    fetchPaymentMethods();
  }, []);

  // Định dạng tiền VNĐ
  const formatMoney = (amount) => {
    if (!amount) return "0 ₫";
    return new Intl.NumberFormat("vi-VN", {
      style: "currency",
      currency: "VND",
    }).format(amount);
  };

  // Xử lý khi nhập số tiền
  const handleAmountChange = (e) => {
    // Chỉ cho phép nhập số
    const value = e.target.value.replace(/\D/g, "");
    setAmount(value);
  };

  // Xử lý chọn phương thức thanh toán
  const handlePaymentMethodChange = (id) => {
    setSelectedPaymentMethod(id);
  };

  // Xử lý nạp tiền
  const handleDeposit = async () => {
    // Validate input
    if (!amount || parseInt(amount) < 10000) {
      setError("Số tiền nạp tối thiểu là 10.000 ₫");
      return;
    }

    if (!selectedPaymentMethod) {
      setError("Vui lòng chọn phương thức thanh toán");
      return;
    }

    setLoading(true);
    setError("");

    try {
      // Gọi API nạp tiền
      const response = await axiosInstance.post(
        "/wallet/deposit",
        {
          amount: parseInt(amount),
          payment_method_id: selectedPaymentMethod,
        },
        {
          headers: {
            Authorization: `Bearer ${localStorage.getItem("authToken")}`,
            "Content-Type": "application/json",
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
      } else {
        // Trường hợp khác, chuyển hướng đến trang thành công
        navigate("/account/wallet/deposit-success");
      }
    } catch (error) {
      console.error("Lỗi khi nạp tiền:", error);
      setError(
        error.response?.data?.errors?.amount ||
          "Có lỗi xảy ra. Vui lòng thử lại sau."
      );
    } finally {
      setLoading(false);
    }
  };

  const presetAmounts = [100000, 200000, 500000, 1000000, 2000000];

  return (
    <motion.div
      initial={{ opacity: 0, y: 20 }}
      animate={{ opacity: 1, y: 0 }}
      transition={{ duration: 0.5 }}
      className="min-h-screen p-4 bg-gray-50"
    >
      <div className="max-w-lg mx-auto">
        <div className="flex items-center mb-6">
          <Link
            to="/account/wallet"
            className="text-indigo-600 hover:text-indigo-800"
          >
            &larr; Quay lại ví
          </Link>
        </div>

        {/* Form nạp tiền */}
        <motion.div
          initial={{ scale: 0.95, opacity: 0 }}
          animate={{ scale: 1, opacity: 1 }}
          transition={{ delay: 0.2 }}
          className="bg-white rounded-2xl shadow p-6"
        >
          <div className="text-center mb-6">
            <FaWallet className="text-4xl text-indigo-600 mx-auto mb-2" />
            <h2 className="text-2xl font-bold text-gray-800">
              Nạp tiền vào ví
            </h2>
            <p className="text-gray-500">
              Chọn số tiền và phương thức thanh toán
            </p>
          </div>

          {/* Hiển thị lỗi nếu có */}
          {error && (
            <div className="bg-red-100 text-red-700 p-3 rounded-lg mb-4">
              {error}
            </div>
          )}

          {/* Nhập số tiền */}
          <div className="mb-6">
            <label className="block text-gray-700 mb-2 font-medium">
              Số tiền nạp
            </label>
            <div className="relative">
              <input
                type="text"
                value={amount}
                onChange={handleAmountChange}
                placeholder="Nhập số tiền"
                className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
              />
              <span className="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500">
                VNĐ
              </span>
            </div>
            {amount && (
              <p className="text-gray-500 mt-2">
                Số tiền: {formatMoney(amount)}
              </p>
            )}
          </div>

          {/* Chọn nhanh số tiền */}
          <div className="mb-6">
            <p className="text-gray-700 mb-2 font-medium">Chọn nhanh</p>
            <div className="grid grid-cols-3 gap-2">
              {presetAmounts.map((presetAmount) => (
                <button
                  key={presetAmount}
                  type="button"
                  onClick={() => setAmount(presetAmount.toString())}
                  className={`px-2 py-2 border ${
                    amount === presetAmount.toString()
                      ? "border-indigo-500 bg-indigo-50 text-indigo-700"
                      : "border-gray-300 hover:bg-gray-100"
                  } rounded-md text-sm`}
                >
                  {formatMoney(presetAmount)}
                </button>
              ))}
            </div>
          </div>

          {/* Chọn phương thức thanh toán */}
          <div className="mb-6">
            <label className="block text-gray-700 mb-2 font-medium">
              Phương thức thanh toán
            </label>
            {fetchingMethods ? (
              <div className="flex items-center justify-center py-4">
                <FaSpinner className="animate-spin mr-2 text-indigo-600" />
                <span>Đang tải phương thức thanh toán...</span>
              </div>
            ) : paymentMethods.length === 0 ? (
              <p className="text-red-500">
                Không có phương thức thanh toán khả dụng
              </p>
            ) : (
              <div className="space-y-2">
                {paymentMethods.map((method) => (
                  <div
                    key={method.id}
                    onClick={() => handlePaymentMethodChange(method.id)}
                    className={`flex items-center p-3 border rounded-lg cursor-pointer ${
                      selectedPaymentMethod === method.id
                        ? "border-indigo-500 bg-indigo-50"
                        : "border-gray-300 hover:bg-gray-50"
                    }`}
                  >
                    <div className="mr-3">
                      {method.image ? (
                        <div className="w-12 h-12 flex items-center justify-center rounded-lg overflow-hidden bg-white p-1 border border-gray-100 shadow-sm">
                          <img
                            src={`http://localhost:8000/storage/${method.image}`}
                            alt={method.name}
                            className="h-8 object-contain"
                          />
                        </div>
                      ) : (
                        <FaCreditCard className="text-gray-600 text-2xl" />
                      )}
                    </div>
                    <div>
                      <p className="font-medium">{method.name}</p>
                    </div>
                  </div>
                ))}
              </div>
            )}
          </div>

          {/* Nút nạp tiền */}
          <button
            onClick={handleDeposit}
            disabled={loading || !amount || !selectedPaymentMethod}
            className={`w-full py-3 px-4 rounded-lg flex items-center justify-center ${
              loading || !amount || !selectedPaymentMethod
                ? "bg-gray-300 cursor-not-allowed"
                : "bg-indigo-600 hover:bg-indigo-700 text-white"
            }`}
          >
            {loading ? (
              <>
                <FaSpinner className="animate-spin mr-2" />
                Đang xử lý...
              </>
            ) : (
              <>
                <FaMoneyBillWave className="mr-2" />
                Nạp tiền
              </>
            )}
          </button>

          <p className="text-xs text-gray-500 mt-4 text-center">
            Bằng việc nhấn "Nạp tiền", bạn đồng ý với các điều khoản nạp tiền
            của chúng tôi.
          </p>
        </motion.div>
      </div>
    </motion.div>
  );
};

export default WalletDeposit;
