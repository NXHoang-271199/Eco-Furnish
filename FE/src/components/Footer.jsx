import React from "react";
import { Link } from "react-router-dom";

const Footer = () => {
  return (
    <footer className="bg-zinc-900">
      <div className="max-w-6xl mx-auto text-slate-50">
        <div className="grid grid-cols-4 py-14">
          <div>
            <div className="text-2xl font-bold text-black">
              <div>
                {/* <img src="/logo.svg" alt="Eco-Furnish" /> */}
                <p>
                  <span className="text-yellow-300">E</span>
                  <span className="text-yellow-50">co-</span>
                  <span className="text-yellow-300">F</span>
                  <span className="text-yellow-50">urnish</span>
                </p>
              </div>
            </div>
            <p>400 University Drive Suite 200 Coral Gables, FL 33134 USA</p>
          </div>
          <div>
            <div className="font-semibold">Site Map</div>
            <ul className="leading-10">
              <li>
                <Link to="/">Trang chủ</Link>
              </li>
              <li>
                <Link to="/products">Sản phẩm</Link>
              </li>
              <li>
                <Link to="/about">Về chúng tôi</Link>
              </li>
              <li>
                <Link to="/contact">Liên hệ</Link>
              </li>
            </ul>
          </div>
          <div>
            <div className="font-semibold">Hỗ trợ</div>
            <ul className="leading-10">
              <li>Phương thức thanh toán</li>
              <li>Hoàn tiền</li>
              <li>Chính sách và điều khoản</li>
            </ul>
          </div>
          <div>
            <div className="font-semibold">Vị trí </div>
            <ul className="leading-10">
              <li>support@euphoria.in</li>
              <li>Ahmedabad Main Road</li>
              <li>Udaipur, India- 313002</li>
            </ul>
          </div>
        </div>
        <hr className="leading-7" />
        <div className="">
          <p className="text-center py-8">
            Bản quyền © 2025 Nội thất Eco Furnish. Bảo lưu mọi quyền
          </p>
        </div>
      </div>
    </footer>
  );
};

export default Footer;
