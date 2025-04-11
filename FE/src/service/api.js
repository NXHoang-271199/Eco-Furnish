import axios from "axios";

// Cấu hình URL API
const API_URL = "http://localhost:8000/api/";

const api = axios.create({
  baseURL: API_URL,
});

// Thêm interceptor để xử lý token
api.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem("authToken");
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

// Thêm interceptor để xử lý response
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      // Token hết hạn hoặc không hợp lệ
      localStorage.removeItem("authToken");
      localStorage.removeItem("userData");
      // Có thể thêm logic chuyển hướng đến trang đăng nhập ở đây
    }
    return Promise.reject(error);
  }
);

export default api;
