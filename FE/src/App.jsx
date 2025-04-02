import "./App.css";
import { Route, Routes } from "react-router-dom";
import LayoutWebsite from "./pages/(website)/layout";
import Homes from "./pages/(website)/home/Homes";
import Products from "./pages/(website)/Products/Products";
import ProductDetail from "./pages/(website)/Detail/ProductDetail";
import Contact from "./pages/(website)/Contact/Contact";
import Cart from "./pages/(website)/Cart/Cart";
import Blogs from "./pages/(website)/Blog/Blogs";
import About from "./pages/(website)/About/About";
import BlogsDetail from "./pages/(website)/BlogDetail/BlogsDetail";
import SignIn from "./pages/auth/SignIn/SignIn";
import SignUp from "./pages/auth/SignUp/SignUp";
import EmailVerification from "./pages/EmailVerification/EmailVerification";
import ResetPassword from "./pages/auth/ResetPassword/ResetPassword";
import ConfirmPassword from "./pages/ConfirmPassword/ConfirmPassword";
import Payment from "./pages/(website)/Payment/Payment";
import LayoutAccount from "./pages/(website)/UserAccount/LayoutAccount";
import Account from "./pages/(website)/UserAccount/Account/Account";
import Address from "./pages/(website)/UserAccount/Address/Address";
import Edit_Pass from "./pages/(website)/UserAccount/Password/Edit_Pass";
import ForgotPasswordModal from "./pages/auth/SignIn/ForgotPasswordModal";
import ChatBot from "./components/ChatBot";
import OrderSuccess from "./pages/(website)/OrderSuccess/OrderSuccess";
import { useEffect } from "react";
import OrderHistory from "./pages/(website)/UserAccount/OrderHistory/OrderHistory";

function App() {
  // Đồng bộ token khi ứng dụng khởi động
  useEffect(() => {
    // Đồng bộ token giữa access_token và authToken
    const accessToken = localStorage.getItem("access_token");
    const authToken = localStorage.getItem("authToken");

    if (accessToken && !authToken) {
      // Nếu có access_token nhưng không có authToken, sao chép sang authToken
      localStorage.setItem("authToken", accessToken);
      console.log("Đã đồng bộ từ access_token sang authToken");
    } else if (!accessToken && authToken) {
      // Nếu có authToken nhưng không có access_token, sao chép sang access_token
      localStorage.setItem("access_token", authToken);
      console.log("Đã đồng bộ từ authToken sang access_token");
    }
  }, []);

  return (
    <>
      <Routes>
        <Route path="/" element={<LayoutWebsite />}>
          <Route index element={<Homes />} />
          <Route path="products" element={<Products />} />
          <Route path="product-detail/:id" element={<ProductDetail />} />
          <Route path="contact" element={<Contact />} />
          <Route path="cart" element={<Cart />} />
          <Route path="blogs" element={<Blogs />} />
          <Route path="blog-detail/:slug" element={<BlogsDetail />} />
          <Route path="about" element={<About />} />
          <Route path="payment" element={<Payment />} />
          <Route path="order-success" element={<OrderSuccess />} />
          <Route path="account" element={<LayoutAccount />}>
            <Route path="order-history" element={<OrderHistory />} />
            <Route index element={<Account />} />
            <Route path="address" element={<Address />} />
            <Route path="password" element={<Edit_Pass />} />
          </Route>
        </Route>
        <Route path="/sign-in" element={<SignIn />} />
        <Route path="/sign-up" element={<SignUp />} />
        <Route path="/auth/verify-email" element={<EmailVerification />} />
        <Route path="/reset-password" element={<ResetPassword />} />
        <Route path="/confirm-password" element={<ConfirmPassword />} />
        <Route path="/forgot-password" element={<ForgotPasswordModal />} />
      </Routes>
      <ChatBot />
    </>
  );
}

export default App;
