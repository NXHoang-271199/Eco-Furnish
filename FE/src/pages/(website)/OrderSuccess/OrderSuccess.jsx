import React from "react";
import { Link } from "react-router-dom";

const OrderSuccess = () => {
  return (
    <>
      <div className=" max-w-6xl my-24 mx-auto p-4 ">
        <h1 class="text-5xl text-center font-semibold text-gray-900 my-7">
          Hoàn thành!
        </h1>

        <div className="max-w-3xl w-full mx-auto bg-white shadow-lg rounded-lg p-6 text-center relative overflow-hidden">
          <p className="text-gray-600 mt-2 font-semibold text-3xl my-4">
            Cảm ơn bạn! 🎉
          </p>
          <p className="text-gray-700 mt-1 text-lg">
            Đơn hàng của bạn sẽ được chuẩn bị.
          </p>

          <div className="flex justify-center space-x-4 my-5">
            <div className="relative">
              <img
                src="https://picsum.photos/200/300"
                alt="Sản phẩm 1"
                className="rounded-lg w-12 h-12"
              />
              <span className="absolute -top-2 -right-2 bg-black text-white text-xs w-5 h-5 flex items-center justify-center rounded-full">
                1
              </span>
            </div>
            <div className="relative">
              <img
                src="https://picsum.photos/200/300"
                alt="Sản phẩm 2"
                className="rounded-lg w-12 h-12"
              />
              <span className="absolute -top-2 -right-2 bg-black text-white text-xs w-5 h-5 flex items-center justify-center rounded-full">
                1
              </span>
            </div>
          </div>

          <div className=" mt-4 flex justify-center">
            <div className="text-left space-y-2">
              <p>
                <span className="font-semibold">Mã đơn hàng:</span>{" "}
                ORD-67E3C374-DQG6N4
              </p>
              <p>
                <span className="font-semibold">Ngày:</span> Thứ Tư, 26 tháng 3,
                2025
              </p>
              <p>
                <span className="font-semibold">Tổng cộng:</span> 1.499.000 đ
              </p>
              <p>
                <span className="font-semibold">Phương thức thanh toán:</span>{" "}
                Thanh toán khi nhận hàng
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
              to="/list-order"
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
