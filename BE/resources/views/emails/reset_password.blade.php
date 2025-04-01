<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt lại mật khẩu</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333333;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            padding: 20px 0;
            border-bottom: 1px solid #eeeeee;
        }
        .logo {
            max-width: 150px;
            height: auto;
        }
        .content {
            padding: 30px 20px;
        }
        h1 {
            color: #2c3e50;
            font-size: 24px;
            margin-bottom: 20px;
            text-align: center;
        }
        .button {
            text-align: center;
            margin: 30px 0;
        }
        .button a {
            display: inline-block;
            padding: 12px 30px;
            background-color: #405189;
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            transition: background-color 0.3s ease;
        }
        .button a:hover {
            background-color: #45a049;
        }
        .footer {
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid #eeeeee;
            color: #666666;
            font-size: 14px;
        }
        .warning {
            background-color: #fff8e1;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
            border-left: 4px solid #ffc107;
        }
        @media only screen and (max-width: 600px) {
            .container {
                width: 100%;
                margin: 0;
                border-radius: 0;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <!-- Thêm logo của bạn ở đây -->
            {{-- <img src="{{ asset('images/logo.png') }}" alt="Logo" class="logo"> --}}
        </div>
        
        <div class="content">
            <h1>Xin chào {{ $user->name }}!</h1>
            
            <p>Chúng tôi nhận được yêu cầu đặt lại mật khẩu cho tài khoản của bạn tại Eco Furnish.</p>
            
            <p>Để đặt lại mật khẩu, vui lòng nhấp vào nút bên dưới:</p>
            
            <div class="button">
                <a href="{{ $resetUrl }}">ĐẶT LẠI MẬT KHẨU</a>
            </div>
            
            <div class="warning">
                <strong>Lưu ý:</strong> Liên kết này sẽ hết hạn sau 60 phút kể từ khi bạn nhận được email này.
            </div>
            
            <p>Nếu bạn không yêu cầu đặt lại mật khẩu, bạn có thể bỏ qua email này. Tài khoản của bạn vẫn an toàn.</p>
        </div>
        
        <div class="footer">
            <p>Email này được gửi tự động, vui lòng không trả lời.</p>
            <p>&copy; {{ date('Y') }} Eco Furnish. Tất cả các quyền được bảo lưu.</p>
        </div>
    </div>
</body>
</html>