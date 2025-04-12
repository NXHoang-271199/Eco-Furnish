import { useEffect } from "react";
import { useNavigate } from "react-router-dom";

function OAuthCallback() {
  const navigate = useNavigate();

  useEffect(() => {
    // Lấy token và thông tin user từ query parameters
    const params = new URLSearchParams(window.location.search);
    const token = params.get("token");
    const refreshToken = params.get("refresh_token");
    const userJson = params.get("user");

    if (token && refreshToken && userJson) {
      try {
        // Lưu vào localStorage
        localStorage.setItem("authToken", token);
        localStorage.setItem("refreshToken", refreshToken);
        localStorage.setItem("userData", userJson);

        // Redirect về trang chủ
        navigate("/", { replace: true }); // Sử dụng replace để xóa callback khỏi lịch sử
      } catch (error) {
        console.error("Error processing OAuth callback:", error);
        navigate("/", { replace: true });
      }
    } else {
      // Nếu không có token, chuyển về trang đăng nhập
      console.error(
        "OAuth callback missing token, refreshToken, or user data."
      );
      navigate("/sign-in", { replace: true });
    }
    // Thêm dependency navigate để tránh warning
  }, [navigate]);

  return (
    <div className="flex min-h-screen items-center justify-center">
      <div className="text-center">
        <div className="mb-4 h-12 w-12 animate-spin rounded-full border-4 border-blue-600 border-t-transparent mx-auto"></div>
        <h2 className="text-xl font-semibold text-gray-800">
          Đang xử lý đăng nhập...
        </h2>
        <p className="mt-2 text-gray-600">Vui lòng đợi trong giây lát.</p>
      </div>
    </div>
  );
}

export default OAuthCallback;
