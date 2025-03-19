@extends('layouts.auth')

@section('title')
    Đăng nhập Admin
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
                                        <a href="/" class="d-block">
                                            <img src="{{ asset('assets/images/logo-light.png') }}" alt="" height="18">
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
                                    <p class="text-muted">Đăng nhập vào trang quản trị Eco Furnish</p>
                                </div>

                                @if ($errors->any())
                                    <div class="alert alert-danger">
                                        @foreach ($errors->all() as $error)
                                            <div>{{ $error }}</div>
                                        @endforeach
                                    </div>
                                @endif

                                <div class="mt-4">
                                    <form action="{{ route('admin.login.post') }}" method="POST">
                                        @csrf
                                        <div class="mb-3">
                                            <label for="email" class="form-label">Email</label>
                                            <input type="email" class="form-control" id="email" name="email"
                                                placeholder="Nhập email..." value="{{ old('email') }}" required>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label" for="password-input">Mật khẩu</label>
                                            <div class="position-relative auth-pass-inputgroup mb-3">
                                                <input type="password" class="form-control pe-5 password-input" name="password"
                                                    placeholder="Nhập mật khẩu..." id="password-input" required>
                                                <button class="btn btn-link position-absolute end-0 top-0 text-decoration-none text-muted password-addon"
                                                    type="button" id="password-addon"><i class="ri-eye-fill align-middle"></i></button>
                                            </div>
                                        </div>

                                        <div class="float-end">
                                            <a href="{{ route('admin.password.request') }}" class="text-muted">Quên mật khẩu?</a>
                                        </div>

                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="remember" value="1"
                                                id="auth-remember-check">
                                            <label class="form-check-label" for="auth-remember-check">Nhớ đăng nhập</label>
                                        </div>

                                        <div class="mt-4">
                                            <button class="btn btn-success w-100" type="submit">Đăng nhập</button>
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
<script>
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
</script>
@endpush
