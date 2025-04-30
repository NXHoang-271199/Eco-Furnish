import React from "react";
import { useNavigate } from "react-router-dom";

const SessionExpiredPopup = ({ isOpen, onClose }) => {
  const navigate = useNavigate();

  // Hàm xử lý khi người dùng nhấn "Quay về trang đăng nhập"
  const handleRelogin = () => {
    localStorage.removeItem("authToken");
    localStorage.removeItem("refreshToken");
    localStorage.removeItem("userData");
    navigate("/sign-in", { replace: true });
    onClose(); // Đóng popup
  };

  if (!isOpen) return null;

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
      <div className="bg-white p-6 rounded-lg shadow-lg max-w-md w-full">
        <h2 className="text-xl font-bold text-gray-800 mb-4">
          Phiên đăng nhập đã hết hạn
        </h2>
        <p className="text-gray-600 mb-6">
          Phiên đăng nhập của bạn đã hết hạn. Vui lòng đăng nhập lại để tiếp
          tục.
        </p>
        <div className="flex justify-end">
          <button
            onClick={handleRelogin}
            className="py-2 px-4 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition duration-200"
          >
            Quay về trang đăng nhập
          </button>
        </div>
      </div>
    </div>
  );
};

export default SessionExpiredPopup;
