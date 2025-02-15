import { useState } from "react";
import reactLogo from "./assets/react.svg";
import viteLogo from "/vite.svg";
import "./App.css";
import { Route, Routes } from "react-router-dom";
import LayoutWebsite from "./pages/layout";
import Homes from "./pages/Home/Homes";
import Products from "./pages/Products/Products";
import ProductDetail from "./pages/Detail/ProductDetail";
import Contact from "./pages/Contact/Contact";
import Cart from "./pages/Cart/Cart";
import Blogs from "./pages/Blogs/Blogs";
import About from "./pages/About/About";
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
          <Route path="blogs" element={<Blogs />} />
          <Route path="about" element={<About />} />
        </Route>
      </Routes>
    </>
  );
}

export default App;
