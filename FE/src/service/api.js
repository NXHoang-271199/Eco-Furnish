import axios from "axios";

// Cấu hình URL API
const API_URL = "http://localhost:8000/api/";

const api = axios.create({
  baseURL: API_URL,
});

// Thêm một mảng để theo dõi các request đang chờ xử lý
let pendingRequests = [];
let apiLoadingListeners = [];

// Thêm function để đăng ký listener
export const addApiLoadingListener = (listener) => {
  if (typeof listener === 'function' && !apiLoadingListeners.includes(listener)) {
    apiLoadingListeners.push(listener);
  }
};

// Thêm function để hủy đăng ký listener
export const removeApiLoadingListener = (listener) => {
  apiLoadingListeners = apiLoadingListeners.filter(l => l !== listener);
};

// Function để thông báo cho tất cả listeners về thay đổi trạng thái
const notifyListeners = (isLoading) => {
  apiLoadingListeners.forEach(listener => listener(isLoading));
};

// Thêm interceptor để xử lý token
api.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem("authToken");
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    pendingRequests.push(config.url);
    notifyListeners(true);
    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

// Thêm interceptor để xử lý response
api.interceptors.response.use(
  (response) => {
    const index = pendingRequests.indexOf(response.config.url);
    if (index > -1) {
      pendingRequests.splice(index, 1);
    }
    if (pendingRequests.length === 0) {
      notifyListeners(false);
    }
    return response;
  },
  (error) => {
    if (error.response?.status === 401) {
      // Token hết hạn hoặc không hợp lệ
      localStorage.removeItem("authToken");
      localStorage.removeItem("userData");
      // Có thể thêm logic chuyển hướng đến trang đăng nhập ở đây
    }
    if (error.config) {
      const index = pendingRequests.indexOf(error.config.url);
      if (index > -1) {
        pendingRequests.splice(index, 1);
      }
    }
    if (pendingRequests.length === 0) {
      notifyListeners(false);
    }
    return Promise.reject(error);
  }
);

// Hàm kiểm tra xem có request nào đang chờ xử lý hay không
export const hasActiveRequests = () => pendingRequests.length > 0;

export default api;
