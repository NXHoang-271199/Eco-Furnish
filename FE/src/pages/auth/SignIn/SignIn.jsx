// import React from "react";

// const SignIn = () => {
//   return (
//     <div className="flex w-full bg-white shadow-lg">
//       <div className="w-1/2 hidden md:block">
//         <img
//           src="https://storage.googleapis.com/a1aa/image/b4hLqB1dzafXLGnsjIoUS_dRgsM8O7qFGuuhpONmhSE.jpg"
//           width="100"
//           alt="A close-up of a flower in a glass with other glasses in the background"
//           class="object-cover w-full h-full"
//         />
//       </div>
//       <div className="w-full md:w-1/2 p-8">
//         <div className="flex justify-end mb-4">
//           <img src="https://placehold.co/100x50" alt="Logo" className="h-8" />
//         </div>
//         <h2 className="text-2xl font-bold mb-2">Đăng Nhập</h2>
//         <p className="mb-4">
//           Chưa có tài khoản?
//           <a href="#" className="text-green-500">
//             Đăng Ký
//           </a>
//         </p>
//         <form>
//           <div className="mb-4">
//             <input
//               type="email"
//               placeholder="Địa chỉ email của bạn"
//               className="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
//             />
//           </div>
//           <div className="mb-4 relative">
//             <input
//               type="password"
//               placeholder="Mật khẩu"
//               className="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
//             />
//             <i className="fas fa-eye absolute right-3 top-3 text-gray-500 cursor-pointer"></i>
//           </div>
//           <div className="flex items-center mb-4">
//             <input type="checkbox" id="remember" className="mr-2" />
//             <label for="remember" className="text-sm">
//               Ghi nhớ
//             </label>
//           </div>
//           <div className="flex justify-between items-center mb-4">
//             <button className="w-full bg-blue-900 text-white py-2 rounded-md">
//               Đăng Nhập
//             </button>
//           </div>
//           <div className="text-center mb-4">
//             <a href="./ChangePass" class="text-blue-500">
//               Bạn quên mật khẩu ?
//             </a>
//           </div>
//           <div className="flex items-center mb-4">
//             <div className="flex-grow border-t border-gray-300"></div>
//             <span className="mx-4 text-gray-500">HOẶC</span>
//             <div className="flex-grow border-t border-gray-300"></div>
//           </div>
//           <div className="flex flex-col space-y-2">
//             <button className="flex items-center justify-center w-full py-2 border border-gray-300 rounded-md">
//               <img
//                 src="https://img.icons8.com/?size=48&id=17949&format=png"
//                 alt="Google logo"
//                 className="mr-2"
//               />
//               Đăng nhập với Google
//             </button>
//             <button className="flex items-center justify-center w-full py-2 border border-gray-300 rounded-md">
//               <img
//                 src="https://img.icons8.com/?size=48&id=uLWV5A9vXIPu&format=png"
//                 alt="Facebook logo"
//                 className="mr-2"
//               />
//               Đăng nhập với Facebook
//             </button>
//           </div>
//         </form>
//       </div>
//     </div>
//   );
// };

// export default SignIn;
import React, { useState } from "react";
import ForgotPasswordModal from "./ForgotPasswordModal";
import { motion } from "framer-motion"; // npm install framer-motion để chạy hiệu ứng

const SignIn = () => {
  const [isModalOpen, setIsModalOpen] = useState(false);

  return (
    <div className="flex w-full bg-white shadow-lg">
      {/* đang chạy hiệu ứng ảnh */}
      <div className="relative overflow-hidden w-1/2 hidden md:block">
        <motion.img
          src="https://i.pinimg.com/236x/0a/c9/ce/0ac9ce43730b62e7563a8ab9d0e8d5ba.jpg"
          alt="Background"
          className="object-cover w-full h-[800px]"
          initial={{ x: "100%" }}
          animate={{ x: "0%" }}
          transition={{ duration: 1.5, ease: "easeInOut" }}
        />
      </div>
      <div className="w-full md:w-1/2 p-32">
        <h2 className="text-2xl font-bold mb-2">Đăng Nhập</h2>
        <p className="mb-4">
          Chưa có tài khoản?{" "}
          <a href="/signup" className="text-green-500">
            Đăng Ký
          </a>
        </p>
        <form>
          <div className="mb-4">
            <input
              type="email"
              placeholder="Địa chỉ email của bạn"
              className="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>
          <div className="mb-4 relative">
            <input
              type="password"
              placeholder="Mật khẩu"
              className="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
            <i className="fas fa-eye absolute right-3 top-3 text-gray-500 cursor-pointer"></i>
          </div>
          <div className="flex items-center mb-4">
            <input type="checkbox" id="remember" className="mr-2" />
            <label htmlFor="remember" className="text-sm">
              Ghi nhớ
            </label>
          </div>
          <div className="flex justify-between items-center mb-4">
            <button className="w-full bg-blue-900 text-white py-2 rounded-md">
              Đăng Nhập
            </button>
          </div>
          <div className="text-center mb-4">
            <p
              className="text-blue-500 cursor-pointer"
              onClick={() => setIsModalOpen(true)}
            >
              Bạn quên mật khẩu ?
            </p>
          </div>
          <div className="flex items-center mb-4">
            <div className="flex-grow border-t border-gray-300"></div>
            <span className="mx-4 text-gray-500">HOẶC</span>
            <div className="flex-grow border-t border-gray-300"></div>
          </div>
          <div className="flex flex-col space-y-2">
            <button className="flex items-center justify-center w-full py-2 border border-gray-300 rounded-md">
              <img
                src="https://img.icons8.com/?size=48&id=17949&format=png"
                alt="Google logo"
                className="mr-2"
                width="20"
                height="20"
              />
              Đăng nhập với Google
            </button>
            <button className="flex items-center justify-center w-full py-2 border border-gray-300 rounded-md">
              <img
                src="https://img.icons8.com/?size=48&id=uLWV5A9vXIPu&format=png"
                alt="Facebook logo"
                className="mr-2"
                width="20"
                height="20"
              />
              Đăng nhập với Facebook
            </button>
          </div>
        </form>
      </div>

      {/* Popup Quên Mật Khẩu */}
      <ForgotPasswordModal
        isOpen={isModalOpen}
        onClose={() => setIsModalOpen(false)}
      />
    </div>
  );
};

export default SignIn;
