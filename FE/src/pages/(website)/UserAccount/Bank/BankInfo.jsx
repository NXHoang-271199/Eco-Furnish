import React, { useState, useEffect, useRef } from "react";
import { motion, AnimatePresence } from "framer-motion";
import axiosInstance from "../../../../utils/axiosConfig";
import { toast } from "react-hot-toast";

const BankInfo = () => {
  const [bankAccounts, setBankAccounts] = useState([]);
  const [banks, setBanks] = useState([]);
  const [loading, setLoading] = useState(false);
  const [lookupLoading, setLookupLoading] = useState(false);
  const [error, setError] = useState("");
  const [editingAccount, setEditingAccount] = useState(null);
  const [formData, setFormData] = useState({
    bank_code: "",
    bank_name: "",
    bank_account_number: "",
    account_holder_name: "",
    is_default: false,
    acq_id: "",
    bank_logo_url: "",
  });
  const [isDropdownOpen, setIsDropdownOpen] = useState(false);
  const dropdownRef = useRef(null);

  // Animation variants
  const cardVariants = {
    hidden: { opacity: 0, y: 20 },
    visible: { opacity: 1, y: 0, transition: { duration: 0.5 } },
  };

  const itemVariants = {
    hidden: { opacity: 0, x: -20 },
    visible: { opacity: 1, x: 0, transition: { duration: 0.3 } },
  };

  const alertVariants = {
    hidden: { opacity: 0, y: -20 },
    visible: { opacity: 1, y: 0, transition: { duration: 0.3 } },
  };

  // Fetch bank accounts and banks list
  useEffect(() => {
    const fetchData = async () => {
      try {
        setLoading(true);
        const token = localStorage.getItem("authToken");

        // Fetch bank accounts
        const accountsResponse = await axiosInstance.get("/bank-accounts", {
          headers: { Authorization: `Bearer ${token}` },
        });
        setBankAccounts(accountsResponse.data.data || []);

        // Fetch banks list
        const banksResponse = await axiosInstance.get("/banks", {
          headers: { Authorization: `Bearer ${token}` },
        });
        console.log("Banks data:", banksResponse.data); // Debug dữ liệu banks
        setBanks(banksResponse.data || []);
      } catch (err) {
        setError(
          err.response?.data?.message ||
            "Không thể lấy thông tin tài khoản ngân hàng hoặc danh sách ngân hàng"
        );
        toast.error("Không thể lấy thông tin");
      } finally {
        setLoading(false);
      }
    };

    fetchData();
  }, []);

  // Handle click outside to close dropdown
  useEffect(() => {
    const handleClickOutside = (event) => {
      if (dropdownRef.current && !dropdownRef.current.contains(event.target)) {
        setIsDropdownOpen(false);
      }
    };
    document.addEventListener("mousedown", handleClickOutside);
    return () => document.removeEventListener("mousedown", handleClickOutside);
  }, []);

  // Handle bank selection
  const handleBankSelect = (bank) => {
    setFormData((prev) => ({
      ...prev,
      bank_code: bank.code,
      bank_name: bank.name,
      acq_id: bank.bin,
      bank_logo_url: bank.logo,
    }));
    setIsDropdownOpen(false);
    lookupAccountHolder();
  };

  // Handle form input change
  const handleInputChange = (e) => {
    const { name, value } = e.target;
    if (name === "bank_code") {
      const selectedBank = banks.find((bank) => bank.code === value);
      setFormData((prev) => ({
        ...prev,
        bank_code: value,
        bank_name: selectedBank ? selectedBank.name : "",
        acq_id: selectedBank ? selectedBank.id : "",
        bank_logo_url: selectedBank ? selectedBank.logo : "",
      }));
    } else {
      setFormData((prev) => ({ ...prev, [name]: value }));
    }
  };

  // Lookup account holder name
  const lookupAccountHolder = async () => {
    if (!formData.bank_code || !formData.bank_account_number) return;

    try {
      setLookupLoading(true);
      const token = localStorage.getItem("authToken");
      const response = await axiosInstance.post(
        "/bank-accounts/lookup",
        {
          bank_code: formData.bank_code,
          bank_account_number: formData.bank_account_number,
        },
        {
          headers: { Authorization: `Bearer ${token}` },
        }
      );

      setFormData((prev) => ({
        ...prev,
        account_holder_name: response.data.accountName || "",
      }));
    } catch (err) {
      toast.error("Không thể tra cứu tên chủ tài khoản");
      setFormData((prev) => ({ ...prev, account_holder_name: "" }));
    } finally {
      setLookupLoading(false);
    }
  };

  // Handle set default
  const handleSetDefault = async (accountId) => {
    try {
      const token = localStorage.getItem("authToken");
      const response = await axiosInstance.put(
        `/bank-accounts/${accountId}`,
        { is_default: true },
        {
          headers: { Authorization: `Bearer ${token}` },
        }
      );

      setBankAccounts((prev) =>
        prev.map((account) =>
          account.id === accountId
            ? { ...account, is_default: true }
            : { ...account, is_default: false }
        )
      );
      toast.success("Đặt tài khoản mặc định thành công!");
    } catch (err) {
      toast.error("Không thể đặt tài khoản mặc định");
    }
  };

  // Handle edit account
  const handleEdit = (account) => {
    setEditingAccount(account);
    setFormData({
      bank_code: account.bank_code,
      bank_name: account.bank_name,
      bank_account_number: account.bank_account_number,
      account_holder_name: account.account_holder_name,
      is_default: account.is_default,
      acq_id: account.acq_id,
      bank_logo_url: account.bank_logo_url || "",
    });
  };

  // Handle save edited account
  const handleSaveEdit = async (e) => {
    e.preventDefault();
    try {
      const token = localStorage.getItem("authToken");

      // Nếu đang chỉnh sửa và chưa có logo, thử lấy từ danh sách banks
      if (!formData.bank_logo_url && formData.bank_code) {
        const selectedBank = banks.find(
          (bank) => bank.code === formData.bank_code
        );
        if (selectedBank && selectedBank.logo) {
          formData.bank_logo_url = selectedBank.logo;
        }
      }

      const response = await axiosInstance.put(
        `/bank-accounts/${editingAccount.id}`,
        formData,
        {
          headers: { Authorization: `Bearer ${token}` },
        }
      );

      setBankAccounts((prev) =>
        prev.map((account) =>
          account.id === editingAccount.id ? response.data.data : account
        )
      );
      setEditingAccount(null);
      setFormData({
        bank_code: "",
        bank_name: "",
        bank_account_number: "",
        account_holder_name: "",
        is_default: false,
        acq_id: "",
        bank_logo_url: "",
      });
      toast.success("Cập nhật tài khoản ngân hàng thành công!");
    } catch (err) {
      const errorMessage =
        err.response?.data?.message || "Không thể cập nhật tài khoản ngân hàng";
      toast.error(errorMessage);
      setError(errorMessage);
    }
  };

  // Handle delete account
  const handleDelete = async (accountId) => {
    try {
      const token = localStorage.getItem("authToken");
      await axiosInstance.delete(`/bank-accounts/${accountId}`, {
        headers: { Authorization: `Bearer ${token}` },
      });

      setBankAccounts((prev) =>
        prev.filter((account) => account.id !== accountId)
      );
      toast.success("Xóa tài khoản ngân hàng thành công!");
    } catch (err) {
      const errorMessage =
        err.response?.data?.message || "Không thể xóa tài khoản ngân hàng";
      toast.error(errorMessage);
      setError(errorMessage);
    }
  };

  // Handle add new account
  const handleAddAccount = async (e) => {
    e.preventDefault();
    try {
      // Kiểm tra dữ liệu trước khi gửi
      if (
        !formData.bank_code ||
        !formData.bank_name ||
        !formData.bank_account_number ||
        !formData.account_holder_name
      ) {
        toast.error("Vui lòng điền đầy đủ thông tin");
        return;
      }

      // Nếu chưa có logo, thử lấy từ danh sách banks
      if (!formData.bank_logo_url && formData.bank_code) {
        const selectedBank = banks.find(
          (bank) => bank.code === formData.bank_code
        );
        if (selectedBank && selectedBank.logo) {
          formData.bank_logo_url = selectedBank.logo;
        }
      }

      const token = localStorage.getItem("authToken");
      const response = await axiosInstance.post("/bank-accounts", formData, {
        headers: { Authorization: `Bearer ${token}` },
      });

      setBankAccounts((prev) => [...prev, response.data.data]);
      setFormData({
        bank_code: "",
        bank_name: "",
        bank_account_number: "",
        account_holder_name: "",
        is_default: false,
        acq_id: "",
        bank_logo_url: "",
      });
      toast.success("Thêm tài khoản ngân hàng thành công!");
    } catch (err) {
      const errorMessage =
        err.response?.data?.message || "Không thể thêm tài khoản ngân hàng";
      toast.error(errorMessage);
      setError(errorMessage);
    }
  };

  // Get bank logo by bank_code
  const getBankLogo = (bankCode) => {
    const bank = banks.find((b) => b.code === bankCode);
    const logo = bank?.logo || "";
    console.log(`Logo for bank ${bankCode}:`, logo); // Debug URL logo
    return logo;
  };

  return (
    <motion.div
      className="max-w-4xl mx-auto p-6"
      variants={cardVariants}
      initial="hidden"
      animate="visible"
    >
      <div className="bg-white shadow-lg rounded-lg p-6">
        <h2 className="text-2xl font-bold mb-4 text-gray-800">
          Tài khoản ngân hàng
        </h2>

        {/* Loading spinner */}
        {loading && (
          <div className="flex justify-center mb-4">
            <svg
              className="animate-spin h-8 w-8 text-blue-500"
              xmlns="http://www.w3.org/2000/svg"
              fill="none"
              viewBox="0 0 24 24"
            >
              <circle
                className="opacity-25"
                cx="12"
                cy="12"
                r="10"
                stroke="currentColor"
                strokeWidth="4"
              ></circle>
              <path
                className="opacity-75"
                fill="currentColor"
                d="M4 12a8 8 0 018-8v8h8a8 8 0 01-8 8 8 8 0 01-8-8z"
              ></path>
            </svg>
          </div>
        )}

        {/* Error message */}
        <AnimatePresence>
          {error && (
            <motion.div
              variants={alertVariants}
              initial="hidden"
              animate="visible"
              exit="hidden"
              className="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded"
            >
              {error}
            </motion.div>
          )}
        </AnimatePresence>

        {/* Danh sách tài khoản ngân hàng */}
        <div className="mb-8">
          {bankAccounts.length === 0 ? (
            <div className="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 rounded">
              Bạn chưa có tài khoản ngân hàng nào. Hãy thêm tài khoản bên dưới.
            </div>
          ) : (
            <div className="space-y-4">
              <AnimatePresence>
                {bankAccounts.map((account) => (
                  <motion.div
                    key={account.id}
                    variants={itemVariants}
                    initial="hidden"
                    animate="visible"
                    exit="hidden"
                    className="p-4 border rounded-lg shadow-sm bg-gray-50 flex justify-between items-center"
                  >
                    <div className="flex items-center">
                      <img
                        src={getBankLogo(account.bank_code)}
                        alt={`${account.bank_name} logo`}
                        className="w-20 h-18 mr-2" // Kích thước logo ban đầu
                        onError={(e) => (e.target.style.display = "none")} // Ẩn nếu logo không tải được
                      />
                      <div>
                        <p className="text-gray-800 font-semibold">
                          {account.bank_name}{" "}
                          {account.is_default && (
                            <span className="ml-2 inline-block px-2 py-1 text-xs font-medium text-white bg-green-500 rounded">
                              Mặc định
                            </span>
                          )}
                        </p>
                        <p className="text-gray-600">
                          <strong>Số tài khoản:</strong>{" "}
                          {account.bank_account_number}
                        </p>
                        <p className="text-gray-600">
                          <strong>Chủ tài khoản:</strong>{" "}
                          {account.account_holder_name}
                        </p>
                      </div>
                    </div>
                    <div className="flex space-x-2">
                      {!account.is_default && (
                        <button
                          onClick={() => handleSetDefault(account.id)}
                          className="text-blue-500 hover:text-blue-700 font-medium"
                        >
                          Đặt làm mặc định
                        </button>
                      )}
                      <button
                        onClick={() => handleEdit(account)}
                        className="text-yellow-500 hover:text-yellow-700 font-medium"
                      >
                        Chỉnh sửa
                      </button>
                      <button
                        onClick={() => handleDelete(account.id)}
                        className="text-red-500 hover:text-red-700 font-medium"
                      >
                        Xóa
                      </button>
                    </div>
                  </motion.div>
                ))}
              </AnimatePresence>
            </div>
          )}
        </div>

        {/* Form thêm/chỉnh sửa tài khoản ngân hàng */}
        <div className="bg-gray-100 p-6 rounded-lg">
          <h3 className="text-lg font-semibold mb-4 text-gray-800">
            {editingAccount
              ? "Chỉnh sửa tài khoản ngân hàng"
              : "Thêm tài khoản ngân hàng"}
          </h3>
          <form onSubmit={editingAccount ? handleSaveEdit : handleAddAccount}>
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
              <div ref={dropdownRef}>
                <label
                  htmlFor="bank_code"
                  className="block text-gray-700 font-medium mb-2"
                >
                  Tên ngân hàng
                </label>
                <div className="relative">
                  <div
                    className="w-full p-3 border rounded-lg bg-white cursor-pointer flex items-center justify-between"
                    onClick={() => setIsDropdownOpen(!isDropdownOpen)}
                  >
                    {formData.bank_code ? (
                      <div className="flex items-center">
                        <img
                          src={
                            banks.find(
                              (bank) => bank.code === formData.bank_code
                            )?.logo
                          }
                          alt="Bank logo"
                          className="w-20 h-18 mr-2" // Kích thước logo ban đầu
                          onError={(e) => (e.target.style.display = "none")} // Ẩn nếu logo không tải được
                        />
                        <span>
                          {formData.bank_name} ({formData.bank_code})
                        </span>
                      </div>
                    ) : (
                      <span className="text-gray-500">Chọn ngân hàng</span>
                    )}
                    <svg
                      className={`w-5 h-5 transform transition-transform ${
                        isDropdownOpen ? "rotate-180" : ""
                      }`}
                      fill="none"
                      stroke="currentColor"
                      viewBox="0 0 24 24"
                      xmlns="http://www.w3.org/2000/svg"
                    >
                      <path
                        strokeLinecap="round"
                        strokeLinejoin="round"
                        strokeWidth="2"
                        d="M19 9l-7 7-7-7"
                      />
                    </svg>
                  </div>
                  {isDropdownOpen && (
                    <div className="absolute z-10 w-full mt-1 bg-white border rounded-lg shadow-lg max-h-80 overflow-y-auto">
                      {banks.map((bank) => (
                        <div
                          key={bank.code}
                          className="flex items-center p-3 hover:bg-gray-100 cursor-pointer"
                          onClick={() => handleBankSelect(bank)}
                        >
                          <img
                            src={bank.logo}
                            alt={`${bank.name} logo`}
                            className="w-18 h-8 mr-2" // Kích thước logo ban đầu
                            onError={(e) => (e.target.style.display = "none")} // Ẩn nếu logo không tải được
                          />
                          <span>
                            {bank.name} ({bank.code})
                          </span>
                        </div>
                      ))}
                    </div>
                  )}
                </div>
              </div>
              <div>
                <label
                  htmlFor="bank_account_number"
                  className="block text-gray-700 font-medium mb-2"
                >
                  Số tài khoản
                </label>
                <input
                  type="text"
                  id="bank_account_number"
                  name="bank_account_number"
                  value={formData.bank_account_number}
                  onChange={handleInputChange}
                  onBlur={lookupAccountHolder}
                  className="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                  placeholder="Nhập số tài khoản"
                  required
                />
              </div>
              <div>
                <label
                  htmlFor="account_holder_name"
                  className="block text-gray-700 font-medium mb-2"
                >
                  Chủ tài khoản
                </label>
                <input
                  type="text"
                  id="account_holder_name"
                  name="account_holder_name"
                  value={formData.account_holder_name}
                  onChange={handleInputChange}
                  className="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                  placeholder="Tên chủ tài khoản (tự động điền)"
                  disabled
                  required
                />
                {lookupLoading && (
                  <span className="text-sm text-gray-500">Đang tra cứu...</span>
                )}
              </div>
            </div>
            <div className="flex space-x-4">
              <motion.button
                type="submit"
                whileHover={{ scale: 1.05 }}
                whileTap={{ scale: 0.95 }}
                className="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600"
              >
                {editingAccount ? "Lưu thay đổi" : "Thêm tài khoản"}
              </motion.button>
              {editingAccount && (
                <motion.button
                  type="button"
                  whileHover={{ scale: 1.05 }}
                  whileTap={{ scale: 0.95 }}
                  onClick={() => {
                    setEditingAccount(null);
                    setFormData({
                      bank_code: "",
                      bank_name: "",
                      bank_account_number: "",
                      account_holder_name: "",
                      is_default: false,
                      acq_id: "",
                      bank_logo_url: "",
                    });
                  }}
                  className="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400"
                >
                  Hủy
                </motion.button>
              )}
            </div>
          </form>
        </div>
      </div>
    </motion.div>
  );
};

export default BankInfo;
