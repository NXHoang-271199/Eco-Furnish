import React from "react";

const Edit_Pass = () => {
  return (
    <main class="w-full md:w-3/4 p-6">
      <h2 class="text-xl font-semibold mb-4">Thay đổi mật khẩu</h2>
      <form>
        <div class="mb-4 relative">
          <label class="block text-gray-600">Mật khẩu cũ</label>
          <input
            type="password"
            placeholder="Mật khẩu cũ"
            class="w-full border-gray-300 rounded p-2"
          />
        </div>
        <div class="mb-4 relative">
          <label class="block text-gray-600">Mật khẩu mới</label>
          <input
            type="password"
            placeholder="Mật khẩu mới"
            class="w-full border-gray-300 rounded p-2"
          />
        </div>
        <div class="mb-4 relative">
          <label class="block text-gray-600">Xác nhận mật khẩu</label>
          <input
            type="password"
            placeholder="Xác nhận mật khẩu"
            class="w-full border-gray-300 rounded p-2"
          />
        </div>

        <button class="mt-6 w-full px-4 py-2 bg-black text-white rounded hover:bg-gray-800">
          Thay đổi
        </button>
      </form>
    </main>
  );
};

export default Edit_Pass;
