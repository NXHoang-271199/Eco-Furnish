import axios from "axios";

// Cấu hình URL API
const API_URL = "http://localhost:8000/api/";

const api = axios.create({
  baseURL: API_URL,
});

export default api;
