import { useState } from "react";
import "./App.css";
import { Route, Routes } from "react-router-dom";
import LayoutWebsite from "./pages/(website)/layout";
import Homes from "./pages/(website)/Home/Homes";
import Products from "./pages/(website)/Products/Products";
import ProductDetail from "./pages/(website)/Detail/ProductDetail";
import Contact from "./pages/(website)/Contact/Contact";
import Cart from "./pages/(website)/Cart/Cart";
import About from "./pages/(website)/About/About";
import BlogsDetail from "./pages/(website)/BlogDetail/BlogsDetail";
import Blogs from "./pages/(website)/Blog/Blogs";
import SignIn from "./pages/auth/SignIn/SignIn";
import SignUp from "./pages/auth/SignUp/SignUp";
import Payment from "./pages/(website)/Payment/Payment";
import LayoutAccount from "./pages/(website)/UserAccount/LayoutAccount";
import Account from "./pages/(website)/UserAccount/Account/Account";
import Address from "./pages/(website)/UserAccount/Address/Address";
import Edit_Pass from "./pages/(website)/UserAccount/Password/Edit_Pass";
import ForgotPasswordModal from "./pages/auth/SignIn/ForgotPasswordModal";
import ChatBot from "./components/ChatBot";

function App() {
  return (
    <>
      <Routes>
        <Route path="/" element={<LayoutWebsite />}>
          <Route index element={<Homes />} />
          <Route path="products" element={<Products />} />
          <Route path="product" element={<ProductDetail />} />
          <Route path="contact" element={<Contact />} />
          <Route path="cart" element={<Cart />} />
          <Route path="blog" element={<Blogs />} />
          <Route path="about" element={<About />} />
          <Route path="payment" element={<Payment />} />
          <Route path="blog-detail" element={<BlogDetail />} />
          <Route path="account" element={<LayoutAccount />}>
            <Route index element={<Account />} />
            <Route path="change_password" element={<Edit_Pass />} />
            <Route path="address" element={<Address />} />
            <Route path="list_order" element={<></>} />
          </Route>
        </Route>
        <Route path="signin" element={<SignIn />} />
        <Route path="changepass" element={<ForgotPasswordModal />} />
        <Route path="signup" element={<SignUp />} />
      </Routes>

      {/* ChatBot component */}
      <ChatBot />
    </>
  );
}

export default App;
