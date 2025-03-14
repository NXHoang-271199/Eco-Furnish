@extends('layouts.admin')

@section('title', 'Bình luận sản phẩm: ' . $product->name)

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Thông tin sản phẩm</h3>
                    <div class="card-tools">
                        <a href="{{ route('comments.index') }}" class="btn btn-sm btn-primary">
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
                            <p><strong>Tổng số bình luận:</strong> <span class="badge bg-primary">{{ $comments->total() }}</span></p>
                            <a href="{{ route('products.show', $product->id) }}" class="btn btn-sm btn-info" target="_blank">
                                <i class="fas fa-external-link-alt"></i> Xem chi tiết sản phẩm
                            </a>
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
                    <h3 class="card-title">Danh sách bình luận</h3>
                    <div class="card-tools">
                        <form action="{{ route('comments.product', $product->id) }}" method="GET" class="input-group input-group-sm" style="width: 250px;">
                            <input type="text" name="search" class="form-control float-right" placeholder="Tìm kiếm bình luận" value="{{ request('search') }}">
                            <div class="input-group-append">
                                <button type="submit" class="btn btn-default">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                <!-- /.card-header -->
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th style="width: 50px">STT</th>
                                <th style="width: 150px">Người dùng</th>
                                <th>Nội dung</th>
                                <th style="width: 100px">Trạng thái</th>
                                <th style="width: 150px">Ngày tạo</th>
                                <th style="width: 200px">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($comments as $key => $comment)
                            <tr>
                                <td>{{ $comments->firstItem() + $key }}</td>
                                <td>
                                    <a href="{{ route('comments.user-info', $comment->user_id) }}" class="user-info-link" data-toggle="tooltip" title="Xem thông tin người dùng">
                                        {{ $comment->user->name }}
                                    </a>
                                </td>
                                <td>{{ $comment->content }}</td>
                                <td>
                                    <span class="badge {{ $comment->status === 'Hiển thị' ? 'bg-success' : 'bg-danger' }}">
                                        {{ $comment->status }}
                                    </span>
                                </td>
                                <td>{{ $comment->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    <div class="btn-group">
                                        <a href="{{ route('comments.show', $comment->id) }}" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i> Chi tiết
                                        </a>
                                        <form action="{{ route('comments.toggle-status', $comment->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm {{ $comment->status === 'Hiển thị' ? 'btn-warning' : 'btn-success' }}">
                                                <i class="fas {{ $comment->status === 'Hiển thị' ? 'fa-eye-slash' : 'fa-eye' }}"></i>
                                                {{ $comment->status === 'Hiển thị' ? 'Ẩn' : 'Hiển thị' }}
                                            </button>
                                        </form>
                                        <!-- <form action="{{ route('comments.destroy', $comment->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa bình luận này?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="fas fa-trash"></i> Xóa
                                            </button>
                                        </form> -->
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center">Không có bình luận nào</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <!-- /.card-body -->
                <div class="card-footer clearfix">
                    <div class="float-right">
                        {{ $comments->appends(request()->query())->links('pagination::bootstrap-4') }}
                    </div>
                </div>
            </div>
            <!-- /.card -->
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