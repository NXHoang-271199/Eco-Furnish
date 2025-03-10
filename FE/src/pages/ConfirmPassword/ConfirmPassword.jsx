import { useState } from "react";

const ConfirmPassword = () => {
  const [password, setPassword] = useState("");

  const handleSubmit = (e) => {
    e.preventDefault();
    // Xác nhận mật khẩu trước khi thực hiện thao tác quan trọng
  };

  return (
    <div className="flex items-center justify-center h-screen bg-gray-100">
      <div className="bg-white p-8 shadow-lg rounded-lg w-96">
        <h2 className="text-2xl font-bold text-center mb-4">Xác Nhận Mật Khẩu</h2>
        <p className="text-gray-600 text-center mb-4">Vui lòng nhập mật khẩu để tiếp tục.</p>
        <form onSubmit={handleSubmit}>
          <input 
            type="password" 
            className="w-full px-4 py-2 border rounded-md mb-3" 
            placeholder="Mật khẩu" 
            value={password} 
            onChange={(e) => setPassword(e.target.value)} 
            required 
          />
          <button 
            type="submit" 
            className="w-full bg-blue-600 text-white py-2 rounded-md hover:bg-blue-700"
          >
            Xác nhận
          </button>
        </form>
      </div>
    </div>
  );
};

export default ConfirmPassword;
