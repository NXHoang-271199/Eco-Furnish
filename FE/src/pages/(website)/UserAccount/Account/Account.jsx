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

        // Kiểm tra xem có slug không
        if (!userData.slug) {
          console.error(
            "Không tìm thấy slug trong dữ liệu người dùng:",
            userData
          );
          throw new Error("Không thể xác định ID người dùng");
        }

        console.log("Slug người dùng:", userData.slug);

        // Gọi API để lấy thông tin chi tiết của người dùng
        const response = await axios.get(
          `http://localhost:8000/api/users/${userData.slug}`,
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
            localStorage.removeItem("authToken");
            localStorage.removeItem("userData");
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
    <div className="account-container">
      <h1 className="account-title">Thông tin tài khoản</h1>

      {user ? (
        <div className="user-info">
          <div className="user-avatar">
            {user.avatar ? (
              <img
                src={user.avatar}
                alt="Avatar"
                className="avatar-image"
                onError={(e) => {
                  e.target.onerror = null;
                  e.target.src = "/images/default-avatar.png"; // Hình ảnh mặc định cục bộ
                }}
              />
            ) : (
              <div className="default-avatar">
                {user.name ? user.name.charAt(0).toUpperCase() : "U"}
              </div>
            )}
          </div>

          <div className="user-details">
            <div className="info-row">
              <span className="info-label">Họ tên:</span>
              <span className="info-value">{user.name || "Chưa cập nhật"}</span>
            </div>

            <div className="info-row">
              <span className="info-label">Email:</span>
              <span className="info-value">
                {user.email || "Chưa cập nhật"}
              </span>
            </div>

            {user.joined_date && (
              <div className="info-row">
                <span className="info-label">Ngày tham gia:</span>
                <span className="info-value">{user.joined_date}</span>
              </div>
            )}

            {user.phone && (
              <div className="info-row">
                <span className="info-label">Số điện thoại:</span>
                <span className="info-value">{user.phone}</span>
              </div>
            )}

            {user.address && (
              <div className="info-row">
                <span className="info-label">Địa chỉ:</span>
                <span className="info-value">{user.address}</span>
              </div>
            )}
          </div>

          <div className="account-actions">
            <button className="edit-profile-btn">Chỉnh sửa thông tin</button>
            <button className="change-password-btn">Đổi mật khẩu</button>
          </div>
        </div>
      ) : (
        <div className="no-user-data">Không tìm thấy thông tin người dùng</div>
      )}
    </div>
  );
};

export default Account;
