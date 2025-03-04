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
// import ForgotPasswordModal from "./pages/SignIn/ForgotPasswordModal";
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
          <Route path="blogdetail" element={<BlogsDetail />} />
          <Route path="about" element={<About />} />
          {/* <Route path="payment" element={<Payment />} /> */}
        </Route>
        <Route path="signin" element={<SignIn />} />
        {/* <Route path="changepass" element={<ForgotPasswordModal />} /> */}
        <Route path="signup" element={<SignUp />} />
      </Routes>
    </>
  );
}

export default App;
