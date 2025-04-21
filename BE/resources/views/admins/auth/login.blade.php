@extends('layouts.auth')

@section('title')
    Đăng nhập Admin
@endsection

@push('styles')
<style>
    .auth-one-bg {
        background-image: url("{{ asset('assets/admins/images/login/loginImage.jpg') }}");
        background-position: center;
        background-size: cover;
        position: relative;
    }
    
    .auth-one-bg .bg-overlay {
        background: linear-gradient(to right, rgba(0, 158, 139, 0.6), rgba(0, 80, 140, 0.6));
        opacity: 0.7;
    }
    
    .login-background-container {
        position: relative;
        overflow: hidden;
        height: 100%;
    }
    
    .login-background {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        z-index: 0;
        transition: transform 8s ease-in-out;
        transform-origin: center;
    }
    
    .login-content {
        position: relative;
        z-index: 1;
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.5);
    }
    
    .card {
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
        overflow: hidden;
    }
    
    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
    }
    
    .carousel-item p {
        opacity: 0;
        transform: translateY(20px);
        transition: all 0.5s ease;
    }
    
    .carousel-item.active p {
        opacity: 1;
        transform: translateY(0);
    }
    
    .btn-success {
        background: linear-gradient(to right, #009e8b, #1a8cff);
        border: none;
        transition: all 0.3s ease;
    }
    
    .btn-success:hover {
        background: linear-gradient(to right, #008c7a, #0072e6);
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0, 158, 139, 0.3);
    }
    
    .form-control {
        border-radius: 8px;
        padding: 10px 15px;
        border: 1px solid #e2e8f0;
        transition: all 0.3s ease;
    }
    
    .form-control:focus {
        border-color: #009e8b;
        box-shadow: 0 0 0 3px rgba(0, 158, 139, 0.1);
    }
    
    .text-primary {
        color: #009e8b !important;
    }
    
    .form-label {
        font-weight: 500;
    }
    
    .auth-logo {
        height: 50px;
        transition: all 0.3s ease;
    }
    
    .auth-logo:hover {
        transform: scale(1.05);
    }
    
    .auth-one-bg .quotes-icon {
        font-size: 40px;
        animation: float 3s ease-in-out infinite;
    }
    
    @keyframes float {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-10px); }
    }
    
    @keyframes zoom {
        0% { transform: scale(1); }
        50% { transform: scale(1.05); }
        100% { transform: scale(1); }
    }
    
    .alert {
        border-radius: 10px;
        border: none;
    }
    
    .password-addon {
        background: transparent;
        border: none;
        color: #6c757d;
    }
    
    .carousel-indicators button {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        margin: 0 5px;
        opacity: 0.5;
        transition: all 0.3s ease;
    }
    
    .carousel-indicators button.active {
        width: 12px;
        height: 12px;
        opacity: 1;
        background-color: #fff;
    }
    
    .carousel-text-shadow {
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.5);
    }
</style>
@endpush

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="card overflow-hidden card-bg-fill galaxy-border-none">
                    <div class="row g-0">
                        <div class="col-lg-6">
                            <div class="p-lg-5 p-4 auth-one-bg h-100">
                                <div class="bg-overlay"></div>
                                <div class="position-relative h-100 d-flex flex-column">
                                    <!-- <div class="mb-4">
                                        <a href="/" class="d-block">
                                            <img src="{{ asset('assets/images/logo-light.png') }}" alt="Logo" class="auth-logo" data-aos="fade-down" data-aos-delay="100">
                                        </a>
                                    </div> -->
                                    <div class="mt-auto">
                                        <div class="mb-3">
                                            <i class="ri-double-quotes-l quotes-icon text-success"></i>
                                        </div>

                                        <div id="qoutescarouselIndicators" class="carousel slide" data-bs-ride="carousel">
                                            <div class="carousel-indicators">
                                                <button type="button" data-bs-target="#qoutescarouselIndicators"
                                                    data-bs-slide-to="0" class="active" aria-label="Slide 1"
                                                    aria-current="true"></button>
                                                <button type="button" data-bs-target="#qoutescarouselIndicators"
                                                    data-bs-slide-to="1" aria-label="Slide 2" class=""></button>
                                                <button type="button" data-bs-target="#qoutescarouselIndicators"
                                                    data-bs-slide-to="2" aria-label="Slide 3" class=""></button>
                                            </div>
                                            <div class="carousel-inner text-center text-white-50 pb-5 carousel-text-shadow">
                                                <div class="carousel-item active">
                                                    <p class="fs-15 fst-italic">" Chào mừng bạn đến với trang quản trị Eco Furnish "</p>
                                                </div>
                                                <div class="carousel-item">
                                                    <p class="fs-15 fst-italic">" Eco Furnish - Nơi mang đến những sản phẩm nội thất thân thiện với môi trường và thiết kế hiện đại cho không gian sống của bạn "</p>
                                                </div>  
                                                <div class="carousel-item">
                                                    <p class="fs-15 fst-italic">" Chúc bạn có một ngày làm việc hiệu quả và thành công "</p>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- end carousel -->
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- end col -->

                        <div class="col-lg-6">
                            <div class="p-lg-5 p-4">
                                <div data-aos="fade-up" data-aos-delay="200">
                                    <h5 class="text-primary">Xin chào !</h5>
                                    <p class="text-muted">Đăng nhập vào trang quản trị Eco Furnish</p>
                                </div>

                                @if ($errors->any())
                                    <div class="alert alert-danger" data-aos="fade-up" data-aos-delay="250">
                                        @foreach ($errors->all() as $error)
                                            <div>{{ $error }}</div>
                                        @endforeach
                                    </div>
                                @endif

                                @if(session('error'))
                                    <div class="alert alert-danger" role="alert" data-aos="fade-up" data-aos-delay="250">
                                        {{ session('error') }}
                                    </div>
                                @endif

                                <div class="mt-4" data-aos="fade-up" data-aos-delay="300">
                                    <form action="{{ route('admin.login.post') }}" method="POST" id="loginForm">
                                        @csrf
                                        <div class="mb-3">
                                            <label for="email" class="form-label">Email</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light"><i class="ri-mail-line"></i></span>
                                                <input type="email" class="form-control" id="email" name="email"
                                                    placeholder="Nhập email..." value="{{ old('email') }}" required>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label" for="password-input">Mật khẩu</label>
                                            <div class="position-relative auth-pass-inputgroup mb-3">
                                                <div class="input-group">
                                                    <span class="input-group-text bg-light"><i class="ri-lock-2-line"></i></span>
                                                    <input type="password" class="form-control pe-5 password-input" name="password"
                                                        placeholder="Nhập mật khẩu..." id="password-input" required>
                                                    <button class="btn position-absolute end-0 top-0 text-decoration-none text-muted password-addon"
                                                        type="button" id="password-addon"><i class="ri-eye-fill align-middle"></i></button>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mt-4">
                                            <button class="btn btn-success w-100" type="submit" id="loginButton">
                                                <span id="loadingSpinner" class="spinner-border spinner-border-sm me-2 d-none" role="status" aria-hidden="true"></span>
                                                Đăng nhập
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <!-- end col -->
                    </div>
                    <!-- end row -->
                </div>
                <!-- end card -->
            </div>
            <!-- end col -->

        </div>
        <!-- end row -->
    </div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css" rel="stylesheet">

