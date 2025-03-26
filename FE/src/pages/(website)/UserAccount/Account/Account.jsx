import axios from "axios";
import React, { useEffect, useState } from "react";
import { useNavigate } from "react-router-dom";

const Account = () => {
  const [user, setUser] = useState(null);
  const [error, setError] = useState(null); // Thêm trạng thái lỗi
  const [loading, setLoading] = useState(true);
  const navigate = useNavigate();

  useEffect(() => {
    const fetchUserData = async () => {
      try {
        // Kiểm tra xem người dùng đã đăng nhập chưa
        const token = localStorage.getItem("authToken");
        console.log("Token từ localStorage:", token);

        if (!token || token === "undefined") {
          console.log("Token không hợp lệ, chuyển hướng đến trang đăng nhập");
          localStorage.removeItem("authToken");
          localStorage.removeItem("userData");
          navigate("/signin");
          return;
        }

        // Lấy thông tin người dùng từ localStorage
        const userDataStr = localStorage.getItem("userData");
        console.log("userData từ localStorage:", userDataStr);

        if (!userDataStr || userDataStr === "undefined") {
          console.log("Không tìm thấy dữ liệu người dùng trong localStorage");
          throw new Error("Dữ liệu người dùng không hợp lệ");
        }

        // Phân tích dữ liệu người dùng
        const userData = JSON.parse(userDataStr);

        // Kiểm tra xem có email không
        // if (!userData.email) {
        //   console.error(
        //     "Không tìm thấy email trong dữ liệu người dùng:",
        //     userData
        //   );
        //   throw new Error("Không thể xác định ID người dùng");
        // }

        console.log("Email người dùng:", userData.email);

        // Gọi API để lấy thông tin chi tiết của người dùng
        const response = await axios.get(
          `http://127.0.0.1:8000/api/users/${userData.id}`,
          {
            headers: {
              Authorization: `Bearer ${token}`,
              "Content-Type": "application/json",
              Accept: "application/json",
            },
          }
        );
        console.log(response.data);

        console.log("Dữ liệu người dùng từ API:", response.data);

        // Cập nhật state với dữ liệu người dùng
        // Lưu ý: Dựa vào cấu trúc dữ liệu bạn cung cấp, dữ liệu nằm trong response.data.data
        if (response.data.data) {
          setUser(response.data.data);
        } else {
          setUser(response.data);
        }

        setLoading(false);
      } catch (error) {
        console.error("Lỗi khi lấy thông tin người dùng:", error);

        if (error.response) {
          console.error(
            "Lỗi từ máy chủ:",
            error.response.status,
            error.response.data
          );

          if (error.response.status === 401) {
            console.log(
              "Token không hợp lệ hoặc hết hạn, đăng xuất và chuyển hướng"
            );
            localStorage.removeItem("userData");
            localStorage.removeItem("authToken");

            navigate("/signin");
          }

          setError(
            error.response.data?.message ||
              `Lỗi từ máy chủ: ${error.response.status}`
          );
        } else if (error.request) {
          console.error("Không nhận được phản hồi từ máy chủ");
          setError(
            "Không thể kết nối đến máy chủ. Vui lòng kiểm tra kết nối mạng."
          );
        } else {
          console.error("Lỗi khi thiết lập yêu cầu:", error.message);
          setError(error.message || "Đã xảy ra lỗi khi gửi yêu cầu.");
        }

        setLoading(false);
      }
    };

    fetchUserData();
  }, [navigate]);

  if (loading) {
    return <div className="loading">Đang tải thông tin...</div>;
  }

  if (error) {
    return <div className="error">{error}</div>;
  }

  return (
    <main class="w-full md:w-3/4 p-6">
      <h2 class="text-xl font-semibold mb-4">Chi tiết tài khoản</h2>
      <form>
        <div class="mb-4">
          <label class="block text-gray-600">Tên</label>
          <input
            type="text"
            value={user.name || "Chưa cập nhật"}
            class="w-full border-gray-300 rounded p-2"
          />
        </div>
        <div class="mb-4">
          <label class="block text-gray-600">Email</label>
          <input
            type="email"
            value={user.email || "Chưa cập nhật"}
            class="w-full border-gray-300 rounded p-2"
          />
        </div>
        {/* <div class="mb-4">
          <label class="block text-gray-600">Mật khẩu</label>
          <input
            type="password"
            value={user.password || "Chưa cập nhật"}
            class="w-full border-gray-300 rounded p-2"
          />
        </div> */}
        <div class="flex space-x-4">
          <button class="px-4 py-2 bg-gray-700 text-white rounded hover:bg-gray-900">
            Thay đổi
          </button>
          <button class="px-4 py-2 bg-gray-400 text-white rounded">
            Lưu lại
          </button>
        </div>
        {/* <button class="mt-6 px-4 py-2 bg-red-500 text-white rounded hover:bg-red-700">
          Xóa tài khoản
        </button> */}
      </form>
    </main>
  );
};

export default Account;
