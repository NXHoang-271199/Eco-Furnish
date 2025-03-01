// import { useState } from "react";

// const EmailVerification = () => {
//   const [resent, setResent] = useState(false);

//   const handleResend = () => {
//     setResent(true);
//     // Gửi yêu cầu gửi lại email xác thực
//   };

//   return (
//     <div className="flex items-center justify-center h-screen bg-gray-100">
//       <div className="bg-white p-8 shadow-lg rounded-lg w-96 text-center">
//         <h2 className="text-2xl font-bold mb-4">Xác Thực Email</h2>
//         <input
//           type="text"
//           placeholder="Email"
//           className="w-full px-4 py-2 border rounded-md mb-3"
//         />
//         <p className="text-gray-600">
//           Kiểm tra email của bạn và nhấn vào liên kết xác thực.
//         </p>
//         {resent && (
//           <p className="text-green-500 mt-4">Email xác thực đã được gửi lại!</p>
//         )}
//         <button
//           onClick={handleResend}
//           className="w-full bg-blue-600 text-white py-2 rounded-md mt-6 hover:bg-blue-700"
//         >
//           Gửi lại email xác thực
//         </button>
//       </div>
//     </div>
//   );
// };

// export default EmailVerification;
import React from "react";
const EmailVerification = () => {
  return (
    <div className="flex items-center justify-center flex-col mt-1 mb-1">
      <section className=" mx-auto bg-white">
        {/* max-w-2xl */} 
      
        <div className="h-[200px] bg-gray-200 w-full text-white flex items-center justify-center flex-col gap-5">
          <div className="flex items-center gap-3">
            <div className="w-10 h-[1px] bg-white"></div>
            <EmailIcon />
            <div className="w-10 h-[1px] bg-white"></div>
          </div>
          <div className="text-2xl font-bold text-black">
            <div>
              {/* <img src="/logo.svg" alt="Eco-Furnish" /> */}
              <a href="/">
                <span className="text-yellow-300">E</span>co-
                <span className="text-yellow-300">F</span>urnish
              </a>
            </div>
          </div>
        </div>
        <main className="mt-8 px-5 sm:px-10">
          <h2 className="text-gray-700 ">Hello John Deo,</h2>
          <p className="mt-2 leading-loose text-gray-600 ">
            Please use the following One Time Password(OTP)
          </p>
          <div className="flex items-center mt-4 gap-x-4">
            <p className="flex items-center justify-center w-10 h-10 text-2xl font-medium text-[#365CCE] border border-[#365CCE] rounded-md">
              7
            </p>
            <p className="flex items-center justify-center w-10 h-10 text-2xl font-medium text-[#365CCE] border border-[#365CCE] rounded-md">
              8
            </p>
            <p className="flex items-center justify-center w-10 h-10 text-2xl font-medium text-[#365CCE] border border-[#365CCE] rounded-md">
              7
            </p>
            <p className="flex items-center justify-center w-10 h-10 text-2xl font-medium text-[#365CCE] border border-[#365CCE] rounded-md">
              8
            </p>
          </div>
          <p className="mt-4 leading-loose text-gray-600">
            This passcode will only be valid for the next
            <span className="font-bold"> 2 minutes</span> . If the passcode does
            not work, you can use this login verification link:
          </p>
          <button className="px-6 py-2 mt-6 text-sm font-bold tracking-wider text-white capitalize transition-colors duration-300 transform bg-orange-600 rounded-lg hover:bg-orange-500 focus:outline-none focus:ring focus:ring-orange-300 focus:ring-opacity-80">
            Verify email
          </button>
          <p className="mt-8 text-gray-600">
            Thank you, <br />
            Infynno Team
          </p>
        </main>
        <p className="text-gray-500  px-5 sm:px-10 mt-8">
          This email was sent from{" "}
          <a
            href="mailto:sales@infynno.com"
            className="text-[#365CCE] hover:underline"
            alt="sales@infynno.com"
            target="_blank"
          >
            sales@infynno.com
          </a>
          . If you&apos;d rather not receive this kind of email, you can{" "}
          <a href="#" className="text-[#365CCE] hover:underline">
            unsubscribe
          </a>{" "}
          or{" "}
          <a href="#" className="text-[#365CCE] hover:underline">
            manage your email preferences
          </a>
          .
        </p>
        <footer className="mt-8">
          <div className="bg-gray-300/60 h-[200px] flex flex-col gap-3 justify-center items-center">
            <div className="text-center flex flex-col gap-2">
              <h1 className="text-[#365CCE] font-semibold tracking-wide text-lg">
                Get in touch
              </h1>
              <a
                href="tel:+91-848-883-8308"
                className="text-gray-500"
                alt="+91-848-883-8308"
              >
                +91-848-883-8308
              </a>
              <a
                href="mailto:sales@infynno.com"
                className="text-gray-500"
                alt="sales@infynno.com"
              >
                sales@infynno.com
              </a>
            </div>
            <div className="flex items-center justify-center gap-3">
              <a href="#_">
                <FacebookIcon />
              </a>
              <a href="#_">
                <LinkedinIcon />
              </a>
              <a href="#_">
                <InstagramIcon />
              </a>
            </div>
          </div>
          
        </footer>
      </section>
    </div>
  );
};
export default EmailVerification;
const EmailIcon = () => {
  return (
    <svg
      stroke="currentColor"
      fill="currentColor"
      strokeWidth="0"
      viewBox="0 0 24 24"
      height="20"
      width="20"
      xmlns="http://www.w3.org/2000/svg"
    >
      <path fill="none" d="M0 0h24v24H0V0z"></path>
      <path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 14H4V8l8 5 8-5v10zm-8-7L4 6h16l-8 5z"></path>
    </svg>
  );
};
const FacebookIcon = () => {
  return (
    <svg
      stroke="currentColor"
      fill="gray"
      strokeWidth="0"
      viewBox="0 0 16 16"
      height="18"
      width="18"
      xmlns="http://www.w3.org/2000/svg"
    >
      <path d="M16 8.049c0-4.446-3.582-8.05-8-8.05C3.58 0-.002 3.603-.002 8.05c0 4.017 2.926 7.347 6.75 7.951v-5.625h-2.03V8.05H6.75V6.275c0-2.017 1.195-3.131 3.022-3.131.876 0 1.791.157 1.791.157v1.98h-1.009c-.993 0-1.303.621-1.303 1.258v1.51h2.218l-.354 2.326H9.25V16c3.824-.604 6.75-3.934 6.75-7.951z"></path>
    </svg>
  );
};
const InstagramIcon = () => {
  return (
    <svg
      stroke="currentColor"
      fill="gray"
      strokeWidth="0"
      viewBox="0 0 1024 1024"
      height="18"
      width="18"
      xmlns="http://www.w3.org/2000/svg"
    >
      <path d="M512 378.7c-73.4 0-133.3 59.9-133.3 133.3S438.6 645.3 512 645.3 645.3 585.4 645.3 512 585.4 378.7 512 378.7zM911.8 512c0-55.2.5-109.9-2.6-165-3.1-64-17.7-120.8-64.5-167.6-46.9-46.9-103.6-61.4-167.6-64.5-55.2-3.1-109.9-2.6-165-2.6-55.2 0-109.9-.5-165 2.6-64 3.1-120.8 17.7-167.6 64.5C132.6 226.3 118.1 283 115 347c-3.1 55.2-2.6 109.9-2.6 165s-.5 109.9 2.6 165c3.1 64 17.7 120.8 64.5 167.6 46.9 46.9 103.6 61.4 167.6 64.5 55.2 3.1 109.9 2.6 165 2.6 55.2 0 109.9.5 165-2.6 64-3.1 120.8-17.7 167.6-64.5 46.9-46.9 61.4-103.6 64.5-167.6 3.2-55.1 2.6-109.8 2.6-165zM512 717.1c-113.5 0-205.1-91.6-205.1-205.1S398.5 306.9 512 306.9 717.1 398.5 717.1 512 625.5 717.1 512 717.1zm213.5-370.7c-26.5 0-47.9-21.4-47.9-47.9s21.4-47.9 47.9-47.9 47.9 21.4 47.9 47.9a47.84 47.84 0 0 1-47.9 47.9z"></path>
    </svg>
  );
};
const LinkedinIcon = () => {
  return (
    <svg
      stroke="currentColor"
      fill="gray"
      strokeWidth="0"
      viewBox="0 0 16 16"
      height="16"
      width="16"
      xmlns="http://www.w3.org/2000/svg"
    >
      <path d="M0 1.146C0 .513.526 0 1.175 0h13.65C15.474 0 16 .513 16 1.146v13.708c0 .633-.526 1.146-1.175 1.146H1.175C.526 16 0 15.487 0 14.854V1.146zm4.943 12.248V6.169H2.542v7.225h2.401zm-1.2-8.212c.837 0 1.358-.554 1.358-1.248-.015-.709-.52-1.248-1.342-1.248-.822 0-1.359.54-1.359 1.248 0 .694.521 1.248 1.327 1.248h.016zm4.908 8.212V9.359c0-.216.016-.432.08-.586.173-.431.568-.878 1.232-.878.869 0 1.216.662 1.216 1.634v3.865h2.401V9.25c0-2.22-1.184-3.252-2.764-3.252-1.274 0-1.845.7-2.165 1.193v.025h-.016a5.54 5.54 0 0 1 .016-.025V6.169h-2.4c.03.678 0 7.225 0 7.225h2.4z"></path>
    </svg>
  );
};
