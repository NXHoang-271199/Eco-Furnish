@extends('layouts.admin')

@section('title', 'Đánh giá sản phẩm: ' . $product->name)

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Thông tin sản phẩm</h3>
                    <div class="card-tools">
                        <a href="{{ route('reviews.index') }}" class="btn btn-sm btn-primary">
                            <i class="fas fa-arrow-left"></i> Quay lại danh sách
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-2">
                            @if($product->thumbnail)
                                <img src="{{ asset('storage/' . $product->thumbnail) }}" alt="{{ $product->name }}" class="img-fluid">
                            @else
                                <div class="text-center p-4 bg-light">
                                    <i class="fas fa-image fa-3x text-muted"></i>
                                    <p class="mt-2">Không có ảnh</p>
                                </div>
                            @endif
                        </div>
                        <div class="col-md-10">
                            <h4>{{ $product->name }}</h4>
                            <p><strong>ID:</strong> {{ $product->id }}</p>
                            <p><strong>Giá:</strong> {{ number_format($product->price) }} VNĐ</p>
                            <p><strong>Danh mục:</strong> {{ $product->category->name ?? 'Không có' }}</p>
                            <p><strong>Tổng số đánh giá:</strong> <span class="badge bg-primary">{{ $reviews->total() }}</span></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Danh sách đánh giá</h3>
                    <div class="card-tools">
                        <form action="{{ route('reviews.product', $product->id) }}" method="GET" class="input-group input-group-sm" style="width: 250px;">
                            <input type="text" name="search" class="form-control float-right" placeholder="Tìm kiếm đánh giá" value="{{ request('search') }}">
                            <div class="input-group-append">
                                <button type="submit" class="btn btn-default">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th style="width: 50px">STT</th>
                                <th style="width: 150px">Người dùng</th>
                                <th style="width: 100px">Số sao</th>
                                <th>Nội dung</th>
                                <th style="width: 100px">Trạng thái</th>
                                <th style="width: 150px">Ngày tạo</th>
                                <th style="width: 200px">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($reviews as $key => $review)
                            <tr>
                                <td>{{ $reviews->firstItem() + $key }}</td>
                                <td>
                                    <a href="{{ route('reviews.user-info', $review->user_id) }}" class="user-info-link" data-toggle="tooltip" title="Xem thông tin người dùng">
                                        {{ $review->user->name }}
                                    </a>
                                </td>
                                <td>
                                    <span class="badge bg-warning">
                                        {{ $review->rating }} ★
                                    </span>
                                </td>
                                <td>{{ $review->review_text }}</td>
                                <td>
                                    <span class="badge {{ $review->is_hidden ? 'bg-danger' : 'bg-success' }}">
                                        {{ $review->is_hidden ? 'Ẩn' : 'Hiển thị' }}
                                    </span>
                                </td> <!-- Hiển thị trạng thái ẩn/hiển thị -->
                                <td>{{ $review->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    <a href="{{ route('reviews.show', $review->id) }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i> Xem chi tiết
                                    </a>
                                    <form action="{{ route('reviews.toggle', $review->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm {{ $review->is_hidden ? 'btn-success' : 'btn-warning' }}">
                                            <i class="fas {{ $review->is_hidden ? 'fa-eye' : 'fa-eye-slash' }}"></i>
                                            {{ $review->is_hidden ? 'Hiển thị' : 'Ẩn' }}
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center">Không có đánh giá nào</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer clearfix">
                    <div class="float-right">
                        {{ $reviews->appends(request()->query())->links('pagination::bootstrap-4') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@section('JS')
<script>
    $(function() {
        $('[data-toggle="tooltip"]').tooltip();
    });
</script>
@endsection
@endsection
