<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xác thực email</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #495057;
            margin: 0;
            padding: 0;
            background-color: #f3f3f9;
        }
        .container {
            max-width: 600px;
            margin: 40px auto;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,.08);
        }
        .header {
            background: #405189;
            padding: 30px;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }
        .header h1 {
            color: #fff;
            margin: 0;
            font-size: 24px;
        }
        .content {
            padding: 40px 30px;
        }
        .welcome {
            font-size: 20px;
            color: #405189;
            margin-bottom: 20px;
        }
        .message {
            margin-bottom: 30px;
            color: #495057;
        }
        .button {
            display: inline-block;
            padding: 12px 28px;
            background: #405189;
            color: #fff!important;
            text-decoration: none;
            border-radius: 4px;
            font-weight: 500;
            margin-bottom: 20px;
            transition: background 0.3s;
        }
        .button:hover {
            background: #099885;
        }
        .note {
            font-size: 13px;
            color: #878a99;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e9ebec;
        }
        .footer {
            text-align: center;
            padding: 20px;
            background: #f3f3f9;
            border-radius: 0 0 8px 8px;
            font-size: 13px;
            color: #878a99;
        }
        .link {
            word-break: break-all;
            color: #405189;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Eco-Furnish</h1>
        </div>
        <div class="content">
            <div class="welcome">
                Xin chào {{ $user->name }},
            </div>
            <div class="message">
                Cảm ơn bạn đã đăng ký tài khoản tại Eco-Furnish. Để hoàn tất quá trình đăng ký, vui lòng xác thực địa chỉ email của bạn bằng cách nhấp vào nút bên dưới:
            </div>
            <center>
                <a href="{{ $verificationUrl }}" class="button">Xác thực email</a>
            </center>
            <div class="message">
                Hoặc bạn có thể copy và paste đường link sau vào trình duyệt:
                <br>
                <a href="{{ $verificationUrl }}" class="link">{{ $verificationUrl }}</a>
            </div>
            <div class="note">
                Lưu ý: Link xác thực này sẽ hết hạn sau 24 giờ.
                <br><br>
                Email này được gửi tự động, vui lòng không trả lời.
                <br>
                Nếu bạn không đăng ký tài khoản, vui lòng bỏ qua email này.
            </div>
        </div>
        <div class="footer">
            © {{ date('Y') }} Eco-Furnish. All rights reserved.
        </div>
    </div>
</body>
</html>
