import { useState } from "react";

const VerifyEmail = () => {
  const [resent, setResent] = useState(false);

  const handleResend = () => {
    setResent(true);
    // Gửi request xác minh email tại đây (API call)
  };

  return (
    <div className="flex items-center justify-center h-screen bg-gray-100">
      <div className="bg-white p-8 shadow-lg rounded-lg w-96 text-center">
        <h2 className="text-2xl font-bold mb-4">Xác Minh Email</h2>
        <p className="text-gray-600">Vui lòng kiểm tra email để xác minh tài khoản.</p>
        {resent && <p className="text-green-500 mt-4">Một email xác minh mới đã được gửi!</p>}
        <button 
          onClick={handleResend}
          className="w-full bg-blue-600 text-white py-2 rounded-md mt-6 hover:bg-blue-700"
        >
          Gửi lại email xác minh
        </button>
      </div>
    </div>
  );
};

export default VerifyEmail;
