@extends('layouts.auth')

@section('title')
    Quên mật khẩu
@endsection

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6 col-xl-5">
                <div class="card mt-4 card-bg-fill">
                    <div class="card-body p-4">
                        <div class="text-center mt-2">
                            <h5 class="text-primary">Quên mật khẩu?</h5>
                            <p class="text-muted">Nhập email để lấy lại mật khẩu</p>
                        </div>

                        @if (session('status'))
                            <div class="alert alert-success">
                                {{ session('status') }}
                            </div>
                        @endif

                        <div class="p-2">
                            <form action="{{ route('admin.password.email') }}" method="POST">
                                @csrf
                                <div class="mb-4">
                                    <label class="form-label">Email</label>
                                    <input type="email" class="form-control" name="email" value="{{ old('email') }}"
                                        placeholder="Nhập email" required>
                                    @error('email')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="text-center mt-4">
                                    <button class="btn btn-success w-100" type="submit">Gửi link đặt lại mật khẩu</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="mt-4 text-center">
                    <p class="mb-0">Đã nhớ mật khẩu? <a href="{{ route('admin.login') }}"
                            class="fw-semibold text-primary text-decoration-underline"> Đăng nhập </a> </p>
                </div>
            </div>
        </div>
    </div>
@endsection