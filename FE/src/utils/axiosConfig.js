import axios from "axios";

// Thống nhất baseURL
const baseURL = "http://localhost:8000/api";

// Tạo instance axios chính
const axiosInstance = axios.create({
  baseURL,
  headers: {
    "Content-Type": "application/json",
    Accept: "application/json",
  },
});

// Biến để theo dõi trạng thái refresh token
let isRefreshing = false;
// Mảng các request đang chờ token mới
let failedQueue = [];

// Xử lý các request sau khi refresh token
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

// Hàm refresh token
const refreshToken = async () => {
  try {
    const refreshToken = localStorage.getItem("refreshToken");
    if (!refreshToken) {
      console.error("Không tìm thấy refresh token trong localStorage");
      return null;
    }
    
    console.log("Đang gọi API refresh token với token:", refreshToken);
    const response = await axios.post(`${baseURL}/users/refresh-token`, {
      refresh_token: refreshToken
    }, {
      headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
      },
      timeout: 10000 // Thêm timeout để tránh treo quá lâu
    });
    
    console.log("Kết quả refresh token:", response.data);
    
    if (response.data.status === "success") {
      const newToken = response.data.data.access_token;
      const newRefreshToken = response.data.data.refresh_token;
      
      // Chỉ cập nhật token mới khi API trả về thành công
      localStorage.setItem("authToken", newToken);
      localStorage.setItem("access_token", newToken);
      localStorage.setItem("refreshToken", newRefreshToken);
      
      console.log("Token đã được làm mới thành công");
      return newToken;
    } else {
      console.error("Làm mới token thất bại:", response.data);
      return null;
    }
  } catch (error) {
    console.error("Lỗi khi refresh token:", error);
    
    // Kiểm tra lỗi cụ thể
    if (error.response) {
      // Chỉ trả về null khi server trả về lỗi xác thực
      if (error.response.status === 401 || error.response.status === 403) {
        console.warn("Refresh token không hợp lệ");
        return null;
      }
      
      // Các lỗi server khác, giữ token hiện tại
      return "server_error";
    }
    
    // Lỗi mạng, giữ token
    if (error.request) {
      return "network_error";
    }
    
    return "unknown_error";
  }
};

// Kiểm tra và làm mới token khi khởi động ứng dụng
const checkAndRefreshTokenOnStartup = async () => {
  try {
    const token = localStorage.getItem("authToken");
    const refreshTokenValue = localStorage.getItem("refreshToken");
    
    if (token && refreshTokenValue) {
      // Kiểm tra xem token đã gần hết hạn chưa bằng cách gọi API ping
      try {
        await axios.get(`${baseURL}/user`, {
          headers: { Authorization: `Bearer ${token}` }
        });
        // Nếu gọi thành công, token vẫn hợp lệ
        console.log("Token hiện tại vẫn còn hiệu lực");
      } catch (error) {
        // Nếu token không hợp lệ hoặc hết hạn, thử refresh
        if (error.response && error.response.status === 401) {
          console.log("Token hiện tại đã hết hạn, đang làm mới...");
          await refreshToken();
        }
      }
    }
  } catch (err) {
    console.error("Lỗi khi kiểm tra token:", err);
  }
};

// Thực hiện kiểm tra token khi trang web được tải
// checkAndRefreshTokenOnStartup();

// Interceptor cho request
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

// Interceptor cho response
axiosInstance.interceptors.response.use(
  (response) => response,
  async (error) => {
    const originalRequest = error.config;
    
    // Chỉ xử lý 401 và chưa thử lại
    if (error.response && error.response.status === 401 && !originalRequest._retry) {
      console.log("Nhận được lỗi 401, bắt đầu quy trình refresh token");
      
      if (isRefreshing) {
        console.log("Đang refresh token, thêm request vào hàng đợi");
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
        const newToken = await refreshToken();
        isRefreshing = false;
        
        if (newToken && typeof newToken === 'string' && newToken !== "network_error" && newToken !== "server_error" && newToken !== "unknown_error") {
          console.log("Refresh token thành công, tiếp tục request ban đầu");
          processQueue(null, newToken);
          originalRequest.headers.Authorization = `Bearer ${newToken}`;
          return axiosInstance(originalRequest);
        } else if (newToken === "network_error") {
          console.log("Lỗi mạng khi refresh token, giữ token và thông báo");
          processQueue(new Error('Lỗi mạng'));
          return Promise.reject(new Error('Lỗi kết nối mạng, vui lòng thử lại sau'));
        } else if (newToken === "server_error" || newToken === "unknown_error") {
          console.log("Lỗi server/không xác định, giữ token và thông báo");
          processQueue(new Error('Lỗi hệ thống'));
          return Promise.reject(new Error('Lỗi hệ thống, vui lòng thử lại sau'));
        } else {
          console.log("Refresh token thất bại, cần đăng nhập lại");
          processQueue(new Error('Token không hợp lệ'));
          
        //   // Chỉ xóa token, giữ lại dữ liệu khác
        //   localStorage.removeItem("authToken");
        //   localStorage.removeItem("access_token");
        //   localStorage.removeItem("refreshToken");
        //   localStorage.clear();
          // Không chuyển hướng tự động, để người dùng thấy thông báo lỗi
          return Promise.reject(new Error('Phiên đăng nhập đã hết hạn, vui lòng đăng nhập lại'));
        }
      } catch (refreshError) {
        isRefreshing = false;
        processQueue(refreshError);
        console.error("Lỗi không xác định khi refresh token:", refreshError);
        return Promise.reject(refreshError);
      }
    }
    
    return Promise.reject(error);
  }
);

export default axiosInstance;