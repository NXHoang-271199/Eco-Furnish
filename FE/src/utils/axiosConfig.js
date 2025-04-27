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

    if (
      error.response &&
      error.response.status === 401 &&
      !originalRequest._retry
    ) {
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
      console.log("axiosConfig: Attempting to refresh token...");

      try {
        const refreshToken = localStorage.getItem("refreshToken");
        if (!refreshToken) {
          console.error("axiosConfig: No refresh token found in localStorage.");
          throw new Error("No refresh token available");
        }

        const res = await axios.post(
          "http://localhost:8000/api/users/refresh-token",
          {
            refresh_token: refreshToken,
          }
        );

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
        localStorage.removeItem("authToken");
        localStorage.removeItem("refreshToken");
        localStorage.removeItem("userData");
        return Promise.reject(
          new Error("Phiên đăng nhập đã hết hạn, vui lòng đăng nhập lại")
        );
      }
    }

    return Promise.reject(error);
  }
);

export default axiosInstance;
