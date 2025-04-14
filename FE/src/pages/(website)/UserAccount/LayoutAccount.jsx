import React, { useEffect } from "react";
import { Outlet } from "react-router-dom";
import Header from "../../../components/Header";
import Footer from "../../../components/Footer";
import { useLoading } from "../../../context/LoadingContext";
import { addApiLoadingListener, removeApiLoadingListener, hasActiveRequests } from "../../../service/api";

const LayoutAccount = () => {
  const { markApiAsLoaded } = useLoading();

  // Theo dõi việc tải API
  useEffect(() => {
    // Hàm xử lý khi trạng thái loading của API thay đổi
    const handleApiLoadingChange = (isLoading) => {
      if (!isLoading) {
        // Khi không còn API nào đang tải, đánh dấu là đã tải xong
        markApiAsLoaded();
      }
    };

    // Đăng ký listener
    addApiLoadingListener(handleApiLoadingChange);

    // Kiểm tra ngay lần đầu tiên nếu không có request nào đang chờ xử lý
    if (!hasActiveRequests()) {
      markApiAsLoaded();
    }

    // Cleanup
    return () => {
      removeApiLoadingListener(handleApiLoadingChange);
    };
  }, [markApiAsLoaded]);

  return (
    <>
      <Header />
      <div className="min-h-screen bg-gray-50 pb-12">
        <Outlet />
      </div>
    </>
  );
};

export default LayoutAccount;
