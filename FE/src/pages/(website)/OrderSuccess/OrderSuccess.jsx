import React, { useState, useEffect } from "react";
import { Link, useLocation } from "react-router-dom";

const OrderSuccess = () => {
  const [orderInfo, setOrderInfo] = useState(null);
  const location = useLocation();

  useEffect(() => {
    const queryParams = new URLSearchParams(location.search);
    console.log("URL Params:", Object.fromEntries(queryParams.entries()));

    // 1. Luôn lấy thông tin đơn hàng từ localStorage trước
    let orderData = JSON.parse(localStorage.getItem("orderInfo"));
    console.log("Initial orderData from localStorage:", orderData);

    // 2. Kiểm tra mã đơn hàng trong URL params (từ MoMo/VNPAY)
    const momoOrderId = queryParams.get("orderId");
    const vnpayOrderId = queryParams.get("vnp_TxnRef");
    const extraOrderId = queryParams.get("extraData");
    const partnerRef = queryParams.get("partnerRef");
    const orderCodeFromUrl =
      vnpayOrderId || momoOrderId || extraOrderId || partnerRef;

    // 3. Nếu có mã từ URL, cập nhật orderData và localStorage *chỉ khi* nó khác mã đã lưu
    if (orderData && orderCodeFromUrl) {
      if (orderData.order_code !== orderCodeFromUrl) {
        console.log(
          `Updating order_code from URL: ${orderCodeFromUrl} (was ${orderData.order_code})`
        );
        orderData.order_code = orderCodeFromUrl;
        // Lưu lại vào localStorage nếu có sự thay đổi từ URL
        localStorage.setItem("orderInfo", JSON.stringify(orderData));
      }
    }

    // 4. Set state với dữ liệu đơn hàng (đã được cập nhật nếu cần)
    setOrderInfo(orderData);
    console.log("Final orderInfo state set:", orderData);
  }, [location]);

  const formatPrice = (price) => {
    return new Intl.NumberFormat("vi-VN", {
      style: "currency",
      currency: "VND",
    }).format(price);
  };

  const formatDate = (dateString) => {
    const options = {
      weekday: "long",
      year: "numeric",
      month: "long",
      day: "numeric",
    };
    return new Date(dateString).toLocaleDateString("vi-VN", options);
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
            Đơn hàng của bạn đã được thanh toán thành công và sẽ được chuẩn bị.
          </p>

          {orderInfo && (
            <>
              <div className="flex justify-center space-x-4 my-5">
                {orderInfo.products &&
                  orderInfo.products.slice(0, 3).map((item, index) => (
                    <div className="relative" key={index}>
                      <img
                        src={`http://localhost:8000/storage/${item.product?.image_thumnail}`}
                        alt={item.product?.name}
                        className="rounded-lg w-12 h-12 object-cover"
                        onError={(e) => {
                          e.target.src = "https://picsum.photos/200/300";
                        }}
                      />
                      <span className="absolute -top-2 -right-2 bg-black text-white text-xs w-5 h-5 flex items-center justify-center rounded-full">
                        {item.quantity}
                      </span>
                    </div>
                  ))}
                {orderInfo.products && orderInfo.products.length > 3 && (
                  <div className="relative">
                    <div className="rounded-lg w-12 h-12 bg-gray-200 flex items-center justify-center">
                      <span className="text-gray-700 font-bold">
                        +{orderInfo.products.length - 3}
                      </span>
                    </div>
                  </div>
                )}
              </div>

              <div className="mt-4 flex justify-center">
                <div className="text-left space-y-2">
                  <p>
                    <span className="font-semibold">Mã đơn hàng:</span>{" "}
                    {orderInfo.order_code || "Đang cập nhật..."}
                  </p>
                  <p>
                    <span className="font-semibold">Ngày:</span>{" "}
                    {orderInfo.order_date
                      ? formatDate(orderInfo.order_date)
                      : formatDate(new Date())}
                  </p>
                  <p>
                    <span className="font-semibold">Tổng cộng:</span>{" "}
                    {formatPrice(orderInfo.total)}
                  </p>
                  <p>
                    <span className="font-semibold">
                      Phương thức thanh toán:
                    </span>{" "}
                    {orderInfo.payment_method === "MoMo"
                      ? "MoMo"
                      : orderInfo.payment_method === "VNPAY"
                        ? "VNPAY"
                        : orderInfo.payment_method === "Ví"
                          ? "Ví"
                          : orderInfo.payment_method === "Tiền mặt"
                            ? "Tiền mặt (COD)"
                            : "Đang cập nhật..."}
                  </p>
                </div>
              </div>
            </>
          )}

          <div className="flex justify-center my-8 gap-x-20">
            <Link
              to="/"
              className="mt-6 inline-block bg-black text-white px-6 py-2 rounded-lg hover:bg-gray-800 transition"
            >
              Trang chủ
            </Link>
            <Link
              to="/account/list_order"
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
