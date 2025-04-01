@extends('layouts.admin')

@section('title', 'Chi tiết đánh giá')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Chi tiết đánh giá #{{ $review->id }}</h3>
                        <div class="card-tools">
                            <a href="{{ route('reviews.product', $review->product_id) }}" class="btn btn-sm btn-primary">
                                <i class="fas fa-arrow-left"></i> Quay lại
                            </a>
                        </div>
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body">
                        <div class="row">
                            <!-- Cột 1: Thông tin đánh giá -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>ID:</label>
                                    <p>{{ $review->id }}</p>
                                </div>
                                <div class="form-group">
                                    <label>Người dùng:</label>
                                    <a href="{{ route('reviews.user-info', $review->user_id) }}" class="user-info-link"
                                        data-toggle="tooltip" title="Xem thông tin người dùng">
                                        <p>{{ $review->user->name }}</p>
                                    </a>
                                </div>
                                <div class="form-group">
                                    <label>Email:</label>
                                    <p>{{ $review->user->email }}</p>
                                </div>
                                <div class="form-group">
                                    <label>Sản phẩm:</label>
                                    <p>{{ $review->product->name }}</p>
                                </div>
                                <div class="form-group">
                                    <label>Số sao:</label>
                                    <p>
                                        <span class="badge bg-warning">
                                            {{ $review->rating }} ★
                                        </span>
                                    </p>
                                </div>
                            </div>

                            <!-- Cột 2: Thông tin trạng thái -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Trạng thái:</label>
                                    <p>
                                        <span class="badge {{ $review->is_hidden ? 'bg-danger' : 'bg-success' }}">
                                            {{ $review->is_hidden ? 'Ẩn' : 'Hiển thị' }}
                                        </span>
                                    </p>
                                </div>
                                <div class="form-group">
                                    <label>Ngày tạo:</label>
                                    <p>{{ $review->created_at->format('d/m/Y H:i:s') }}</p>
                                </div>
                                <div class="form-group">
                                    <label>Ngày cập nhật:</label>
                                    <p>{{ $review->updated_at->format('d/m/Y H:i:s') }}</p>
                                </div>
                                <div class="form-group">
                                    <label>Mã đơn hàng:</label>
                                    <p>
                                        @if ($review->order)
                                            <a href="{{ route('orders.show', $review->order->id) }}" class="text-primary">
                                                {{ $review->order->order_code }}
                                            </a>
                                        @else
                                            <span class="text-muted">Không có thông tin đơn hàng</span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Hình ảnh đánh giá -->
                        @if ($review->images && count($review->images) > 0)
                            <div class="row">
                                <div class="col-12">
                                    <label>Hình ảnh đính kèm:</label>
                                    <div class="row g-1"> {{-- Thêm g-1 để giảm khoảng cách --}}
                                        @foreach ($review->images as $image)
                                            <div class="col-auto"> {{-- Sử dụng col-auto để ảnh không bị dàn trải quá rộng --}}
                                                <img src="{{ Storage::url($image) }}" alt="Ảnh đánh giá"
                                                    class="img-fluid rounded shadow-sm" style="max-width: 100px;">
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Nội dung đánh giá -->
                        <div class="row mt-3">
                            <div class="col-12">
                                <div class="form-group">
                                    <label>Nội dung đánh giá:</label>
                                    <div class="p-3 bg-light rounded">
                                        {{ $review->review_text }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- /.card-body -->
                    <div class="card-footer">
                        <div class="btn-group">
                            <form action="{{ route('reviews.toggle', $review->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit"
                                    class="btn {{ $review->is_hidden ? 'btn-success' : 'btn-warning' }}">
                                    <i class="fas {{ $review->is_hidden ? 'fa-eye' : 'fa-eye-slash' }}"></i>
                                    {{ $review->is_hidden ? 'Hiển thị đánh giá' : 'Ẩn đánh giá' }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                <!-- /.card -->
            </div>
        </div>
    </div>
@endsection
