@extends('layouts.auth')

@section('title')
    Quên mật khẩu
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

    .card {
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
    }

    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
    }

    .form-control {
        border-radius: 8px;
        padding: 12px 15px;
        border: 1px solid #e2e8f0;
        transition: all 0.3s ease;
    }

    .form-control:focus {
        border-color: #009e8b;
        box-shadow: 0 0 0 3px rgba(0, 158, 139, 0.1);
    }

    .alert {
        border-radius: 10px;
        border: none;
    }

    .display-5 i {
        animation: bounce 2s infinite;
    }

    @keyframes bounce {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-10px); }
    }
</style>
@endpush

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6 col-xl-5">
            <div class="card mt-4">
                <div class="card-body p-4">
                    <div class="text-center mt-2">
                        <h5 class="text-primary">Quên mật khẩu?</h5>
                        <p class="text-muted">Đặt lại mật khẩu của bạn</p>

                        <div class="display-5 mb-4 text-danger">
                            <i class="ri-mail-send-line"></i>
                        </div>
                    </div>

                    <div class="alert alert-borderless alert-warning text-center mb-2 mx-2" role="alert">
                        Nhập email của bạn và hướng dẫn sẽ được gửi đến bạn!
                    </div>

                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            @foreach ($errors->all() as $error)
                                <div>{{ $error }}</div>
                            @endforeach
                        </div>
                    @endif

                    <div class="p-2">
                        <form action="{{ route('admin.password.email') }}" method="POST">
                            @csrf
                            <div class="mb-4">
                                <label class="form-label">Email</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="ri-mail-line"></i></span>
                                    <input type="email" class="form-control" name="email" value="{{ old('email') }}" 
                                           placeholder="Nhập email của bạn" required>
                                </div>
                            </div>

                            <div class="text-center mt-4">
                                <button class="btn btn-success w-100" type="submit">
                                    <i class="ri-send-plane-line me-1 align-middle"></i> Gửi link đặt lại mật khẩu
                                </button>
                            </div>
                        </form>
                    </div>

                    <div class="mt-4 text-center">
                        <p class="mb-0">Đã nhớ mật khẩu? 
                            <a href="{{ route('admin.login') }}" class="fw-semibold text-primary text-decoration-underline">
                                Đăng nhập
                            </a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Hiệu ứng khi submit form
        $("form").on('submit', function() {
            $(this).find('button[type="submit"]')
                .prop('disabled', true)
                .html('<span class="spinner-border spinner-border-sm me-2"></span>Đang gửi...');
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
    });
</script>
@endpush 