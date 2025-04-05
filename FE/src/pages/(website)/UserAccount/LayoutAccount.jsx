import React from "react";
import { Outlet } from "react-router-dom";
import Header from "../../../components/Header";
import Footer from "../../../components/Footer";

const LayoutAccount = () => {
  return (
    <>
      <Header />
      <div className="min-h-screen bg-gray-50 pb-12">
        <Outlet />
      </div>
      <Footer />
    </>
  );
};

export default LayoutAccount;