<script>
    $(document).ready(function() {
        // Khởi tạo hiệu ứng AOS
        AOS.init({
            duration: 800,
            easing: 'ease-in-out',
            once: true
        });
        
        // Password show & hide
        $("#password-addon").on('click', function() {
            var $input = $(".password-input");
            if ($input.attr('type') === "password") {
                $input.attr('type', 'text');
                $(this).find('i').removeClass('ri-eye-fill').addClass('ri-eye-off-fill');
            } else {
                $input.attr('type', 'password');
                $(this).find('i').removeClass('ri-eye-off-fill').addClass('ri-eye-fill');
            }
        });
        
        // Hiệu ứng khi submit form
        $("#loginForm").on('submit', function() {
            $("#loadingSpinner").removeClass('d-none');
            $("#loginButton").attr('disabled', true);
            
            // Form sẽ tự submit sau khi hiển thị hiệu ứng
            return true;
        });
        
        // Hiệu ứng focus input
        $(".form-control").on('focus', function() {
            $(this).parent().addClass('focused');
        }).on('blur', function() {
            $(this).parent().removeClass('focused');
        });
        
        // Hiệu ứng ripple cho nút
        $(".btn").on('click', function(e) {
            var x = e.pageX - $(this).offset().left;
            var y = e.pageY - $(this).offset().top;
            
            var $ripple = $("<span class='ripple'></span>");
            $ripple.css({
                top: y + 'px',
                left: x + 'px'
            });
            
            $(this).append($ripple);
            
            setTimeout(function() {
                $ripple.remove();
            }, 700);
        });
        
        // Hiệu ứng zoom cho hình nền
        function startZoomEffect() {
            $('.auth-one-bg').css('animation', 'zoom 20s infinite alternate');
        }
        
        // Bắt đầu hiệu ứng sau khi trang đã tải xong
        setTimeout(startZoomEffect, 1000);
    });
</script>

<style>
    /* Hiệu ứng ripple cho nút */
    .btn {
        position: relative;
        overflow: hidden;
    }
    
    .ripple {
        position: absolute;
        border-radius: 50%;
        background-color: rgba(255, 255, 255, 0.3);
        transform: scale(0);
        animation: ripple 0.7s linear;
        pointer-events: none;
        width: 100px;
        height: 100px;
        margin-top: -50px;
        margin-left: -50px;
    }
    
    @keyframes ripple {
        to {
            transform: scale(3);
            opacity: 0;
        }
    }
    
    /* Hiệu ứng focus cho input */
    .input-group.focused {
        box-shadow: 0 0 0 3px rgba(0, 158, 139, 0.1);
    }
    
    /* Hiệu ứng loading */
    .spinner-border {
        width: 1rem;
        height: 1rem;
    }
    
    /* Hiệu ứng zoom cho hình nền */
    .auth-one-bg {
        animation: zoom 20s infinite alternate;
        animation-timing-function: ease-in-out;
    }
    
    @keyframes zoom {
        0% {
            background-size: 100% auto;
        }
        100% {
            background-size: 120% auto;
        }
    }
</style>
@endpush
