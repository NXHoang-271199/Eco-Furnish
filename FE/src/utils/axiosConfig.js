import axios from "axios";

const axiosInstance = axios.create({
  baseURL: "http://localhost:8000/api",
  withCredentials: true,
});

let sessionExpiredCallback = null;

// Hàm đăng ký callback xử lý khi phiên hết hạn
export const registerSessionExpiredCallback = (callback) => {
  sessionExpiredCallback = callback;
};

axiosInstance.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem("authToken");
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  },
  (error) => Promise.reject(error)
);

axiosInstance.interceptors.response.use(
  (response) => response,
  async (error) => {
    const originalRequest = error.config;

    if (error.response && error.response.status === 401 && !originalRequest._retry) {
      originalRequest._retry = true;

      // Kích hoạt popup thay vì làm mới token
      if (sessionExpiredCallback) {
        sessionExpiredCallback();
        return Promise.reject(new Error("SESSION_EXPIRED"));
      }

      // Nếu không có callback, xử lý như cũ
      localStorage.removeItem("authToken");
      localStorage.removeItem("refreshToken");
      localStorage.removeItem("userData");
      return Promise.reject(new Error("Phiên đăng nhập đã hết hạn, vui lòng đăng nhập lại"));
    }

    return Promise.reject(error);
  }
);

export default axiosInstance;