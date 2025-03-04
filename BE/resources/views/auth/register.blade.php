@extends('layouts.auth')

@section('title')
    Đăng ký
@endsection

@section('css')
    <link href="{{ asset('assets/admins/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />
    <style>
        .form-control.is-valid,
        .was-validated .form-control:valid {
            padding-right: calc(1.5em + 0.94rem);
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 8 8'%3e%3cpath fill='%2334c38f' d='M2.3 6.73L.6 4.53c-.4-1.04.46-1.4 1.1-.8l1.1 1.4 3.4-3.8c.6-.63 1.6-.27 1.2.7l-4 4.6c-.43.5-.8.4-1.1.1z'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right calc(0.375em + 0.235rem) center;
            background-size: calc(0.75em + 0.47rem) calc(0.75em + 0.47rem);
        }
    </style>
@endsection

@section('content')
    <div class="auth-page-wrapper auth-bg-cover py-5 d-flex justify-content-center align-items-center min-vh-100">
        <div class="bg-overlay"></div>
        <div class="auth-page-content overflow-hidden pt-lg-5">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card overflow-hidden">
                            <div class="row g-0">
                                <div class="col-lg-6">
                                    <div class="p-lg-5 p-4 auth-one-bg h-100">
                                        <div class="bg-overlay"></div>
                                        <div class="position-relative h-100 d-flex flex-column">
                                            <div class="mb-4">
                                                <a href="/" class="d-block">
                                                    <img src="{{ asset('assets/admins/images/logo-light.png') }}"
                                                        alt="" height="18">
                                                </a>
                                            </div>
                                            <div class="mt-auto">
                                                <div class="mb-3">
                                                    <i class="ri-double-quotes-l display-4 text-success"></i>
                                                </div>

                                                <div id="qoutescarouselIndicator" class="carousel slide"
                                                    data-bs-ride="carousel">
                                                    <div class="carousel-indicators">
                                                        <button type="button" data-bs-target="#qoutescarouselIndicator"
                                                            data-bs-slide-to="0" class="active" aria-current="true"
                                                            aria-label="Slide 1"></button>
                                                        <button type="button" data-bs-target="#qoutescarouselIndicator"
                                                            data-bs-slide-to="1" aria-label="Slide 2"></button>
                                                        <button type="button" data-bs-target="#qoutescarouselIndicator"
                                                            data-bs-slide-to="2" aria-label="Slide 3"></button>
                                                    </div>
                                                    <div class="carousel-inner text-center text-white pb-5">
                                                        <div class="carousel-item active">
                                                            <p class="fs-15 fst-italic">"Great! Clean code, clean design,
                                                                easy for customization. Thanks very much!"</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <div class="p-lg-5 p-4">
                                        <div>
                                            <h5 class="text-primary">Đăng ký miễn phí</h5>
                                            <p class="text-muted">Tạo tài khoản mới ngay hôm nay.</p>
                                        </div>
                                        @if (session('success'))
                                            <!-- Secondary Alert -->
                                            <div class="alert alert-secondary alert-border-left alert-dismissible fade show material-shadow"
                                                role="alert">
                                                <i class="ri-check-double-line me-3 align-middle"></i>
                                                {{ session('success') }}
                                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                                    aria-label="Close"></button>
                                            </div>
                                        @endif
                                        <div class="mt-4">
                                            <form method="POST" action="{{ route('register') }}" class="needs-validation"
                                                novalidate>
                                                @csrf

                                                <div class="mb-3">
                                                    <label for="name" class="form-label">Họ tên <span
                                                            class="text-danger">*</span></label>
                                                    <input type="text"
                                                        class="form-control @error('name') is-invalid @enderror"
                                                        id="name" name="name" placeholder="Nhập họ tên"
                                                        value="{{ old('name') }}">
                                                    @error('name')
                                                        <div class="invalid-feedback">
                                                            {{ $message }}
                                                        </div>
                                                    @enderror
                                                </div>

                                                <div class="mb-3">
                                                    <label for="email" class="form-label">Email <span
                                                            class="text-danger">*</span></label>
                                                    <input type="email"
                                                        class="form-control @error('email') is-invalid @enderror"
                                                        id="email" name="email" placeholder="Nhập email"
                                                        value="{{ old('email') }}">
                                                    @error('email')
                                                        <div class="invalid-feedback">
                                                            {{ $message }}
                                                        </div>
                                                    @enderror
                                                </div>

                                                <div class="mb-3">
                                                    <label for="password" class="form-label">Mật khẩu <span
                                                            class="text-danger">*</span></label>
                                                    <div class="position-relative auth-pass-inputgroup">
                                                        <input type="password"
                                                            class="form-control pe-5 password-input @error('password') is-invalid @enderror"
                                                            name="password" placeholder="Nhập mật khẩu" id="password-input">
                                                        <button
                                                            class="btn btn-link position-absolute end-0 top-0 text-decoration-none text-muted password-addon"
                                                            type="button" id="password-addon"><i
                                                                class="ri-eye-fill align-middle"></i></button>
                                                        @error('password')
                                                            <div class="invalid-feedback">
                                                                {{ $message }}
                                                            </div>
                                                        @enderror
                                                    </div>
                                                </div>

                                                <div class="mb-3">
                                                    <label for="password-confirm" class="form-label">Xác nhận mật khẩu
                                                        <span class="text-danger">*</span></label>
                                                    <div class="position-relative auth-pass-inputgroup">
                                                        <input type="password"
                                                            class="form-control pe-5 password-confirm-input @error('password_confirmation') is-invalid @enderror"
                                                            name="password_confirmation" placeholder="Nhập lại mật khẩu"
                                                            id="password-confirm-input">
                                                        <button
                                                            class="btn btn-link position-absolute end-0 top-0 text-decoration-none text-muted password-confirm-addon"
                                                            type="button" id="password-confirm-addon"><i
                                                                class="ri-eye-fill align-middle"></i></button>
                                                        @error('password_confirmation')
                                                            <div class="invalid-feedback">
                                                                {{ $message }}
                                                            </div>
                                                        @enderror
                                                    </div>
                                                </div>

                                                <div class="mb-4">
                                                    <p class="mb-0 fs-12 text-muted fst-italic">Bằng cách đăng ký, bạn đồng
                                                        ý với
                                                        <a href="#"
                                                            class="text-primary text-decoration-underline fst-normal fw-medium">Điều
                                                            khoản sử dụng</a>
                                                    </p>
                                                </div>

                                                <div class="mt-4">
                                                    <button class="btn btn-success w-100" type="submit">Đăng ký</button>
                                                </div>
                                            </form>
                                        </div>

                                        <div class="mt-5 text-center">
                                            <p class="mb-0">Đã có tài khoản? <a href="{{ route('login') }}"
                                                    class="fw-semibold text-primary text-decoration-underline">Đăng
                                                    nhập</a></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script src="{{ asset('assets/admins/libs/sweetalert2/sweetalert2.min.js') }}"></script>
    <script src="{{ asset('assets/admins/libs/parsleyjs/parsley.min.js') }}"></script>
    <script src="{{ asset('assets/admins/js/pages/form-validation.init.js') }}"></script>
    <script src="{{ asset('assets/admins/js/pages/sweetalerts.init.js') }}"></script>

    <script>
        // Password show/hide
        document.getElementById('password-addon').addEventListener('click', function() {
            var passwordInput = document.getElementById('password-input');
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                this.innerHTML = '<i class="ri-eye-off-fill align-middle"></i>';
            } else {
                passwordInput.type = 'password';
                this.innerHTML = '<i class="ri-eye-fill align-middle"></i>';
            }
        });

        // Confirm Password show/hide
        document.getElementById('password-confirm-addon').addEventListener('click', function() {
            var passwordInput = document.getElementById('password-confirm-input');
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                this.innerHTML = '<i class="ri-eye-off-fill align-middle"></i>';
            } else {
                passwordInput.type = 'password';
                this.innerHTML = '<i class="ri-eye-fill align-middle"></i>';
            }
        });

        // Validation với dấu tick
        document.querySelectorAll('.form-control').forEach(function(input) {
            input.addEventListener('input', function() {
                if (this.value.length > 0) {
                    this.classList.add('is-valid');
                } else {
                    this.classList.remove('is-valid');
                }
            });
        });
    </script>

    @if ($errors->any())
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                Swal.fire({
                    html: '<div class="mt-3">' +
                        '<lord-icon src="https://cdn.lordicon.com/tdrtiskw.json" trigger="loop" colors="primary:#f06548,secondary:#f7b84b" style="width:120px;height:120px"></lord-icon>' +
                        '<div class="mt-4 pt-2 fs-15">' +
                        '<h4>Lỗi!</h4>' +
                        '<p class="text-muted mx-4 mb-0">{{ $errors->first() }}</p>' +
                        '</div>' +
                        '</div>',
                    showCancelButton: false,
                    showConfirmButton: true,
                    confirmButtonClass: 'btn btn-primary w-xs me-2 mb-1',
                    confirmButtonText: "OK",
                    buttonsStyling: false,
                    showCloseButton: true
                });
            });
        </script>
    @endif
@endsection
