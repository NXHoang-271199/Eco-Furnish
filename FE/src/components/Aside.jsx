import React from "react";
import { FaCamera } from "react-icons/fa";
const Aside = () => {
  return (
    <aside class="w-full md:w-1/4 bg-gray-200 p-6">
      <div class="flex flex-col items-center">
        <div class="relative">
          <img
            src="https://via.placeholder.com/100"
            alt="Avatar"
            class="rounded-full w-24 h-24"
          />
          <span class="absolute bottom-0 right-0 bg-black p-1 rounded-full text-white text-xs cursor-pointer">
            <FaCamera />
          </span>
        </div>
        {/* <h2 class="mt-3 font-bold">Name</h2> */}
      </div>
      <nav class="mt-6">
        <ul>
          <li class="py-2 border-b">
            <a href="/account" class="text-gray-700 hover:text-black">
              Tài khoản
            </a>
          </li>
          <li class="py-2 border-b">
            <a
              href="/account/change_password"
              class="text-gray-700 hover:text-black"
            >
              Thay đổi mật khẩu
            </a>
          </li>
          <li class="py-2 border-b">
            <a href="/account/address" class="text-gray-700 hover:text-black">
              Địa chỉ
            </a>
          </li>
          <li class="py-2 border-b">
            <a
              href="/account/list_order"
              class="text-gray-700 hover:text-black"
            >
              Đơn hàng
            </a>
          </li>
          {/* <li class="py-2 border-b">
            <a href="#" class="text-gray-700 hover:text-black">
              Yêu thích
            </a>
          </li> */}
          <li class="py-2">
            <a href="#" class="text-red-500 font-bold hover:text-red-700">
              Đăng xuất
            </a>
          </li>
        </ul>
      </nav>
    </aside>
  );
};

export default Aside;
