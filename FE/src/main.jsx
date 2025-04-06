import React from "react";
import ReactDOM from "react-dom/client";
import { Provider } from "react-redux";
import { store } from "./store";
import App from "./App";
import "./index.css";
import { BrowserRouter } from "react-router-dom";
import { ToastContainer } from "react-toastify";
import "react-toastify/dist/ReactToastify.css";
import "./utils/axiosConfig"; // Import cấu hình axios toàn cục

// Log thông tin người dùng từ localStorage khi ứng dụng khởi động
const userData = localStorage.getItem('userData');
if (userData) {
  try {
    const parsedUserData = JSON.parse(userData);
    console.log('User data from localStorage on app startup:', parsedUserData);
  } catch (error) {
    console.error('Error parsing user data from localStorage:', error);
  }
}

ReactDOM.createRoot(document.getElementById("root")).render(
  <React.StrictMode>
    <Provider store={store}>
      <BrowserRouter>
        <App />
      </BrowserRouter>
    </Provider>
    <ToastContainer position="top-right" autoClose={3000} />
  </React.StrictMode>
);
