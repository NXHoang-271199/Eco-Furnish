<!DOCTYPE html>
<html>
<head>
    <title>Thông báo yêu cầu hoàn hàng</title>
</head>
<body>
    <h2>Xin chào {{ $order->user->name }},</h2>
    <p>Yêu cầu hoàn hàng của bạn cho đơn hàng <strong>#{{ $order->order_code }}</strong> {{ $statusMessage }}</p>

    <p>Trạng thái hiện tại: <strong>{{ $refundRequest->status }}</strong></p>

    <p>Cảm ơn bạn đã mua hàng!</p>
</body>
</html>
