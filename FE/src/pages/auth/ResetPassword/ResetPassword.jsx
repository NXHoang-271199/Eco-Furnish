import React, { useState, useEffect } from "react";
import { useNavigate, useSearchParams } from "react-router-dom";
import axios from "axios";

const ResetPassword = () => {
  const [searchParams] = useSearchParams();
  const navigate = useNavigate();
  const [formData, setFormData] = useState({
    email: decodeURIComponent(searchParams.get("email")) || "",
    token: searchParams.get("token") || "",
    password: "",
    password_confirmation: "",
  });
  const [message, setMessage] = useState("");
  const [loading, setLoading] = useState(false);

  console.log(formData);

  useEffect(() => {
    if (!formData.email || !formData.remember_token) {
      setMessage("Link đặt lại mật khẩu không hợp lệ");
    }
  }, [formData.email, formData.remember_token]);

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true);

    try {
      const response = await axios.post(
        "http://127.0.0.1:8000/api/users/reset-password",
        formData
      );

      setMessage(response.data.message);
      setTimeout(() => {
        navigate("/signin");
      }, 3000);
    } catch (error) {
      setMessage(error.response?.data?.message || "Có lỗi xảy ra");
    } finally {
      setLoading(false);
    }
  };
  return (
    // <div className="flex items-center justify-center flex-col mt-1 mb-1">
    //   <section className=" mx-auto bg-white">
    //     {/* max-w-2xl */}

    //     <main className="mt-8 sm:px-10">
    //       <h2 className="mb-1 text-xl font-bold leading-tight tracking-tight text-gray-900 md:text-2xl dark:text-black">
    //         Change Password
    //       </h2>
    //       <form className="mt-4 space-y-4 lg:mt-5 md:space-y-5" action="#">
    //         <div>
    //           <label
    //             htmlFor="email"
    //             className="block mb-2 text-sm font-medium text-gray-600 dark:text-black"
    //           >
    //             Your email
    //           </label>
    //           <input
    //             type="email"
    //             name="email"
    //             id="email"
    //             className="bg-gray-50 border border-gray-600 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5"
    //             placeholder="name@company.com"
    //             required
    //           />
    //         </div>
    //         <div>
    //           <label
    //             htmlFor="password"
    //             className="block mb-2 text-sm font-medium text-gray-600 dark:text-black"
    //           >
    //             New Password
    //           </label>
    //           <input
    //             type="password"
    //             name="password"
    //             id="password"
    //             placeholder="••••••••"
    //             className="bg-gray-50 border border-gray-600 text-gray-600 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5"
    //             required
    //           />
    //         </div>
    //         <div>
    //           <label
    //             htmlFor="confirm-password"
    //             className="block mb-2 text-sm font-medium text-gray-600 dark:text-black"
    //           >
    //             Confirm password
    //           </label>
    //           <input
    //             type="password"
    //             name="confirm-password"
    //             id="confirm-password"
    //             placeholder="••••••••"
    //             className="bg-gray-50 border border-gray-600 text-gray-600 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5"
    //             required
    //           />
    //         </div>
    //         <div className="flex items-start">
    //           <div className="flex items-center h-5">
    //             <input
    //               id="newsletter"
    //               aria-describedby="newsletter"
    //               type="checkbox"
    //               className="w-4 h-4 border border-gray-300 rounded bg-gray-50 focus:ring-3 focus:ring-primary-300 "
    //               required
    //             />
    //           </div>
    //           <div className="ml-3 text-sm">
    //             <label
    //               htmlFor="newsletter"
    //               className="font-light text-gray-600 dark:text-gray-600"
    //             >
    //               I accept the{" "}
    //               <a
    //                 className="font-medium text-primary-600 hover:underline dark:text-primary-500"
    //                 href="#"
    //               >
    //                 Terms and Conditions
    //               </a>
    //             </label>
    //           </div>
    //         </div>
    //         <button
    //           type="submit"
    //           className="w-full text-white bg-slate-600 hover:bg-primary-700 focus:ring-4 focus:outline-none focus:ring-primary-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800"
    //         >
    //           Reset password
    //         </button>
    //       </form>
    //     </main>
    //     <p className="text-gray-500  px-5 sm:px-10 mt-8">
    //       This email was sent from{" "}
    //       <a
    //         href="mailto:sales@infynno.com"
    //         className="text-[#365CCE] hover:underline"
    //         alt="sales@infynno.com"
    //         target="_blank"
    //       >
    //         sales@infynno.com
    //       </a>
    //       . If you&apos;d rather not receive this kind of email, you can{" "}
    //       <a href="#" className="text-[#365CCE] hover:underline">
    //         unsubscribe
    //       </a>{" "}
    //       or{" "}
    //       <a href="#" className="text-[#365CCE] hover:underline">
    //         manage your email preferences
    //       </a>
    //       .
    //     </p>
    //   </section>
    // </div>
    <div className="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
      <div className="max-w-md w-full space-y-8">
        <div>
          <h2 className="mt-6 text-center text-3xl font-extrabold text-gray-900">
            Đặt lại mật khẩu
          </h2>
        </div>
        <form className="mt-8 space-y-6" onSubmit={handleSubmit}>
          <div className="rounded-md shadow-sm -space-y-px">
            <div>
              <label htmlFor="password" className="sr-only">
                Mật khẩu mới
              </label>
              <input
                id="password"
                name="password"
                type="password"
                required
                className="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm"
                placeholder="Mật khẩu mới"
                value={formData.password}
                onChange={(e) =>
                  setFormData({ ...formData, password: e.target.value })
                }
              />
            </div>
            <div>
              <label htmlFor="password_confirmation" className="sr-only">
                Xác nhận mật khẩu
              </label>
              <input
                id="password_confirmation"
                name="password_confirmation"
                type="password"
                required
                className="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-b-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm"
                placeholder="Xác nhận mật khẩu"
                value={formData.password_confirmation}
                onChange={(e) =>
                  setFormData({
                    ...formData,
                    password_confirmation: e.target.value,
                  })
                }
              />
            </div>
          </div>

          {message && (
            <div className="text-sm text-center text-red-600">{message}</div>
          )}

          <div>
            <button
              type="submit"
              disabled={loading}
              className="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
            >
              {loading ? "Đang xử lý..." : "Đặt lại mật khẩu"}
            </button>
          </div>
        </form>
      </div>
    </div>
  );
};

export default ResetPassword;
