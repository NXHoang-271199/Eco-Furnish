import React, { useState, useEffect } from "react";
import { motion } from "framer-motion";
import { useNavigate } from "react-router-dom";
import axiosInstance from "../../../../utils/axiosConfig";
import { toast } from "react-hot-toast";

const WalletWithdraw = () => {
  const [balance, setBalance] = useState(0);
  const [bankAccounts, setBankAccounts] = useState([]);
  const [formData, setFormData] = useState({
    amount: "",
    bank_account_id: "",
    qr_code: "",
  });
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState("");
  const [amountError, setAmountError] = useState("");
  const [qrCode, setQrCode] = useState("");
  const [showQrCode, setShowQrCode] = useState(false);
  const navigate = useNavigate();

  // Animation variants for the entire container
  const cardVariants = {
    hidden: { opacity: 0, y: 20 },
    visible: { opacity: 1, y: 0, transition: { duration: 0.5 } },
  };

  // Animation variants for the QR code section
  const qrVariants = {
    hidden: { opacity: 0, x: 20 },
    visible: { opacity: 1, x: 0, transition: { duration: 0.3 } },
  };

  // Fetch balance and bank accounts
  useEffect(() => {
    const fetchData = async () => {
      try {
        setLoading(true);
        const token = localStorage.getItem("authToken");

        // Fetch balance
        const balanceResponse = await axiosInstance.get("/wallet/balance", {
          headers: { Authorization: `Bearer ${token}` },
        });
        setBalance(parseFloat(balanceResponse.data.balance));

        // Fetch bank accounts
        const accountsResponse = await axiosInstance.get("/bank-accounts", {
          headers: { Authorization: `Bearer ${token}` },
        });
        setBankAccounts(accountsResponse.data.data || []);
      } catch (err) {
        setError("Không thể tải dữ liệu ví hoặc tài khoản ngân hàng");
        toast.error("Không thể tải dữ liệu");
      } finally {
        setLoading(false);
      }
    };

    fetchData();
  }, []);

  // Handle input change
  const handleInputChange = (e) => {
    const { name, value } = e.target;
    setFormData((prev) => ({ ...prev, [name]: value }));

    // Kiểm tra lỗi số tiền khi người dùng nhập
    if (name === "amount") {
      const amount = parseFloat(value);
      if (isNaN(amount)) {
        setAmountError("");
        return;
      }
      if (amount < 100000 || amount > 10000000) {
        setAmountError("Giá trị phải từ 100.000 VNĐ đến 10.000.000 VNĐ.");
      } else if (amount > balance) {
        setAmountError("Số dư ví không đủ.");
      } else {
        setAmountError("");
      }
    }
  };

  // Hàm tạo mã QR
  const generateQrCode = async () => {
    if (!formData.bank_account_id) {
      toast.error("Vui lòng chọn tài khoản ngân hàng");
      return null;
    }
    if (!formData.amount) {
      toast.error("Vui lòng nhập số tiền");
      return null;
    }
    const amount = parseFloat(formData.amount);
    if (amount < 100000 || amount > 10000000) {
      setAmountError("Giá trị phải từ 100.000 VNĐ đến 10.000.000 VNĐ.");
      return null;
    }
    if (amount > balance) {
      setAmountError("Số dư ví không đủ.");
      return null;
    }

    try {
      const token = localStorage.getItem("authToken");
      const qrResponse = await axiosInstance.post(
        "/wallet/generate-qr-preview",
        {
          amount: formData.amount,
          bank_account_id: formData.bank_account_id,
        },
        {
          headers: { Authorization: `Bearer ${token}` },
        }
      );
      const generatedQrCode = qrResponse.data.qr_code;
      setQrCode(generatedQrCode);
      return generatedQrCode;
    } catch (qrError) {
      console.error("Lỗi khi tạo mã QR:", qrError);
      toast.error("Không thể tạo mã QR cho yêu cầu này.");
      return null;
    }
  };

  // Handle hiển thị/ẩn mã QR khi bật/tắt cần gạt
  const handleToggleQrCode = async (e) => {
    const isChecked = e.target.checked;
    setShowQrCode(isChecked);

    if (isChecked && !qrCode) {
      const generatedQrCode = await generateQrCode();
      if (!generatedQrCode) {
        setShowQrCode(false); // Nếu không tạo được mã QR, tắt cần gạt
        e.target.checked = false; // Đặt lại trạng thái cần gạt
      }
    }
  };

  // Handle form submission
  const handleSubmit = async (e) => {
    e.preventDefault();
    const amount = parseFloat(formData.amount);

    // Client-side validation
    if (!formData.bank_account_id) {
      toast.error("Vui lòng chọn tài khoản ngân hàng");
      return;
    }
    if (amount < 100000 || amount > 10000000) {
      setAmountError("Giá trị phải từ 100.000 VNĐ đến 10.000.000 VNĐ.");
      return;
    }
    if (amount > balance) {
      setAmountError("Số dư ví không đủ.");
      return;
    }

    try {
      setLoading(true);
      const token = localStorage.getItem("authToken");

      // 1. Tạo mã QR nếu chưa có
      let generatedQrCode = qrCode;
      if (!generatedQrCode) {
        generatedQrCode = await generateQrCode();
      }

      // 2. Tạo payload cuối cùng bao gồm mã QR (nếu có)
      const finalPayload = {
        amount: formData.amount,
        bank_account_id: formData.bank_account_id,
        qr_code: generatedQrCode || "",
      };

      // 3. Gọi API để tạo yêu cầu rút tiền
      await axiosInstance.post("/wallet/withdraw-requests", finalPayload, {
        headers: { Authorization: `Bearer ${token}` },
      });

      toast.success("Yêu cầu rút tiền đã được gửi thành công!");
      navigate("/account/wallet");
    } catch (err) {
      const message =
        err.response?.data?.message || "Không thể tạo yêu cầu rút tiền";
      setError(message);
      toast.error(message);
    } finally {
      setLoading(false);
    }
  };

  // Format money
  const formatMoney = (amount) =>
    new Intl.NumberFormat("vi-VN", {
      style: "currency",
      currency: "VND",
    }).format(amount);

  return (
    <motion.div
      className="max-w-6xl mx-auto"
      variants={cardVariants}
      initial="hidden"
      animate="visible"
    >
      <div className="bg-white shadow-lg rounded-lg p-6">
        <h2 className="text-2xl font-bold mb-4 text-gray-800">
          Rút tiền từ ví
        </h2>

        {/* Grid layout: 2 columns on medium screens and above */}
        <div className="md:grid md:grid-cols-2 md:gap-6">
          {/* Left column: Form and inputs */}
          <div className="mb-6 md:mb-0">
            {/* Balance */}
            <div className="mb-6 p-4 bg-gray-100 rounded-lg">
              <p className="text-gray-600">Số dư hiện tại</p>
              <p className="text-2xl font-semibold text-green-600">
                {formatMoney(balance)}
              </p>
            </div>

            {/* Error message */}
            {error && (
              <div className="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded">
                {error}
              </div>
            )}

            {/* Bank accounts */}
            {bankAccounts.length === 0 ? (
              <div className="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-4 rounded">
                Bạn chưa có tài khoản ngân hàng.{" "}
                <button
                  onClick={() => navigate("/account/bank")}
                  className="text-blue-500 hover:underline"
                >
                  Thêm tài khoản ngay
                </button>
              </div>
            ) : (
              <div className="mb-6">
                <label className="block text-gray-700 font-medium mb-2">
                  Chọn tài khoản ngân hàng
                </label>
                <select
                  name="bank_account_id"
                  value={formData.bank_account_id}
                  onChange={handleInputChange}
                  className="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                  required
                >
                  <option value="">Chọn tài khoản</option>
                  {bankAccounts.map((account) => (
                    <option key={account.id} value={account.id}>
                      {account.bank_name} - {account.bank_account_number} (
                      {account.account_holder_name})
                    </option>
                  ))}
                </select>
              </div>
            )}

            {/* Withdraw form */}
            {bankAccounts.length > 0 && (
              <form onSubmit={handleSubmit}>
                <div className="mb-4">
                  <label
                    htmlFor="amount"
                    className="block text-gray-700 font-medium mb-2"
                  >
                    Số tiền muốn rút
                  </label>
                  <input
                    type="number"
                    id="amount"
                    name="amount"
                    value={formData.amount}
                    onChange={handleInputChange}
                    className="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="Nhập số tiền (100.000 - 10.000.000 VNĐ)"
                    min="100000"
                    max="10000000"
                    required
                  />
                  {/* Hiển thị số tiền người dùng nhập */}
                  {formData.amount && (
                    <p className="mt-2 text-gray-600">
                      Số tiền bạn nhập:{" "}
                      {formatMoney(parseFloat(formData.amount))}
                    </p>
                  )}
                  {/* Hiển thị thông báo lỗi */}
                  {amountError && (
                    <div className="mt-2 flex items-center text-orange-600">
                      <svg
                        className="w-5 h-5 mr-2"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg"
                      >
                        <path
                          strokeLinecap="round"
                          strokeLinejoin="round"
                          strokeWidth="2"
                          d="M12 9v2m0 4h.01M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2z"
                        />
                      </svg>
                      {amountError}
                    </div>
                  )}
                </div>
                <div className="flex items-center space-x-4">
                  <motion.button
                    type="submit"
                    whileHover={{ scale: 1.05 }}
                    whileTap={{ scale: 0.95 }}
                    disabled={loading}
                    className={`px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 ${
                      loading ? "opacity-50 cursor-not-allowed" : ""
                    }`}
                  >
                    {loading ? "Đang xử lý..." : "Gửi yêu cầu rút tiền"}
                  </motion.button>
                  {/* Cần gạt thay cho nút "Hiển thị mã QR" */}
                  <label className="flex items-center cursor-pointer">
                    <div className="relative">
                      <input
                        type="checkbox"
                        checked={showQrCode}
                        onChange={handleToggleQrCode}
                        className="sr-only"
                        disabled={loading}
                      />
                      <div
                        className={`block w-12 h-6 rounded-full transition-colors duration-200 ${
                          showQrCode ? "bg-blue-500" : "bg-gray-300"
                        }`}
                      ></div>
                      <div
                        className={`absolute left-1 top-1 w-4 h-4 bg-white rounded-full transition-transform duration-200 transform ${
                          showQrCode ? "translate-x-6" : ""
                        }`}
                      ></div>
                    </div>
                    <span className="ml-2 text-gray-700">Hiển thị mã QR</span>
                  </label>
                </div>
              </form>
            )}
          </div>

          {/* Right column: QR code or placeholder */}
          <motion.div
            className="mt-6 md:mt-0"
            variants={qrVariants}
            initial="hidden"
            animate="visible"
          >
            <div className="p-4 bg-gray-100 rounded-lg h-full flex flex-col items-center justify-center">
              {showQrCode ? (
                <>
                  <p className="text-gray-600 mb-2">Mã QR</p>
                  {qrCode ? (
                    <img src={qrCode} alt="QR Code" className="w-48 h-48" />
                  ) : (
                    <p className="text-red-600">Không thể hiển thị mã QR.</p>
                  )}
                </>
              ) : (
                <p className="text-gray-600 text-center">
                  Bật cần gạt để hiển thị mã QR
                </p>
              )}
            </div>
          </motion.div>
        </div>
      </div>
    </motion.div>
  );
};

export default WalletWithdraw;
