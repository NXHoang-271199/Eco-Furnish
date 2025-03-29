import React, { useState, useEffect } from "react";
import { Link } from "react-router-dom";

const OrderSuccess = () => {
  const [orderInfo, setOrderInfo] = useState({
    order_id: "",
    total: 0,
    items: 0,
    shipping_method: "",
    payment_method: "",
  });

  useEffect(() => {
    // Lấy thông tin đơn hàng từ localStorage
    const lastOrderInfo = localStorage.getItem("lastOrderInfo");
    if (lastOrderInfo) {
      setOrderInfo(JSON.parse(lastOrderInfo));
    }

    // Lấy ngày hiện tại để hiển thị
    const today = new Date();
    const options = {
      weekday: "long",
      year: "numeric",
      month: "long",
      day: "numeric",
    };

    setCurrentDate(new Intl.DateTimeFormat("vi-VN", options).format(today));
  }, []);

  const [currentDate, setCurrentDate] = useState("");

  const formatPrice = (price) => {
    return new Intl.NumberFormat("vi-VN", {
      style: "currency",
      currency: "VND",
    }).format(price);
  };

  const getPaymentMethodText = (method) => {
    switch (method) {
      case "COD":
        return "Thanh toán khi nhận hàng";
      case "VNPAY":
        return "Thanh toán qua VNPAY";
      case "MoMo":
        return "Thanh toán qua Ví MoMo";
      default:
        return method;
    }
  };

  return (
    <>
      <div className="max-w-6xl my-24 mx-auto p-4">
        <h1 className="text-5xl text-center font-semibold text-gray-900 my-7">
          Hoàn thành!
        </h1>

        <div className="max-w-3xl w-full mx-auto bg-white shadow-lg rounded-lg p-6 text-center relative overflow-hidden">
          <p className="text-gray-600 mt-2 font-semibold text-3xl my-4">
            Cảm ơn bạn! 🎉
          </p>
          <p className="text-gray-700 mt-1 text-lg">
            Đơn hàng của bạn đã được tiếp nhận và sẽ được chuẩn bị.
          </p>

          <div className="flex justify-center space-x-4 my-5">
            <div className="relative">
              <div className="rounded-lg w-16 h-16 bg-gray-200 flex items-center justify-center">
                <span className="text-xl font-bold">{orderInfo.items}</span>
              </div>
              <span className="absolute -top-2 -right-2 bg-black text-white text-xs w-5 h-5 flex items-center justify-center rounded-full">
                {orderInfo.items}
              </span>
            </div>
          </div>

          <div className="mt-4 flex justify-center">
            <div className="text-left space-y-2">
              <p>
                <span className="font-semibold">Mã đơn hàng:</span>{" "}
                {orderInfo.order_id || "Đang xử lý"}
              </p>
              <p>
                <span className="font-semibold">Ngày:</span> {currentDate}
              </p>
              <p>
                <span className="font-semibold">Tổng cộng:</span>{" "}
                {formatPrice(orderInfo.total)}
              </p>
              <p>
                <span className="font-semibold">Phương thức vận chuyển:</span>{" "}
                {orderInfo.shipping_method}
              </p>
              <p>
                <span className="font-semibold">Phương thức thanh toán:</span>{" "}
                {getPaymentMethodText(orderInfo.payment_method)}
              </p>
            </div>
          </div>

          <div className="flex justify-center my-8 gap-x-20">
            <Link
              to="/"
              className="mt-6 inline-block bg-black text-white px-6 py-2 rounded-lg hover:bg-gray-800 transition"
            >
              Trang chủ
            </Link>
            <Link
              to="/account/orders"
              className="mt-6 inline-block bg-black text-white px-6 py-2 rounded-lg hover:bg-gray-800 transition"
            >
              Lịch sử mua hàng
            </Link>
          </div>
        </div>
      </div>
    </>
  );
};

export default OrderSuccess;
