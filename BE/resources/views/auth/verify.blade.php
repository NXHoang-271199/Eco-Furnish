@extends('layouts.auth')

@section('title')
    Xác Thực Email
@endsection

@section('content')
<div class="auth-page-wrapper pt-5">
    <div class="auth-one-bg-position auth-one-bg" id="auth-particles">
        <div class="bg-overlay"></div>
        <div class="shape">
            <svg xmlns="http://www.w3.org/2000/svg" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 1440 120">
                <path d="M 0,36 C 144,53.6 432,123.2 720,124 C 1008,124.8 1296,56.8 1440,40L1440 140L0 140z"></path>
            </svg>
        </div>
    </div>

    <div class="auth-page-content">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-6 col-xl-5">
                    <div class="card mt-4 border-0 shadow-lg">
                        <div class="card-header bg-primary bg-opacity-10 border-bottom border-primary border-opacity-25">
                            <div class="text-center">
                                <a href="/" class="d-inline-block auth-logo">
                                    <img src="{{ URL::asset('assets/images/logo-dark.png') }}" alt="Logo" height="50">
                                </a>
                            </div>
                            <h4 class="text-primary text-center mt-3 mb-2">Xác Thực Email</h4>
                            <p class="text-muted text-center mb-0">Vui lòng xác thực email để tiếp tục sử dụng dịch vụ</p>
                        </div>
                        
                        <div class="card-body p-4">
                            @if (session('resent'))
                                <div class="alert alert-success alert-borderless" role="alert">
                                    <i class="ri-check-double-line me-2"></i>
                                    Một liên kết xác thực mới đã được gửi đến địa chỉ email của bạn.
                                </div>
                            @endif

                            <div class="text-center mb-4">
                                <div class="avatar-lg mx-auto mt-2">
                                    <div class="avatar-title bg-light text-primary display-5 rounded-circle">
                                        <i class="ri-mail-send-line"></i>
                                    </div>
                                </div>
                                <div class="mt-4">
                                    <p class="text-muted mb-2">Chúng tôi đã gửi email xác thực đến địa chỉ email của bạn.</p>
                                    <h5 class="text-muted">Vui lòng kiểm tra hộp thư của bạn!</h5>
                                </div>
                            </div>

                            <div class="mt-4 text-center">
                                <form class="d-inline" method="POST" action="{{ route('verification.resend') }}">
                                    @csrf
                                    <p class="text-muted mb-3">
                                        Không nhận được email? 
                                        <button type="submit" class="btn btn-link p-0 text-primary text-decoration-underline">
                                            Gửi lại
                                        </button>
                                    </p>
                                </form>
                                <div class="mt-3">
                                    <a href="{{ route('login') }}" class="btn btn-light w-100">
                                        <i class="ri-arrow-left-line me-1 align-bottom"></i> 
                                        Quay lại trang đăng nhập
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="text-center">
                        <p class="mb-0 text-muted">
                            &copy; {{ date('Y') }} {{ config('app.name') }}. 
                            <i class="mdi mdi-heart text-danger"></i> Thiết kế bởi Themesbrand
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </footer>
</div>
@endsection

@section('script')
    <script src="{{ URL::asset('assets/libs/particles.js/particles.js.min.js') }}"></script>
    <script src="{{ URL::asset('assets/js/pages/particles.app.js') }}"></script>
@endsection
