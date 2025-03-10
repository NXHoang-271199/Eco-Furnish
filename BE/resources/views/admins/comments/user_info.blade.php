@extends('layouts.admin')

@section('title', 'Thông tin người dùng: ' . $user->name)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Thông tin người dùng</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-sm btn-default" onclick="window.history.back();">
                            <i class="fas fa-arrow-left"></i> Quay lại
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 text-center">
                            @if($user->avatar)
                                <img src="{{ asset('storage/' . $user->avatar) }}" alt="{{ $user->name }}" class="img-circle img-fluid" style="max-width: 150px;">
                            @else
                                <div class="text-center p-4">
                                    <i class="fas fa-user-circle fa-5x text-muted"></i>
                                </div>
                            @endif
                        </div>
                        <div class="col-md-9">
                            <h4>{{ $user->name }}</h4>
                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <p><strong><i class="fas fa-id-card mr-2"></i> ID:</strong> {{ $user->id }}</p>
                                    <p><strong><i class="fas fa-envelope mr-2"></i> Email:</strong> {{ $user->email }}</p>
                                    <p><strong><i class="fas fa-phone mr-2"></i> Số điện thoại:</strong> {{ $user->phone ?? 'Chưa cập nhật' }}</p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong><i class="fas fa-comments mr-2"></i> Tổng số bình luận:</strong> <span class="badge bg-primary">{{ $commentCount }}</span></p>
                                    <p><strong><i class="fas fa-calendar-alt mr-2"></i> Ngày đăng ký:</strong> {{ $user->created_at->format('d/m/Y H:i') }}</p>
                                    <p>
                                        <strong><i class="fas fa-user-shield mr-2"></i> Trạng thái:</strong>
                                        <span class="badge {{ $user->status ? 'bg-success' : 'bg-danger' }}">
                                            {{ $user->status ? 'Hoạt động' : 'Bị khóa' }}
                                        </span>
                                    </p>
                                </div>
                            </div>
                            <div class="mt-3">
                                <a href="{{ route('users.show', $user->id) }}" class="btn btn-info">
                                    <i class="fas fa-user"></i> Xem chi tiết người dùng
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 