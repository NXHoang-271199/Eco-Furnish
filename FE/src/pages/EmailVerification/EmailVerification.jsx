import React from "react";
const EmailVerification = () => {
  return (
    <div className="flex items-center justify-center flex-col mt-1 mb-1">
      <section className=" mx-auto bg-white">
        {/* max-w-2xl */}

        <main className="mt-8 px-5 sm:px-10">
          <h2 className="text-gray-700 ">Hello John Deo,</h2>
          <p className="mt-2 leading-loose text-gray-600 ">
            Please use the following One Time Password(OTP)
          </p>
          <div className="flex items-center mt-4 gap-x-4">
            <input type="text" className="flex items-center justify-center w-10 h-10 text-2xl font-medium text-[#365CCE] border border-[#365CCE] rounded-md" />
            <i className="flex items-center justify-center w-10 h-10 text-2xl font-medium text-[#365CCE] border border-[#365CCE] rounded-md">
              8
            </i>
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
      </section>
    </div>
  );
};
export default EmailVerification;
