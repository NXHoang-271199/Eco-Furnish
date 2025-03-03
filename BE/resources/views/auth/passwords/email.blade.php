@extends('layouts.auth')

@section('title') Quên mật khẩu @endsection

@section('css')
<!-- Sweet Alert css-->
<link href="{{ asset('assets/admins/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />
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
                                                <img src="{{ asset('assets/admins/images/logo-light.png') }}" alt="" height="18">
                                            </a>
                                        </div>
                                        <div class="mt-auto">
                                            <div class="mb-3">
                                                <i class="ri-double-quotes-l display-4 text-success"></i>
                                            </div>

                                            <div id="qoutescarouselIndicator" class="carousel slide" data-bs-ride="carousel">
                                                <div class="carousel-indicators">
                                                    <button type="button" data-bs-target="#qoutescarouselIndicator" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
                                                    <button type="button" data-bs-target="#qoutescarouselIndicator" data-bs-slide-to="1" aria-label="Slide 2"></button>
                                                    <button type="button" data-bs-target="#qoutescarouselIndicator" data-bs-slide-to="2" aria-label="Slide 3"></button>
                                                </div>
                                                <div class="carousel-inner text-center text-white pb-5">
                                                    <div class="carousel-item active">
                                                        <p class="fs-15 fst-italic">"Great! Clean code, clean design, easy for customization. Thanks very much!"</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="p-lg-5 p-4">
                                    <div class="text-center mt-2">
                                        <h5 class="text-primary">Quên mật khẩu?</h5>
                                        <p class="text-muted">Đặt lại mật khẩu với Velzon</p>

                                        <div class="mb-4 text-center">
                                            <lord-icon
                                                src="https://cdn.lordicon.com/rhvddzym.json"
                                                trigger="loop"
                                                colors="primary:#0ab39c"
                                                class="avatar-xl">
                                            </lord-icon>
                                        </div>
                                    </div>

                                    <div class="alert alert-borderless alert-warning text-center mb-2 mx-2" role="alert">
                                        Nhập email của bạn và hướng dẫn sẽ được gửi đến bạn!
                                    </div>

                                    <div class="p-2">
                                        @if (session('status'))
                                            <div class="alert alert-success" role="alert">
                                                {{ session('status') }}
                                            </div>
                                        @endif

                                        <form method="POST" action="{{ route('password.email') }}" class="needs-validation" novalidate>
                                            @csrf
                                            <div class="mb-4">
                                                <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                                <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" placeholder="Nhập email" value="{{ old('email') }}" required>
                                                @error('email')
                                                    <div class="invalid-feedback">
                                                        {{ $message }}
                                                    </div>
                                                @enderror
                                            </div>

                                            <div class="text-center mt-4">
                                                <button class="btn btn-success w-100" type="submit">Gửi liên kết đặt lại</button>
                                            </div>
                                        </form>
                                    </div>

                                    <div class="mt-4 text-center">
                                        <p class="mb-0">Đợi, tôi nhớ mật khẩu rồi... <a href="{{ route('login') }}" class="fw-semibold text-primary text-decoration-underline">Đăng nhập</a></p>
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
<script src="{{ asset('assets/admins/libs/lordicon/lordicon-2.1.0.js') }}"></script>
<script src="{{ asset('assets/admins/libs/sweetalert2/sweetalert2.min.js') }}"></script>

<!-- validation init -->
<script src="{{ asset('assets/admins/js/pages/form-validation.init.js') }}"></script>

<!-- Sweet alert init js-->
<script src="{{ asset('assets/admins/js/pages/sweetalerts.init.js') }}"></script>

@if(session('status'))
<script>
    document.addEventListener("DOMContentLoaded", function() {
        Swal.fire({
            html: '<div class="mt-3">' +
                '<lord-icon src="https://cdn.lordicon.com/lupuorrc.json" trigger="loop" colors="primary:#0ab39c,secondary:#405189" style="width:120px;height:120px"></lord-icon>' +
                '<div class="mt-4 pt-2 fs-15">' +
                '<h4>Thành công !</h4>' +
                '<p class="text-muted mx-4 mb-0">{{ session('status') }}</p>' +
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

@if($errors->any())
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
