import { useEffect, useState } from "react";
import { useSearchParams, useNavigate } from "react-router-dom";
import axios from "axios";

const EmailVerification = () => {
  const [searchParams] = useSearchParams();
  const navigate = useNavigate();
  const [verifying, setVerifying] = useState(true);

  useEffect(() => {
    const verifyEmail = async () => {
      try {
        const token = searchParams.get("token");
        const email = decodeURIComponent(searchParams.get("email"));
        console.log("Token:", token);
        console.log("Email:", email);

        if (!token || !email) {
          alert("Link xác thực không hợp lệ");
          navigate("/signin");
          return;
        }

        const response = await axios.post(
          `http://127.0.0.1:8000/api/users/verify-email`,
          {
            verify_token: token,
            email: email,
          }
        );

        if (response.data.status === "success") {
          alert("Xác thực email thành công! Vui lòng đăng nhập.");
          navigate("/signin");
        }
      } catch (error) {
        console.error("Lỗi xác thực:", error);
        alert("Xác thực email thất bại. Vui lòng thử lại sau.");
        navigate("/signin");
      } finally {
        setVerifying(false);
      }
    };

    verifyEmail();
  }, [searchParams, navigate]);

  if (verifying) {
    return <div>Đang xác thực email...</div>;
  }
  return null;
};
export default EmailVerification;
