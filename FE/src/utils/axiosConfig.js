import axios from "axios";

const axiosInstance = axios.create({
  baseURL: "http://localhost:8000/api",
  withCredentials: true,
});

let isRefreshing = false;
let failedQueue = [];

const processQueue = (error, token = null) => {
  failedQueue.forEach((promise) => {
    if (error) {
      promise.reject(error);
    } else {
      promise.resolve(token);
    }
  });
  failedQueue = [];
};

// Hàm xử lý logout
const handleLogout = () => {
  localStorage.removeItem("authToken");
  localStorage.removeItem("refreshToken");
  localStorage.removeItem("userData");
  window.location.href = "/sign-in";
};

// Hàm kiểm tra trạng thái tài khoản
const checkAccountStatus = async () => {
  try {
    const token = localStorage.getItem("authToken");
    if (token) {
      const response = await axiosInstance.get("/auth/check");
      if (!response.data.user.is_active) {
        handleLogout();
      }
    }
  } catch (error) {
    if (error.response?.status === 403) {
      handleLogout();
    }
  }
};

// Thiết lập interval và event listeners để kiểm tra trạng thái tài khoản
if (typeof window !== "undefined") {
  // Kiểm tra khi load trang
  window.addEventListener("load", checkAccountStatus);
  
  // Kiểm tra khi focus vào tab
  window.addEventListener("focus", checkAccountStatus);
  
  // Kiểm tra định kỳ mỗi 30 giây
  const statusCheckInterval = setInterval(checkAccountStatus, 30000);
  
  // Cleanup interval khi component unmount
  window.addEventListener("unload", () => {
    clearInterval(statusCheckInterval);
  });
}

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

    // Xử lý lỗi 403 khi tài khoản bị vô hiệu hóa
    if (error.response && error.response.status === 403) {
      // Kiểm tra nếu là lỗi tài khoản bị vô hiệu hóa
      if (error.response.data?.message?.includes('vô hiệu hóa')) {
        handleLogout();
        return Promise.reject(new Error("Tài khoản của bạn đã bị vô hiệu hóa."));
      }
      // Nếu là lỗi khác (như chưa xác thực email), để component xử lý
      return Promise.reject(error);
    }

    if (error.response && error.response.status === 401 && !originalRequest._retry) {
      if (isRefreshing) {
        return new Promise((resolve, reject) => {
          failedQueue.push({ resolve, reject });
        })
          .then((token) => {
            originalRequest.headers.Authorization = `Bearer ${token}`;
            return axiosInstance(originalRequest);
          })
          .catch((err) => Promise.reject(err));
      }

      originalRequest._retry = true;
      isRefreshing = true;

      try {
        const refreshToken = localStorage.getItem("refreshToken");
        if (!refreshToken) {
          throw new Error("No refresh token available");
        }

        const res = await axios.post("http://localhost:8000/api/users/refresh-token", {
          refresh_token: refreshToken,
        });

        if (res.data.status === "success") {
          const newToken = res.data.data.access_token;
          const newRefreshToken = res.data.data.refresh_token;
          localStorage.setItem("authToken", newToken);
          localStorage.setItem("refreshToken", newRefreshToken);

          processQueue(null, newToken);
          originalRequest.headers.Authorization = `Bearer ${newToken}`;
          isRefreshing = false;
          return axiosInstance(originalRequest);
        } else {
          throw new Error("Refresh token failed");
        }
      } catch (refreshError) {
        isRefreshing = false;
        processQueue(refreshError);
        handleLogout();
        return Promise.reject(new Error("Phiên đăng nhập đã hết hạn, vui lòng đăng nhập lại"));
      }
    }

    return Promise.reject(error);
  }
);

export default axiosInstance;