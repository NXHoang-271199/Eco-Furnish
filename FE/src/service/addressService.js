import api from "./api";

// Service cho quản lý địa chỉ người dùng
const addressService = {
    // Lấy danh sách địa chỉ của người dùng
    getUserAddresses: async (userId) => {
        try {
            const token = localStorage.getItem("authToken");
            const response = await api.get(`/users/${userId}/addresses`, {
                headers: {
                    Authorization: `Bearer ${token}`
                }
            });
            return response.data;
        } catch (error) {
            console.error("Lỗi khi lấy danh sách địa chỉ:", error);
            throw error;
        }
    },

    // Thêm địa chỉ mới
    addAddress: async (userId, addressData) => {
        try {
            const token = localStorage.getItem("authToken");
            const response = await api.post(`/users/${userId}/addresses`, addressData, {
                headers: {
                    Authorization: `Bearer ${token}`,
                    "Content-Type": "application/json"
                }
            });
            return response.data;
        } catch (error) {
            console.error("Lỗi khi thêm địa chỉ:", error);
            throw error;
        }
    },

    // Cập nhật địa chỉ
    updateAddress: async (userId, addressId, addressData) => {
        try {
            const token = localStorage.getItem("authToken");
            const response = await api.put(`/users/${userId}/addresses/${addressId}`, addressData, {
                headers: {
                    Authorization: `Bearer ${token}`,
                    "Content-Type": "application/json"
                }
            });
            return response.data;
        } catch (error) {
            console.error("Lỗi khi cập nhật địa chỉ:", error);
            throw error;
        }
    },

    // Xóa địa chỉ
    deleteAddress: async (userId, addressId) => {
        try {
            const token = localStorage.getItem("authToken");
            const response = await api.delete(`/users/${userId}/addresses/${addressId}`, {
                headers: {
                    Authorization: `Bearer ${token}`
                }
            });
            return response.data;
        } catch (error) {
            console.error("Lỗi khi xóa địa chỉ:", error);
            throw error;
        }
    }
};

export default addressService; 