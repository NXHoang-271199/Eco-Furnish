<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xác nhận đơn hàng</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.5; background-color: #f8f8f8; padding: 0; margin: 0; }
        .container { max-width: 600px; margin: auto; padding: 20px; background: #ffffff; border-radius: 8px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); }
        h2 { text-align: center; color: #333; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 14px; }
        th, td { padding: 8px; border: 1px solid #ddd; text-align: left; }
        th { background-color: #f4f4f4; }
        .total { font-weight: bold; color: #d9534f; }
        .summary p { margin: 5px 0; font-size: 14px; }
        .note { font-size: 13px; color: #555; text-align: center; margin-top: 10px; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Xác nhận đơn hàng</h2>
        <p>Xin chào <strong>{{ $order->user_name }}</strong>,</p>
        <p>Cảm ơn bạn đã đặt hàng. Dưới đây là thông tin đơn hàng của bạn:</p>

        <h3>Thông tin đơn hàng</h3>
        <p><strong>Mã đơn hàng:</strong> {{ $order->order_code }}</p>
        <p><strong>Phương thức thanh toán:</strong> {{ $order->paymentMethod->name }}</p>
        <p><strong>Trạng thái thanh toán:</strong>
            {{ ($order->payment_status == 1) ? "✅ Đã thanh toán" : "❌ Chưa thanh toán"}}
        </p>

        <h3>Chi tiết đơn hàng</h3>
        <table>
            <thead>
                <tr>
                    <th>STT</th>
                    <th>Sản phẩm</th>
                    <th>SL</th>
                    <th>Giá</th>
                    <th>Tổng</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $subtotal = 0;
                    $index = 1;
                @endphp
                @foreach ($order->orderItems as $item)
                    @php
                        $originalPrice = $item->product_variant_id
                            ? $item->productVariant->price
                            : $item->product->price;
                        $discountPrice = $item->product_variant_id
                            ? ($item->productVariant->discount_price ?? $originalPrice)
                            : ($item->product->discount_price ?? $originalPrice);
                        $totalPrice = $discountPrice * $item->quantity;
                        $subtotal += $totalPrice;
                    @endphp
                    <tr>
                        <td>{{ $index++ }}</td>
                        <td>{{ $item->product_name }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>{{ number_format($discountPrice, 0, ',', '.') }} đ</td>
                        <td class="total">{{ number_format($totalPrice, 0, ',', '.') }} đ</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <h3>Tóm tắt đơn hàng</h3>
        <div class="summary">
            <p><strong>Tạm tính:</strong> {{ number_format($subtotal, 0, ',', '.') }} đ</p>
            <p><strong>Phí vận chuyển:</strong> {{ number_format($order->shipping_fee, 0, ',', '.') }} đ</p>
            <p><strong>Giảm giá đơn hàng:</strong> {{ number_format($discountAmount, 0, ',', '.') }} đ</p>
            <p><strong>Tổng cộng:</strong> <span class="total">{{ number_format($order->total_price, 0, ',', '.') }} đ</span></p>
        </div>

        <p class="note">(* Tổng cộng = Tạm tính + Phí vận chuyển - Giảm giá đơn hàng)</p>
        <p style="text-align: center;">Cảm ơn bạn đã mua sắm tại cửa hàng của chúng tôi!</p>
    </div>
</body>
</html>
