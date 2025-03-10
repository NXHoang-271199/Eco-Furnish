# Chức năng Chat với AI tích hợp API Gemini

Dự án này tích hợp chức năng chat với AI sử dụng API Gemini của Google vào ứng dụng web Eco-Furnish.

## Tính năng

- Bong bóng chat ở góc phải dưới cùng của trang web
- Tích hợp API Gemini để trả lời câu hỏi của người dùng
- Lưu lịch sử chat vào localStorage
- Giao diện người dùng thân thiện và đáp ứng

## Cài đặt

### Yêu cầu

- Node.js và npm cho Frontend
- PHP 8.1+ và Composer cho Backend
- API key của Google Gemini

### Bước 1: Cài đặt Frontend

```bash
cd FE
npm install
```

### Bước 2: Cài đặt Backend

```bash
cd BE
composer install
```

### Bước 3: Cấu hình API Gemini

1. Đăng ký và lấy API key từ [Google AI Studio](https://makersuite.google.com/app/apikey)
2. Mở file `BE/.env` và cập nhật API key:

```
GEMINI_API_KEY=YOUR_GEMINI_API_KEY_HERE
```

## Sử dụng

1. Khởi động Backend:

```bash
cd BE
php artisan serve
```

2. Khởi động Frontend:

```bash
cd FE
npm run dev
```

3. Truy cập ứng dụng web và nhấp vào biểu tượng robot ở góc phải dưới cùng để mở bong bóng chat.

## Cấu trúc dự án

### Frontend

- `FE/src/components/ChatBot.jsx`: Component React cho bong bóng chat
- `FE/src/App.jsx`: File chính của ứng dụng, nơi tích hợp component ChatBot

### Backend

- `BE/app/Http/Controllers/Api/ChatController.php`: Controller xử lý API chat
- `BE/routes/api.php`: Định nghĩa route API cho chức năng chat

## Tùy chỉnh

### Thay đổi giao diện

Bạn có thể tùy chỉnh giao diện bong bóng chat bằng cách chỉnh sửa các lớp CSS trong file `FE/src/components/ChatBot.jsx`.

### Thay đổi cấu hình Gemini API

Bạn có thể điều chỉnh các tham số của API Gemini như temperature, topK, topP trong file `BE/app/Http/Controllers/Api/ChatController.php`.

## Xử lý sự cố

- **Lỗi kết nối API**: Kiểm tra API key và kết nối internet
- **Lỗi CORS**: Đảm bảo rằng cấu hình CORS trong Laravel cho phép yêu cầu từ frontend
- **Lỗi 429 (Too Many Requests)**: API Gemini có giới hạn số lượng yêu cầu, hãy thử lại sau
