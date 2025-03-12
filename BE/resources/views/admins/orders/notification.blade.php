@extends('layouts.admin')

@section('title')
    Quản lý đơn hàng
@endsection
@section('content')
    <div class="container-fluid">
        <div class="card">
            <div class="d-flex justify-content-between m-3" style="margin-left: 20px">
                <div>
                    <a href="{{ route('order.notifications') }}"><b class="fs-4 fw-bold">Danh Sách Thông Báo Đơn Hàng</b></a>
                </div>
                <div class="d-flex align-items-center mt-1">
                    <p class="badge bg-primary-subtle text-danger fs-13">{{ $notificationsCount }}</p>
                </div>
            </div>
        </div>
        <h2></h2>
        <ul class="list-group">
            @forelse ($notifications as $notification)
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <strong>Đơn hàng: {{ $notification->order->order_code }}</strong><br>
                        <span>{{ $notification->order->user_name }} đã đặt hàng.</span>
                        <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                    </div>
                    @if (!$notification->is_read)
                        <form method="POST" action="{{ route('order.notification.read', $notification->id) }}">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-primary">Đánh dấu đã đọc</button>
                        </form>
                    @endif
                </li>
            @empty
                <li class="list-group-item">Không có thông báo nào.</li>
            @endforelse
        </ul>
    </div>
@endsection
