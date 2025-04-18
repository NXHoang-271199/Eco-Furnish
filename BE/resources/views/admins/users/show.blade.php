@extends('layouts.admin')

@section('title')
    {{ $singerUser->role->name }}: {{ $singerUser->name }}
@endsection
@section('CSS')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/pnotify/5.2.0/PNotifyBrightTheme.min.css" rel="stylesheet">
    <style>
        .profile-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.05);
        }

        .profile-header {
            background: linear-gradient(135deg, #4b38b3 0%, #2c2484 100%);
            padding: 20px;
            border-radius: 15px 15px 0 0;
            margin-bottom: 20px;
        }

        .avatar-wrapper {
            position: relative;
            width: 150px;
            height: 150px;
            margin: 0 auto;
        }

        .avatar-xl {
            width: 150px;
            height: 150px;
            border: 5px solid #fff;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .user-info {
            background-color: #fff;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .info-label {
            color: #495057;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .info-label i {
            color: #4b38b3;
            font-size: 1.1em;
        }

        .edit-btn {
            transition: all 0.3s;
        }

        .edit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .stats-box {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 15px;
            margin-top: 20px;
            transition: all 0.3s ease;
        }

        .stats-box:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .stats-icon {
            width: 45px;
            height: 45px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            font-size: 24px;
            margin-bottom: 10px;
        }
    </style>
@endsection
@section('JS')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pnotify/5.2.0/PNotify.min.js"></script>
@endsection
@section('content')
    @if (Auth::user()->hasPermission('view-users'))
        <div class="container-fluid">
            <div class="row">
                <div class="col-xl-12">
                    <div class="card profile-card">
                        <div class="profile-header text-center text-white">
                            <h3 class="mb-0 text-white">Thông tin tài khoản</h3>
                            <p class="text-white-50">{{ $singerUser->role->name }}</p>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="text-center">
                                        <div class="avatar-wrapper mb-3">
                                            @if ($singerUser->avatar)
                                                <img src="{{ Storage::url($singerUser->avatar) }}"
                                                    alt="ảnh {{ $singerUser->name }}" class="rounded-circle avatar-xl">
                                            @else
                                                <img src="{{ asset('assets/admins/images/users/avatarUser.png') }}"
                                                    alt="ảnh mặc định" class="rounded-circle avatar-xl">
                                            @endif
                                        </div>
                                        <div class="user-info">
                                            <h5 class="mb-1">{{ $singerUser->name }}</h5>
                                            <span class="badge bg-primary rounded-pill px-3 py-2">
                                                <i class="ri-shield-star-line me-1"></i>
                                                {{ $singerUser->role->name }}
                                            </span>
                                            @if (auth()->id() === $singerUser->id)
                                                <div class="mt-4">
                                                    <a href="{{ route('users.edit', $singerUser->id) }}"
                                                        class="btn btn-primary w-100 edit-btn">
                                                        <i class="ri-edit-box-line align-bottom"></i> Chỉnh sửa thông tin
                                                    </a>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-9">
                                    <div class="user-info p-4">
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="info-label">
                                                        <i class="ri-user-line"></i>
                                                        Tên đầy đủ
                                                    </label>
                                                    <p class="mb-0">{{ $singerUser->name }}</p>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="info-label">
                                                        <i class="ri-mail-line"></i>
                                                        Email
                                                    </label>
                                                    <p class="mb-0">{{ $singerUser->email }}</p>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="info-label">
                                                        <i class="ri-map-pin-line"></i>
                                                        Địa chỉ
                                                    </label>
                                                    <p class="mb-0">{{ $singerUser->address ?: 'Chưa cập nhật' }}</p>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="info-label">
                                                        <i class="ri-shield-star-line"></i>
                                                        Vai trò
                                                    </label>
                                                    <p class="mb-0">
                                                        <span class="badge bg-primary">{{ $singerUser->role->name }}</span>
                                                    </p>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="info-label">
                                                        <i class="ri-calendar-check-line"></i>
                                                        Ngày tham gia
                                                    </label>
                                                    <p class="mb-0">{{ $singerUser->created_at->format('d/m/Y H:i') }}
                                                    </p>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="info-label">
                                                        <i class="ri-history-line"></i>
                                                        Số điện thoại
                                                    </label>
                                                    <p class="mb-0">{{ $singerUser->phone ?: 'Chưa cập nhật' }}</p>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Thống kê hoạt động -->
                                        <div class="row mt-4">
                                            <div class="col-sm-6">
                                                <div class="stats-box text-center">
                                                    <div class="stats-icon bg-primary-subtle text-primary mx-auto">
                                                        <i class="ri-time-line"></i>
                                                    </div>
                                                    <div class="stats-info">
                                                        <h5>{{ $singerUser->created_at->diffForHumans() }}</h5>
                                                        <p>{{ $singerUser->role->slug === 'client' ? 'Thời gian tham gia' : 'Thời gian hoạt động' }}
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-sm-6">
                                                <div class="stats-box text-center">
                                                    <div class="stats-icon bg-warning-subtle text-warning mx-auto">
                                                        @if ($singerUser->role->slug === 'client')
                                                            <i class="ri-shopping-cart-line"></i>
                                                        @else
                                                            <i class="ri-calendar-line"></i>
                                                        @endif
                                                    </div>
                                                    <div class="stats-info">
                                                        @if ($singerUser->role->slug === 'client')
                                                            <h5>{{ $singerUser->orders->count() }}</h5>
                                                            <p>Đơn hàng đã đặt</p>
                                                        @else
                                                            <h5>{{ round((time() - strtotime($singerUser->created_at)) / 86400) }}
                                                            </h5>
                                                            <p>Ngày làm việc</p>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Submit Button -->
                    <div class="col-lg-12 text-end">
                        @if($singerUser->role->slug === 'admin' || $singerUser->role->slug === 'staff')
                            <a href="{{ route('users.admins') }}" class="btn btn-soft-secondary">
                                <i class="ri-arrow-left-line align-bottom"></i> Quay lại
                            </a>
                        @else
                            <a href="{{ route('users.index') }}" class="btn btn-soft-secondary">
                                <i class="ri-arrow-left-line align-bottom"></i> Quay lại
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="container-fluid">
            <div class="row">
                <div class="col-12 text-center">
                    <div class="alert alert-danger">
                        <h4 class="alert-heading">Không có quyền truy cập!</h4>
                        <p>Bạn không có quyền xem thông tin này.</p>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection
