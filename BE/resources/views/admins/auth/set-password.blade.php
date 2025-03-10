@extends('layouts.auth')

@section('title')
    Đặt lại mật khẩu
@endsection

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6 col-xl-5">
                <div class="card mt-4 card-bg-fill">
                    <div class="card-body p-4">
                        <div class="text-center mt-2">
                            <h5 class="text-primary">Đặt lại mật khẩu</h5>
                            <p class="text-muted">Nhập mật khẩu mới của bạn</p>
                        </div>

                        <div class="p-2">
                            <form action="{{ route('admin.password.update') }}" method="POST">
                                @csrf
                                <input type="hidden" name="token" value="{{ $token }}">
                                <input type="hidden" name="email" value="{{ $email }}">

                                <div class="mb-3">
                                    <label class="form-label">Mật khẩu mới</label>
                                    <div class="position-relative auth-pass-inputgroup">
                                        <input type="password" class="form-control pe-5 password-input" name="password"
                                            placeholder="Nhập mật khẩu mới" required>
                                        @error('password')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Xác nhận mật khẩu</label>
                                    <div class="position-relative auth-pass-inputgroup">
                                        <input type="password" class="form-control pe-5 password-input"
                                            name="password_confirmation" placeholder="Xác nhận mật khẩu mới" required>
                                    </div>
                                </div>

                                <div class="text-center mt-4">
                                    <button class="btn btn-success w-100" type="submit">Đặt lại mật khẩu</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection