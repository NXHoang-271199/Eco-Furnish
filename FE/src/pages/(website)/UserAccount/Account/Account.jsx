import React from "react";
const Account = () => {
  return (
    <>
      <main class="w-full md:w-3/4 p-6  ">
        <h2 class="text-xl font-semibold mb-4">Chi tiết tài khoản</h2>
        <form>
          <div class="mb-4">
            <label class="block text-gray-600">Tên</label>
            <input
              type="text"
              value=""
              class="w-full border-gray-300 rounded p-2"
              placeholder="Name"
              disabled
            />
          </div>
          <div class="mb-4">
            <label class="block text-gray-600">Email</label>
            <input
              type="email"
              value=""
              class="w-full border-gray-300 rounded p-2"
              placeholder="Email"
              disabled
            />
          </div>
          <div class="mb-4">
            <label class="block text-gray-600">Mật khẩu</label>
            <input
              type="password"
              value=""
              class="w-full border-gray-300 rounded p-2"
              placeholder="Password"
              disabled
            />
          </div>
          <div class="flex space-x-4">
            <button class="px-4 py-2 bg-gray-700 text-white rounded hover:bg-gray-900">
              Thay đổi
            </button>
            <button class="px-4 py-2 bg-gray-400 text-white rounded">
              Lưu lại
            </button>
          </div>
          <button class="mt-6 px-4 py-2 bg-red-500 text-white rounded hover:bg-red-700">
            Xóa tài khoản
          </button>
        </form>
      </main>
    </>
  );
};

export default Account;
