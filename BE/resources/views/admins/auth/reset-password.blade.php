@extends('layouts.auth')

@section('title')
    Đặt lại mật khẩu
@endsection

@push('styles')
<style>
    .auth-one-bg {
        background-image: url("{{ asset('assets/admins/images/login/loginImage.jpg') }}");
        background-position: center;
        background-size: cover;
    }
    
    .auth-one-bg .bg-overlay {
        background: linear-gradient(to right, rgba(0, 158, 139, 0.6), rgba(0, 80, 140, 0.6));
        opacity: 0.7;
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

    .password-addon {
        cursor: pointer;
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const passwordToggles = document.querySelectorAll('.password-addon');
        
        passwordToggles.forEach(toggle => {
            toggle.addEventListener('click', function () {
                const inputGroup = this.closest('.auth-pass-inputgroup');
                const passwordInput = inputGroup.querySelector('input');
                const icon = this.querySelector('i');
                
                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    icon.classList.remove('ri-eye-fill');
                    icon.classList.add('ri-eye-off-fill');
                } else {
                    passwordInput.type = 'password';
                    icon.classList.remove('ri-eye-off-fill');
                    icon.classList.add('ri-eye-fill');
                }
            });
        });
    });
</script>
@endpush

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6 col-xl-5">
                <div class="card mt-4 card-bg-fill">
                    <div class="card-body p-4">
                        <div class="text-center mt-2">
                            <h5 class="text-primary">Đặt lại mật khẩu</h5>
                            <p class="text-muted">Tạo mật khẩu mới cho tài khoản của bạn</p>
                        </div>

                        @if ($errors->any())
                            <div class="alert alert-danger">
                                @foreach ($errors->all() as $error)
                                    <div>{{ $error }}</div>
                                @endforeach
                            </div>
                        @endif

                        <div class="p-2">
                            <form action="{{ route('admin.password.update') }}" method="POST">
                                @csrf
                                <input type="hidden" name="token" value="{{ $token }}">
                                <input type="hidden" name="email" value="{{ $email }}">

                                <div class="mb-3">
                                    <label class="form-label">Mật khẩu mới</label>
                                    <div class="position-relative auth-pass-inputgroup">
                                        <input type="password" class="form-control pe-5" name="password" placeholder="Nhập mật khẩu mới" required>
                                        <button class="btn btn-link position-absolute end-0 top-0 text-decoration-none text-muted password-addon" type="button">
                                            <i class="ri-eye-fill align-middle"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Xác nhận mật khẩu</label>
                                    <div class="position-relative auth-pass-inputgroup">
                                        <input type="password" class="form-control pe-5" name="password_confirmation" placeholder="Xác nhận mật khẩu mới" required>
                                        <button class="btn btn-link position-absolute end-0 top-0 text-decoration-none text-muted password-addon" type="button">
                                            <i class="ri-eye-fill align-middle"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="mt-4">
                                    <button class="btn btn-success w-100" type="submit">Đặt lại mật khẩu</button>
                                </div>
                            </form>
                            
                            <div class="mt-4 text-center">
                                <p class="mb-0">Đã nhớ mật khẩu? <a href="{{ route('admin.login') }}"
                                        class="fw-semibold text-primary text-decoration-underline"> Đăng nhập </a> </p>
                            </div>
                        </div>
                    </div>
                    
                </div>

            </div>
        </div>
    </div>
@endsection