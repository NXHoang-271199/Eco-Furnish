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

// import CursorGlow from "./CursorGlow";
// import VerifyEmail from "./pages/VerifyEmail/VerifyEmail";
import EmailVerification from "./pages/EmailVerification/EmailVerification";
import ResetPassword from "./pages/auth/ResetPassword/ResetPassword";
import ConfirmPassword from "./pages/ConfirmPassword/ConfirmPassword";

// import ForgotPasswordModal from "./pages/SignIn/ForgotPasswordModal";

import Payment from "./pages/(website)/Payment/Payment";
import LayoutAccount from "./pages/(website)/UserAccount/LayoutAccount";
import Account from "./pages/(website)/UserAccount/Account/Account";
import Address from "./pages/(website)/UserAccount/Address/Address";
import Edit_Pass from "./pages/(website)/UserAccount/Password/Edit_Pass";
import ForgotPasswordModal from "./pages/auth/SignIn/ForgotPasswordModal";
import ChatBot from "./components/ChatBot";
import OrderSuccess from "./pages/(website)/OrderSuccess/OrderSuccess";

function App() {
  return (
    <>
      {/* <CursorGlow /> */}
      <Routes>
        <Route path="/" element={<LayoutWebsite />}>
          <Route index element={<Homes />} />
          <Route path="products" element={<Products />} />
          <Route path="product/:id" element={<ProductDetail />} />
          <Route path="contact" element={<Contact />} />
          <Route path="cart" element={<Cart />} />
          <Route path="blogs" element={<Blogs />} />
          <Route path="blog-detail/:slug" element={<BlogsDetail />} />
          <Route path="about" element={<About />} />
          <Route path="payment" element={<Payment />} />
          <Route path="order-success" element={<OrderSuccess />} />
          <Route path="account" element={<LayoutAccount />}>
            <Route index element={<Account />} />
            <Route path="address" element={<Address />} />
            <Route path="password" element={<Edit_Pass />} />
          </Route>
        </Route>
        <Route path="/signin" element={<SignIn />} />
        <Route path="/signup" element={<SignUp />} />
        <Route path="/verify-email" element={<EmailVerification />} />
        <Route path="/reset-password" element={<ResetPassword />} />
        <Route path="/confirm-password" element={<ConfirmPassword />} />
        <Route path="/forgot-password" element={<ForgotPasswordModal />} />
      </Routes>
      <ChatBot />
    </>
  );
}

export default App;
