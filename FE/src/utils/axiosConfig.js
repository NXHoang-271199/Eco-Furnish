import axios from "axios";

// Thêm biến để quản lý số lượng requests đồng thời
let activeRequests = 0;
const MAX_CONCURRENT_REQUESTS = 10; // Số lượng request tối đa cùng lúc
const requestQueue = [];
let isRefreshing = false;
let failedQueue = [];

const processQueue = (error, token = null) => {
  failedQueue.forEach(prom => {
    if (error) {
      prom.reject(error);
    } else {
      prom.resolve(token);
    }
  });

  failedQueue = [];
};

// Hàm xử lý hàng đợi request
const processRequestQueue = () => {
  if (requestQueue.length === 0 || activeRequests >= MAX_CONCURRENT_REQUESTS) return;

  while (requestQueue.length > 0 && activeRequests < MAX_CONCURRENT_REQUESTS) {
    const nextRequest = requestQueue.shift();
    activeRequests++;

    // Thực hiện request từ queue
    nextRequest.execute()
      .then((response) => {
        nextRequest.resolve(response);
      })
      .catch((error) => {
        nextRequest.reject(error);
      })
      .finally(() => {
        activeRequests--;
        // Xử lý request tiếp theo nếu có
        setTimeout(processRequestQueue, 50); // Thêm độ trễ nhỏ để tránh too many attempts
      });
  }
};

const axiosInstance = axios.create({
  baseURL: "http://localhost:8000/api",
  withCredentials: true,
});

let sessionExpiredCallback = null;

// Hàm đăng ký callback xử lý khi phiên hết hạn
export const registerSessionExpiredCallback = (callback) => {
  sessionExpiredCallback = callback;
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

// Thêm thông tin retry cho mỗi request
axiosInstance.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem("authToken");
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }

    // Thêm cấu hình retry nếu chưa có
    if (!config.retryCount) {
      config.retryCount = 0;
      config.retryLimit = 3; // Số lần thử lại tối đa
      config.retryDelay = 1000; // Thời gian chờ giữa các lần thử lại (ms)
    }

    return config;
  },
  (error) => Promise.reject(error)
);

// Bọc tất cả các request trong queue và thêm retry
const originalRequest = axiosInstance.request;
axiosInstance.request = function (config) {
  return new Promise((resolve, reject) => {
    // Đưa request vào queue
    requestQueue.push({
      execute: () => originalRequest.call(axiosInstance, config),
      resolve,
      reject
    });

    // Xử lý queue
    processRequestQueue();
  });
};

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

    // Xử lý too many attempts (429)
    if (error.response && error.response.status === 429) {
      const retryCount = originalRequest.retryCount || 0;
      if (retryCount < originalRequest.retryLimit) {
        originalRequest.retryCount = retryCount + 1;

        // Tăng thời gian chờ theo số lần thử lại
        const delay = originalRequest.retryDelay * Math.pow(2, retryCount);

        // Trì hoãn và thử lại request
        return new Promise(resolve => {
          setTimeout(() => {
            resolve(axiosInstance(originalRequest));
          }, delay);
        });
      }
    }

    // Xử lý token hết hạn
    if (error.response && error.response.status === 401 && !originalRequest._retry) {
      originalRequest._retry = true;

      if (isRefreshing) {
        return new Promise((resolve, reject) => {
          failedQueue.push({ resolve, reject });
        })
          .then(token => {
            originalRequest.headers.Authorization = `Bearer ${token}`;
            return axiosInstance(originalRequest);
          })
          .catch(err => {
            return Promise.reject(err);
          });
      }

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