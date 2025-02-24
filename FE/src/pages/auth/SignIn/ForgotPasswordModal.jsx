import React from "react";

const ForgotPasswordModal = ({ isOpen, onClose }) => {
  if (!isOpen) return null;

  return (
    <div className="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
      <div className="bg-white p-6 rounded-lg shadow-lg w-96">
        <h2 className="text-xl font-semibold text-gray-800 mb-4">Quên mật khẩu</h2>
        <p className="text-gray-600 text-sm mb-4">
          Nhập email của bạn và chúng tôi sẽ gửi liên kết đặt lại mật khẩu.
        </p>
        <input
          type="email"
          placeholder="Nhập email của bạn"
          className="w-full border p-2 rounded-md mb-4"
        />
        <div className="flex justify-end">
          <button
            className="bg-gray-300 px-4 py-2 rounded-md mr-2"
            onClick={onClose}
          >
            Hủy
          </button>
          <button className="bg-blue-500 text-white px-4 py-2 rounded-md">
            Gửi
          </button>
        </div>
      </div>
    </div>
  );
};

export default ForgotPasswordModal;
