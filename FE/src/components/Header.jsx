import React from "react";
import { Link } from "react-router-dom";
import { IoCartOutline } from "react-icons/io5";
import { AiOutlineUser, AiOutlineSearch, AiOutlineHeart } from "react-icons/ai";

const Header = () => {
  return (
    <>
      {/* <header>
        <div className="max-w-6xl mx-auto relative">
          <div className="grid grid-cols-3 gap-8 items-center py-7 ">
            <div>
              <img src="/logo.svg" alt="Eco-Furnish" />
            </div>
            <nav>
              <ul className="flex justify-center space-x-6 ">
                <li>
                  <Link to="/" className="hover:text-yellow-400">
                    Home
                  </Link>
                </li>
                <li>
                  <Link to="/products" className="hover:text-yellow-400">
                    Shop
                  </Link>
                </li>
                <li>
                  <Link to="/about" className="hover:text-yellow-400">
                    About
                  </Link>
                </li>
                <li>
                  <Link to="/contact" className="hover:text-yellow-400">
                    Contact
                  </Link>
                </li>
                <li>
                  <Link to="/blogs" className="hover:text-yellow-400">
                    Blog
                  </Link>
                </li>
              </ul>
            </nav>
            <div className="flex justify-end space-x-4">
              <Link to="/search">
                <AiOutlineSearch />
              </Link>

              <Link to="/cart">
                <IoCartOutline />
              </Link>
              <Link to="/auth/login">
                <AiOutlineUser />
              </Link>
            </div>
          </div>
        </div>
      </header> */}
      <header className="bg-white fixed top-0 left-0 w-full z-50">
        <div className="max-w-6xl mx-auto flex justify-between items-center p-4">
          {/* <!-- Logo --> */}
          <div className="text-2xl font-bold text-black">
            <div>
              {/* <img src="/logo.svg" alt="Eco-Furnish" /> */}
              <p>
                <span className="text-yellow-300">E</span>co-
                <span className="text-yellow-300">F</span>urnish
              </p>
            </div>
          </div>

          {/* <!-- Navigation --> */}
          <nav className="hidden md:flex space-x-6">
            <ul className="flex justify-center space-x-6 ">
              <li>
                <Link to="/" className="hover:text-yellow-400">
                  Home
                </Link>
              </li>
              <li>
                <Link to="/products" className="hover:text-yellow-400">
                  Shop
                </Link>
              </li>
              <li>
                <Link to="/about" className="hover:text-yellow-400">
                  About
                </Link>
              </li>
              <li>
                <Link to="/contact" className="hover:text-yellow-400">
                  Contact
                </Link>
              </li>
              <li>
                <Link to="/blog" className="hover:text-yellow-400">
                  Blog
                </Link>
              </li>
            </ul>
          </nav>

          {/* <!-- Icons --> */}
          <div className="flex items-center space-x-4">
            {/* <!-- Login --> */}
            {/* <a href="#" class="text-gray-700 hidden md:block hover:text-black">
              Login / Register
            </a> */}

            <Link to="/search">
              <AiOutlineSearch />
            </Link>

            <Link to="/cart">
              <IoCartOutline />
            </Link>
            <Link to="/signin">
              <AiOutlineUser />
            </Link>
          </div>
        </div>
      </header>

      {/* <!-- Để tránh nội dung bị che khuất do header cố định --> */}
      {/* <div class="mt-10"></div> */}
    </>
  );
};

export default Header;
