# API

http://localhost:8000/api/users -> User

http://localhost:8000/api/users/login -> Login

- register
- {id}/profile
- /logout
  http://localhost:8000/api/products/ -> Products
- products
- products/{id}
- products/search
  http://localhost:8000/api/posts/ -> Post
- {slug} (lấy chi tiết bài viết theo slug)
- posts?category={category_slug} (lấy tất cả bài viết không lọc theo danh mục)
  http://localhost:8000/api/category-posts/ -> CategoryPost
- Lấy danh sách các danh mục bài viết
  http://localhost:8000/api/vouchers/ -> Vouchers

- /{code}

# Lưu ý khi lọc bài viết theo danh mục

Để lọc bài viết theo danh mục:

1. Lấy danh sách tất cả bài viết qua API `/posts`
2. Với mỗi bài viết, gọi API `/posts/{slug}` để lấy chi tiết bài viết
3. Kiểm tra trường `category_id` trong chi tiết bài viết
4. Lọc các bài viết có `category_id` trùng với ID của danh mục đang xem

# Lưu ý khi lọc bài viết theo danh mục

Để lọc bài viết theo danh mục:

1. Lấy danh sách tất cả bài viết qua API `/posts`
2. Với mỗi bài viết, gọi API `/posts/{slug}` để lấy chi tiết bài viết
3. Kiểm tra trường `category_id` trong chi tiết bài viết
4. Lọc các bài viết có `category_id` trùng với ID của danh mục đang xem

// Kiểm tra cách lấy ID từ URL
import { useParams } from 'react-router-dom';
import { useState, useEffect } from 'react';
import axios from 'axios';

const Detail = () => {
const { id } = useParams(); // Lấy ID từ URL
const [product, setProduct] = useState(null);
const [loading, setLoading] = useState(true);
const [error, setError] = useState(null);

useEffect(() => {
// Kiểm tra xem id có tồn tại không
if (!id) {
setError('Không tìm thấy ID sản phẩm');
setLoading(false);
return;
}

    // Gọi API để lấy chi tiết sản phẩm
    axios.get(`http://localhost:8000/api/products/${id}`)
      .then(response => {
        console.log('Dữ liệu sản phẩm chi tiết:', response.data);
        if (response.data.status === 'success') {
          setProduct(response.data.data);
        } else {
          setError('Không thể lấy thông tin sản phẩm');
        }
      })
      .catch(error => {
        console.error('Lỗi khi lấy chi tiết sản phẩm:', error);
        setError('Đã xảy ra lỗi khi tải dữ liệu sản phẩm');
      })
      .finally(() => {
        setLoading(false);
      });

}, [id]); // Chạy lại khi id thay đổi

if (loading) return <div>Đang tải...</div>;
if (error) return <div>Lỗi: {error}</div>;
if (!product) return <div>Không tìm thấy sản phẩm</div>;

return (
// Hiển thị thông tin sản phẩm

<div>
<h1>{product.name}</h1>
<img
src={`http://localhost:8000/storage/${product.image_thumnail}`}
alt={product.name}
/>
<p>Giá: {new Intl.NumberFormat('vi-VN', {
style: 'currency',
currency: 'VND'
}).format(product.price)}</p>
<div dangerouslySetInnerHTML={{ __html: product.description }} />
</div>
);
};

export default Detail;

# test MoMo account

NGUYEN VAN A
9704 0000 0000 0018
03/07
OTP
Card Successful

//

Route sản phẩm: Cung cấp các route để lấy danh sách sản phẩm, tìm kiếm và hiển thị chi tiết sản phẩm.

- `/products`: Lấy tất cả sản phẩm.
- `/products/{id}`: Lấy thông tin sản phẩm theo ID.
  Route lấy sản phẩm bán chạy: Thêm route `/best-sellers` để lấy các sản phẩm bán chạy nhất.
  Route danh mục sản phẩm: Các route `/categories` giúp lấy thông tin về danh mục sản phẩm.
- `/categories/all`: Lấy tất cả các danh mục.
- `/categories/{slug}`: Lấy danh mục cụ thể theo `slug`.
  Route biến thể sản phẩm: Cung cấp thông tin về các biến thể của sản phẩm thông qua route `/variants`.
Route trò chuyện (chat): Các route để gửi và nhận tin nhắn trong hệ thống chat.
   - `/chat`: Gửi tin nhắn từ người dùng.
   - `/chat/order-success`: Gửi tin nhắn thông báo thành công đơn hàng.
Route quản lý người dùng: Định nghĩa các route liên quan đến người dùng như đăng ký, đăng nhập, đổi mật khẩu, v.v.
   - `/users/register`: Route đăng ký người dùng mới.
   - `/users/login`: Route đăng nhập người dùng.
   - `/users/logout`: Route đăng xuất người dùng.
   - `/users/profile`:Route profile
Social OAuth Routes: Các route để tích hợp đăng nhập qua Google và Facebook.
   - `/auth/google/redirect`: Redirect người dùng đến Google để đăng nhập.
   - `/auth/facebook/redirect`: Redirect người dùng đến Facebook để đăng nhập.
Social OAuth Routes: Các route để tích hợp đăng nhập qua Google và Facebook.
   - `/auth/google/redirect`: Redirect người dùng đến Google để đăng nhập.
   - `/auth/facebook/redirect`: Redirect người dùng đến Facebook để đăng nhập.
Route bài viết (posts): Định nghĩa các route để hiển thị bài viết, bao gồm bài viết theo danh mục.
   - `/posts`: Lấy tất cả các bài viết.
   - `/posts/{slug}`: Lấy bài viết theo `slug`.
Voucher Routes: Các route để lấy voucher và kiểm tra tính hợp lệ của voucher.
    - `/vouchers`: Lấy tất cả voucher.
    - `/check-voucher`: Kiểm tra tính hợp lệ của voucher.
Banner Routes: Cung cấp các route để lấy danh sách banner cho website.
    - `/banners`: Lấy tất cả các banner.
Route gửi tin nhắn: Cung cấp API để gửi tin nhắn qua sự kiện.
    - `/send-message`: Gửi tin nhắn thông qua sự kiện `MessageSent`.
Route tin nhắn (messages): Các route để quản lý tin nhắn của người dùng.
    - `/messages/user/{userId}`: Lấy tin nhắn theo ID người dùng.
    - `/messages/unread`: Lấy tin nhắn chưa đọc.

Quản lý token xác thực: Cung cấp API xác thực token người dùng cho Socket server.
    - `/auth/verify-token`: Xác thực token người dùng.

Route kiểm tra token: Kiểm tra tính hợp lệ của token đang sử dụng.
    - `/auth/check-token`: Kiểm tra token người dùng.

 Test xác thực: API để kiểm tra việc xác thực người dùng qua token.
    - `/auth/check`: API kiểm tra thông tin người dùng đã đăng nhập.
API lấy thông tin người dùng theo ID: Cung cấp API để lấy thông tin người dùng qua ID.
    - `/users/{id}`: Lấy thông tin người dùng theo ID.



