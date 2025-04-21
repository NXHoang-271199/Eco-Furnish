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

