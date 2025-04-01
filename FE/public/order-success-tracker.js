/**
 * Eco-Furnish - Order Success Tracker
 * Script này theo dõi các đơn hàng thành công và gửi thông báo đến chatbot AI
 */

(function () {
    // Lắng nghe sự kiện đặt hàng thành công
    document.addEventListener('DOMContentLoaded', function () {
        // Kiểm tra URL có chứa tham số đặt hàng thành công không
        const urlParams = new URLSearchParams(window.location.search);
        const orderSuccess = urlParams.get('order_success');
        const orderId = urlParams.get('order_id');

        if (orderSuccess === 'true' && orderId) {
            // Lấy thông tin đơn hàng từ localStorage nếu có
            let orderInfo = localStorage.getItem('lastOrderInfo');
            let products = [];
            let orderTotal = 0;

            if (orderInfo) {
                try {
                    const orderData = JSON.parse(orderInfo);
                    products = orderData.products || [];
                    orderTotal = orderData.total || 0;

                    // Gửi thông báo đến chatbot
                    if (window.chatbotProcessOrderSuccess) {
                        window.chatbotProcessOrderSuccess(orderId, orderTotal, products);

                        // Xóa dữ liệu đơn hàng khỏi localStorage sau khi đã xử lý
                        localStorage.removeItem('lastOrderInfo');
                    }
                } catch (error) {
                    console.error('Error parsing order info:', error);
                }
            }
        }
    });

    // Theo dõi sự kiện "Thêm vào giỏ hàng"
    function trackAddToCart() {
        document.addEventListener('click', function (event) {
            // Kiểm tra xem nút được nhấn có phải là nút "Thêm vào giỏ hàng" không
            if (event.target.matches('a[href^="/cart/add/"]') ||
                event.target.closest('a[href^="/cart/add/"]')) {

                // Lưu sản phẩm vào localStorage để theo dõi hành vi người dùng
                try {
                    // Trích xuất ID sản phẩm từ href
                    const link = event.target.closest('a[href^="/cart/add/"]');
                    const href = link.getAttribute('href');
                    const productId = href.split('/').pop();

                    // Lấy thông tin sản phẩm từ các thuộc tính data nếu có
                    const productName = link.getAttribute('data-product-name') || 'Sản phẩm';
                    const categoryId = link.getAttribute('data-category-id');
                    const price = link.getAttribute('data-price');

                    // Lưu vào danh sách sản phẩm đã thêm vào giỏ hàng
                    let cartProducts = localStorage.getItem('cartProducts');
                    try {
                        cartProducts = cartProducts ? JSON.parse(cartProducts) : [];
                    } catch (e) {
                        cartProducts = [];
                    }

                    cartProducts.push({
                        id: productId,
                        name: productName,
                        category_id: categoryId,
                        price: price
                    });

                    localStorage.setItem('cartProducts', JSON.stringify(cartProducts));
                } catch (error) {
                    console.error('Error tracking add to cart:', error);
                }
            }
        });
    }

    // Theo dõi quá trình thanh toán
    function trackCheckout() {
        // Lắng nghe form thanh toán
        document.addEventListener('submit', function (event) {
            const checkoutForm = event.target.closest('form.checkout-form');
            if (checkoutForm) {
                // Lưu thông tin đơn hàng từ form thanh toán
                try {
                    // Lấy sản phẩm từ localStorage
                    let cartProducts = localStorage.getItem('cartProducts');
                    try {
                        cartProducts = cartProducts ? JSON.parse(cartProducts) : [];
                    } catch (e) {
                        cartProducts = [];
                    }

                    // Lấy tổng giá trị đơn hàng từ form nếu có
                    let orderTotal = checkoutForm.querySelector('input[name="order_total"]')?.value || 0;

                    // Lưu thông tin để sử dụng sau khi thanh toán thành công
                    localStorage.setItem('lastOrderInfo', JSON.stringify({
                        products: cartProducts,
                        total: orderTotal
                    }));

                    // Không cần xóa cartProducts vì chúng ta sẽ xóa lastOrderInfo sau khi xử lý
                } catch (error) {
                    console.error('Error tracking checkout:', error);
                }
            }
        });
    }

    // Khởi tạo các chức năng theo dõi
    trackAddToCart();
    trackCheckout();
})(); 