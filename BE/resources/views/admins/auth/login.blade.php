@extends('layouts.auth')

@section('title')
    Đăng nhập
@endsection

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
                                    <div class="mb-4">
                                        <a href="index.html" class="d-block">
                                            <img src="assets/images/logo-light.png" alt="" height="18">
                                        </a>
                                    </div>
                                    <div class="mt-auto">
                                        <div class="mb-3">
                                            <i class="ri-double-quotes-l display-4 text-success"></i>
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
                                            <div class="carousel-inner text-center text-white-50 pb-5">
                                                <div class="carousel-item active">
                                                    <p class="fs-15 fst-italic">" Great! Clean code, clean design, easy for
                                                        customization. Thanks very much! "</p>
                                                </div>
                                                <div class="carousel-item">
                                                    <p class="fs-15 fst-italic">" The theme is really great with an amazing
                                                        customer support."</p>
                                                </div>
                                                <div class="carousel-item">
                                                    <p class="fs-15 fst-italic">" Great! Clean code, clean design, easy for
                                                        customization. Thanks very much! "</p>
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
                                <div>
                                    <h5 class="text-primary">Xin chào !</h5>
                                    <p class="text-muted">Đăng nhập với Eco Furnish</p>
                                </div>

                                <div class="mt-4">
                                    <form action="index.html">

                                        <div class="mb-3">
                                            <label for="email" class="form-label">Email</label>
                                            <input type="email" class="form-control" id="email"
                                                placeholder="Nhập email..." name="email">
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label" for="password-input">Mật khẩu</label>
                                            <div class="position-relative auth-pass-inputgroup mb-3">
                                                <input type="password" class="form-control pe-5 password-input"
                                                    placeholder="Nhập mật khẩu..." id="password-input">
                                                <button
                                                    class="btn btn-link position-absolute end-0 top-0 text-decoration-none text-muted password-addon material-shadow-none"
                                                    type="button" id="password-addon"><i
                                                        class="ri-eye-fill align-middle"></i></button>
                                            </div>
                                        </div>
                                        <div class="float-end">
                                            <a href="auth-pass-reset-cover.html" class="text-muted">Quên mật khẩu?</a>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" value=""
                                                id="auth-remember-check">
                                            <label class="form-check-label" for="auth-remember-check">Nhớ tài khoản</label>
                                        </div>

                                        <div class="mt-4">
                                            <button class="btn btn-success w-100" type="submit">Đăng nhập</button>
                                        </div>

                                        <div class="mt-4 text-center">
                                            <div class="signin-other-title">
                                                <h5 class="fs-13 mb-4 title">Đăng nhập với</h5>
                                            </div>

                                            <div>
                                                <button type="button"
                                                    class="btn btn-primary btn-icon waves-effect waves-light"><i
                                                        class="ri-facebook-fill fs-16"></i></button>
                                                <button type="button"
                                                    class="btn btn-danger btn-icon waves-effect waves-light"><i
                                                        class="ri-google-fill fs-16"></i></button>
                                                <button type="button"
                                                    class="btn btn-info btn-icon waves-effect waves-light"><i
                                                        class="ri-twitter-fill fs-16"></i></button>
                                            </div>
                                        </div>

                                    </form>
                                </div>

                                <div class="mt-5 text-center">
                                    <p class="mb-0">Bạn không có tài khoản ? <a href="{{ route('register') }}"
                                            class="fw-semibold text-primary text-decoration-underline"> Đăng kí</a> </p>
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
