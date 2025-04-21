import React, { useState, useEffect } from "react";
import { motion } from "framer-motion";
import { useNavigate, useLocation } from "react-router-dom";
import axiosInstance from "../../../../utils/axiosConfig";
import { toast } from "react-hot-toast";

const WalletWithdraw = () => {
  const [balance, setBalance] = useState(0);
  const [bankAccounts, setBankAccounts] = useState([]);
  const [allBanks, setAllBanks] = useState([]);
  const [formData, setFormData] = useState({
    amount: "",
    bank_account_id: "",
    qr_code: "",
  });
  const [selectedBankAccount, setSelectedBankAccount] = useState(null);
  const [selectedBankLogoUrl, setSelectedBankLogoUrl] = useState("");
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState("");
  const [amountError, setAmountError] = useState("");
  const [qrCode, setQrCode] = useState("");
  const [showQrCode, setShowQrCode] = useState(false);
  const [hasLevel2Password, setHasLevel2Password] = useState(false);
  const [showLevel2PasswordModal, setShowLevel2PasswordModal] = useState(false);
  const [level2Password, setLevel2Password] = useState("");
  const [level2PasswordError, setLevel2PasswordError] = useState("");
  const [checkingLevel2Password, setCheckingLevel2Password] = useState(false);
  const [showProcessingPopup, setShowProcessingPopup] = useState(false);
  const [processing, setProcessing] = useState(true);
  const [completed, setCompleted] = useState(false);

  const navigate = useNavigate();
  const location = useLocation();

  const cardVariants = {
    hidden: { opacity: 0, y: 20 },
    visible: { opacity: 1, y: 0, transition: { duration: 0.5 } },
  };

  const qrVariants = {
    hidden: { opacity: 0, x: 20 },
    visible: { opacity: 1, x: 0, transition: { duration: 0.3 } },
  };

  const popupVariants = {
    hidden: { opacity: 0, scale: 0.9, y: -20 },
    visible: {
      opacity: 1,
      scale: 1,
      y: 0,
      transition: {
        duration: 0.4,
        ease: "easeOut",
        when: "beforeChildren",
        staggerChildren: 0.1,
      },
    },
    exit: {
      opacity: 0,
      scale: 0.9,
      y: 20,
      transition: { duration: 0.3, ease: "easeIn" },
    },
  };

  const childVariants = {
    hidden: { opacity: 0, y: 10 },
    visible: { opacity: 1, y: 0, transition: { duration: 0.3 } },
  };

  const spinnerVariants = {
    animate: {
      rotate: 360,
      transition: {
        repeat: Infinity,
        duration: 1,
        ease: "linear",
      },
    },
  };

  useEffect(() => {
    const fetchData = async () => {
      try {
        setLoading(true);
        const token = localStorage.getItem("authToken");

        const balanceResponse = await axiosInstance.get("/wallet/balance");
        setBalance(parseFloat(balanceResponse.data.balance));

        const accountsResponse = await axiosInstance.get("/bank-accounts");
        setBankAccounts(accountsResponse.data.data || []);

        const allBanksResponse = await axiosInstance.get("/banks");
        setAllBanks(allBanksResponse.data || []);

        await checkLevel2PasswordStatus();
      } catch (err) {
        setError("Không thể tải dữ liệu ví hoặc tài khoản ngân hàng");
        toast.error("Không thể tải dữ liệu");
      } finally {
        setLoading(false);
      }
    };

    fetchData();

    const searchParams = new URLSearchParams(location.search);
    const fromLevel2Setup = searchParams.get("from_level2_setup");
    if (fromLevel2Setup === "true") {
      toast.custom(
        (t) => (
          <div
            className={`${
              t.visible ? "animate-enter" : "animate-leave"
            } max-w-md w-full bg-white shadow-lg rounded-lg pointer-events-auto flex ring-1 ring-black ring-opacity-5`}
          >
            <div className="flex-1 w-0 p-4">
              <div className="flex items-start">
                <div className="flex-shrink-0 pt-0.5">
                  <div className="h-10 w-10 rounded-full bg-green-100 flex items-center justify-center">
                    <svg
                      className="h-6 w-6 text-green-600"
                      fill="none"
                      viewBox="0 0 24 24"
                      stroke="currentColor"
                    >
                      <path
                        strokeLinecap="round"
                        strokeLinejoin="round"
                        strokeWidth={2}
                        d="M5 13l4 4L19 7"
                      />
                    </svg>
                  </div>
                </div>
                <div className="ml-3 flex-1">
                  <p className="text-sm font-medium text-gray-900">
                    Thiết lập thành công!
                  </p>
                  <p className="mt-1 text-sm text-gray-500">
                    Mật khẩu cấp 2 đã được thiết lập. Bạn có thể tiếp tục rút
                    tiền.
                  </p>
                </div>
              </div>
            </div>
            <div className="flex border-l border-gray-200">
              <button
                onClick={() => toast.dismiss(t.id)}
                className="w-full border border-transparent rounded-none rounded-r-lg p-4 flex items-center justify-center text-sm font-medium text-indigo-600 hover:text-indigo-500 focus:outline-none"
              >
                Đóng
              </button>
            </div>
          </div>
        ),
        { duration: 4000, id: "level2-password-success" }
      );

      navigate("/account/wallet/withdraw", { replace: true });

      try {
        const pendingState = localStorage.getItem("pendingWithdrawState");
        if (pendingState) {
          const withdrawData = JSON.parse(pendingState);
          setFormData({
            amount: withdrawData.amount || "",
            bank_account_id: withdrawData.bank_account_id || "",
            qr_code: withdrawData.qr_code || "",
          });
          localStorage.removeItem("pendingWithdrawState");
        }
      } catch (e) {
        console.error("Lỗi khi khôi phục dữ liệu rút tiền:", e);
      }
    }
  }, [location, navigate]);

  const checkLevel2PasswordStatus = async () => {
    try {
      const token = localStorage.getItem("authToken");
      if (!token) return;

      const response = await axiosInstance.get("/users/level2-password/status");

      if (response.data.status === "success") {
        setHasLevel2Password(response.data.data.has_level2_password);
      }
    } catch (error) {
      console.error("Lỗi khi kiểm tra trạng thái mật khẩu cấp 2:", error);
    }
  };

  const handleAmountBlur = async (e) => {
    if (formData.bank_account_id && formData.amount) {
      const amount = parseFloat(formData.amount);
      if (
        !isNaN(amount) &&
        amount >= 100000 &&
        amount <= 10000000 &&
        amount <= balance
      ) {
        const generatedQrCode = await generateQrCode();
        if (generatedQrCode) {
          setShowQrCode(true);
        }
      }
    }
  };

  const handleAmountKeyDown = async (e) => {
    if (e.key === "Enter") {
      e.preventDefault();
      e.target.blur();
    }
  };

  const handleInputChange = (e) => {
    const { name, value } = e.target;
    setFormData((prev) => ({ ...prev, [name]: value }));

    if (name === "bank_account_id") {
      const selectedAccount = bankAccounts.find(
        (account) => account.id === parseInt(value, 10)
      );
      setSelectedBankAccount(selectedAccount || null);

      if (selectedAccount && allBanks.length > 0) {
        const matchingBank = allBanks.find(
          (bank) => bank.code === selectedAccount.bank_code
        );
        setSelectedBankLogoUrl(matchingBank ? matchingBank.logo : "");
      } else {
        setSelectedBankLogoUrl("");
      }
    }

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

  const handleToggleQrCode = async (e) => {
    const isChecked = e.target.checked;
    setShowQrCode(isChecked);

    if (isChecked && !qrCode) {
      const generatedQrCode = await generateQrCode();
      if (!generatedQrCode) {
        setShowQrCode(false);
        e.target.checked = false;
      }
    }
  };

  const handleConfirmLevel2Password = () => {
    if (!level2Password) {
      setLevel2PasswordError("Vui lòng nhập mật khẩu cấp 2");
      return;
    }

    setCheckingLevel2Password(true);
    processWithdrawRequest(level2Password);
  };

  const processWithdrawRequest = async (level2PasswordValue) => {
    try {
      setLoading(true);

      let generatedQrCode = qrCode;
      if (!generatedQrCode) {
        generatedQrCode = await generateQrCode();
      }

      const finalPayload = {
        amount: formData.amount,
        bank_account_id: formData.bank_account_id,
        qr_code: generatedQrCode || "",
        level2_password: level2PasswordValue,
      };

      const response = await axiosInstance.post(
        "/wallet/withdraw-requests",
        finalPayload
      );

      if (response.data) {
        setShowProcessingPopup(true);
        setProcessing(true);
        setCompleted(false);

        if (response.data.new_balance !== undefined) {
          setBalance(response.data.new_balance);
        }

        // Sau 3 giây, chuyển sang trạng thái hoàn tất
        setTimeout(() => {
          setProcessing(false);
          setCompleted(true);

          // Tự động chuyển hướng sau 2 giây khi hiển thị nút tích xanh
          setTimeout(() => {
            setShowProcessingPopup(false);
            navigate("/account/wallet");
          }, 2000);
        }, 3000);
      } else {
        toast.error(
          response.data?.message || "Không thể tạo yêu cầu rút tiền",
          {
            duration: 5000,
          }
        );
      }
    } catch (err) {
      let message =
        err.response?.data?.message || "Không thể tạo yêu cầu rút tiền";

      if (
        err.response?.status === 400 &&
        err.response?.data?.message?.includes("mật khẩu cấp 2")
      ) {
        setLevel2PasswordError("Mật khẩu cấp 2 không đúng");
        toast.error("Mật khẩu cấp 2 không đúng", {
          duration: 3000,
        });
      } else {
        setError(message);
        toast.error(message, {
          duration: 5000,
        });
      }
    } finally {
      setLoading(false);
      setCheckingLevel2Password(false);
      setShowLevel2PasswordModal(false);
    }
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    const amount = parseFloat(formData.amount);

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

    if (!hasLevel2Password) {
      localStorage.setItem(
        "pendingWithdrawState",
        JSON.stringify({
          amount: formData.amount,
          bank_account_id: formData.bank_account_id,
          qr_code: qrCode || "",
        })
      );

      toast.custom(
        (t) => (
          <div
            className={`${
              t.visible ? "animate-enter" : "animate-leave"
            } max-w-md w-full bg-white shadow-lg rounded-lg pointer-events-auto flex`}
          >
            <div className="flex-1 w-0 p-4">
              <div className="flex items-start">
                <div className="flex-shrink-0 pt-0.5">
                  <svg
                    className="h-10 w-10 text-blue-500"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                  >
                    <path
                      strokeLinecap="round"
                      strokeLinejoin="round"
                      strokeWidth={2}
                      d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                    />
                  </svg>
                </div>
                <div className="ml-3 flex-1">
                  <p className="text-sm font-medium text-gray-900">
                    Thiết lập mật khẩu cấp 2
                  </p>
                  <p className="mt-1 text-sm text-gray-500">
                    Bạn cần thiết lập mật khẩu cấp 2 để bảo vệ các giao dịch rút
                    tiền.
                  </p>
                </div>
              </div>
            </div>
          </div>
        ),
        { duration: 3000 }
      );

      setTimeout(() => {
        navigate("/account?tab=level2password&redirect=wallet_withdraw");
      }, 2000);

      return;
    }

    setShowLevel2PasswordModal(true);
    setLevel2Password("");
    setLevel2PasswordError("");
  };

  const formatMoney = (amount) =>
    new Intl.NumberFormat("vi-VN", {
      style: "currency",
      currency: "VND",
    }).format(amount);

  const handleComplete = () => {
    setShowProcessingPopup(false);
    navigate("/account/wallet");
  };

  return (
    <motion.div
      className="max-w-6xl mx-auto p-6"
      variants={cardVariants}
      initial="hidden"
      animate="visible"
    >
      <div className="bg-white shadow-lg rounded-lg p-6">
        <h2 className="text-2xl font-bold mb-4 text-gray-800">
          Rút tiền từ ví
        </h2>

        <div className="md:grid md:grid-cols-2 md:gap-6">
          <div className="mb-6 md:mb-0">
            <div className="mb-6 p-4 bg-gray-100 rounded-lg">
              <p className="text-gray-600">Số dư hiện tại</p>
              <p className="text-2xl font-semibold text-green-600">
                {formatMoney(balance)}
              </p>
            </div>

            {error && (
              <div className="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded">
                {error}
              </div>
            )}

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

                {selectedBankAccount && (
                  <div className="mt-4 p-4 bg-white border border-blue-100 rounded-lg flex items-center space-x-4">
                    <div className="flex-shrink-0">
                      <img
                        src={
                          selectedBankLogoUrl ||
                          selectedBankAccount.bank_logo_url ||
                          "https://via.placeholder.com/40"
                        }
                        alt={`${selectedBankAccount.bank_name} Logo`}
                        className="w-10 h-10 object-contain"
                        onError={(e) => {
                          e.target.onerror = null;
                          e.target.src = "https://via.placeholder.com/40";
                        }}
                      />
                    </div>
                    <div className="flex-1">
                      <div className="flex justify-between items-center">
                        <p className="text-gray-800 font-medium">
                          {selectedBankAccount.bank_name}
                        </p>
                        {selectedBankAccount.is_default && (
                          <span className="px-2 py-1 bg-green-500 text-white text-xs rounded">
                            Mặc định
                          </span>
                        )}
                      </div>
                      <p className="text-gray-600">
                        Số tài khoản: {selectedBankAccount.bank_account_number}
                      </p>
                      <p className="text-gray-600">
                        Chủ tài khoản: {selectedBankAccount.account_holder_name}
                      </p>
                    </div>
                  </div>
                )}
              </div>
            )}

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
                    onBlur={handleAmountBlur}
                    onKeyDown={handleAmountKeyDown}
                    className="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="Nhập số tiền (100.000 - 10.000.000 VNĐ)"
                    min="100000"
                    max="10000000"
                    required
                  />
                  {formData.amount && (
                    <p className="mt-2 text-gray-600">
                      Số tiền bạn nhập:{" "}
                      {formatMoney(parseFloat(formData.amount))}
                    </p>
                  )}
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
                <div className="flex items-center">
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
                </div>
              </form>
            )}
          </div>

          <motion.div
            className="mt-6 md:mt-0"
            variants={qrVariants}
            initial="hidden"
            animate="visible"
          >
            <div className="p-4 bg-gray-100 rounded-lg h-full flex flex-col items-center justify-center">
              {showQrCode ? (
                <>
                  {qrCode ? (
                    <img src={qrCode} alt="QR Code" className="p-2" />
                  ) : (
                    <p className="text-red-600">Không thể hiển thị mã QR.</p>
                  )}
                </>
              ) : (
                <p className="text-gray-600 text-center">
                  Chọn tài khoản ngân hàng và nhập số tiền để hiển thị mã QR
                </p>
              )}
            </div>
          </motion.div>
        </div>
      </div>

      {showLevel2PasswordModal && (
        <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
          <div className="bg-white rounded-lg p-6 max-w-md w-full">
            <h3 className="text-xl font-bold mb-4 text-gray-800">
              Xác nhận rút tiền
            </h3>
            <p className="mb-4 text-gray-700">
              Để bảo mật giao dịch, vui lòng nhập mật khẩu cấp 2 của bạn.
            </p>
            <div className="mb-4">
              <input
                type="password"
                value={level2Password}
                onChange={(e) => setLevel2Password(e.target.value)}
                className="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="Nhập mật khẩu cấp 2"
              />
              {level2PasswordError && (
                <p className="mt-2 text-red-500">{level2PasswordError}</p>
              )}
            </div>
            <div className="flex justify-end space-x-3">
              <button
                onClick={() => setShowLevel2PasswordModal(false)}
                className="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-100"
                disabled={checkingLevel2Password}
              >
                Hủy
              </button>
              <button
                onClick={handleConfirmLevel2Password}
                className={`px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 ${
                  checkingLevel2Password ? "opacity-50 cursor-not-allowed" : ""
                }`}
                disabled={checkingLevel2Password}
              >
                {checkingLevel2Password ? "Đang xử lý..." : "Xác nhận"}
              </button>
            </div>
          </div>
        </div>
      )}

      {showProcessingPopup && (
        <motion.div
          className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
          variants={popupVariants}
          initial="hidden"
          animate="visible"
          exit="exit"
        >
          <motion.div className="bg-white rounded-lg p-6 max-w-md w-full text-center shadow-lg border border-blue-500 relative">
            <div className="absolute top-4 right-4">
              <img
                src="https://via.placeholder.com/60x20?text=VietQR"
                alt="VietQR Logo"
                className="h-5"
              />
            </div>

            {processing ? (
              <>
                <motion.div
                  className="flex justify-center mb-4"
                  variants={childVariants}
                >
                  <motion.svg
                    className="h-8 w-8 text-blue-500"
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                    variants={spinnerVariants}
                    animate="animate"
                  >
                    <circle
                      className="opacity-25"
                      cx="12"
                      cy="12"
                      r="10"
                      stroke="currentColor"
                      strokeWidth="4"
                    />
                    <path
                      className="opacity-75"
                      fill="currentColor"
                      d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"
                    />
                  </motion.svg>
                </motion.div>
                <motion.h3
                  className="text-lg font-semibold text-gray-800 mb-2"
                  variants={childVariants}
                >
                  Yêu cầu của bạn đang được xử lý
                </motion.h3>
                <motion.p
                  className="text-gray-600 mb-4"
                  variants={childVariants}
                >
                  Vui lòng đợi trong giây lát ...
                </motion.p>
                <motion.div
                  className="bg-gray-100 p-4 rounded-lg text-left"
                  variants={childVariants}
                >
                  <p className="text-gray-700 font-medium">
                    Số tiền rút:{" "}
                    <span className="text-green-600">
                      {formatMoney(parseFloat(formData.amount))}
                    </span>
                  </p>
                  <p className="text-gray-700 mt-2">
                    Ngân hàng:{" "}
                    <span className="font-medium">
                      {selectedBankAccount?.bank_name || "N/A"}
                    </span>
                  </p>
                  <p className="text-gray-700">
                    Số tài khoản:{" "}
                    <span className="font-medium">
                      {selectedBankAccount?.bank_account_number || "N/A"}
                    </span>
                  </p>
                  <p className="text-gray-700">
                    Chủ tài khoản:{" "}
                    <span className="font-medium">
                      {selectedBankAccount?.account_holder_name || "N/A"}
                    </span>
                  </p>
                </motion.div>
              </>
            ) : completed ? (
              <motion.div
                className="flex flex-col items-center"
                variants={childVariants}
              >
                <motion.button
                  onClick={handleComplete}
                  whileHover={{ scale: 1.1 }}
                  whileTap={{ scale: 0.9 }}
                  className="mb-4"
                >
                  <svg
                    className="h-16 w-16 text-green-500"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                  >
                    <path
                      strokeLinecap="round"
                      strokeLinejoin="round"
                      strokeWidth={2}
                      d="M5 13l4 4L19 7"
                    />
                  </svg>
                </motion.button>
                <motion.h3
                  className="text-lg font-semibold text-gray-800 mb-2"
                  variants={childVariants}
                >
                  Rút tiền thành công!
                </motion.h3>
                <motion.p
                  className="text-gray-600 mb-4"
                  variants={childVariants}
                >
                  Yêu cầu rút tiền của bạn đã được xử lý.
                </motion.p>
                <motion.div
                  className="bg-gray-100 p-4 rounded-lg text-left w-full"
                  variants={childVariants}
                >
                  <p className="text-gray-700 font-medium">
                    Số tiền rút:{" "}
                    <span className="text-green-600">
                      {formatMoney(parseFloat(formData.amount))}
                    </span>
                  </p>
                  <p className="text-gray-700 mt-2">
                    Ngân hàng:{" "}
                    <span className="font-medium">
                      {selectedBankAccount?.bank_name || "N/A"}
                    </span>
                  </p>
                  <p className="text-gray-700">
                    Số tài khoản:{" "}
                    <span className="font-medium">
                      {selectedBankAccount?.bank_account_number || "N/A"}
                    </span>
                  </p>
                  <p className="text-gray-700">
                    Chủ tài khoản:{" "}
                    <span className="font-medium">
                      {selectedBankAccount?.account_holder_name || "N/A"}
                    </span>
                  </p>
                </motion.div>
              </motion.div>
            ) : null}
          </motion.div>
        </motion.div>
      )}
    </motion.div>
  );
};

export default WalletWithdraw;
