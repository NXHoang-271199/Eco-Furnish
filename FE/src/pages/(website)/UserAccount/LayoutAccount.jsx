import React from "react";
import Aside from "../../../components/Aside";
import { Outlet } from "react-router-dom";
import Header from "../../../components/Header";
import Footer from "../../../components/Footer";

const LayoutAccount = () => {
  return (
    <>
      <Header />
      <h1 class="text-5xl font-bold text-center mb-6 mt-28">
        Tài khoản của tôi
      </h1>
      <div class="max-w-6xl mx-auto p-4 ">
        <div class="flex flex-col md:flex-row bg-white rounded-lg overflow-hidden ">
          <Aside />

          <Outlet />
        </div>
      </div>
      <Footer />
    </>
  );
};

export default LayoutAccount;
