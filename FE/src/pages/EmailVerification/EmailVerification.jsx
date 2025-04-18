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
          navigate("/sign-in");
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
          localStorage.setItem("authToken", response.data.data.access_token);
          localStorage.setItem(
            "refreshToken",
            response.data.data.refresh_token
          );
          localStorage.setItem(
            "userData",
            JSON.stringify({
              id: response.data.data.id,
              name: response.data.data.name,
              email: response.data.data.email,
            })
          );
          alert("Xác thực email thành công! Vui lòng đăng nhập.");
          navigate("/sign-in");
        }
      } catch (error) {
        navigate("/sign-in");
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
