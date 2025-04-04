import React from "react";
import { useState } from "react";
import axios from "axios";
const ForgotPasswordModal = ({ isOpen, onClose }) => {
  const [email, setEmail] = useState("");
  const [message, setMessage] = useState("");

  const handleSubmit = async (e) => {
    e.preventDefault();
    try {
      const response = await axios.post(
        "http://127.0.0.1:8000/api/users/forgot-password",
        { email }
      );
      console.log("Response:", response.data);

      setEmail(response.data.data.email);
      setMessage(
        "Chúng tôi đã gửi liên kết đặt lại mật khẩu vào email của bạn."
      );
    } catch (error) {
      setMessage(error.response?.data?.message || "Có lỗi xảy ra!");
    }
  };

  if (!isOpen) return null;

  return (
    <div className="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
      <form
        onSubmit={handleSubmit}
        className="bg-white p-6 rounded-lg shadow-lg w-96"
      >
        <h2 className="text-xl font-semibold text-gray-800 mb-4">
          Quên mật khẩu
        </h2>
        <p className="text-gray-600 text-sm mb-4">
          Nhập email của bạn và chúng tôi sẽ gửi liên kết đặt lại mật khẩu.
        </p>
        <input
          type="email"
          placeholder="Nhập email của bạn"
          className="w-full border p-2 rounded-md mb-4"
          onChange={(e) => setEmail(e.target.value)}
          required
        />
        {message && <p className="text-red-500 text-sm mb-4">{message}</p>}
        <div className="flex justify-end">
          <button
            className="bg-gray-300 px-4 py-2 rounded-md mr-2"
            onClick={onClose}
          >
            Hủy
          </button>
          <button
            type="submit"
            className="bg-blue-500 text-white px-4 py-2 rounded-md"
          >
            Gửi
          </button>
        </div>
      </form>
    </div>
  );
};

export default ForgotPasswordModal;
