<!DOCTYPE html>
<html>

<head>
    <title>Đặt lại mật khẩu cấp 2</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            background-color: #4f46e5;
            padding: 20px;
            text-align: center;
            color: white;
        }

        .content {
            padding: 20px;
            background-color: #f9f9f9;
        }

        .button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #4f46e5;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
        }

        .footer {
            text-align: center;
            padding: 20px;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h2>Đặt lại mật khẩu cấp 2</h2>
        </div>
        <div class="content">
            <p>Xin chào {{ $user->name }},</p>
            <p>Bạn nhận được email này vì chúng tôi đã nhận được yêu cầu đặt lại mật khẩu cấp 2 cho tài khoản của bạn.</p>
            <p>Nhấn vào nút bên dưới để đặt lại mật khẩu cấp 2 của bạn:</p>
            <p style="text-align: center;">    
                <a href="{{ $resetUrl }}" style="color: white;" class="button">Đặt lại mật khẩu cấp 2</a>
            </p>
            <p>Đường dẫn này sẽ hết hạn sau 60 phút.</p>
            <p>Nếu bạn không yêu cầu đặt lại mật khẩu cấp 2, bạn có thể bỏ qua email này.</p>
            <p>Trân trọng,<br>
                Đội ngũ Eco-Furnish</p>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} Eco-Furnish. Đây là email tự động, vui lòng không trả lời.</p>
        </div>
    </div>
</body>

</html> 